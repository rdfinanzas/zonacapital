<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\PermisoHelper;
use App\Models\Licencia;
use App\Models\Personal;
use App\Models\MotivoLicencia;
use App\Models\Disposicion;
use App\Models\Feriado;
use App\Models\Configuracion;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class LicenciasController extends Controller
{
    public function index()
    {
        $permisos = PermisoHelper::obtenerPermisos(session('usuario_id'), 'licencias');
        $personal = Personal::all();
        // Mostrar SOLO los motivos específicamente vinculados al módulo de Licencias
        $motivos = MotivoLicencia::where('ModuloId', MotivoLicencia::MODULO_LICENCIAS)
            ->whereNull('FechaEliminacion')
            ->orderBy('Motivo')
            ->get();
        $disposiciones = Disposicion::orderBy('AnioDisp', 'desc')
            ->orderByRaw('CAST(NumDisp AS UNSIGNED) DESC')
            ->get();
        return view('licencias', compact('permisos', 'personal', 'motivos', 'disposiciones'));
    }

    public function indexLar()
    {
        $permisos = PermisoHelper::obtenerPermisos(session('usuario_id'), 'licencias-lar');
        $personal = Personal::all();
        return view('licencias-lar', compact('permisos', 'personal'));
    }

    /**
     * Vista de lista de todas las LAR
     */
    public function listarLar()
    {
        $permisos = PermisoHelper::obtenerPermisos(session('usuario_id'), 'licencias-lar');
        $personal = Personal::all();
        return view('lar-lista', compact('permisos', 'personal'));
    }

    /**
     * Filtrar LAR para la lista
     */
    public function filtrarLar(Request $request)
    {
        try {
            Log::info('Filtros LAR recibidos:', $request->all());

            // Query base: solo LAR (Motivo_Id IS NULL y no son Orden Médica)
            $query = Licencia::with(['personal', 'disposicion', 'creador'])
                ->whereNull('Motivo_Id')
                ->where(function($q) {
                    $q->whereNull('OrdenMedica')
                      ->orWhere('OrdenMedica', 0);
                });

            // Filtro por año LAR
            if ($request->filled('anio_lar')) {
                $query->where('AnioLar', $request->anio_lar);
            }

            // Filtro por fecha creación (desde/hasta)
            if ($request->filled('fecha_desde')) {
                $query->whereDate('FechaCreacion', '>=', $request->fecha_desde);
            }
            if ($request->filled('fecha_hasta')) {
                $query->whereDate('FechaCreacion', '<=', $request->fecha_hasta);
            }

            // Filtro por personal (LegajoPersonal)
            if ($request->filled('personal_id')) {
                $query->where('LegajoPersonal', $request->personal_id);
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

            // Ordenar por año LAR descendente y fecha
            $query->orderBy('AnioLar', 'desc')->orderBy('FechaCreacion', 'desc');

            Log::info('SQL Query LAR: ' . $query->toSql());

            // Paginación
            $perPage = $request->get('per_page', 15);
            $lars = $query->paginate($perPage);

            Log::info('Total de LAR encontradas: ' . $lars->total());

            // Obtener los items y transformarlos con cálculo de días correspondientes
            $items = $lars->items();
            $data = [];
            
            // Cache para parámetros LAR por año y relación (evita consultas repetidas)
            $cacheParametros = [];
            
            foreach ($items as $lar) {
                // Calcular días correspondientes y pendientes para esta LAR
                $infoDias = $this->calcularDiasLarParaEmpleado(
                    $lar->LegajoPersonal, 
                    $lar->AnioLar, 
                    $cacheParametros
                );
                
                $data[] = [
                    'IdLicencia' => $lar->IdLicencia,
                    'FechaCreacion' => $lar->FechaCreacion,
                    'FechaLic' => $lar->FechaLic,
                    'FechaLicFin' => $lar->FechaLicFin,
                    'DiasTotal' => $lar->DiasTotal,
                    'AnioLar' => $lar->AnioLar,
                    'ObservacionLic' => $lar->ObservacionLic,
                    'LegajoPersonal' => $lar->LegajoPersonal,
                    'NumDisp' => $lar->NumDisp,
                    'personal' => $lar->personal ? [
                        'Nombre' => $lar->personal->Nombre,
                        'Apellido' => $lar->personal->Apellido,
                        'DNI' => $lar->personal->DNI,
                        'Legajo' => $lar->personal->Legajo,
                    ] : null,
                    'disposicion' => $lar->disposicion ? [
                        'NumDisp' => $lar->disposicion->NumDisp,
                        'AnioDisp' => $lar->disposicion->AnioDisp,
                    ] : null,
                    'creador' => $lar->creador ? [
                        'Nombre' => $lar->creador->Nombre,
                        'Apellido' => $lar->creador->Apellido,
                    ] : null,
                    // Información de días LAR
                    'dias_correspondientes' => $infoDias['dias_correspondientes'],
                    'dias_tomados' => $infoDias['dias_tomados'],
                    'dias_pendientes' => $infoDias['dias_pendientes'],
                    'antiguedad' => $infoDias['antiguedad'],
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'current_page' => $lars->currentPage(),
                'last_page' => $lars->lastPage(),
                'from' => $lars->firstItem(),
                'to' => $lars->lastItem(),
                'total' => $lars->total(),
            ]);

        } catch (\Exception $e) {
            Log::error('Error al filtrar LAR: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las LAR: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calcular días LAR correspondientes para un empleado en un año específico
     * Usado en la lista de LAR para mostrar días tomados/correspondientes/pendientes
     */
    private function calcularDiasLarParaEmpleado($legajo, $anio, &$cacheParametros = [])
    {
        try {
            // Obtener información del empleado
            $empleado = DB::table('empleados')
                ->where('Legajo', $legajo)
                ->select('idTipoRelacion', 'FAltaAP')
                ->first();

            if (!$empleado || !$empleado->FAltaAP) {
                return [
                    'dias_correspondientes' => null,
                    'dias_tomados' => 0,
                    'dias_pendientes' => null,
                    'antiguedad' => null,
                ];
            }

            // Calcular antigüedad
            $sqlAntiguedad = "SELECT 
                {$anio} - YEAR(FAltaAP) + IF(DATE_FORMAT('{$anio}-12-31','%m-%d') > DATE_FORMAT(FAltaAP,'%m-%d'), 0, -1) AS Antiguedad 
                FROM empleados 
                WHERE Legajo = {$legajo}";
            
            $resultado = DB::selectOne($sqlAntiguedad);
            
            if (!$resultado || $resultado->Antiguedad === null) {
                return [
                    'dias_correspondientes' => null,
                    'dias_tomados' => 0,
                    'dias_pendientes' => null,
                    'antiguedad' => null,
                ];
            }

            $anti = (int) $resultado->Antiguedad;
            $relacion = $empleado->idTipoRelacion;

            // Calcular días correspondientes según parámetros LAR
            $cacheKey = $anio . '_' . $relacion . '_' . $anti;
            
            if (isset($cacheParametros[$cacheKey])) {
                $dias_lar = $cacheParametros[$cacheKey];
            } else {
                $dias_lar = $this->obtenerDiasLarParametro($anio, $relacion, $anti, $legajo);
                $cacheParametros[$cacheKey] = $dias_lar;
            }

            // Calcular días tomados en este año por este empleado
            $diasTomados = Licencia::where('LegajoPersonal', $legajo)
                ->where('AnioLar', $anio)
                ->whereNull('Motivo_Id')
                ->whereNull('estado_om')
                ->sum('DiasTotal');

            return [
                'dias_correspondientes' => $dias_lar,
                'dias_tomados' => (int) $diasTomados,
                'dias_pendientes' => $dias_lar - $diasTomados,
                'antiguedad' => $anti,
            ];

        } catch (\Exception $e) {
            Log::error('Error calculando días LAR: ' . $e->getMessage());
            return [
                'dias_correspondientes' => null,
                'dias_tomados' => 0,
                'dias_pendientes' => null,
                'antiguedad' => null,
            ];
        }
    }

    /**
     * Obtener días LAR según parámetros
     */
    private function obtenerDiasLarParametro($anio, $relacion, $anti, $legajo)
    {
        // Buscar parámetro LAR según tipo de relación y año
        $paramLar = DB::table('parametros_lar as pl')
            ->where('AnioAPartir', '<=', $anio)
            ->where('RelacionPLar_Id', $relacion)
            ->orderBy('pl.AnioAPartir', 'desc')
            ->first();

        $dias_lar = 0;
        $id_param = null;

        if ($paramLar) {
            $id_param = $paramLar->IdParamLar;
            $detalle = DB::table('param_lar_detalle')
                ->where('ParamLar_Id', $id_param)
                ->where('DesdePL', '<=', $anti)
                ->where('HastaPL', '>', $anti)
                ->first();

            if ($detalle) {
                $dias_lar = $detalle->DiasLarPL;
            }
        } else {
            // Usar parámetro por defecto (RelacionPLar_Id = 0)
            $paramLarDefault = DB::table('parametros_lar as pl')
                ->where('AnioAPartir', '<=', $anio)
                ->where('RelacionPLar_Id', 0)
                ->orderBy('pl.AnioAPartir', 'desc')
                ->first();

            if ($paramLarDefault) {
                $id_param = $paramLarDefault->IdParamLar;
                $detalle = DB::table('param_lar_detalle')
                    ->where('ParamLar_Id', $id_param)
                    ->where('DesdePL', '<=', $anti)
                    ->where('HastaPL', '>', $anti)
                    ->first();

                if ($detalle) {
                    $dias_lar = $detalle->DiasLarPL;
                }
            }
        }

        // Caso especial: antigüedad 0 o 1 en el año actual con 6 meses o más
        if (($anti == 1 || $anti == 0) && (date('Y') == $anio)) {
            $mesesAlta = DB::table('empleados')
                ->where('Legajo', $legajo)
                ->selectRaw("TIMESTAMPDIFF(MONTH, FAltaAP, NOW()) AS DifMonth")
                ->first();

            if ($mesesAlta && $mesesAlta->DifMonth >= 6) {
                $detalle = DB::table('param_lar_detalle')
                    ->where('ParamLar_Id', $id_param ?? 0)
                    ->where('DesdePL', 1)
                    ->first();

                if ($detalle) {
                    $dias_lar = $detalle->DiasLarPL;
                }
            }
        }

        return $dias_lar;
    }

    public function getLicenciasXLegajo(Request $request)
    {
        try {
            $legajo = $request->legajo;

            \Log::info('getLicenciasXLegajo - Legajo: ' . $legajo);

            // Obtener el empleado con sus relaciones (solo si tiene idTipoRelacion)
            $empleado = Personal::with(['tipoRelacion' => function($query) {
                $query->select('idRelacion', 'Relacion');
            }])
                ->where('Legajo', $legajo)
                ->first();

            if (!$empleado) {
                \Log::info('Empleado no encontrado para legajo: ' . $legajo);
                return response()->json([
                    'success' => true,
                    'licencias' => [],
                    'licencias_lar' => [],
                    'lar' => [],
                    'info' => null
                ]);
            }

            \Log::info('Empleado encontrado: ' . $empleado->Apellido . ', ' . $empleado->Nombre);
            \Log::info('idTipoRelacion: ' . ($empleado->idTipoRelacion ?? 'null'));

            // Obtener licencias del empleado
            $licencias = Licencia::with(['motivo', 'usuario'])
                ->where('LegajoPersonal', $legajo)
                ->orderBy('FechaLic', 'desc')
                ->get()
                ->map(function ($lic) use ($empleado) {
                    $lic->FIni = Carbon::parse($lic->FechaLic)->format('d/m/Y');
                    $lic->FFin = $lic->FechaLicFin ? Carbon::parse($lic->FechaLicFin)->format('d/m/Y') : null;
                    $lic->FC = $lic->FechaCreacion ? Carbon::parse($lic->FechaCreacion)->format('d/m/Y') : null;
                    $lic->Apellido = $empleado->Apellido ?? '';
                    $lic->Nombre = $empleado->Nombre ?? '';
                    return $lic;
                });

            // Separar licencias normales de LAR
            $licenciasNormales = $licencias->where('Motivo_Id', '!==', null)->values();
            $licenciasLar = $licencias->where('Motivo_Id', null)->values();

            // Obtener parámetros LAR del empleado
            $parametrosLar = DB::table('config_lar as cl')
                ->where('cl.LegajoLarP', $legajo)
                ->orderBy('cl.Anio', 'desc')
                ->get();

            $larData = [];
            foreach ($parametrosLar as $param) {
                $larData[] = [
                    'IdConfigLar' => $param->IdConfigLar,
                    'Anio' => $param->Anio,
                    'Tomados' => $param->Tomados ?? 0,
                    'Total' => $param->Total
                ];
            }

            // Preparar información del empleado
            $info = null;
            if ($empleado) {
                $info = [
                    'idRelacion' => $empleado->idTipoRelacion ?? 0,
                    'Relacion' => $empleado->tipoRelacion ? $empleado->tipoRelacion->Relacion : 'Sin relación',
                    'FF' => $empleado->FAltaAP ? Carbon::parse($empleado->FAltaAP)->format('d/m/Y') : ''
                ];
            }

            \Log::info('getLicenciasXLegajo - Response:', [
                'licencias_lar_count' => count($licenciasLar),
                'lar_count' => count($larData),
                'info' => $info
            ]);

            return response()->json([
                'success' => true,
                'licencias' => $licenciasNormales,
                'licencias_lar' => $licenciasLar,
                'lar' => $larData,
                'info' => $info
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en getLicenciasXLegajo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function obtenerProximoCertificadoMedico()
    {
        try {
            $ultimoCertificado = Licencia::whereYear('FechaLic', now()->year)
                ->where('CertMedico', '!=', 0)
                ->orderBy('CertMedico', 'desc')
                ->value('CertMedico');

            $proximoNumero = ($ultimoCertificado ?? 0) + 1;

            return response()->json([
                'success' => true,
                'numero' => $proximoNumero
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener próximo número de certificado médico'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'legajo' => 'required|integer',
            'desde' => 'required|string',
            'hasta' => 'required|string',
            'dias' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        $legajo = (int) $request->input('legajo');
        $motivoId = $request->input('motivo_id');
        $desdeStr = $request->input('desde'); // DD/MM/YYYY
        $hastaStr = $request->input('hasta'); // DD/MM/YYYY
        $fechaOrden = $request->input('fechaOrden'); // YYYY-MM-DD
        $dias = (int) $request->input('dias');
        $obs = $request->input('obs');
        $anio = $request->input('anio');
        $cont = (int) $request->input('corrido', 0);
        $om = $request->input('om');
        $cm = $request->input('cm');
        $disp = $request->input('NumDisp');
        $disp2 = $request->input('NumDispPoster');
        $motPoster = $request->input('MotPoster');
        $estadoOm = $request->input('estado_om');

        try {
            $fini = Carbon::createFromFormat('d/m/Y', $desdeStr)->format('Y-m-d');
            $ffin = Carbon::createFromFormat('d/m/Y', $hastaStr)->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Formato de fecha inválido']);
        }

        // Validación de solapamiento (rango)
        $overlap = Licencia::where('LegajoPersonal', $legajo)
            ->where(function ($q) use ($fini, $ffin) {
                $q->whereBetween('FechaLic', [$fini, $ffin])
                  ->orWhereBetween('FechaLicFin', [$fini, $ffin])
                  ->orWhere(function ($q2) use ($fini, $ffin) {
                      $q2->where('FechaLic', '<=', $fini)
                         ->where('FechaLicFin', '>=', $ffin);
                  });
            })
            ->exists();

        if ($overlap) {
            return response()->json(['success' => false, 'message' => 'Ya existe una licencia cargada en esta fecha']);
        }

        // Validación OM único por año
        if (!empty($om) && !empty($anio)) {
            $existsOm = Licencia::where('OrdenMedica', $om)->where('AnioLar', $anio)->exists();
            if ($existsOm) {
                return response()->json(['success' => false, 'message' => "Ya existe una orden médica con el número $om para el año $anio"]);
            }
        }

        // Validación de límite de días LAR
        if (empty($motivoId) && !empty($anio)) {
            // Es una LAR, validar días disponibles
            $tomados = DB::table('licencias')
                ->where('LegajoPersonal', $legajo)
                ->where('AnioLar', $anio)
                ->whereNull('Motivo_Id')
                ->whereNull('estado_om')
                ->sum('DiasTotal');

            // Obtener el límite de días LAR para este empleado y año
            $sqlAntiguedad = "SELECT idTipoRelacion,
                {$anio} - YEAR(FAltaAP) + IF(DATE_FORMAT('{$anio}-12-31','%m-%d') > DATE_FORMAT(FAltaAP,'%m-%d'), 0, -1) AS Antiguedad
                FROM empleados
                WHERE Legajo = {$legajo}";

            $resultado = DB::selectOne($sqlAntiguedad);

            if ($resultado && $resultado->Antiguedad !== null) {
                $anti = (int) $resultado->Antiguedad;
                $relacion = $resultado->idTipoRelacion;

                $paramLar = DB::table('parametros_lar as pl')
                    ->where('AnioAPartir', '<=', $anio)
                    ->where('RelacionPLar_Id', $relacion)
                    ->orderBy('pl.AnioAPartir', 'desc')
                    ->first();

                $dias_lar = 0;

                if ($paramLar) {
                    $id_param = $paramLar->IdParamLar;
                    $detalle = DB::table('param_lar_detalle')
                        ->where('ParamLar_Id', $id_param)
                        ->where('DesdePL', '<=', $anti)
                        ->where('HastaPL', '>', $anti)
                        ->first();

                    if ($detalle) {
                        $dias_lar = $detalle->DiasLarPL;
                    }
                } else {
                    $paramLarDefault = DB::table('parametros_lar as pl')
                        ->where('AnioAPartir', '<=', $anio)
                        ->where('RelacionPLar_Id', 0)
                        ->orderBy('pl.AnioAPartir', 'desc')
                        ->first();

                    if ($paramLarDefault) {
                        $id_param = $paramLarDefault->IdParamLar;
                        $detalle = DB::table('param_lar_detalle')
                            ->where('ParamLar_Id', $id_param)
                            ->where('DesdePL', '<=', $anti)
                            ->where('HastaPL', '>', $anti)
                            ->first();

                        if ($detalle) {
                            $dias_lar = $detalle->DiasLarPL;
                        }
                    }
                }

                // Caso especial: antigüedad 0 o 1 en el año actual
                if (($anti == 1 || $anti == 0) && (date('Y') == $anio)) {
                    $mesesAlta = DB::table('empleados')
                        ->where('Legajo', $legajo)
                        ->selectRaw("TIMESTAMPDIFF(MONTH, FAltaAP, NOW()) AS DifMonth")
                        ->first();

                    if ($mesesAlta && $mesesAlta->DifMonth < 6) {
                        $dias_lar = 0;
                    }
                }

                $pendiente = $dias_lar - $tomados;

                if ($dias > $pendiente) {
                    return response()->json([
                        'success' => false,
                        'message' => "Excede el límite de días LAR. Días disponibles: {$pendiente}, Días solicitados: {$dias}"
                    ]);
                }
            }
        }

        try {
            DB::beginTransaction();

            $licencia = Licencia::create([
                'LegajoPersonal' => $legajo,
                'Motivo_Id' => $motivoId,
                'FechaLic' => $fini,
                'FechaLicFin' => $ffin,
                'DiasTotal' => $dias,
                'FechaCreacion' => $fechaOrden ?? Carbon::now()->format('Y-m-d'),
                'Cont' => $cont,
                'OrdenMedica' => $om ?? 0,
                'CertMedico' => $cm ?? 0,
                'estado_om' => $estadoOm,
                'ObservacionLic' => $obs,
                'NumDisp' => $disp,
                'NumDispPoster' => $disp2,
                'AnioLar' => $anio,
                'MotPoster' => $motPoster ?? 0,
                'Creador_Id' => session('usuario_id'),
            ]);

            // Generación de días en dia_x_lic
            $start = Carbon::parse($fini);
            $end = Carbon::parse($ffin);
            $rows = [];
            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                $rows[] = [
                    'Lic_Id' => $licencia->IdLicencia,
                    'Dia' => $date->format('Y-m-d'),
                ];
            }
            if (!empty($rows)) {
                DB::table('dia_x_lic')->insert($rows);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Licencia creada correctamente', 'data' => $licencia]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'legajo' => 'required|integer',
            'desde' => 'required|string',
            'hasta' => 'required|string',
            'dias' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        $legajo = (int) $request->input('legajo');
        $motivoId = $request->input('motivo_id');
        $desdeStr = $request->input('desde');
        $hastaStr = $request->input('hasta');
        $fechaOrden = $request->input('fechaOrden');
        $dias = (int) $request->input('dias');
        $obs = $request->input('obs');
        $anio = $request->input('anio');
        $cont = (int) $request->input('corrido', 0);
        $om = $request->input('om');
        $cm = $request->input('cm');
        $disp = $request->input('NumDisp');
        $disp2 = $request->input('NumDispPoster');
        $motPoster = $request->input('MotPoster');
        $estadoOm = $request->input('estado_om');

        try {
            $fini = Carbon::createFromFormat('d/m/Y', $desdeStr)->format('Y-m-d');
            $ffin = Carbon::createFromFormat('d/m/Y', $hastaStr)->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Formato de fecha inválido']);
        }

        // Validación de solapamiento excluyendo el actual
        $overlap = Licencia::where('LegajoPersonal', $legajo)
            ->where('IdLicencia', '!=', $id)
            ->where(function ($q) use ($fini, $ffin) {
                $q->whereBetween('FechaLic', [$fini, $ffin])
                  ->orWhereBetween('FechaLicFin', [$fini, $ffin])
                  ->orWhere(function ($q2) use ($fini, $ffin) {
                      $q2->where('FechaLic', '<=', $fini)
                         ->where('FechaLicFin', '>=', $ffin);
                  });
            })
            ->exists();

        if ($overlap) {
            return response()->json(['success' => false, 'message' => 'Ya existe una licencia cargada en esta fecha']);
        }

        // Validación OM único por año
        if (!empty($om) && !empty($anio)) {
            $existsOm = Licencia::where('OrdenMedica', $om)->where('AnioLar', $anio)->where('IdLicencia', '!=', $id)->exists();
            if ($existsOm) {
                return response()->json(['success' => false, 'message' => "Ya existe una orden médica con el número $om para el año $anio"]);
            }
        }

        // Validación de límite de días LAR
        if (empty($motivoId) && !empty($anio)) {
            // Obtener la licencia actual para restar sus días de los ya tomados
            $licenciaActual = Licencia::find($id);
            $diasActuales = $licenciaActual ? $licenciaActual->DiasTotal : 0;

            // Es una LAR, validar días disponibles
            $tomados = DB::table('licencias')
                ->where('LegajoPersonal', $legajo)
                ->where('AnioLar', $anio)
                ->whereNull('Motivo_Id')
                ->whereNull('estado_om')
                ->where('IdLicencia', '!=', $id)
                ->sum('DiasTotal');

            // Obtener el límite de días LAR para este empleado y año
            $sqlAntiguedad = "SELECT idTipoRelacion,
                {$anio} - YEAR(FAltaAP) + IF(DATE_FORMAT('{$anio}-12-31','%m-%d') > DATE_FORMAT(FAltaAP,'%m-%d'), 0, -1) AS Antiguedad
                FROM empleados
                WHERE Legajo = {$legajo}";

            $resultado = DB::selectOne($sqlAntiguedad);

            if ($resultado && $resultado->Antiguedad !== null) {
                $anti = (int) $resultado->Antiguedad;
                $relacion = $resultado->idTipoRelacion;

                $paramLar = DB::table('parametros_lar as pl')
                    ->where('AnioAPartir', '<=', $anio)
                    ->where('RelacionPLar_Id', $relacion)
                    ->orderBy('pl.AnioAPartir', 'desc')
                    ->first();

                $dias_lar = 0;

                if ($paramLar) {
                    $id_param = $paramLar->IdParamLar;
                    $detalle = DB::table('param_lar_detalle')
                        ->where('ParamLar_Id', $id_param)
                        ->where('DesdePL', '<=', $anti)
                        ->where('HastaPL', '>', $anti)
                        ->first();

                    if ($detalle) {
                        $dias_lar = $detalle->DiasLarPL;
                    }
                } else {
                    $paramLarDefault = DB::table('parametros_lar as pl')
                        ->where('AnioAPartir', '<=', $anio)
                        ->where('RelacionPLar_Id', 0)
                        ->orderBy('pl.AnioAPartir', 'desc')
                        ->first();

                    if ($paramLarDefault) {
                        $id_param = $paramLarDefault->IdParamLar;
                        $detalle = DB::table('param_lar_detalle')
                            ->where('ParamLar_Id', $id_param)
                            ->where('DesdePL', '<=', $anti)
                            ->where('HastaPL', '>', $anti)
                            ->first();

                        if ($detalle) {
                            $dias_lar = $detalle->DiasLarPL;
                        }
                    }
                }

                // Caso especial: antigüedad 0 o 1 en el año actual
                if (($anti == 1 || $anti == 0) && (date('Y') == $anio)) {
                    $mesesAlta = DB::table('empleados')
                        ->where('Legajo', $legajo)
                        ->selectRaw("TIMESTAMPDIFF(MONTH, FAltaAP, NOW()) AS DifMonth")
                        ->first();

                    if ($mesesAlta && $mesesAlta->DifMonth < 6) {
                        $dias_lar = 0;
                    }
                }

                $pendiente = $dias_lar - $tomados;

                if ($dias > $pendiente) {
                    return response()->json([
                        'success' => false,
                        'message' => "Excede el límite de días LAR. Días disponibles: {$pendiente}, Días solicitados: {$dias}"
                    ]);
                }
            }
        }

        try {
            DB::beginTransaction();

            $licencia = Licencia::findOrFail($id);
            $licencia->update([
                'LegajoPersonal' => $legajo,
                'Motivo_Id' => $motivoId,
                'FechaLic' => $fini,
                'FechaLicFin' => $ffin,
                'DiasTotal' => $dias,
                'FechaCreacion' => $fechaOrden ?? Carbon::now()->format('Y-m-d'),
                'Cont' => $cont,
                'OrdenMedica' => $om ?? 0,
                'CertMedico' => $cm ?? 0,
                'estado_om' => $estadoOm,
                'ObservacionLic' => $obs,
                'NumDisp' => $disp,
                'NumDispPoster' => $disp2,
                'AnioLar' => $anio,
                'MotPoster' => $motPoster ?? 0,
                'Creador_Id' => session('usuario_id'),
            ]);

            // Regenerar días en dia_x_lic
            DB::table('dia_x_lic')->where('Lic_Id', $licencia->IdLicencia)->delete();
            $start = Carbon::parse($fini);
            $end = Carbon::parse($ffin);
            $rows = [];
            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                $rows[] = [
                    'Lic_Id' => $licencia->IdLicencia,
                    'Dia' => $date->format('Y-m-d'),
                ];
            }
            if (!empty($rows)) {
                DB::table('dia_x_lic')->insert($rows);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Licencia actualizada correctamente', 'data' => $licencia]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            DB::table('dia_x_lic')->where('Lic_Id', $id)->delete();
            Licencia::findOrFail($id)->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Licencia eliminada correctamente']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // Print functions
    public function imprimirLar($id)
    {
        $licencia = Licencia::with([
            'personal.tipoRelacion', 
            'disposicion', 
            'disposicionPoster',
            'creador'
        ])->findOrFail($id);
        
        // Verificar que sea una LAR (Motivo_Id null)
        if ($licencia->Motivo_Id !== null) {
            return redirect()->back()->with('error', 'Esta licencia no es una LAR');
        }
        
        // Obtener la leyenda correspondiente al año de la LAR
        $leyenda = \App\Models\LeyendaAnual::getPorAnio($licencia->AnioLar);
        
        // Verificar si DomPDF está disponible
        if (!class_exists('Barryvdh\DomPDF\Facade\Pdf') && !class_exists('Barryvdh\DomPDF\PDF')) {
            return response()->json([
                'success' => false,
                'message' => 'El generador de PDF no está disponible. Contacte al administrador para instalar la librería DomPDF.'
            ], 500);
        }
        
        try {
            // Generar PDF con DomPDF
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('prints.lar', compact('licencia', 'leyenda'));
            $pdf->setPaper('A4', 'portrait');
            
            $nombreArchivo = 'Disposicion_LAR_' . $licencia->personal->Apellido . '_' . $licencia->AnioLar . '.pdf';
            
            return $pdf->stream($nombreArchivo);
        } catch (\Exception $e) {
            Log::error('Error generando PDF LAR: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    public function imprimirCD($id)
    {
        $licencia = Licencia::with(['personal', 'motivo', 'disposicion'])->findOrFail($id);
        return view('prints.cd', compact('licencia'));
    }

    public function imprimirArticulo30($id)
    {
        $licencia = Licencia::with(['personal', 'motivo', 'disposicion'])->findOrFail($id);
        return view('prints.articulo30', compact('licencia'));
    }

    public function imprimirArticulo43($id)
    {
        $licencia = Licencia::with(['personal', 'motivo', 'disposicion'])->findOrFail($id);
        return view('prints.articulo43', compact('licencia'));
    }

    // Date calculation functions
    public function calcularFecha(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dias' => 'required|integer|min:1',
            'desde' => 'required|string',
            'corridos' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        try {
            $desde = Carbon::createFromFormat('d/m/Y', $request->desde);
            $dias = $request->dias;
            $corridos = (int) $request->input('corridos', 0);

            // Usar la función calcularXDia que replica exactamente la lógica del sistema viejo
            $hastaStr = Feriado::calcularXDia($desde->format('Y-m-d'), $corridos, $dias);

            // Convertir de d/m/Y a Carbon para el formato de respuesta
            $hasta = Carbon::createFromFormat('d/m/Y', $hastaStr);

            return response()->json([
                'success' => true,
                'hasta' => $hasta->format('d/m/Y')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al calcular la fecha: ' . $e->getMessage()
            ]);
        }
    }

    public function calcularDias(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'desde' => 'required|string',
            'hasta' => 'required|string',
            'corridos' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        try {
            $desde = Carbon::createFromFormat('d/m/Y', $request->desde);
            $hasta = Carbon::createFromFormat('d/m/Y', $request->hasta);
            $corridos = (int) $request->input('corridos', 0);

            if ($desde->gt($hasta)) {
                return response()->json([
                    'success' => false,
                    'message' => 'La fecha desde no puede ser mayor a la fecha hasta'
                ]);
            }

            // Usar la función calcularXFecha que replica exactamente la lógica del sistema viejo
            $dias = Feriado::calcularXFecha($desde->format('Y-m-d'), $hasta->format('Y-m-d'), $corridos);

            return response()->json([
                'success' => true,
                'dias' => $dias
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al calcular los días: ' . $e->getMessage()
            ]);
        }
    }

    // LAR days calculation - REPLICA EXACTA DEL SISTEMA ORIGINAL
    public function getDiasLar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'legajo' => 'required|integer',
            'anio' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        try {
            $legajo = $request->legajo;
            $anio = $request->anio;

            // Calcular antigüedad usando la MISMA consulta SQL que el sistema original
            // $anio - YEAR(FAltaAP) + IF(DATE_FORMAT('$anio-12-31','%m-%d') > DATE_FORMAT(FAltaAP,'%m-%d'), 0, -1) AS Antiguedad
            $sqlAntiguedad = "SELECT idTipoRelacion, 
                {$anio} - YEAR(FAltaAP) + IF(DATE_FORMAT('{$anio}-12-31','%m-%d') > DATE_FORMAT(FAltaAP,'%m-%d'), 0, -1) AS Antiguedad 
                FROM empleados 
                WHERE Legajo = {$legajo}";
            
            $resultado = DB::selectOne($sqlAntiguedad);

            if (!$resultado) {
                return response()->json(['success' => false, 'message' => 'Empleado no encontrado']);
            }

            // Verificar que la antigüedad no sea NULL (FAltaAP vacío o inválido)
            if ($resultado->Antiguedad === null) {
                return response()->json([
                    'success' => false, 
                    'message' => 'No se puede calcular la antigüedad porque el empleado no tiene fecha de alta cargada. Por favor, cargue la fecha de alta en la Administración Pública y vuelva a calcular.'
                ]);
            }

            $anti = (int) $resultado->Antiguedad;
            $relacion = $resultado->idTipoRelacion;

            // Buscar parámetro LAR según tipo de relación y año
            $paramLar = DB::table('parametros_lar as pl')
                ->where('AnioAPartir', '<=', $anio)
                ->where('RelacionPLar_Id', $relacion)
                ->orderBy('pl.AnioAPartir', 'desc')
                ->first();

            $dias_lar = 0;

            if ($paramLar) {
                $id_param = $paramLar->IdParamLar;
                $detalle = DB::table('param_lar_detalle')
                    ->where('ParamLar_Id', $id_param)
                    ->where('DesdePL', '<=', $anti)
                    ->where('HastaPL', '>', $anti)
                    ->first();

                if ($detalle) {
                    $dias_lar = $detalle->DiasLarPL;
                }
            } else {
                // Usar parámetro por defecto (RelacionPLar_Id = 0)
                $paramLarDefault = DB::table('parametros_lar as pl')
                    ->where('AnioAPartir', '<=', $anio)
                    ->where('RelacionPLar_Id', 0)
                    ->orderBy('pl.AnioAPartir', 'desc')
                    ->first();

                if ($paramLarDefault) {
                    $id_param = $paramLarDefault->IdParamLar;
                    $detalle = DB::table('param_lar_detalle')
                        ->where('ParamLar_Id', $id_param)
                        ->where('DesdePL', '<=', $anti)
                        ->where('HastaPL', '>', $anti)
                        ->first();

                    if ($detalle) {
                        $dias_lar = $detalle->DiasLarPL;
                    }
                }
            }

            // Caso especial: antigüedad 0 o 1 en el año actual con 6 meses o más
            if (($anti == 1 || $anti == 0) && (date('Y') == $anio)) {
                $mesesAlta = DB::table('empleados')
                    ->where('Legajo', $legajo)
                    ->selectRaw("TIMESTAMPDIFF(MONTH, FAltaAP, NOW()) AS DifMonth, FAltaAP")
                    ->first();

                if ($mesesAlta && $mesesAlta->DifMonth >= 6) {
                    // Buscar parámetro LAR con DesdePL = 1 (primer año)
                    $detalle = DB::table('param_lar_detalle')
                        ->where('ParamLar_Id', $id_param ?? 0)
                        ->where('DesdePL', 1)
                        ->first();

                    if ($detalle) {
                        $dias_lar = $detalle->DiasLarPL;
                    }
                    $anti = 0;
                } else {
                    $dias_lar = 0;
                    $anti = 0;
                }
            }

            // Calcular días tomados
            $tomados = DB::table('licencias')
                ->where('LegajoPersonal', $legajo)
                ->where('AnioLar', $anio)
                ->whereNull('Motivo_Id')
                ->whereNull('estado_om')
                ->sum('DiasTotal');

            if ($dias_lar === null) $dias_lar = 0;

            // Retornar en formato original: [antiguedad, dias_lar, tomados]
            return response()->json([
                'success' => true,
                'response' => [$anti, $dias_lar, $tomados]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // Medical certificate generation
    public function getCertificadoMedico(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'legajo' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        try {
            $legajo = $request->legajo;

            // Get the next medical certificate number
            $ultimoCertificado = DB::table('certificados_medicos')
                ->where('legajo', $legajo)
                ->orderBy('numero', 'desc')
                ->first();

            $numeroSiguiente = $ultimoCertificado ? $ultimoCertificado->numero + 1 : 1;

            // Save the new certificate number
            DB::table('certificados_medicos')->insert([
                'legajo' => $legajo,
                'numero' => $numeroSiguiente,
                'fecha' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            return response()->json([
                'success' => true,
                'numero' => $numeroSiguiente
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // Get license history with proper formatting
    public function getHistorial($legajo)
    {
        try {
            $licencias = Licencia::with(['motivo', 'disposicion'])
                ->where('LegajoPersonal', $legajo)
                ->orderBy('FechaCreacion', 'desc')
                ->get()
                ->map(function ($licencia) {
                    return [
                        'IdLicencia' => $licencia->IdLicencia,
                        'FechaOrden' => $licencia->FechaCreacion,
                        'Motivo_Id' => $licencia->Motivo_Id,
                        'Dias' => $licencia->DiasTotal,
                        'Desde' => $licencia->FechaLic,
                        'Hasta' => $licencia->FechaLicFin,
                        'Anio' => $licencia->AnioLar,
                        'Obs' => $licencia->ObservacionLic,
                        'motivo' => $licencia->motivo,
                        'disposicion' => $licencia->disposicion
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $licencias
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // Get license history by personal ID (instead of legajo)
    public function getHistorialByPersonalId($personalId)
    {
        try {
            // First get the legajo from the personal ID
            $personal = Personal::find($personalId);

            if (!$personal) {
                return response()->json(['success' => false, 'message' => 'Personal no encontrado']);
            }

            $licencias = Licencia::with(['motivo', 'disposicion'])
                ->where('LegajoPersonal', $personal->Legajo)
                ->orderBy('FechaCreacion', 'desc')
                ->get()
                ->map(function ($licencia) use ($personal) {
                    return [
                        'IdLicencia' => $licencia->IdLicencia,
                        'FechaOrden' => $licencia->FechaCreacion ? Carbon::parse($licencia->FechaCreacion)->format('d/m/Y') : '',
                        'Motivo_Id' => $licencia->Motivo_Id,
                        'Dias' => $licencia->DiasTotal,
                        'Desde' => $licencia->FechaLic ? Carbon::parse($licencia->FechaLic)->format('d/m/Y') : '',
                        'Hasta' => $licencia->FechaLicFin ? Carbon::parse($licencia->FechaLicFin)->format('d/m/Y') : '',
                        'Anio' => $licencia->AnioLar,
                        'Obs' => $licencia->ObservacionLic,
                        'motivo' => $licencia->motivo,
                        'disposicion' => $licencia->disposicion,
                        // Agregar información del personal
                        'Legajo' => $personal->Legajo,
                        'NombreCompleto' => $personal->Apellido . ', ' . $personal->Nombre,
                        'DNI' => $personal->DNI
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $licencias,
                'personal' => [
                    'Legajo' => $personal->Legajo,
                    'NombreCompleto' => $personal->Apellido . ', ' . $personal->Nombre,
                    'DNI' => $personal->DNI
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // Vista de Orden Médicas
    public function ordenMedicas()
    {
        $permisos = PermisoHelper::obtenerPermisos(session('usuario_id'), 'orden-medicas');
        $personal = Personal::all();
        $motivos = MotivoLicencia::all();
        $disposiciones = Disposicion::all();
        return view('orden-medicas', compact('permisos', 'personal', 'motivos', 'disposiciones'));
    }

    // Listado de Orden Médicas (filtrar por año opcional)
    public function getOrdenMed(Request $request)
    {
        $request->validate([
            'anio' => 'nullable|integer|min:1900|max:3000',
        ]);

        $anio = $request->input('anio');

        $subquery = "(SELECT SUM(l2.DiasTotal) FROM licencias l2 WHERE l2.Motivo_Id = lic.Motivo_Id AND l2.LegajoPersonal = lic.LegajoPersonal AND l2.estado_om IS NOT NULL";
        if (!empty($anio)) {
            $subquery .= " AND l2.FechaCreacion BETWEEN '" . $anio . "-01-01' AND '" . $anio . "-12-31'";
        }
        $subquery .= ")";

        $query = DB::table('licencias as lic')
            ->join('empleados as emp', 'emp.Legajo', '=', 'lic.LegajoPersonal')
            ->join('tiporelacion', 'emp.idTipoRelacion', '=', 'tiporelacion.idRelacion')
            ->leftJoin('motivo_licencia as ml', 'ml.IdMotivoLicencia', '=', 'lic.Motivo_Id')
            ->leftJoin('usuarios as usr', 'usr.IdUsuario', '=', 'lic.Creador_Id')
            ->where('lic.OrdenMedica', '>', 0)
            ->whereNotNull('lic.estado_om')
            ->select([
                'lic.IdLicencia as id',
                DB::raw("DATE_FORMAT(lic.FechaCreacion, '%d/%m/%Y') as fecha"),
                'lic.OrdenMedica as num',
                'lic.FechaCreacion',
                'lic.AnioLar as anio',
                'emp.Apellido as apellido',
                'emp.Nombre as nombre',
                'emp.DNI as dni',
                'emp.Legajo as legajo',
                'lic.estado_om',
                DB::raw('TIMESTAMPDIFF(YEAR, emp.FecNac, CURDATE()) AS edad'),
                DB::raw("DATE_FORMAT(lic.FechaLic, '%d/%m/%Y') as desde"),
                DB::raw("DATE_FORMAT(lic.FechaLicFin, '%d/%m/%Y') as hasta"),
                'lic.DiasTotal as dias',
                'lic.ObservacionLic',
                'lic.imagen_ficha',
                'ml.Motivo as motivo',
                'lic.Motivo_Id as motivo_id',
                'lic.Cont',
                'tiporelacion.Relacion',
                DB::raw("DATE_FORMAT(emp.FAlta, '%d/%m/%Y') as FF"),
                DB::raw("CONCAT(COALESCE(usr.Nombre, ''), ' ', COALESCE(usr.Apellido, '')) as usuario_creador"),
                DB::raw($subquery . ' AS total_dias_por_motivo_legajo_year'),
            ]);

        if (!empty($anio)) {
            $query->whereBetween('lic.FechaCreacion', [$anio . '-01-01', $anio . '-12-31']);
        }

        $result = $query->orderBy('lic.AnioLar', 'desc')
            ->orderBy('lic.OrdenMedica', 'desc')
            ->get();

        return response()->json($result);
    }

    // Obtener el último número de OM para un año dado (sugerencia de próximo número)
    public function getUltimoNumeroOM(Request $request)
    {
        $request->validate([
            'anio' => 'required|integer|min:1900|max:3000',
        ]);

        $anio = (int) $request->input('anio');
        $max = DB::table('licencias')
            ->where('AnioLar', $anio)
            ->where('OrdenMedica', '>', 0)
            ->max('OrdenMedica');

        $siguiente = ($max ?? 0) + 1;
        return response()->json(['anio' => $anio, 'ultimo' => $max ?? 0, 'sugerido' => $siguiente]);
    }

    // Obtener detalle de una OM por ID
    public function getOrdenMedById($id)
    {
        $row = DB::table('licencias as lic')
            ->leftJoin('empleados as emp', 'emp.Legajo', '=', 'lic.LegajoPersonal')
            ->leftJoin('motivo_licencia as ml', 'ml.IdMotivoLicencia', '=', 'lic.Motivo_Id')
            ->select([
                'lic.IdLicencia',
                'lic.LegajoPersonal',
                'lic.Motivo_Id',
                'lic.FechaLic as Desde',
                'lic.FechaLicFin as Hasta',
                'lic.DiasTotal',
                'lic.FechaCreacion',
                'lic.Cont',
                'lic.OrdenMedica',
                'lic.CertMedico',
                'lic.estado_om',
                'lic.ObservacionLic',
                'lic.AnioLar',
                'ml.Motivo'
            ])
            ->where('lic.IdLicencia', $id)
            ->first();

        if (!$row) {
            return response()->json(['success' => false, 'message' => 'OM no encontrada'], 404);
        }

        return response()->json($row);
    }

    /**
     * Obtener días tomados por motivo y legajo en el año actual
     * Se llama al seleccionar un motivo en el formulario
     */
    public function getDiasMotivoXLegajo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'legajo' => 'required|integer',
            'motivo' => 'nullable|integer'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        try {
            $legajo = $request->legajo;
            $motivoId = $request->motivo;

            // Calcular días totales del motivo en el año actual
            $diasTotales = Licencia::where('LegajoPersonal', $legajo)
                ->where('Motivo_Id', $motivoId)
                ->whereYear('FechaLic', now()->year)
                ->sum('DiasTotal');

            return response()->json([
                'success' => true,
                'response' => $diasTotales
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Crear parámetros de LAR para un empleado
     * Configura los días totales de LAR disponibles para un año específico
     */
    public function createParam(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'legajo' => 'required|integer',
            'anio' => 'required|integer|min:1900|max:3000',
            'total' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        try {
            $legajo = $request->legajo;
            $anio = $request->anio;
            $total = $request->total;

            // Verificar si ya existe parámetro para este legajo y año
            $existe = DB::table('config_lar')
                ->where('LegajoLarP', $legajo)
                ->where('Anio', $anio)
                ->first();

            if ($existe) {
                return response()->json([
                    'success' => false,
                    'message' => "Ya existe un parámetro LAR para el legajo $legajo en el año $anio"
                ]);
            }

            // Crear el parámetro LAR
            DB::table('config_lar')->insert([
                'LegajoLarP' => $legajo,
                'Anio' => $anio,
                'Total' => $total,
                'Tomados' => 0,
                'Creador_Id' => session('usuario_id'),
                'FechaCreacion' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Parámetro LAR creado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Eliminar parámetros de LAR
     */
    public function deleteParam($id)
    {
        try {
            $deleted = DB::table('config_lar')
                ->where('IdConfigLar', $id)
                ->delete();

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parámetro no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Parámetro LAR eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Obtener parámetros LAR de un empleado
     */
    public function getParametrosLar($legajo)
    {
        try {
            // Buscar en la tabla config_lar
            $parametros = DB::table('config_lar as cl')
                ->where('cl.LegajoLarP', $legajo)
                ->orderBy('cl.Anio', 'desc')
                ->get();

            $result = [];
            foreach ($parametros as $param) {
                // Calcular los días tomados sumando las LAR reales para este año
                // Las LAR se identifican por: Motivo_Id IS NULL y AnioLar = [año]
                $tomados = DB::table('licencias')
                    ->where('LegajoPersonal', $legajo)
                    ->where('AnioLar', $param->Anio)
                    ->whereNull('Motivo_Id')
                    ->sum('DiasTotal');

                $result[] = [
                    'IdConfigLar' => $param->IdConfigLar,
                    'Anio' => $param->Anio,
                    'Total' => (int)$param->Total,
                    'Tomados' => (int)$tomados
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
