<?php

namespace App\Http\Controllers;

use App\Models\NotaJuridica;
use App\Models\Personal;
use App\Models\Usuario;
use App\Models\PlantillaDocumento;
use App\Helpers\ConfiguracionNotaHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PDF;

class NotaJuridicaController extends Controller
{
    /**
     * Mostrar la vista principal del módulo
     */
    public function index()
    {
        $personal = Personal::orderBy('Apellido')->orderBy('Nombre')->get();
        $anios = NotaJuridica::select('anio')
            ->distinct()
            ->orderBy('anio', 'desc')
            ->pluck('anio');

        // Obtener plantillas disponibles
        $plantillas = NotaJuridica::plantillas()
            ->orderBy('nombre_plantilla')
            ->get();

        return view('notas-juridicas', compact('personal', 'anios', 'plantillas'));
    }

    /**
     * Obtener notas filtradas y paginadas
     */
    public function filtrar(Request $request)
    {
        try {
            $query = NotaJuridica::with(['personal', 'creador', 'notaReferencia'])
                ->notas(); // Solo notas, no plantillas

            // Filtro por año
            if ($request->filled('anio')) {
                $query->where('anio', $request->anio);
            }

            // Filtro por fecha desde
            if ($request->filled('fecha_desde')) {
                $query->whereDate('fecha_creacion', '>=', $request->fecha_desde);
            }

            // Filtro por fecha hasta
            if ($request->filled('fecha_hasta')) {
                $query->whereDate('fecha_creacion', '<=', $request->fecha_hasta);
            }

            // Filtro por personal (búsqueda por nombre/DNI/legajo)
            if ($request->filled('personal')) {
                $busqueda = $request->personal;
                $query->whereHas('personal', function ($q) use ($busqueda) {
                    $q->where('Nombre', 'LIKE', "%{$busqueda}%")
                        ->orWhere('Apellido', 'LIKE', "%{$busqueda}%")
                        ->orWhere('DNI', 'LIKE', "%{$busqueda}%")
                        ->orWhere('Legajo', 'LIKE', "%{$busqueda}%")
                        ->orWhereRaw("CONCAT(Apellido, ', ', Nombre) LIKE ?", ["%{$busqueda}%"]);
                });
            }

            // Filtro por número de nota
            if ($request->filled('numero')) {
                $query->where('numero', $request->numero);
            }

            // Filtro por estado
            if ($request->filled('estado')) {
                $query->where('estado', $request->estado);
            }

            // Filtro por tipo
            if ($request->filled('tipo')) {
                $query->where('tipo', $request->tipo);
            }

            // Búsqueda general (título, descripción, observación)
            if ($request->filled('busqueda')) {
                $query->buscar($request->busqueda);
            }

            // Ordenamiento
            $query->orderBy('anio', 'desc')->orderBy('numero', 'desc');

            // Paginación
            $perPage = $request->get('per_page', 15);
            $notas = $query->paginate($perPage);

            return response()->json($notas);

        } catch (\Exception $e) {
            Log::error('Error al filtrar notas jurídicas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las notas jurídicas'
            ], 500);
        }
    }

    /**
     * Obtener el próximo número disponible para el año
     */
    public function proximoNumero(Request $request)
    {
        try {
            $anio = $request->get('anio', date('Y'));
            $proximoNumero = NotaJuridica::obtenerProximoNumero((int)$anio);

            return response()->json([
                'success' => true,
                'numero' => $proximoNumero,
                'anio' => $anio
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener próximo número: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el próximo número'
            ], 500);
        }
    }

    /**
     * Buscar notas para Select2 (referencia a nota anterior)
     */
    public function buscarNotas(Request $request)
    {
        try {
            $termino = $request->get('q', '');
            $page = $request->get('page', 1);
            $perPage = 10;

            $query = NotaJuridica::with('personal')
                ->orderBy('anio', 'desc')
                ->orderBy('numero', 'desc');

            if ($termino) {
                $query->where(function ($q) use ($termino) {
                    $q->where('titulo', 'LIKE', "%{$termino}%")
                        ->orWhereRaw("CONCAT(numero, '/', anio) LIKE ?", ["%{$termino}%"]);
                });
            }

            $notas = $query->paginate($perPage, ['*'], 'page', $page);

            $results = $notas->map(function ($nota) {
                return [
                    'id' => $nota->idNotaJuridica,
                    'text' => "Nota {$nota->numero}/{$nota->anio} - {$nota->titulo}",
                    'numero_completo' => $nota->numero_completo,
                    'titulo' => $nota->titulo
                ];
            });

            return response()->json([
                'results' => $results,
                'pagination' => [
                    'more' => $notas->hasMorePages()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al buscar notas: ' . $e->getMessage());
            return response()->json([
                'results' => [],
                'pagination' => ['more' => false]
            ]);
        }
    }

    /**
     * Guardar nueva nota jurídica
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'titulo' => 'required|string|max:255',
                'descripcion' => 'nullable|string',
                'observacion' => 'nullable|string',
                'fecha_creacion' => 'required|date',
                'personal_id' => 'nullable|exists:empleados,idEmpleado',
                'nota_referencia_id' => 'nullable|exists:notas_juridicas,idNotaJuridica',
                'tipo' => 'required|in:creada,adjunta',
                'estado' => 'required|in:borrador,finalizada,enviada',
                'archivo_base64' => 'nullable|string',
                'archivo_nombre' => 'nullable|string',
                'plantilla_id' => 'nullable|exists:plantillas_documentos,idPlantilla',
                'configuracion' => 'nullable|array', // Nueva configuración JSON
            ]);

            DB::beginTransaction();

            $anio = date('Y', strtotime($validated['fecha_creacion']));
            $numero = NotaJuridica::obtenerProximoNumero((int)$anio);

            // Construir configuración si viene en el nuevo formato
            $configuracion = null;
            if ($request->filled('configuracion')) {
                $configuracion = $validated['configuracion'];
            } else {
                // Compatibilidad con formato antiguo
                $configuracion = ConfiguracionNotaHelper::desdeRequest($request->all());
            }

            // Validar configuración
            $errores = ConfiguracionNotaHelper::validar($configuracion);
            if (!empty($errores)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores en configuración',
                    'errors' => $errores
                ], 422);
            }

            $notaData = [
                'numero' => $numero,
                'anio' => $anio,
                'titulo' => $validated['titulo'],
                'descripcion' => $configuracion['contenido'] ?? $validated['descripcion'] ?? null,
                'observacion' => $validated['observacion'] ?? null,
                'fecha_creacion' => $validated['fecha_creacion'],
                'personal_id' => $validated['personal_id'] ?? null,
                'nota_referencia_id' => $validated['nota_referencia_id'] ?? null,
                'tipo' => $validated['tipo'],
                'estado' => $validated['estado'],
                'plantilla_id' => $validated['plantilla_id'] ?? null,
                'configuracion' => $configuracion,
                'creado_por' => session('usuario_id') ?? auth()->id() ?? 1,
            ];

            // Manejar archivo si es tipo adjunta
            if ($validated['tipo'] === 'adjunta' && $request->filled('archivo_base64')) {
                $archivoData = $this->guardarArchivo($request->archivo_base64, $request->archivo_nombre);
                $notaData['archivo_path'] = $archivoData['path'];
                $notaData['google_drive_file_id'] = $archivoData['drive_id'] ?? null;
                $notaData['google_drive_link'] = $archivoData['drive_link'] ?? null;
            }

            // Guardar logo si viene en base64
            if (!empty($configuracion['encabezado']['logo_path']) &&
                str_starts_with($configuracion['encabezado']['logo_path'], 'data:')) {
                $logoData = $this->guardarArchivo($configuracion['encabezado']['logo_path'], 'logo_nota.png');
                $configuracion['encabezado']['logo_path'] = $logoData['path'];
                $notaData['configuracion'] = $configuracion;
            }

            $nota = NotaJuridica::create($notaData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Nota jurídica guardada exitosamente',
                'data' => $nota
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar nota jurídica: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la nota jurídica: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener datos de una nota para edición
     */
    public function show($id)
    {
        try {
            $nota = NotaJuridica::with(['personal', 'notaReferencia', 'creador', 'plantilla'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $nota,
                'configuracion' => $nota->configuracion_completa
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener nota: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Nota no encontrada'
            ], 404);
        }
    }

    /**
     * Actualizar nota jurídica existente
     */
    public function update(Request $request, $id)
    {
        try {
            $nota = NotaJuridica::findOrFail($id);

            $validated = $request->validate([
                'titulo' => 'required|string|max:255',
                'descripcion' => 'nullable|string',
                'observacion' => 'nullable|string',
                'fecha_creacion' => 'required|date',
                'personal_id' => 'nullable|exists:empleados,idEmpleado',
                'nota_referencia_id' => 'nullable|exists:notas_juridicas,idNotaJuridica',
                'tipo' => 'required|in:creada,adjunta',
                'estado' => 'required|in:borrador,finalizada,enviada',
                'archivo_base64' => 'nullable|string',
                'archivo_nombre' => 'nullable|string',
                'eliminar_archivo' => 'nullable|boolean',
                'configuracion' => 'nullable|array',
            ]);

            DB::beginTransaction();

            // Construir configuración si viene en el nuevo formato
            $configuracion = null;
            if ($request->filled('configuracion')) {
                $configuracion = $validated['configuracion'];
            } else {
                // Compatibilidad con formato antiguo
                $configuracion = ConfiguracionNotaHelper::desdeRequest($request->all());
            }

            $notaData = [
                'titulo' => $validated['titulo'],
                'descripcion' => $configuracion['contenido'] ?? $validated['descripcion'] ?? null,
                'observacion' => $validated['observacion'] ?? null,
                'fecha_creacion' => $validated['fecha_creacion'],
                'personal_id' => $validated['personal_id'] ?? null,
                'nota_referencia_id' => $validated['nota_referencia_id'] ?? null,
                'tipo' => $validated['tipo'],
                'estado' => $validated['estado'],
                'configuracion' => $configuracion,
            ];

            // Manejar archivo
            if ($request->filled('eliminar_archivo') && $request->eliminar_archivo) {
                if ($nota->archivo_path && file_exists(public_path($nota->archivo_path))) {
                    unlink(public_path($nota->archivo_path));
                }
                $notaData['archivo_path'] = null;
                $notaData['google_drive_file_id'] = null;
                $notaData['google_drive_link'] = null;
            } elseif ($validated['tipo'] === 'adjunta' && $request->filled('archivo_base64')) {
                if ($nota->archivo_path && file_exists(public_path($nota->archivo_path))) {
                    unlink(public_path($nota->archivo_path));
                }
                $archivoData = $this->guardarArchivo($request->archivo_base64, $request->archivo_nombre);
                $notaData['archivo_path'] = $archivoData['path'];
                $notaData['google_drive_file_id'] = $archivoData['drive_id'] ?? null;
                $notaData['google_drive_link'] = $archivoData['drive_link'] ?? null;
            }

            // Guardar logo si viene en base64
            if (!empty($configuracion['encabezado']['logo_path']) &&
                str_starts_with($configuracion['encabezado']['logo_path'], 'data:')) {
                $logoData = $this->guardarArchivo($configuracion['encabezado']['logo_path'], 'logo_nota.png');
                $configuracion['encabezado']['logo_path'] = $logoData['path'];
                $notaData['configuracion'] = $configuracion;
            }

            $nota->update($notaData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Nota jurídica actualizada exitosamente',
                'data' => $nota
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar nota jurídica: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la nota jurídica: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar nota jurídica (soft delete)
     */
    public function destroy($id)
    {
        try {
            $nota = NotaJuridica::findOrFail($id);
            $nota->delete();

            return response()->json([
                'success' => true,
                'message' => 'Nota jurídica eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al eliminar nota jurídica: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la nota jurídica'
            ], 500);
        }
    }

    /**
     * Exportar nota a PDF
     */
    public function exportarPdf($id)
    {
        try {
            $nota = NotaJuridica::with(['personal', 'notaReferencia', 'creador'])
                ->findOrFail($id);

            $pdf = PDF::loadView('prints.nota-juridica', compact('nota'));
            $pdf->setPaper('legal', 'portrait');

            $nombreArchivo = "Nota_Juridica_{$nota->numero}_{$nota->anio}.pdf";

            return $pdf->stream($nombreArchivo);

        } catch (\Exception $e) {
            Log::error('Error al exportar PDF: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el PDF'
            ], 500);
        }
    }

    /**
     * Obtener lista de plantillas del módulo notas-juridicas
     */
    public function plantillas()
    {
        try {
            // Buscar el módulo de notas jurídicas
            $modulo = \App\Models\Modulo::where('Url', 'laravel-notas-juridicas')->first();

            if (!$modulo) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }

            $plantillas = PlantillaDocumento::porModulo($modulo->IdModulo)
                ->orderBy('nombre')
                ->get(['idPlantilla', 'nombre', 'descripcion', 'configuracion']);

            return response()->json([
                'success' => true,
                'data' => $plantillas
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener plantillas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las plantillas'
            ], 500);
        }
    }

    /**
     * Cargar una plantilla
     */
    public function cargarPlantilla($id)
    {
        try {
            $plantilla = PlantillaDocumento::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $plantilla,
                'configuracion' => $plantilla->configuracion_completa
            ]);

        } catch (\Exception $e) {
            Log::error('Error al cargar plantilla: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar la plantilla'
            ], 500);
        }
    }

    /**
     * Guardar archivo (local y/o Google Drive)
     */
    private function guardarArchivo($base64String, $nombreArchivo = null)
    {
        try {
            // Extraer tipo de archivo y datos
            preg_match('/data:(.*?);base64,(.*)/', $base64String, $matches);

            if (count($matches) !== 3) {
                return ['path' => null, 'drive_id' => null, 'drive_link' => null];
            }

            $mimeType = $matches[1];
            $fileData = base64_decode($matches[2]);

            // Determinar extensión
            $extension = $this->getExtensionFromMime($mimeType);

            // Generar nombre único
            if (!$nombreArchivo) {
                $nombreArchivo = 'nota_' . time() . '_' . uniqid() . '.' . $extension;
            } else {
                $nombreArchivo = pathinfo($nombreArchivo, PATHINFO_FILENAME) . '_' . time() . '.' . $extension;
            }

            // Crear directorio si no existe
            $directorio = public_path('img/notas_juridicas');
            if (!file_exists($directorio)) {
                mkdir($directorio, 0755, true);
            }

            // Guardar localmente
            $rutaCompleta = $directorio . '/' . $nombreArchivo;
            file_put_contents($rutaCompleta, $fileData);

            $path = 'img/notas_juridicas/' . $nombreArchivo;

            // Intentar subir a Google Drive si está configurado
            $driveId = null;
            $driveLink = null;

            if (config('services.google_drive.folder_id')) {
                $driveResult = $this->subirAGoogleDrive($fileData, $nombreArchivo, $mimeType);
                if ($driveResult) {
                    $driveId = $driveResult['id'];
                    $driveLink = $driveResult['link'];
                }
            }

            return [
                'path' => $path,
                'drive_id' => $driveId,
                'drive_link' => $driveLink
            ];

        } catch (\Exception $e) {
            Log::error('Error al guardar archivo: ' . $e->getMessage());
            return ['path' => null, 'drive_id' => null, 'drive_link' => null];
        }
    }

    /**
     * Subir archivo a Google Drive
     */
    private function subirAGoogleDrive($fileData, $fileName, $mimeType)
    {
        try {
            // Verificar si está configurado Google Drive
            $clientId = config('services.google_drive.client_id');
            $clientSecret = config('services.google_drive.client_secret');
            $refreshToken = config('services.google_drive.refresh_token');
            $folderId = config('services.google_drive.folder_id');

            if (!$clientId || !$clientSecret || !$refreshToken || !$folderId) {
                return null;
            }

            // Configurar cliente de Google
            $client = new \Google\Client();
            $client->setClientId($clientId);
            $client->setClientSecret($clientSecret);
            $client->refreshToken($refreshToken);

            $service = new \Google\Service\Drive($client);

            // Crear archivo
            $file = new \Google\Service\Drive\DriveFile();
            $file->setName($fileName);
            $file->setParents([$folderId]);

            // Subir archivo
            $createdFile = $service->files->create($file, [
                'data' => $fileData,
                'mimeType' => $mimeType,
                'uploadType' => 'multipart'
            ]);

            // Hacer público el archivo
            $permission = new \Google\Service\Drive\Permission();
            $permission->setType('anyone');
            $permission->setRole('reader');
            $service->permissions->create($createdFile->getId(), $permission);

            return [
                'id' => $createdFile->getId(),
                'link' => "https://drive.google.com/file/d/{$createdFile->getId()}/view"
            ];

        } catch (\Exception $e) {
            Log::error('Error al subir a Google Drive: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener extensión de archivo desde MIME type
     */
    private function getExtensionFromMime($mimeType)
    {
        $mimeToExt = [
            'application/pdf' => 'pdf',
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        ];

        return $mimeToExt[$mimeType] ?? 'pdf';
    }
}
