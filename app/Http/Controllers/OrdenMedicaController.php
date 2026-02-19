<?php

namespace App\Http\Controllers;

use App\Models\Licencia;
use App\Models\Personal;
use App\Models\MotivoLicencia;
use App\Models\Disposicion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OrdenMedicaController extends Controller
{
    /**
     * Validar que no haya superposición de licencias para el mismo personal
     */
    private function validarSuperposicionLicencias($personalId, $fechaDesde, $fechaHasta, $licenciaExcluir = null)
    {
        $fechaDesde = Carbon::parse($fechaDesde)->startOfDay();
        $fechaHasta = Carbon::parse($fechaHasta)->endOfDay();

        $query = Licencia::where('LegajoPersonal', $personalId)
            ->where(function ($q) use ($fechaDesde, $fechaHasta) {
                // Casos de superposición:
                // 1. La nueva licencia comienza durante una existente
                $q->whereBetween('FechaLic', [$fechaDesde, $fechaHasta])
                // 2. La nueva licencia termina durante una existente
                  ->orWhereBetween('FechaLicFin', [$fechaDesde, $fechaHasta])
                // 3. La nueva licencia abarca completamente una existente
                  ->orWhere(function ($q2) use ($fechaDesde, $fechaHasta) {
                      $q2->where('FechaLic', '>=', $fechaDesde)
                         ->where('FechaLicFin', '<=', $fechaHasta);
                  })
                // 4. Una existente abarca completamente la nueva
                  ->orWhere(function ($q2) use ($fechaDesde, $fechaHasta) {
                      $q2->where('FechaLic', '<=', $fechaDesde)
                         ->where('FechaLicFin', '>=', $fechaHasta);
                  });
            });

        // Excluir la licencia actual si estamos editando
        if ($licenciaExcluir) {
            $query->where('IdLicencia', '!=', $licenciaExcluir);
        }

        return $query->with(['motivo', 'personal'])->first();
    }

    /**
     * Procesar imagen base64 y guardarla
     */
    private function procesarImagenBase64($base64String, $nombreArchivo = null)
    {
        if (!$base64String) return null;

        try {
            // Extraer el tipo de imagen y los datos
            preg_match('/data:image\/(.*?);base64,(.*)/', $base64String, $matches);

            if (count($matches) !== 3) {
                return null;
            }

            $extension = $matches[1];
            $imageData = base64_decode($matches[2]);

            if (!$imageData) {
                return null;
            }

            // Generar nombre único si no se proporciona
            if (!$nombreArchivo) {
                $nombreArchivo = time() . '_' . uniqid() . '.' . $extension;
            }

            // Crear directorio si no existe
            $directorio = public_path('img/certificados');
            if (!file_exists($directorio)) {
                mkdir($directorio, 0755, true);
            }

            $rutaCompleta = $directorio . '/' . $nombreArchivo;

            // Guardar el archivo
            file_put_contents($rutaCompleta, $imageData);

            return 'img/certificados/' . $nombreArchivo;

        } catch (\Exception $e) {
            Log::error('Error al procesar imagen base64: ' . $e->getMessage());
            return null;
        }
    }

    /**
    /**
     * Display a listing of the resource.
     */
    public function index()  {
        $personal = Personal::all();
        // Mostrar SOLO los motivos específicamente vinculados al módulo de Orden Médica
        $motivos = MotivoLicencia::where('ModuloId', MotivoLicencia::MODULO_ORDEN_MEDICA)
            ->whereNull('FechaEliminacion')
            ->orderBy('Motivo')
            ->get();
        $disposiciones = Disposicion::all();

        return view('orden-medicas', compact('personal', 'motivos', 'disposiciones'));
    }

    /**
     * Get filtered medical orders
     */
    public function filtrar(Request $request)
    {
        try {
            // Log para debug
            Log::info('Filtros recibidos:', $request->all());

            // IMPORTANTE: Solo órdenes médicas (OrdenMedica != 0 y no null)
            $query = Licencia::with(['personal', 'motivo', 'creador'])
                ->where('OrdenMedica', '!=', 0)
                ->whereNotNull('OrdenMedica');

            Log::info('Aplicando filtro base: solo órdenes médicas (OrdenMedica != 0)');

            // Filtro por personal (LegajoPersonal)
            if ($request->filled('personal_id')) {
                $query->where('LegajoPersonal', $request->personal_id);
                Log::info('Aplicando filtro personal_id: ' . $request->personal_id);
            }

            // Filtro por AnioLar (año LAR - campo de la tabla)
            if ($request->filled('anio_lar')) {
                $query->where('AnioLar', $request->anio_lar);
                Log::info('Aplicando filtro AnioLar: ' . $request->anio_lar);
            }

            // Filtro por fecha específica (desde)
            if ($request->filled('fecha_desde')) {
                $query->whereDate('FechaCreacion', '>=', $request->fecha_desde);
                Log::info('Aplicando filtro fecha desde: ' . $request->fecha_desde);
            }

            // Filtro por fecha específica (hasta)
            if ($request->filled('fecha_hasta')) {
                $query->whereDate('FechaCreacion', '<=', $request->fecha_hasta);
                Log::info('Aplicando filtro fecha hasta: ' . $request->fecha_hasta);
            }

            // Filtro por motivo (Motivo_Id)
            if ($request->filled('motivo_id')) {
                $query->where('Motivo_Id', $request->motivo_id);
            }

            // Filtro por estado (estado_om)
            if ($request->filled('estado')) {
                $query->where('estado_om', $request->estado);
            }

            // Filtro por número de OM específico
            if ($request->filled('numero_om')) {
                $query->where('OrdenMedica', $request->numero_om);
                Log::info('Aplicando filtro numero_om: ' . $request->numero_om);
            }

            // Filtro por múltiples números de OM (separados por comas)
            if ($request->filled('multiples_om')) {
                $numerosOm = explode(',', $request->multiples_om);
                // Limpiar espacios en blanco y filtrar valores vacíos
                $numerosOm = array_filter(array_map('trim', $numerosOm));

                if (!empty($numerosOm)) {
                    $query->whereIn('OrdenMedica', $numerosOm);
                    Log::info('Aplicando filtro multiples_om: ' . implode(', ', $numerosOm));
                }
            }

            // Filtros de búsqueda por personal

            if ($request->filled('dni')) {
                $query->whereHas('personal', function($q) use ($request) {
                    $q->where('DNI', 'LIKE', '%' . $request->dni . '%');
                });
            }

            if ($request->filled('legajo')) {
                $query->whereHas('personal', function($q) use ($request) {
                    $q->where('Legajo', 'LIKE', '%' . $request->legajo . '%');
                });
            }

            if ($request->filled('personal')) {
                $query->whereHas('personal', function($q) use ($request) {
                    $busqueda = $request->personal;
                    $q->where(function($subQuery) use ($busqueda) {
                        $subQuery->where('Nombre', 'LIKE', '%' . $busqueda . '%')
                                ->orWhere('Apellido', 'LIKE', '%' . $busqueda . '%')
                                ->orWhere(DB::raw("CONCAT(Apellido, ', ', Nombre)"), 'LIKE', '%' . $busqueda . '%')
                                ->orWhere(DB::raw("CONCAT(Nombre, ' ', Apellido)"), 'LIKE', '%' . $busqueda . '%');
                    });
                });
            }

            // Ordenar por año LAR descendente y luego por número de orden médica descendente
            $query->orderBy('AnioLar', 'desc')->orderBy('OrdenMedica', 'desc');

            // Log de la consulta SQL generada
            Log::info('SQL Query: ' . $query->toSql());
            Log::info('SQL Bindings: ', $query->getBindings());

            // Paginación
            $perPage = $request->get('per_page', 15);
            $ordenes = $query->paginate($perPage);

            Log::info('Total de registros encontrados: ' . $ordenes->total());

            return response()->json($ordenes);

        } catch (\Exception $e) {
            Log::error('Error al filtrar órdenes médicas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las órdenes médicas'
            ], 500);
        }
    }

    /**
     * Get last order number
     */
    public function ultimoNumero()
    {
        try {
            $anioActual = date('Y');
            $ultimaOrden = Licencia::where('AnioLar', $anioActual)
                ->orderBy('OrdenMedica', 'desc')
                ->first();

            $siguienteNumero = $ultimaOrden ? ($ultimaOrden->OrdenMedica + 1) : 1;

            return response()->json([
                'success' => true,
                'numero' => $siguienteNumero,
                'sugerido' => $siguienteNumero
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener último número: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el último número'
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'personal_id' => 'required|exists:personal,Legajo',
                'numero' => 'required|integer',
                'fecha' => 'required|date',
                'anio' => 'required|integer',
                'estado' => 'required|string',
                'motivo_id' => 'required|exists:motivo_licencia,IdMotivoLicencia',
                'dias' => 'required|integer|min:1',
                'desde' => 'required|date',
                'hasta' => 'required|date',
                'corridos' => 'required|boolean',
                'disposicion_id' => 'nullable|exists:num_disp,IdNumDisp',
                'observacion' => 'nullable|string',
                'certificado' => 'nullable|integer',
                'poster' => 'nullable|string', // Motivo de postergación
                'disp2' => 'nullable|exists:num_disp,IdNumDisp', // Disposición de postergación
                'imagen_base64' => 'nullable|string', // Imagen en base64
                'imagen_url' => 'nullable|string', // URL de imagen existente
                'imagen' => 'nullable|image|max:2048', // Validación para imagen archivo (mantenemos compatibilidad)
            ]);

            // Validar superposición de licencias
            $licenciaSuperpuesta = $this->validarSuperposicionLicencias(
                $validated['personal_id'],
                $validated['desde'],
                $validated['hasta']
            );

            if ($licenciaSuperpuesta) {
                $nombrePersonal = $licenciaSuperpuesta->personal
                    ? $licenciaSuperpuesta->personal->Apellido . ', ' . $licenciaSuperpuesta->personal->Nombre
                    : 'Personal ID: ' . $validated['personal_id'];

                $motivoExistente = $licenciaSuperpuesta->motivo
                    ? $licenciaSuperpuesta->motivo->Motivo
                    : 'Motivo desconocido';

                $fechaDesdeExistente = Carbon::parse($licenciaSuperpuesta->FechaLic)->format('d/m/Y');
                $fechaHastaExistente = Carbon::parse($licenciaSuperpuesta->FechaLicFin)->format('d/m/Y');

                return response()->json([
                    'success' => false,
                    'message' => "Error: Ya existe una licencia para {$nombrePersonal} que se superpone con las fechas seleccionadas.\n\nLicencia existente:\n- Motivo: {$motivoExistente}\n- Período: {$fechaDesdeExistente} a {$fechaHastaExistente}\n- OM: {$licenciaSuperpuesta->OrdenMedica}/{$licenciaSuperpuesta->AnioLar}\n\nNo se pueden otorgar dos licencias simultáneas al mismo agente."
                ], 422);
            }

            DB::beginTransaction();

            $imagenPath = null;

            // Procesar imagen base64 si se envió
            if ($request->filled('imagen_base64')) {
                $imagenPath = $this->procesarImagenBase64($request->imagen_base64);
            }
            // Procesar imagen archivo si se subió (mantenemos compatibilidad)
            elseif ($request->hasFile('imagen')) {
                $imagen = $request->file('imagen');
                $nombreImagen = time() . '_' . uniqid() . '.' . $imagen->getClientOriginalExtension();

                // Guardar en directorio público
                $imagen->move(public_path('img/certificados'), $nombreImagen);
                $imagenPath = 'img/certificados/' . $nombreImagen;
            }

            $licencia = Licencia::create([
                'LegajoPersonal' => $validated['personal_id'],
                'OrdenMedica' => $validated['numero'],
                'FechaCreacion' => $validated['fecha'],
                'AnioLar' => $validated['anio'],
                'estado_om' => $validated['estado'],
                'Motivo_Id' => $validated['motivo_id'],
                'DiasTotal' => $validated['dias'],
                'FechaLic' => $validated['desde'],
                'FechaLicFin' => $validated['hasta'],
                'Cont' => $validated['corridos'] ? 1 : 0,
                'NumDisp' => $validated['disposicion_id'] ?? null,
                'ObservacionLic' => $validated['observacion'] ?? null,
                'CertMedico' => $validated['certificado'] ?? 0,
                'MotPoster' => $validated['poster'] ?? null,
                'NumDispPoster' => $validated['disp2'] ?? null,
                'imagen_ficha' => $imagenPath,
                'Creador_Id' => auth()->id() ?? 1,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Orden médica guardada exitosamente',
                'data' => $licencia
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar orden médica: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la orden médica: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Licencia $licencia)
    {
        $licencia->load(['personal', 'motivo', 'disposicion']);

        // Agregar URL de imagen si existe
        if ($licencia->imagen_ficha) {
            $licencia->imagen_url = asset($licencia->imagen_ficha);
        }

        return response()->json([
            'success' => true,
            'data' => $licencia
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Licencia $licencia)
    {
        try {
            $validated = $request->validate([
                'personal_id' => 'required|exists:personal,Legajo',
                'numero' => 'required|integer',
                'fecha' => 'required|date',
                'anio' => 'required|integer',
                'estado' => 'required|string',
                'motivo_id' => 'required|exists:motivo_licencia,IdMotivoLicencia',
                'dias' => 'required|integer|min:1',
                'desde' => 'required|date',
                'hasta' => 'required|date',
                'corridos' => 'required|boolean',
                'disposicion_id' => 'nullable|exists:num_disp,IdNumDisp',
                'observacion' => 'nullable|string',
                'certificado' => 'nullable|integer',
                'poster' => 'nullable|string', // Motivo de postergación
                'disp2' => 'nullable|exists:num_disp,IdNumDisp', // Disposición de postergación
                'imagen_base64' => 'nullable|string', // Imagen en base64
                'imagen_url' => 'nullable|string', // URL de imagen existente
                'imagen' => 'nullable|image|max:2048', // Validación para imagen archivo (mantenemos compatibilidad)
            ]);

            // Validar superposición de licencias (excluyendo la licencia actual)
            $licenciaSuperpuesta = $this->validarSuperposicionLicencias(
                $validated['personal_id'],
                $validated['desde'],
                $validated['hasta'],
                $licencia->IdLicencia // Excluir la licencia actual
            );

            if ($licenciaSuperpuesta) {
                $nombrePersonal = $licenciaSuperpuesta->personal
                    ? $licenciaSuperpuesta->personal->Apellido . ', ' . $licenciaSuperpuesta->personal->Nombre
                    : 'Personal ID: ' . $validated['personal_id'];

                $motivoExistente = $licenciaSuperpuesta->motivo
                    ? $licenciaSuperpuesta->motivo->Motivo
                    : 'Motivo desconocido';

                $fechaDesdeExistente = Carbon::parse($licenciaSuperpuesta->FechaLic)->format('d/m/Y');
                $fechaHastaExistente = Carbon::parse($licenciaSuperpuesta->FechaLicFin)->format('d/m/Y');

                return response()->json([
                    'success' => false,
                    'message' => "Error: Ya existe una licencia para {$nombrePersonal} que se superpone con las fechas seleccionadas.\n\nLicencia existente:\n- Motivo: {$motivoExistente}\n- Período: {$fechaDesdeExistente} a {$fechaHastaExistente}\n- OM: {$licenciaSuperpuesta->OrdenMedica}/{$licenciaSuperpuesta->AnioLar}\n\nNo se pueden otorgar dos licencias simultáneas al mismo agente."
                ], 422);
            }

            DB::beginTransaction();

            $imagenPath = $licencia->imagen_ficha; // Mantener imagen existente por defecto

            // Procesar imagen base64 si se envió
            if ($request->filled('imagen_base64')) {
                // Eliminar imagen anterior si existe
                if ($licencia->imagen_ficha && file_exists(public_path($licencia->imagen_ficha))) {
                    unlink(public_path($licencia->imagen_ficha));
                }

                $imagenPath = $this->procesarImagenBase64($request->imagen_base64);
            }
            // Procesar imagen archivo si se subió (mantenemos compatibilidad)
            elseif ($request->hasFile('imagen')) {
                // Eliminar imagen anterior si existe
                if ($licencia->imagen_ficha && file_exists(public_path($licencia->imagen_ficha))) {
                    unlink(public_path($licencia->imagen_ficha));
                }

                $imagen = $request->file('imagen');
                $nombreImagen = time() . '_' . uniqid() . '.' . $imagen->getClientOriginalExtension();

                // Guardar en directorio público
                $imagen->move(public_path('img/certificados'), $nombreImagen);
                $imagenPath = 'img/certificados/' . $nombreImagen;
            }

            $licencia->update([
                'LegajoPersonal' => $validated['personal_id'],
                'OrdenMedica' => $validated['numero'],
                'FechaCreacion' => $validated['fecha'],
                'AnioLar' => $validated['anio'],
                'estado_om' => $validated['estado'],
                'Motivo_Id' => $validated['motivo_id'],
                'DiasTotal' => $validated['dias'],
                'FechaLic' => $validated['desde'],
                'FechaLicFin' => $validated['hasta'],
                'Cont' => $validated['corridos'] ? 1 : 0,
                'NumDisp' => $validated['disposicion_id'] ?? null,
                'ObservacionLic' => $validated['observacion'] ?? null,
                'CertMedico' => $validated['certificado'] ?? 0,
                'MotPoster' => $validated['poster'] ?? null,
                'NumDispPoster' => $validated['disp2'] ?? null,
                'imagen_ficha' => $imagenPath,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Orden médica actualizada exitosamente',
                'data' => $licencia
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar orden médica: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la orden médica: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Licencia $licencia)
    {
        try {
            $licencia->delete();

            return response()->json([
                'success' => true,
                'message' => 'Orden médica eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al eliminar orden médica: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la orden médica'
            ], 500);
        }
    }

    /**
     * Print medical order - Genera PDF de Orden Médica
     */
    public function imprimir(Licencia $licencia)
    {
        try {
            // Verificar si DomPDF está disponible
            if (!class_exists('Barryvdh\DomPDF\Facade\Pdf') && !class_exists('Barryvdh\DomPDF\PDF')) {
                return response()->json([
                    'success' => false,
                    'message' => 'El generador de PDF no está disponible. Contacte al administrador para instalar la librería DomPDF.'
                ], 500);
            }
            
            // Cargar las relaciones necesarias
            $licencia->load(['personal', 'motivo', 'disposicion']);

            // Obtener la leyenda anual
            $leyenda = \App\Models\LeyendaAnual::getPorAnio(date('Y'));

            // Generar PDF con DomPDF
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('prints.orden-medica', compact('licencia', 'leyenda'));
            $pdf->setPaper('legal', 'portrait');

            $nombreArchivo = 'Orden_Medica_' . $licencia->personal->Legajo . '_' . date('Y') . '.pdf';

            return $pdf->stream($nombreArchivo);

        } catch (\Exception $e) {
            Log::error('Error al imprimir orden médica: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al imprimir la orden médica: ' . $e->getMessage()
            ], 500);
        }
    }
}
