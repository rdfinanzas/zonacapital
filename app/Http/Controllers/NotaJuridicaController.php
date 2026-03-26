<?php

namespace App\Http\Controllers;

use App\Models\NotaJuridica;
use App\Models\NotaJuridicaHistorial;
use App\Models\Personal;
use App\Models\Usuario;
use App\Models\PlantillaDocumento;
use App\Helpers\ConfiguracionNotaHelper;
use App\Services\GoogleDriveService;
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

        // Obtener logo y leyenda por defecto del sistema
        $logoDefault = ConfiguracionController::getLogoPath();
        $leyendaDefault = \App\Models\LeyendaAnual::getPorAnio(date('Y'));

        // Obtener estados del modelo para centralizar
        $estados = NotaJuridica::ESTADOS;
        
        return view('notas-juridicas', compact('personal', 'anios', 'plantillas', 'logoDefault', 'leyendaDefault', 'estados'));
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

            // Filtro por estado (ID numérico)
            if ($request->filled('estado')) {
                $query->where('estado', (int) $request->estado);
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

            // Agregar texto y badge de estado a cada nota
            $notas->getCollection()->transform(function ($nota) {
                $nota->estado_texto = $nota->estado_texto;
                $nota->estado_badge = $nota->getEstadoBadgeClass();
                return $nota;
            });

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
                'tipo' => 'nullable|in:creada,adjunta,completa',  // Ya no es required, nullable
                'estado' => 'required|' . NotaJuridica::getEstadosValidacion(),
                'archivo_base64' => 'nullable|string',
                'archivo_nombre' => 'nullable|string',
                'plantilla_id' => 'nullable|exists:plantillas_documentos,idPlantilla',
                'configuracion' => 'nullable|array',
                'crear_google_doc' => 'nullable|boolean',
                'google_doc_template_id' => 'nullable|string',
                'google_doc_id' => 'nullable|string',
                'google_doc_link' => 'nullable|string',
                'es_plantilla' => 'nullable|boolean',
                'nombre_plantilla' => 'nullable|string|max:255',
            ]);

            DB::beginTransaction();

            $anio = date('Y', strtotime($validated['fecha_creacion']));

            // Usar el número enviado desde el formulario, o generar uno automáticamente
            $numero = $request->filled('numero') ? (int)$request->input('numero') : NotaJuridica::obtenerProximoNumero((int)$anio);

            // Validar que no exista otra nota con el mismo número y año (incluyendo soft deletes)
            $existe = NotaJuridica::withTrashed()
                ->where('anio', $anio)
                ->where('numero', $numero)
                ->first();

            if ($existe) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "Ya existe una nota con el número {$numero}/{$anio}. Utilice otro número."
                ], 422);
            }

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
                'google_doc_id' => $validated['google_doc_id'] ?? null,
                'google_doc_link' => $validated['google_doc_link'] ?? null,
                'es_plantilla' => $request->boolean('es_plantilla'),
                'nombre_plantilla' => $request->boolean('es_plantilla') ? ($validated['nombre_plantilla'] ?? $validated['titulo']) : null,
            ];

            // Manejar archivo si viene (independiente del tipo)
            if ($request->filled('archivo_base64')) {
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

            // Crear documento en Google Docs si se solicitó
            if ($request->filled('crear_google_doc') && $request->crear_google_doc) {
                $googleDoc = $this->crearGoogleDoc($nota, $request->google_doc_template_id ?? null);
                if ($googleDoc) {
                    $nota->update([
                        'google_doc_id' => $googleDoc['id'],
                        'google_doc_link' => $googleDoc['link']
                    ]);
                    $nota->refresh();
                }
            }

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

            // Agregar estado con texto para el frontend
            $notaArray = $nota->toArray();
            $notaArray['estado_texto'] = $nota->estado_texto;
            $notaArray['estado_badge'] = $nota->getEstadoBadgeClass();

            return response()->json([
                'success' => true,
                'data' => $notaArray,
                'configuracion' => $nota->configuracion_completa,
                'estados_disponibles' => NotaJuridica::ESTADOS
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

            // Log para depuración
            Log::debug('NotaJuridica update - Request data:', [
                'id' => $id,
                'estado_recibido' => $request->input('estado'),
                'tipo_recibido' => $request->input('tipo'),
                'google_doc_id' => $request->input('google_doc_id'),
                'nota_existente_google_doc_id' => $nota->google_doc_id,
                'nota_existente_estado' => $nota->estado,
            ]);

            $validated = $request->validate([
                'titulo' => 'required|string|max:255',
                'descripcion' => 'nullable|string',
                'observacion' => 'nullable|string',
                'fecha_creacion' => 'required|date',
                'personal_id' => 'nullable|exists:empleados,idEmpleado',
                'nota_referencia_id' => 'nullable|exists:notas_juridicas,idNotaJuridica',
                'tipo' => 'nullable|in:creada,adjunta,completa',
                'estado' => 'required|' . NotaJuridica::getEstadosValidacion(),
                'archivo_base64' => 'nullable|string',
                'archivo_nombre' => 'nullable|string',
                'eliminar_archivo' => 'nullable|boolean',
                'configuracion' => 'nullable|array',
                'google_doc_id' => 'nullable|string',
                'google_doc_link' => 'nullable|string',
            ]);

            DB::beginTransaction();

            // Validar duplicado de número/año si se está cambiando el número
            if ($request->filled('numero') && $request->input('numero') != $nota->numero) {
                $nuevoNumero = (int)$request->input('numero');
                $anio = $nota->anio; // Mantener el año actual de la nota

                $existe = NotaJuridica::withTrashed()
                    ->where('anio', $anio)
                    ->where('numero', $nuevoNumero)
                    ->where('idNotaJuridica', '!=', $id)
                    ->first();

                if ($existe) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Ya existe una nota con el número {$nuevoNumero}/{$anio}. Utilice otro número."
                    ], 422);
                }

                $notaData['numero'] = $nuevoNumero;
            }

            // Construir configuración si viene en el nuevo formato
            $configuracion = null;
            if ($request->filled('configuracion')) {
                $configuracion = $validated['configuracion'];
            } else {
                // Compatibilidad con formato antiguo
                $configuracion = ConfiguracionNotaHelper::desdeRequest($request->all());
            }

            // Determinar el tipo basado en los datos existentes y nuevos
            // Si no se envía tipo, recalcular basado en lo que tiene la nota
            $tipo = $validated['tipo'];
            if (is_null($tipo)) {
                $tieneGoogleDoc = !empty($validated['google_doc_id']) || !empty($validated['google_doc_link']) || !empty($nota->google_doc_id) || !empty($nota->google_doc_link);
                $tieneArchivo = !empty($validated['archivo_base64']) || !empty($nota->archivo_path) || !empty($nota->google_drive_file_id);
                
                if ($tieneGoogleDoc && $tieneArchivo) {
                    $tipo = 'completa';
                } elseif ($tieneGoogleDoc) {
                    $tipo = 'creada';
                } elseif ($tieneArchivo) {
                    $tipo = 'adjunta';
                }
            }

            $notaData = [
                'titulo' => $validated['titulo'],
                'descripcion' => $configuracion['contenido'] ?? $validated['descripcion'] ?? null,
                'observacion' => $validated['observacion'] ?? null,
                'fecha_creacion' => $validated['fecha_creacion'],
                'personal_id' => $validated['personal_id'] ?? null,
                'nota_referencia_id' => $validated['nota_referencia_id'] ?? null,
                'tipo' => $tipo,
                'estado' => $validated['estado'],
                'configuracion' => $configuracion,
                'google_doc_id' => $validated['google_doc_id'] ?? $nota->google_doc_id,
                'google_doc_link' => $validated['google_doc_link'] ?? $nota->google_doc_link,
            ];

            // Manejar archivo (independiente del tipo)
            if ($request->filled('eliminar_archivo') && $request->eliminar_archivo) {
                if ($nota->archivo_path && file_exists(public_path($nota->archivo_path))) {
                    unlink(public_path($nota->archivo_path));
                }
                $notaData['archivo_path'] = null;
                $notaData['google_drive_file_id'] = null;
                $notaData['google_drive_link'] = null;
            } elseif ($request->filled('archivo_base64')) {
                // Eliminar archivo anterior si existe
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
            $nota->refresh(); // Recargar para asegurar datos actualizados

            DB::commit();

            Log::debug('NotaJuridica update - Guardado exitoso:', [
                'id' => $nota->idNotaJuridica,
                'estado_guardado' => $nota->estado,
                'tipo_guardado' => $nota->tipo,
            ]);

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
     * Obtener historial de una nota jurídica
     */
    public function historial($id)
    {
        try {
            $nota = NotaJuridica::findOrFail($id);
            $historial = $nota->historial()->with('usuario')->get();

            return response()->json([
                'success' => true,
                'data' => $historial
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener historial: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el historial'
            ], 500);
        }
    }

    /**
     * Agregar novedad al historial
     */
    public function agregarNovedad(Request $request, $id)
    {
        try {
            $nota = NotaJuridica::findOrFail($id);

            $validated = $request->validate([
                'descripcion' => 'required|string|max:2000',
            ]);

            $novedad = NotaJuridicaHistorial::create([
                'nota_juridica_id' => $nota->idNotaJuridica,
                'usuario_id' => session('usuario_id') ?? auth()->id() ?? null,
                'descripcion' => $validated['descripcion'],
                'created_at' => now(),
            ]);

            // Cargar la relación de usuario
            $novedad->load('usuario');

            return response()->json([
                'success' => true,
                'message' => 'Novedad agregada exitosamente',
                'data' => $novedad
            ]);

        } catch (\Exception $e) {
            Log::error('Error al agregar novedad: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al agregar la novedad: ' . $e->getMessage()
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
     * Guardar archivo (local y opcionalmente a Google Drive)
     * @param string $base64String Archivo en base64
     * @param string|null $nombreArchivo Nombre del archivo
     * @param bool $subirAGoogleDrive Si debe subir a Google Drive (default: false)
     */
    private function guardarArchivo($base64String, $nombreArchivo = null, $subirAGoogleDrive = false)
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

            // Solo subir a Google Drive si explícitamente se solicita
            $driveId = null;
            $driveLink = null;

            if ($subirAGoogleDrive && config('services.google_drive.folder_id')) {
                try {
                    $driveResult = $this->subirAGoogleDrive($fileData, $nombreArchivo, $mimeType);
                    if ($driveResult) {
                        $driveId = $driveResult['id'];
                        $driveLink = $driveResult['link'];
                    }
                } catch (\Exception $e) {
                    Log::warning('No se pudo subir a Google Drive: ' . $e->getMessage());
                    // No fallar el guardado si Google Drive falla
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

    /**
     * Crear documento en Google Docs
     */
    private function crearGoogleDoc($nota, $templateId = null)
    {

        try {
            $googleDrive = new GoogleDriveService();

            if (!$googleDrive->isConfigured()) {
                Log::warning('Google Drive no configurado para crear documento');
                return null;
            }

            $nombreDocumento = "Nota {$nota->numero}/{$nota->anio} - {$nota->titulo}";

            // Si hay template, copiar; si no, crear vacío
            if ($templateId) {
                $result = $googleDrive->copyDocument($templateId, $nombreDocumento);
            } else {
                $contenido = $nota->descripcion ?? '';
                $result = $googleDrive->createDocument($nombreDocumento, $contenido);
            }

            if ($result) {
                $googleDrive->shareDocument($result['id']);
                return $result;
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Error al crear Google Doc: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Endpoint para listar documentos de Google Drive (plantillas)
     */
    public function listarPlantillasDrive()
    {
        try {
            $googleDrive = new GoogleDriveService();

            if (!$googleDrive->isConfigured()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Google Drive no configurado'
                ], 400);
            }

            $documentos = $googleDrive->listDocuments();

            // Filtrar solo documentos de Google Docs
            $docs = array_filter($documentos, function($doc) {
                return $doc['mimeType'] === 'application/vnd.google-apps.document';
            });

            return response()->json([
                'success' => true,
                'data' => array_values($docs)
            ]);

        } catch (\Exception $e) {
            Log::error('Error al listar plantillas Drive: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener documentos'
            ], 500);
        }
    }

    /**
     * Crear documento en Google Drive ANTES de guardar la nota
     * Si ya existe un documento con el número/año, usar ese; si no, crear nuevo
     * Devuelve el ID y link para abrir en nueva pestaña
     */
    public function crearDocDrive(Request $request)
    {
        try {
            $googleDrive = new GoogleDriveService();

            if (!$googleDrive->isConfigured()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Google Drive no está configurado. Verifique las credenciales.'
                ], 400);
            }

            $validated = $request->validate([
                'titulo' => 'nullable|string|max:255',
                'template_id' => 'nullable|string',
                'numero' => 'nullable|integer',
                'anio' => 'nullable|integer',
            ]);

            $numero = $validated['numero'] ?? null;
            $anio = $validated['anio'] ?? date('Y');
            $templateId = $validated['template_id'] ?? null;

            // Construir nombre del documento con número/año
            $nombreDocumento = $numero
                ? "Nota {$numero}/{$anio}"
                : ($validated['titulo'] ?? 'Nueva Nota Jurídica - ' . date('d/m/Y H:i'));

            // Buscar si ya existe un documento con ese nombre en Drive
            $documentoExistente = $googleDrive->buscarDocumentoPorNombre($nombreDocumento);

            if ($documentoExistente) {
                // Si existe, devolver ese documento
                return response()->json([
                    'success' => true,
                    'message' => 'Documento existente encontrado',
                    'existe' => true,
                    'data' => [
                        'google_doc_id' => $documentoExistente['id'],
                        'google_doc_link' => $googleDrive->getDocumentLink($documentoExistente['id']),
                        'nombre' => $documentoExistente['name']
                    ]
                ]);
            }

            // Si no existe, crear nuevo documento
            if ($templateId) {
                $result = $googleDrive->copyDocument($templateId, $nombreDocumento);
            } else {
                $result = $googleDrive->createDocument($nombreDocumento, '');
            }

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo crear el documento en Google Drive'
                ], 500);
            }

            // Compartir el documento para que sea editable por cualquiera con el link
            $googleDrive->shareDocument($result['id']);

            return response()->json([
                'success' => true,
                'message' => 'Documento creado exitosamente',
                'existe' => false,
                'data' => [
                    'google_doc_id' => $result['id'],
                    'google_doc_link' => $result['link'],
                    'nombre' => $result['name'] ?? $nombreDocumento
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al crear documento en Drive: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear documento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar si un número de nota ya existe para un año
     */
    public function verificarNumero(Request $request)
    {
        try {
            $validated = $request->validate([
                'numero' => 'required|integer|min:1',
                'anio' => 'required|integer|min:2000|max:2100',
                'excluir_id' => 'nullable|integer'
            ]);

            $existe = NotaJuridica::withTrashed()
                ->where('numero', $validated['numero'])
                ->where('anio', $validated['anio']);

            if (!empty($validated['excluir_id'])) {
                $existe->where('idNotaJuridica', '!=', $validated['excluir_id']);
            }

            $notaExistente = $existe->first();

            return response()->json([
                'success' => true,
                'existe' => !empty($notaExistente),
                'mensaje' => !empty($notaExistente)
                    ? "El número {$validated['numero']}/{$validated['anio']} ya está en uso"
                    : null
            ]);

        } catch (\Exception $e) {
            Log::error('Error al verificar número: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar el número'
            ], 500);
        }
    }

    /**
     * Exportar notas jurídicas a Excel (CSV)
     */
    public function exportarExcel(Request $request)
    {
        try {
            $query = NotaJuridica::with(['personal', 'creador'])
                ->notas();

            // Aplicar mismos filtros que en el listado
            if ($request->filled('anio')) {
                $query->where('anio', $request->anio);
            }
            if ($request->filled('fecha_desde')) {
                $query->whereDate('fecha_creacion', '>=', $request->fecha_desde);
            }
            if ($request->filled('fecha_hasta')) {
                $query->whereDate('fecha_creacion', '<=', $request->fecha_hasta);
            }
            if ($request->filled('personal')) {
                $busqueda = $request->personal;
                $query->whereHas('personal', function ($q) use ($busqueda) {
                    $q->where('Nombre', 'LIKE', "%{$busqueda}%")
                        ->orWhere('Apellido', 'LIKE', "%{$busqueda}%")
                        ->orWhere('DNI', 'LIKE', "%{$busqueda}%");
                });
            }
            if ($request->filled('numero')) {
                $query->where('numero', $request->numero);
            }
            if ($request->filled('estado')) {
                $query->where('estado', (int) $request->estado);
            }
            if ($request->filled('busqueda')) {
                $query->buscar($request->busqueda);
            }

            $notas = $query->orderBy('anio', 'desc')
                ->orderBy('numero', 'desc')
                ->get();

            // Nombre del archivo
            $fecha = date('Y-m-d_H-i-s');
            $filename = "notas_juridicas_{$fecha}.csv";

            // Headers para descarga
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            ];

            // Generar CSV
            $callback = function () use ($notas) {
                $file = fopen('php://output', 'w');
                
                // BOM para Excel reconozca UTF-8
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

                // Encabezados
                fputcsv($file, [
                    'Nº Nota',
                    'Año',
                    'Fecha Creación',
                    'Título',
                    'Personal',
                    'DNI',
                    'Estado',
                    'Tipo',
                    'Creador',
                    'Observación',
                    'Tiene Google Doc',
                    'Tiene Archivo'
                ], ';');

                // Datos
                foreach ($notas as $nota) {
                    fputcsv($file, [
                        $nota->numero,
                        $nota->anio,
                        $nota->fecha_creacion ? $nota->fecha_creacion->format('d/m/Y') : '',
                        $nota->titulo,
                        $nota->personal ? "{$nota->personal->Apellido}, {$nota->personal->Nombre}" : '-',
                        $nota->personal ? $nota->personal->DNI : '-',
                        $nota->estado_texto,
                        $nota->tipo_label,
                        $nota->creador ? "{$nota->creador->Apellido}, {$nota->creador->Nombre}" : '-',
                        $nota->observacion,
                        $nota->tieneGoogleDoc() ? 'Sí' : 'No',
                        $nota->tieneArchivoAdjunto() ? 'Sí' : 'No'
                    ], ';');
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            Log::error('Error al exportar Excel: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al exportar a Excel: ' . $e->getMessage()
            ], 500);
        }
    }
}
