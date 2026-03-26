<?php

namespace App\Http\Controllers;

use App\Helpers\PermisoHelper;
use App\Models\Departamento;
use App\Models\Empleado;
use App\Models\Gerencia;
use App\Models\Servicio;
use App\Models\Usuario;
use App\Models\ClasificacionPersonal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Throwable;

class ControlHorariosController extends Controller
{
    public function index(Request $request)
    {
        $usuarioId = $request->session()->get('usuario_id');
        $usuario = Usuario::find($usuarioId);
        $permisos = PermisoHelper::obtenerPermisos($usuarioId, $request->path());
//dd($permisos);
        $todoPersonal = $permisos['todo_personal_control'] ?? ($permisos['extras']['todo_personal_control'] ?? 0);

        $gerencias = Gerencia::query()->orderBy('Gerencia')->get(['idGerencia', 'Gerencia']);

        // Valores por defecto del organigrama del usuario
        $usuarioDefaults = $this->obtenerDatosUsuario($usuarioId) ?? ['idGerencia' => 0, 'idDepartamento' => 0, 'idServicio' => 0];

        $servicioDefault = null;
        
        // Si NO tiene permiso para ver todo, solo mostrar su servicio
        if (!$todoPersonal && !empty($usuarioDefaults['idServicio'])) {
            $servicioDefault = $usuarioDefaults['idServicio'];
            $servicios = Servicio::query()
                ->where('idServicio', $servicioDefault)
                ->orderBy('Servicio')
                ->get(['idServicio', 'Servicio']);
        } else {
            // Si tiene permiso, mostrar todos los servicios
            $servicios = Servicio::query()->orderBy('Servicio')->get(['idServicio', 'Servicio']);
        }

        $personalFull = $this->buildPersonalAutocomplete(true, $usuarioId);
        $personalLimit = $this->buildPersonalAutocomplete(false, $usuarioId);
        $clasificaciones = ClasificacionPersonal::orderBy('orden')->get();

        return view('control-horarios', [
            'usuario' => $usuario,
            'permisos' => $permisos,
            'gerencias' => $gerencias,
            'servicios' => $servicios,
            'personalFull' => $personalFull,
            'personalLimit' => $personalLimit,
            'todoPersonalControl' => $todoPersonal,
            'usuarioDefaults' => $usuarioDefaults,
            'servicioDefault' => $servicioDefault,
            'clasificaciones' => $clasificaciones
        ]);
    }

    public function listar(Request $request)
    {
        $data = $request->validate([
            'desde' => ['required', 'string'],
            'hasta' => ['required', 'string'],
            'tipo' => ['nullable', 'string'],
            'idEmpleado' => ['nullable', 'integer'],
            'idServicio' => ['nullable', 'integer'],
            'ger' => ['nullable', 'integer'],
            'dep' => ['nullable', 'integer'],
            'serv' => ['nullable', 'integer'],
            'clasificacion' => ['nullable', 'integer'],
            'id' => ['nullable', 'string'],
        ]);

        $usuarioId = $request->session()->get('usuario_id');
        $permisos = PermisoHelper::obtenerPermisos($usuarioId, 'control-horarios');

        if (!($permisos['leer'] ?? false)) {
            return response()->json(['status' => 0, 'error' => 'Sin permiso para leer'], 403);
        }

        $data['tipo'] = $data['tipo'] ?? '0';
        $data['idEmpleado'] = $data['idEmpleado'] ?? 0;
        $data['idServicio'] = $data['idServicio'] ?? 0;
        $data['ger'] = $data['ger'] ?? 0;
        $data['dep'] = $data['dep'] ?? 0;
        $data['serv'] = $data['serv'] ?? 0;
        $data['clasificacion'] = $data['clasificacion'] ?? 0;

        // Si se proporciona idServicio, usarlo para filtrar
        if (!empty($data['idServicio']) && $data['idServicio'] != 0) {
            $data['serv'] = $data['idServicio'];
        }

        $todoPersonal = $permisos['todo_personal_control'] ?? ($permisos['extras']['todo_personal_control'] ?? 0);
        if ((int) $todoPersonal === 0 && ($data['serv'] ?? 0) === 0) {
            $datosUsuario = $this->obtenerDatosUsuario($usuarioId);
            if (!$datosUsuario) {
                return response()->json(['status' => 0, 'error' => 'No se pudieron obtener los datos del usuario.'], 400);
            }
            $data['ger'] = $datosUsuario['idGerencia'] ?? 0;
            $data['dep'] = $datosUsuario['idDepartamento'] ?? 0;
            $data['serv'] = $datosUsuario['idServicio'] ?? 0;
        }

        try {
            $resultado = $this->controlHorarios($data);

            // DEBUG TEMPORAL - Guardar en archivo para ver qué devuelve
            $debugFile = storage_path('logs/debug_control_horarios.json');
            file_put_contents($debugFile, json_encode([
                'timestamp' => now()->toDateTimeString(),
                'input_data' => $data,
                'resultado' => $resultado,
                'tiene_empleados' => isset($resultado['empleados']),
                'tiene_fechas' => isset($resultado['fechas']),
                'cant_empleados' => isset($resultado['empleados']) ? count($resultado['empleados']) : 0,
                'cant_fechas' => isset($resultado['fechas']) ? count($resultado['fechas']) : 0,
            ], JSON_PRETTY_PRINT));

            // Headers para evitar cache
            return response()
                ->json(['status' => 1, 'response' => $resultado])
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
        } catch (Throwable $e) {
            Log::error('Error en listar control-horarios', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['status' => 0, 'error' => $e->getMessage()], 400);
        }
    }

    public function actualizarMarca(Request $request)
    {
        $data = $request->validate([
            'e0' => ['nullable', 'string'],
            'e1' => ['nullable', 'string'],
            's0' => ['nullable', 'string'],
            's1' => ['nullable', 'string'],
            'ds0' => ['nullable', 'boolean'],
            'ds1' => ['nullable', 'boolean'],
            'fecha' => ['required', 'string'],
            'legajo' => ['required', 'string'],
        ]);

        $usuarioId = $request->session()->get('usuario_id');
        $permisos = PermisoHelper::obtenerPermisos($usuarioId, 'control-horarios');
        if (!($permisos['crear'] ?? false) && !($permisos['editar'] ?? false)) {
            return response()->json(['status' => 0, 'error' => 'Sin permiso para actualizar'], 403);
        }

        try {
            $this->guardarMarca($data);
            return response()->json(['status' => 1, 'message' => 'Se guardó correctamente']);
        } catch (Throwable $e) {
            Log::error('Error al actualizar marca', ['error' => $e->getMessage()]);
            return response()->json(['status' => 0, 'error' => $e->getMessage()], 400);
        }
    }

    public function departamentos(int $id)
    {
        $departamentos = Departamento::query()
            ->where('idGerencia', $id)
            ->orderBy('Departamento')
            ->get(['idDepartamento', 'Departamento']);

        return response()->json(['status' => 1, 'response' => $departamentos]);
    }

    public function servicios(int $id)
    {
        $servicios = Servicio::query()
            ->where('idDepartamento', $id)
            ->orderBy('Servicio')
            ->get(['idServicio', 'Servicio']);

        return response()->json(['status' => 1, 'response' => $servicios]);
    }

    public function exportarExcel(Request $request)
    {
        $data = $request->all();
        $usuarioId = $request->session()->get('usuario_id');
        $permisos = PermisoHelper::obtenerPermisos($usuarioId, 'control-horarios');
        if (!($permisos['leer'] ?? false)) {
            return response()->json(['status' => 0, 'error' => 'Sin permiso para exportar'], 403);
        }

        $data['desde'] = $data['desde'] ?? $request->query('desde');
        $data['hasta'] = $data['hasta'] ?? $request->query('hasta');
        if (empty($data['desde']) || empty($data['hasta'])) {
            return response()->json(['status' => 0, 'error' => 'Fechas requeridas'], 400);
        }

        $data['tipo'] = $data['tipo'] ?? '0';
        $data['idEmpleado'] = (int) ($data['idEmpleado'] ?? 0);
        $data['idServicio'] = (int) ($data['idServicio'] ?? 0);
        $data['ger'] = (int) ($data['ger'] ?? 0);
        $data['dep'] = (int) ($data['dep'] ?? 0);
        $data['serv'] = (int) ($data['serv'] ?? 0);

        // Si se proporciona idServicio, usarlo para filtrar
        if (!empty($data['idServicio']) && $data['idServicio'] != 0) {
            $data['serv'] = $data['idServicio'];
        }

        $todoPersonal = $permisos['todo_personal_control'] ?? ($permisos['extras']['todo_personal_control'] ?? 0);
        if ($todoPersonal === 0 && ($data['serv'] ?? 0) === 0) {
            $datosUsuario = $this->obtenerDatosUsuario($usuarioId);
            if ($datosUsuario) {
                $data['ger'] = $datosUsuario['idGerencia'] ?? 0;
                $data['dep'] = $datosUsuario['idDepartamento'] ?? 0;
                $data['serv'] = $datosUsuario['idServicio'] ?? 0;
            }
        }

        try {
            $rows = $this->controlHorariosExcel($data);
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $headers = ['Personal', 'Fecha', 'Lic/Fer', 'Prog. Turn. 1', 'Guardias', 'Marcas', 'Horas', 'Prog. Turn. 2', 'Marcas', 'Horas', 'Responsable', 'Situacion'];
            $sheet->fromArray($headers, null, 'A1');

            $rowNumber = 2;
            foreach ($rows as $row) {
                $situacion = '';
                if (isset($row[9])) {
                    if ($row[9] == 2) {
                        $situacion = 'Falta Dato';
                    } elseif ($row[9] == 1) {
                        $situacion = 'Ausente';
                    } elseif (!empty($row[1])) {
                        $situacion = 'Dia Justificado';
                    }
                }

                $fechaValor = '';
                if (isset($row[12]) && $row[12]) {
                    try {
                        $fechaValor = Carbon::createFromFormat('Y-m-d', $row[12])->format('d/m/Y');
                    } catch (Throwable $e) {
                        $fechaValor = $row[12];
                    }
                }

                $sheet->fromArray([
                    $row[0] ?? '',
                    $fechaValor,
                    $row[1] ?? '',
                    $row[2] ?? '',
                    $row[3] ?? '',
                    $row[4] ?? '',
                    $row[5] ?? '',
                    $row[6] ?? '',
                    $row[7] ?? '',
                    $row[8] ?? '',
                    $row[11] ?? '',
                    $situacion,
                ], null, 'A' . $rowNumber);

                $rowNumber++;
            }

            $writer = new Xlsx($spreadsheet);

            $response = new StreamedResponse(function () use ($writer) {
                $writer->save('php://output');
            });

            $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            $response->headers->set('Content-Disposition', 'attachment;filename="control-horarios.xlsx"');
            $response->headers->set('Cache-Control', 'max-age=0');

            return $response;
        } catch (Throwable $e) {
            Log::error('Error exportando control horarios', ['error' => $e->getMessage()]);
            return response()->json(['status' => 0, 'error' => $e->getMessage()], 400);
        }
    }

    public function exportarPdf(Request $request)
    {
        $data = $request->all();
        $usuarioId = $request->session()->get('usuario_id');
        $permisos = PermisoHelper::obtenerPermisos($usuarioId, 'control-horarios');
        if (!($permisos['leer'] ?? false)) {
            return response()->json(['status' => 0, 'error' => 'Sin permiso para exportar'], 403);
        }

        $data['desde'] = $data['desde'] ?? $request->query('desde');
        $data['hasta'] = $data['hasta'] ?? $request->query('hasta');
        if (empty($data['desde']) || empty($data['hasta'])) {
            return response()->json(['status' => 0, 'error' => 'Fechas requeridas'], 400);
        }

        $data['tipo'] = $data['tipo'] ?? '0';
        $data['idEmpleado'] = (int) ($data['idEmpleado'] ?? 0);
        $data['idServicio'] = (int) ($data['idServicio'] ?? 0);
        $data['ger'] = (int) ($data['ger'] ?? 0);
        $data['dep'] = (int) ($data['dep'] ?? 0);
        $data['serv'] = (int) ($data['serv'] ?? 0);

        // Si se proporciona idServicio, usarlo para filtrar
        if (!empty($data['idServicio']) && $data['idServicio'] != 0) {
            $data['serv'] = $data['idServicio'];
        }

        $todoPersonal = $permisos['todo_personal_control'] ?? ($permisos['extras']['todo_personal_control'] ?? 0);
        if ($todoPersonal === 0 && ($data['serv'] ?? 0) === 0) {
            $datosUsuario = $this->obtenerDatosUsuario($usuarioId);
            if ($datosUsuario) {
                $data['ger'] = $datosUsuario['idGerencia'] ?? 0;
                $data['dep'] = $datosUsuario['idDepartamento'] ?? 0;
                $data['serv'] = $datosUsuario['idServicio'] ?? 0;
            }
        }

        try {
            $rows = $this->controlHorariosExcel($data);
            
            $pdf = app('dompdf.wrapper');
            $pdf->loadView('pdf.control-horarios', [
                'rows' => $rows,
                'desde' => $data['desde'],
                'hasta' => $data['hasta']
            ]);
            
            return $pdf->download('control-horarios.pdf');
        } catch (Throwable $e) {
            Log::error('Error exportando PDF control horarios', ['error' => $e->getMessage()]);
            return response()->json(['status' => 0, 'error' => $e->getMessage()], 400);
        }
    }

    private function buildPersonalAutocomplete(bool $full, int $usuarioId)
    {
        $query = Empleado::query()
            ->where('Estado', 1)
            ->select([
                'idEmpleado',
                'Legajo',
                'Apellido',
                'Nombre',
                'IdEmpleado2',
                DB::raw("CONCAT(Legajo,' - ',Apellido,' ',Nombre) as value"),
            ])
            ->orderBy('Apellido')
            ->orderBy('Nombre');

        if (!$full) {
            $usuario = Usuario::find($usuarioId);
            if ($usuario && $usuario->Personal_Id) {
                $query->where('IdEmpleado2', $usuario->Personal_Id);
            }
        }

        return $query->get();
    }

    private function obtenerDatosUsuario(int $usuarioId): ?array
    {
        // Primero intentar obtener desde empleado_servicio (nuevo sistema)
        $resultado = DB::table('usuarios')
            ->leftJoin('empleados as emp', 'emp.idEmpleado', '=', 'usuarios.Personal_Id')
            ->leftJoin('empleado_servicio as es', function($join) {
                $join->on('es.empleado_id', '=', 'usuarios.Personal_Id')
                     ->where('es.activo', '=', true);
            })
            ->where('IdUsuario', $usuarioId)
            ->select('usuarios.Personal_Id', 'emp.idGerencia', 'emp.idDepartamento', 
                DB::raw('COALESCE(es.servicio_id, emp.idServicio) as idServicio'))
            ->first();

        return $resultado ? (array) $resultado : null;
    }

    private function controlHorarios(array $data): array
    {
        Log::info('CONTROL HORARIOS - MÉTODO EJECUTÁNDOSE', [
            'desde' => $data['desde'] ?? null,
            'hasta' => $data['hasta'] ?? null,
            'serv' => $data['serv'] ?? null,
            'ger' => $data['ger'] ?? null,
        ]);

        if (empty($data['desde'])) {
            throw new \Exception('Seleccione un periodo');
        }

        $fIni = Carbon::createFromFormat('d/m/Y', $data['desde'])->startOfDay();
        $fFin = Carbon::createFromFormat('d/m/Y', $data['hasta'])->endOfDay();

        // Obtener datos para todo el período
        $marcas = $this->getMarcas($fIni, $fFin, $data['idEmpleado'], $data['ger'], $data['dep'], $data['serv']);
        $licencias = $this->getLicencias($fIni, $fFin);
        $programacion = $this->getProgramacion($fIni, $fFin, $data['idEmpleado'], $data['ger'], $data['dep'], $data['serv']);
        $feriado = $this->getFeriados($fIni, $fFin);
        $guardias = $this->getGuardias($fIni, $fFin);

        // Obtener lista única de empleados para todo el período
        $empleadosUnicos = $this->obtenerEmpleadosUnicos($data, $fIni, $fFin);

        Log::info('CONTROL HORARIOS - Empleados únicos encontrados', ['count' => $empleadosUnicos->count()]);

        $resultado = [];

        // Generar en orden: para cada fecha, primero la fila de fecha, luego los empleados de esa fecha
        // Esto garantiza el orden correcto sin depender de ordenamiento alfabético
        $fechaCursor = $fIni->copy();
        while ($fechaCursor->lessThanOrEqualTo($fFin)) {
            $fechaKey = $fechaCursor->format('Y-m-d');
            $fechaFormateada = $fechaCursor->format('d/m');
            $fechaDM = $fechaCursor->format('d-m'); // Para el índice 12

            // 1. Agregar fila de fecha vacía (dia_v_)
            $filaFechaVacia = [
                '_key' => '0v_' . $fechaKey,
                '_tipo' => 'fecha_vacia',
                0 => '&nbsp;',
            ];
            $resultado[] = $filaFechaVacia;

            // 2. Agregar fila de fecha para esta fecha
            // Usamos formato especial "0_" para que se ordene primero si llega a ordenarse
            $filaFecha = [
                '_key' => '0_' . $fechaKey,
                '_tipo' => 'fecha',
                0 => $fechaFormateada, // "01/01"
                1 => '',
                2 => '',
                3 => '',
                4 => '',
                5 => '',
            ];
            $resultado[] = $filaFecha;

            // 3. Agregar empleados para esta fecha
            foreach ($empleadosUnicos as $empleado) {
                $arrayKey = $fechaKey . '_' . $empleado->Legajo;

                $datosCelda = $this->procesarCelda(
                    $fechaKey,
                    $empleado,
                    $marcas[$fechaKey] ?? [],
                    $licencias[$fechaKey] ?? [],
                    $programacion[$fechaKey] ?? [],
                    $feriado[$fechaKey] ?? null,
                    $guardias[$fechaKey] ?? [],
                    $data['tipo']
                );

                if ($datosCelda !== null) {
                    $filaEmpleado = [
                        '_key' => $arrayKey,
                        '_tipo' => 'empleado',
                        0 => $empleado->Legajo . ' - ' . $empleado->Apellido . ' ' . $empleado->Nombre,
                        1 => $datosCelda['lic_fer'] ?? '',
                        2 => $datosCelda['prog1'] ?? '',
                        3 => $datosCelda['guardia'] ?? '',
                        4 => $datosCelda['marcas1'] ?? '',
                        5 => $datosCelda['horas1'] ?? '',
                        6 => $datosCelda['prog2'] ?? '',
                        7 => $datosCelda['marcas2'] ?? '',
                        8 => $datosCelda['horas2'] ?? '',
                        9 => $datosCelda['tipo_marca'] ?? 0,
                        // Campos adicionales del sistema viejo
                        10 => $empleado->idEmpleado,
                        11 => $empleado->Apellido . ' ' . $empleado->Nombre,
                        12 => $fechaDM,
                    ];
                    $resultado[] = $filaEmpleado;
                }
            }

            $fechaCursor->addDay();
        }

        return $resultado;
    }

    /**
     * Obtiene la lista única de empleados para todo el período
     */
    private function obtenerEmpleadosUnicos(array $data, Carbon $fIni, Carbon $fFin)
    {
        // Límite de seguridad para evitar sobrecarga
        $limite = 500;

        if (!empty($data['serv']) && $data['serv'] != 0) {
            $query = Empleado::porServicio($data['serv'], ['e.idEmpleado', 'e.Apellido', 'e.Nombre', 'e.Legajo', 'e.IdEmpleado2']);
        } else {
            $query = Empleado::query()
                ->where('Estado', 1)
                ->select('idEmpleado', 'Apellido', 'Nombre', 'Legajo', 'IdEmpleado2');
        }

        $useAlias = (!empty($data['serv']) && $data['serv'] != 0);
        $empCol = $useAlias ? 'e.idEmpleado' : 'idEmpleado';
        $gerCol = $useAlias ? 'e.idGerencia' : 'idGerencia';
        $depCol = $useAlias ? 'e.idDepartamento' : 'idDepartamento';

        if (!empty($data['idEmpleado']) && $data['idEmpleado'] != 0) {
            $query->where($empCol, $data['idEmpleado']);
        }
        if (!empty($data['ger']) && $data['ger'] != 0) {
            $query->where($gerCol, $data['ger']);
        }
        if (!empty($data['dep']) && $data['dep'] != 0) {
            $query->where($depCol, $data['dep']);
        }

        return $query->orderBy($useAlias ? 'e.Apellido' : 'Apellido')
            ->orderBy($useAlias ? 'e.Nombre' : 'Nombre')
            ->limit($limite)
            ->get()
            ->unique('idEmpleado')  // Evitar duplicados
            ->values();
    }

    /**
     * Procesa los datos de una celda (empleado en una fecha específica)
     */
    private function procesarCelda(
        string $fechaKey,
        $empleado,
        array $marcasDia,
        array $licenciasDia,
        array $programacionDia,
        $feriadoDia,
        array $guardiasDia,
        string $tipoFiltro
    ): ?array {
        $indiceEmpleado = $empleado->idEmpleado;

        $marcasPersonal = $marcasDia[$indiceEmpleado][$empleado->Legajo] ?? [
            ['Legajo' => $empleado->Legajo, 'Nombre' => $empleado->Nombre, 'Apellido' => $empleado->Apellido, 'idEmpleado' => $empleado->idEmpleado, 'Entrada' => '-01:00:00', 'Salida' => '-01:00:00'],
        ];

        $programacionPersonal = $programacionDia[$indiceEmpleado][$empleado->Legajo] ?? null;
        $licenciaPersonal = $licenciasDia[$indiceEmpleado][$empleado->Legajo] ?? null;
        $guardiaPersonal = $guardiasDia[$indiceEmpleado][$empleado->Legajo] ?? null;

        $tipoMarca = 0;
        $tieneLicencia = false;
        $tieneProgramacion = false;

        // Licencia/Feriado
        $licFer = '';
        if ($feriadoDia !== null) {
            $licFer = $feriadoDia[0]['Feriado'];
        } elseif ($licenciaPersonal !== null && isset($licenciaPersonal[0])) {
            $licFer = $licenciaPersonal[0]['Motivo'] ?? ('LAR: ' . ($licenciaPersonal[0]['AnioLar'] ?? ''));
            $tieneLicencia = true;
        }

        // Programación turno 1
        $prog1 = '';
        if ($programacionPersonal !== null && isset($programacionPersonal[0])) {
            $prog1 = $programacionPersonal[0]['Entrada'] . ' - ' . $programacionPersonal[0]['Salida'];
            $tieneProgramacion = true;
        }

        // Guardias
        $guardia = '';
        if ($guardiaPersonal !== null && isset($guardiaPersonal[0])) {
            $guardia = $guardiaPersonal[0]['Entrada'] . ' - ' . $guardiaPersonal[0]['Salida'];
            $tieneProgramacion = true;
        }

        // Marcas turno 1
        $marcas1 = '';
        $horas1 = '';
        if (isset($marcasPersonal[0])) {
            if (!$feriadoDia && !$tieneLicencia && $marcasPersonal[0]['Entrada'] === '-01:00:00' && $marcasPersonal[0]['Salida'] === '-01:00:00' && $tieneProgramacion) {
                // Ausencia (sin marcas pero con programación)
                $tipoMarca = 1;
            } elseif ($marcasPersonal[0]['Entrada'] === '-01:00:00' && $marcasPersonal[0]['Salida'] === '-01:00:00') {
                // Sin datos
            } else {
                $entrada = $marcasPersonal[0]['Entrada'] === '-01:00:00' ? 'SIN DATO' : $marcasPersonal[0]['Entrada'];
                $salida = $marcasPersonal[0]['Salida'] === '-01:00:00' ? 'SIN DATO' : $marcasPersonal[0]['Salida'];
                $marcas1 = trim($entrada . ' - ' . $salida);
                $horas1 = $marcasPersonal[0]['Dif'] ?? '';

                // Marca incompleta
                if (($marcasPersonal[0]['Entrada'] === '-01:00:00' && $marcasPersonal[0]['Salida'] !== '-01:00:00') ||
                    ($marcasPersonal[0]['Salida'] === '-01:00:00' && $marcasPersonal[0]['Entrada'] !== '-01:00:00')) {
                    $tipoMarca = 2;
                }
            }
        } elseif (!$feriadoDia && !$tieneLicencia && $tieneProgramacion) {
            $tipoMarca = 1;
        }

        // Programación turno 2
        $prog2 = '';
        if ($programacionPersonal !== null && isset($programacionPersonal[1])) {
            $prog2 = $programacionPersonal[1]['Entrada'] . ' - ' . $programacionPersonal[1]['Salida'];
        }

        // Marcas turno 2
        $marcas2 = '';
        $horas2 = '';
        if (isset($marcasPersonal[1])) {
            $entrada2 = $marcasPersonal[1]['Entrada'] === '-01:00:00' ? 'SIN DATO' : $marcasPersonal[1]['Entrada'];
            $salida2 = $marcasPersonal[1]['Salida'] === '-01:00:00' ? 'SIN DATO' : $marcasPersonal[1]['Salida'];
            $marcas2 = trim($entrada2 . ' - ' . $salida2);
            $horas2 = $marcasPersonal[1]['Dif'] ?? '';

            if (($entrada2 === 'SIN DATO' xor $salida2 === 'SIN DATO')) {
                $tipoMarca = 2;
            }
        }

        // Aplicar filtro por tipo si está activo
        if ($tipoFiltro !== '0') {
            $acepta = false;
            if ($tipoFiltro === '1' && $tipoMarca === 1) {
                $acepta = true;
            }
            if ($tipoFiltro === '4' && $tipoMarca === 2) {
                $acepta = true;
            }
            if ($tipoFiltro === '5' && ($tipoMarca === 1 || $tipoMarca === 2)) {
                $acepta = true;
            }
            if (!$acepta) {
                return null;
            }
        }

        return [
            'lic_fer' => $licFer,
            'prog1' => $prog1,
            'guardia' => $guardia,
            'marcas1' => $marcas1,
            'horas1' => $horas1,
            'prog2' => $prog2,
            'marcas2' => $marcas2,
            'horas2' => $horas2,
            'tipo_marca' => $tipoMarca,
            'tiene_licencia' => $tieneLicencia,
            'key' => $fechaKey . '_' . $empleado->Legajo,
        ];
    }

    private function controlHorariosExcel(array $data): array
    {
        $resultado = $this->controlHorarios($data);
        return $resultado;
    }

    private function getMarcas(Carbon $fIni, Carbon $fFin, int $idEmp, int $ger, int $dep, int $serv): array
    {
        $sql = "SELECT mr.Estado, emp.Legajo, emp.IdEmpleado2, emp.idEmpleado, Entrada, EXtra, emp.Apellido, emp.Nombre, Salida, FechaMarca, Dif
            FROM marcas_reloj mr
            INNER JOIN marcas_x_dia mxd ON mxd.Marca_Id = mr.IdMarcaReloj
            INNER JOIN empleados emp ON emp.idEmpleado = mr.PersonalMarca_Id
            WHERE emp.Estado = 1 AND FechaMarca BETWEEN ? AND ?";

        $params = [$fIni->format('Y-m-d'), $fFin->format('Y-m-d')];
        if ($idEmp !== 0) {
            $sql .= ' AND emp.idEmpleado = ?';
            $params[] = $idEmp;
        }
        if ($ger !== 0) {
            $sql .= ' AND emp.idGerencia = ?';
            $params[] = $ger;
        }
        if ($dep !== 0) {
            $sql .= ' AND emp.idDepartamento = ?';
            $params[] = $dep;
        }
        if ($serv !== 0) {
            // Filtrar por servicio: buscar en idServicio (legacy) O en empleado_servicio (nuevo)
            $sql .= ' AND (emp.idServicio = ? OR EXISTS (
                SELECT 1 FROM empleado_servicio 
                WHERE empleado_servicio.empleado_id = emp.idEmpleado 
                AND empleado_servicio.servicio_id = ? 
                AND empleado_servicio.activo = 1
            ))';
            $params[] = $serv;
            $params[] = $serv;
        }

        $sql .= ' ORDER BY FechaMarca, emp.Apellido, emp.Nombre, IF(Entrada="-01:00:00","25:00:00",Entrada)';

        $rows = DB::select($sql, $params);
        $dataQuery = [];
        foreach ($rows as $row) {
            $reg = (array) $row;
            $dataQuery[$reg['FechaMarca']][(int) $reg['idEmpleado']][(int) $reg['Legajo']][] = $reg;
        }

        return $dataQuery;
    }

    private function getLicencias(Carbon $fIni, Carbon $fFin): array
    {
        $sql = "SELECT emp.Legajo, emp.IdEmpleado2, AnioLar, emp.idEmpleado, Dia, Motivo, DATE_FORMAT(Dia, '%d/%m/%Y') AS DF
            FROM licencias lic
            LEFT JOIN motivo_licencia ml ON ml.IdMotivoLicencia = lic.Motivo_Id
            INNER JOIN dia_x_lic dxl ON dxl.Lic_Id = lic.IdLicencia
            INNER JOIN empleados emp ON emp.Legajo = lic.LegajoPersonal
            WHERE Dia BETWEEN ? AND ?
            ORDER BY Dia, emp.Apellido, emp.Nombre";

        $rows = DB::select($sql, [$fIni->format('Y-m-d'), $fFin->format('Y-m-d')]);
        $dataQuery = [];
        foreach ($rows as $row) {
            $reg = (array) $row;
            $dataQuery[$reg['Dia']][(int) $reg['idEmpleado']][(int) $reg['Legajo']][] = $reg;
        }

        return $dataQuery;
    }

    private function getProgramacion(Carbon $fIni, Carbon $fFin, int $idEmp, int $ger, int $dep, int $serv): array
    {
        $sql = "SELECT emp.Legajo, emp.IdEmpleado2, emp.idEmpleado, hr.FechaRot, Horario_Id, DATE_FORMAT(h.entrada, '%H:%i') AS Entrada, DATE_FORMAT(h.Salida, '%H:%i') AS Salida
            FROM horarios_rotativos hr
            INNER JOIN empleados emp ON emp.idEmpleado = hr.EmpleadoRot_Id
            INNER JOIN horarios_rot_x_personal hrxp ON hrxp.HorarioRot_Id = hr.IdHorarioRotativo
            INNER JOIN horarios h ON h.IdHorario = hrxp.Horario_Id
            WHERE emp.Estado = 1 AND FechaRot BETWEEN ? AND ?";

        $params = [$fIni->format('Y-m-d'), $fFin->format('Y-m-d')];
        if ($idEmp !== 0) {
            $sql .= ' AND emp.idEmpleado = ?';
            $params[] = $idEmp;
        }
        if ($ger !== 0) {
            $sql .= ' AND emp.idGerencia = ?';
            $params[] = $ger;
        }
        if ($dep !== 0) {
            $sql .= ' AND emp.idDepartamento = ?';
            $params[] = $dep;
        }
        if ($serv !== 0) {
            // Filtrar por servicio: buscar en idServicio (legacy) O en empleado_servicio (nuevo)
            $sql .= ' AND (emp.idServicio = ? OR EXISTS (
                SELECT 1 FROM empleado_servicio 
                WHERE empleado_servicio.empleado_id = emp.idEmpleado 
                AND empleado_servicio.servicio_id = ? 
                AND empleado_servicio.activo = 1
            ))';
            $params[] = $serv;
            $params[] = $serv;
        }

        $sql .= ' ORDER BY FechaRot, emp.Apellido, emp.Nombre';

        $rows = DB::select($sql, $params);
        $dataQuery = [];
        foreach ($rows as $row) {
            $reg = (array) $row;
            $dataQuery[$reg['FechaRot']][(int) $reg['idEmpleado']][(int) $reg['Legajo']][] = $reg;
        }

        return $dataQuery;
    }

    private function getFeriados(Carbon $fIni, Carbon $fFin): array
    {
        $rows = DB::table('feriados')
            ->whereNull('FechaEliminacion')
            ->whereBetween('FechaFer', [$fIni->format('Y-m-d'), $fFin->format('Y-m-d')])
            ->get();

        $dataQuery = [];
        foreach ($rows as $row) {
            $dataQuery[$row->FechaFer][] = (array) $row;
        }

        return $dataQuery;
    }

    private function getGuardias(Carbon $fIni, Carbon $fFin): array
    {
        $sql = "SELECT emp.Legajo, emp.IdEmpleado2, emp.idEmpleado, FechaGuard, DATE_FORMAT(h.entrada, '%H:%i') AS Entrada, DATE_FORMAT(h.Salida, '%H:%i') AS Salida
            FROM guardias g
            INNER JOIN horarios h ON h.IdHorario = g.HorarioGuar_Id
            INNER JOIN empleados emp ON emp.idEmpleado = g.EmpleadoGuar_Id
            WHERE FechaGuard BETWEEN ? AND ?
            ORDER BY FechaGuard, emp.Apellido, emp.Nombre";

        $rows = DB::select($sql, [$fIni->format('Y-m-d'), $fFin->format('Y-m-d')]);
        $dataQuery = [];
        foreach ($rows as $row) {
            $reg = (array) $row;
            $dataQuery[$reg['FechaGuard']][(int) $reg['idEmpleado']][(int) $reg['Legajo']][] = $reg;
        }

        return $dataQuery;
    }

    private function obtenerEmpleadosParaDia(array $data, Carbon $fecha)
    {
        if (!empty($data['serv']) && $data['serv'] != 0) {
            // Usar helper que consulta ambas tablas (empleado_servicio + idServicio legacy)
            // El helper ya filtra por servicio, Estado y FBaja
            $query = Empleado::porServicio($data['serv'], ['e.idEmpleado', 'e.Apellido', 'e.Nombre', 'e.Legajo', 'e.IdEmpleado2', 'e.idClasificacion']);
        } else {
            $query = Empleado::query()->where('Estado', 1)->select('idEmpleado', 'Apellido', 'Nombre', 'Legajo', 'IdEmpleado2', 'idClasificacion');
        }

        // Para la consulta porServicio, necesitamos usar el alias 'e' para las columnas
        $useAlias = (!empty($data['serv']) && $data['serv'] != 0);
        $empCol = $useAlias ? 'e.idEmpleado' : 'idEmpleado';
        $gerCol = $useAlias ? 'e.idGerencia' : 'idGerencia';
        $depCol = $useAlias ? 'e.idDepartamento' : 'idDepartamento';
        $clasifCol = $useAlias ? 'e.idClasificacion' : 'idClasificacion';
        $apellidoCol = $useAlias ? 'e.Apellido' : 'Apellido';
        $nombreCol = $useAlias ? 'e.Nombre' : 'Nombre';

        if (!empty($data['idEmpleado']) && $data['idEmpleado'] != 0) {
            $query->where($empCol, $data['idEmpleado']);
        }
        if (!empty($data['ger']) && $data['ger'] != 0) {
            $query->where($gerCol, $data['ger']);
        }
        if (!empty($data['dep']) && $data['dep'] != 0) {
            $query->where($depCol, $data['dep']);
        }
        if (!empty($data['clasificacion']) && $data['clasificacion'] != 0) {
            $query->where($clasifCol, $data['clasificacion']);
        }

        return $query->orderBy($apellidoCol)->orderBy($nombreCol)->get();
    }

    private function guardarMarca(array $data): void
    {
        $empleado = DB::table('empleados')->where('Legajo', $data['legajo'])->first();
        if (!$empleado) {
            throw new \Exception('Legajo no encontrado');
        }

        DB::transaction(function () use ($data, $empleado) {
            $marca = DB::table('marcas_reloj')
                ->where('FechaMarca', $data['fecha'])
                ->where('PersonalMarca_Id', $empleado->idEmpleado)
                ->first();

            if ($marca) {
                DB::table('marcas_reloj')->where('IdMarcaReloj', $marca->IdMarcaReloj)->update(['Estado' => 0]);
                DB::table('marcas_x_dia')->where('Marca_Id', $marca->IdMarcaReloj)->delete();
                $marcaId = $marca->IdMarcaReloj;
            } else {
                $marcaId = DB::table('marcas_reloj')->insertGetId([
                    'Estado' => 0,
                    'Legajo' => '',
                    'PersonalMarca_Id' => $empleado->idEmpleado,
                    'FechaMarca' => $data['fecha'],
                ]);
            }

            $pares = [
                ['e' => $data['e0'] ?? '', 's' => $data['s0'] ?? '', 'ds' => (bool) ($data['ds0'] ?? false)],
                ['e' => $data['e1'] ?? '', 's' => $data['s1'] ?? '', 'ds' => (bool) ($data['ds1'] ?? false)],
            ];

            foreach ($pares as $par) {
                if ($par['e'] === '' && $par['s'] === '') {
                    DB::table('marcas_x_dia')->insert([
                        'Entrada' => '-01:00:00',
                        'Salida' => '-01:00:00',
                        'Marca_Id' => $marcaId,
                        'Dif' => '0',
                    ]);
                    continue;
                }

                $entrada = $par['e'] !== '' ? $par['e'] . ':00' : '-01:00:00';
                $salida = $par['s'] !== '' ? $par['s'] . ':00' : '-01:00:00';
                $dif = '0';

                if ($par['e'] !== '' && $par['s'] !== '') {
                    $inicio = Carbon::createFromFormat('H:i:s', $entrada);
                    $fin = Carbon::createFromFormat('H:i:s', $salida);
                    if ($par['ds']) {
                        $fin->addDay();
                    }
                    $dif = $inicio->diff($fin)->format('%H:%I:%S');
                }

                DB::table('marcas_x_dia')->insert([
                    'Entrada' => $entrada,
                    'Salida' => $salida,
                    'Marca_Id' => $marcaId,
                    'Dif' => $dif,
                ]);
            }
        });
    }
}
