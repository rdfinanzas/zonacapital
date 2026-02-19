<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Empleado;
use App\Models\Departamento;
use App\Models\Servicio;
use App\Models\Sector;
use App\Models\Gerencia;
use App\Models\Provincia;
use App\Models\Localidad;
use App\Models\EstadoCivil;
use App\Models\Estado;
use App\Models\Profesion;
use App\Models\Funcion;
use App\Models\Instruccion;
use App\Models\TipoRelacion;
use App\Models\TipoJornada;
use App\Models\MotivoBaja;
use App\Models\Pais;
use App\Models\DocumentoEscaneado;
use App\Models\HistorialRelacion;
use App\Models\HistorialModPers;
use App\Models\JornadaXEmp;
use App\Models\Agrupamiento;
use App\Models\Categoria;
use App\Models\Cargo;
use App\Helpers\PermisoHelper;
use App\Helpers\LogHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PersonalController extends Controller
{
    /**
     * Display a listing of the personnel records.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Get the user ID from session
        $usuarioId = session('usuario_id');

        // Get permissions for this view
        $permisos = PermisoHelper::obtenerPermisos($usuarioId, 'personal');

        // Get all data for dropdown filters
        $gerencias = Gerencia::orderBy('Gerencia')->get();
        $profesiones = Profesion::orderBy('profesion')->get();
        $funciones = Funcion::orderBy('Funcion')->get();
        $estados = Estado::orderBy('estado')->get();

        // Get additional filter data
        $jefes = Empleado::where('idCargo', '!=', 0)
            ->whereNotNull('idCargo')
            ->orderBy('Apellido')
            ->orderBy('Nombre')
            ->get(['idEmpleado', 'Apellido', 'Nombre', 'Legajo']);

        $cargos = Cargo::orderBy('cargo')->get(['idCargo', 'cargo']);

        return view('personal', [
            'permisos' => $permisos,
            'gerencias' => $gerencias,
            'profesiones' => $profesiones,
            'funciones' => $funciones,
            'estados' => $estados,
            'jefes' => $jefes,
            'cargos' => $cargos
        ]);
    }

    /**
     * Show a single personnel record in a read-only view
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Get the user ID from session

        $usuarioId = session('usuario_id');

        // Get permissions for this view - use 'personal' instead of the full path
        $permisos = PermisoHelper::obtenerPermisos($usuarioId, 'personal');

        // Check if user has permission to read
        if (!($permisos['leer'] ?? false)) {
            return response('No tiene permisos para ver este contenido.', 403);
        }

        // Get the employee data directly
        $empleadoData = $this->getById($id);

        $empleado = null;

        if ($empleadoData instanceof \Illuminate\Http\JsonResponse) {
            $responseData = $empleadoData->getData(true);
            if ($responseData['success'] && isset($responseData['data'])) {
                $empleado = $responseData['data'];
            }
        }


        return view('personal-ver', [
            'permisos' => $permisos,
            'empleadoId' => $id,
            'empleado' => $empleado
        ]);
    }

    /**
     * Get filtered personnel data with pagination
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getPersonal(Request $request)
    {
        // Get filter parameters
        $apellidoNombre = $request->input('apellido_nombre', '');
        $legajo = $request->input('legajo', '');
        $dni = $request->input('dni', '');
        $sexo = $request->input('sexo', 0);
        $edad = $request->input('edad', '');
        $profesion = $request->input('profesion', 0);
        $funcion = $request->input('funcion', 0);
        $gerencia = $request->input('gerencia', 0);
        $departamento = $request->input('departamento', 0);
        $servicio = $request->input('servicio', 0);
        $sector = $request->input('sector', 0);
        // Por defecto mostrar activos (id 1). Si el cliente envía 0 significa 'Todos'.
        $estado = $request->input('estado', 1);
        $jefe = $request->input('jefe', 0);
        $cargo = $request->input('cargo', 0);
        $pagina = $request->input('pagina', 1);
        $porPagina = $request->input('porPagina', 10);

        // Build query with relationships
        $query = Empleado::with([
            'gerencia',
            'departamento',
            'servicio',
            'sector',
            'serviciosActivos' => function ($query) {
                $query->with(['gerencia', 'departamento']);
            }
        ]);

        // Apply filters
        if (!empty($apellidoNombre)) {
            $query->where(function ($q) use ($apellidoNombre) {
                $q->where('Apellido', 'LIKE', "%{$apellidoNombre}%")
                    ->orWhere('Nombre', 'LIKE', "%{$apellidoNombre}%")
                    ->orWhereRaw("CONCAT(Apellido, ' ', Nombre) LIKE ?", ["%{$apellidoNombre}%"]);
            });
        }

        if (!empty($legajo)) {
            $query->where('Legajo', 'LIKE', "%{$legajo}%");
        }

        if (!empty($dni)) {
            $query->where('DNI', 'LIKE', "%{$dni}%");
        }

        if ($sexo > 0) {
            $query->where('sexo', $sexo);
        }

        if (!empty($edad)) {
            $query->whereRaw('TIMESTAMPDIFF(YEAR, FecNac, CURDATE()) = ?', [$edad]);
        }

        if ($profesion > 0) {
            $query->where('idProfesion', $profesion);
        }

        if ($funcion > 0) {
            $query->where('Funcion', $funcion);
        }

        if ($gerencia > 0) {
            $query->where('idGerencia', $gerencia);
        }

        if ($departamento > 0) {
            $query->where('idDepartamento', $departamento);
        }

        if ($servicio > 0) {
            $query->where('idServicio', $servicio);
        }

        if ($sector > 0) {
            $query->where('idSector', $sector);
        }

        if ($estado > 0) {
            $query->where('Estado', $estado);
        }

        if ($jefe > 0) {
            $query->where('IdEmpleado2', $jefe);
        }

        if ($cargo > 0) {
            $query->where('idCargo', $cargo);
        }

        // Order by apellido and nombre
        $query->orderBy('Apellido')->orderBy('Nombre');

        // Count total records (before pagination)
        $totalRegistros = $query->count();

        // Apply pagination
        $offset = ($pagina - 1) * $porPagina;
        $empleados = $query->skip($offset)->take($porPagina)->get();

        // Calculate total pages
        $totalPaginas = ceil($totalRegistros / $porPagina);

        // Format the data
        $formattedEmpleados = $empleados->map(function ($item) {
            // Obtener todos los servicios activos con su información jerárquica
            $serviciosActivos = [];
            if ($item->serviciosActivos && $item->serviciosActivos->count() > 0) {
                foreach ($item->serviciosActivos as $servicio) {
                    $serviciosActivos[] = [
                        'servicio' => $servicio->servicio ?? '-',
                        'gerencia' => $servicio->gerencia->Gerencia ?? '-',
                        'departamento' => $servicio->departamento->departamento ?? '-'
                    ];
                }
            }

            return [
                'idEmpleado' => $item->idEmpleado,
                'legajo' => $item->Legajo,
                'nombre_completo' => $item->Apellido . ', ' . $item->Nombre,
                'dni' => $item->DNI,
                'sexo' => $item->sexo == 1 ? 'M' : 'F',
                'edad' => $item->FecNac ? \Carbon\Carbon::parse($item->FecNac)->age : '-',
                'gerencia' => $item->gerencia->Gerencia ?? '-',
                'departamento' => $item->departamento->departamento ?? '-',
                'servicio' => $item->servicio->servicio ?? '-',
                'sector' => $item->sector->sector ?? '-',
                'servicios_activos' => $serviciosActivos,
                'estado' => $item->Estado == 1 ? 'Activo' : 'Inactivo',
                'fecha_alta' => $item->FAlta ? \Carbon\Carbon::parse($item->FAlta)->format('d/m/Y') : '-'
            ];
        });

        return response()->json([
            'data' => $formattedEmpleados,
            'totalRegistros' => $totalRegistros,
            'totalPaginas' => $totalPaginas,
            'paginaActual' => $pagina,
            'porPagina' => $porPagina
        ]);
    }

    /**
     * Get a single personnel record
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getById($id)
    {
        // Find the record with all relationships
        $empleado = Empleado::with([
            'gerencia',
            'departamento',
            'servicio',
            'sector',
            'provincia',
            'localidad',
            'estadoCivil',
            'profesion',
            'funcion',
            'instruccion',
            'tipoRelacion',
            'tipoJornada',
            'motivoBaja',
            'pais'
        ])->find($id);

        if (!$empleado) {
            return response()->json([
                'success' => false,
                'message' => 'Empleado no encontrado'
            ], 404);
        }

        // Get historial de relaciones
        $historialRelaciones = HistorialRelacion::with('tipoRelacion')
            ->where('Personal_Id', $id)
            ->orderBy('Desde', 'desc')
            ->get();

        // Get documentos escaneados
        $documentos = DocumentoEscaneado::where('Empleado_Id', $id)->get();

        // Get jornadas
        $jornadas = JornadaXEmp::with('tipoJornada')
            ->where('Emp_Id', $id)
            ->orderBy('FechaJor', 'desc')
            ->get();

        // Get historial de modificaciones personales (cambios de cargo, jefes, etc.)
        $historialModificaciones = HistorialModPers::with('usuario')
            ->where('EmpleadoMod_Id', $id)
            ->orderBy('FechaMod', 'desc')
            ->get();

        // Format the data with descriptive names
        $data = [
            'idEmpleado' => $empleado->idEmpleado,
            'legajo' => $empleado->Legajo,
            'apellido' => $empleado->Apellido,
            'nombre' => $empleado->Nombre,
            'dni' => $empleado->DNI,
            'cuit' => $empleado->cuit,
            'sexo' => $empleado->sexo,
            'fecha_nacimiento' => $this->formatearFechaSegura($empleado->FecNac),

            // Datos con nombres descriptivos en lugar de IDs
            'estado_civil' => $empleado->estadoCivil->EstadoCivil ?? 'Sin definir',
            'estado_civil_id' => $empleado->EstCiv,
            'nacionalidad' => $empleado->pais->Pais ?? 'Sin definir',
            'nacionalidad_id' => $empleado->Nacionalidad,
            'provincia' => $empleado->provincia->Provincia ?? 'Sin definir',
            'provincia_id' => $empleado->Provincia,
            'localidad' => $empleado->localidad->Localidad ?? 'Sin definir',
            'localidad_id' => $empleado->Localidad,
            'instruccion' => $empleado->instruccion->instruccion ?? 'Sin definir',
            'instruccion_id' => $empleado->idInstrucion,

            'cp' => $empleado->CP,
            'calle' => $empleado->calle,
            'calle_num' => $empleado->CalleNum,
            'piso' => $empleado->Piso,
            'departamento_dir' => $empleado->Departamento,
            'barrio' => $empleado->Barrio,
            'manzana' => $empleado->Manzana,
            'casa' => $empleado->Casa,
            'email' => $empleado->Email,
            'telefono' => $empleado->Telefono,
            'celular' => $empleado->Celular,

            // Información laboral con nombres descriptivos
            'profesion' => $empleado->profesion->profesion ?? 'Sin definir',
            'profesion_id' => $empleado->idProfesion,
            'funcion' => $empleado->funcion->Funcion ?? 'Sin definir',
            'funcion_id' => $empleado->Funcion,
            'tipo_tarea' => $empleado->instruccion->instruccion ?? 'Sin definir',
            'tipo_tarea_id' => $empleado->idInstrucion,
            'tipo_relacion' => $empleado->tipoRelacion->Relacion ?? 'Sin definir',
            'tipo_relacion_id' => $empleado->idTipoRelacion,

            // Jerarquía organizacional con nombres descriptivos
            'gerencia' => $empleado->gerencia->Gerencia ?? 'Sin asignar',
            'gerencia_id' => $empleado->idGerencia,
            'departamento' => $empleado->departamento->departamento ?? 'Sin asignar',
            'departamento_id' => $empleado->idDepartamento,
            'servicio' => $empleado->servicio->servicio ?? 'Sin asignar',
            'servicio_id' => $empleado->idServicio,
            'sector' => $empleado->sector->Sector ?? 'Sin asignar',
            'sector_id' => $empleado->idSector,

            'fecha_alta' => $this->formatearFechaSegura($empleado->FAlta),
            'fecha_baja' => $this->formatearFechaSegura($empleado->FBaja),
            'fecha_adm_publica' => $this->formatearFechaSegura($empleado->FAltaAP),
            'estado' => $empleado->Estado,
            'descripcion_baja' => $empleado->DescripcionBaja,
            'observacion' => $empleado->Observacion,
            // Jornada con nombre descriptivo
            'tipo_jornada' => $empleado->tipoJornada->Jornada ?? 'Sin definir',
            'tipo_jornada_id' => $empleado->Jornada_Id,
            'motivo_baja' => $empleado->motivoBaja->MotivoBaja ?? 'Sin definir',
            'motivo_baja_id' => $empleado->MotivoBaja_Id,

            'doble_fs' => $empleado->DobleFS,
            'nocturno' => $empleado->Nocturno,
            'fe' => $empleado->FE,
            'gremio' => $empleado->Gremio,
            'nro_contrato' => $empleado->NroContrato,
            'matricula' => $empleado->Matricula,

            // Campos que necesitan consultas adicionales
            'agrupamiento' => $this->getAgrupamientoNombre($empleado->idAgrupamiento),
            'agrupamiento_id' => $empleado->idAgrupamiento,
            'categoria' => $this->getCategoriaNombre($empleado->categoria),
            'categoria_id' => $empleado->categoria,
            'cargo' => $this->getCargoNombre($empleado->idCargo),
            'cargo_id' => $empleado->idCargo,
            'certifica' => $this->getCertificaNombre($empleado->IdEmpleado2),
            'certifica_id' => $empleado->IdEmpleado2,
            'convenios' => $empleado->Convenios,
            'foto' => $empleado->Foto,
            'servicios_asignados' => $this->obtenerServiciosAsignados($empleado),
            'historial_servicios' => $empleado->servicios->map(function ($s) {
                return [
                    'id' => $s->idServicio,
                    'nombre' => $s->servicio,
                    'fecha_inicio' => $s->pivot->fecha_inicio ? \Carbon\Carbon::parse($s->pivot->fecha_inicio)->format('d/m/Y') : '',
                    'fecha_fin' => $s->pivot->fecha_fin ? \Carbon\Carbon::parse($s->pivot->fecha_fin)->format('d/m/Y') : '-',
                    'activo' => $s->pivot->activo,
                    'motivo' => $s->pivot->motivo
                ];
            })->sortByDesc('fecha_inicio')->values(),
            'historial_relaciones' => $historialRelaciones->map(function ($item) {
                return [
                    'IdHisRelacion' => $item->IdHisRelacion,
                    'relacion_id' => $item->Relacion_Id,
                    'relacion_nombre' => $item->tipoRelacion->Relacion ?? '',
                    'desde' => $item->Desde ? \Carbon\Carbon::parse($item->Desde)->format('d/m/Y') : '',
                    'hasta' => $item->Hasta ? \Carbon\Carbon::parse($item->Hasta)->format('d/m/Y') : '',
                    'observacion' => $item->Observacion
                ];
            }),
            'documentos' => $documentos->map(function ($item) {
                return [
                    'IdDocumento' => $item->IdDocumento,
                    'nombre' => $item->NombreDoc,
                    'imagen' => $item->Imagen,
                    'fecha' => $item->FechaDoc ? \Carbon\Carbon::parse($item->FechaDoc)->format('d/m/Y H:i') : ''
                ];
            }),
            'jornadas' => $jornadas->map(function ($item) {
                return [
                    'IdJornadaXEmp' => $item->IdJornadaXEmp,
                    'jornada_id' => $item->JornadaXEmp_Id,
                    'jornada_nombre' => $item->tipoJornada->Jornada ?? '',
                    'fecha' => $item->FechaJor ? \Carbon\Carbon::parse($item->FechaJor)->format('d/m/Y') : '',
                    'fechaSinFormato' => $item->FechaJor ? \Carbon\Carbon::parse($item->FechaJor)->format('Y-m-d') : '',
                ];
            }),
            'historial_modificaciones' => $historialModificaciones->map(function ($item) {
                $modificaciones = $item->Modificaciones;

                // Si es un UPDATE SQL del sistema antiguo, formatearlo
                if (stripos($modificaciones, 'UPDATE empleados') === 0) {
                    $modificaciones = $this->formatearUpdateSql($modificaciones);
                }
                // Si es un INSERT SQL del sistema antiguo, formatearlo
                elseif (stripos($modificaciones, 'INSERT INTO empleados') === 0) {
                    $modificaciones = $this->formatearInsertSql($modificaciones);
                }

                return [
                    'id' => $item->IdHisModPers,
                    'fecha' => $item->FechaMod ? \Carbon\Carbon::parse($item->FechaMod)->format('d/m/Y') : '',
                    'fechaSinFormato' => $item->FechaMod ? \Carbon\Carbon::parse($item->FechaMod)->format('Y-m-d H:i:s') : '',
                    'modificaciones' => $modificaciones,
                    'modificador' => $item->nombre_modificador, // null si no hay modificador
                    'tipo_cambio' => $item->tipo_cambio ?? 'personal' // Por defecto 'personal' para registros antiguos
                ];
            })
        ];





        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Store a new personnel record
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Get user ID from session
        $usuarioId = session('usuario_id');

        // Check if user has permission to create
        $permisos = PermisoHelper::obtenerPermisos($usuarioId, 'personal');

        if (!($permisos['crear'] ?? false)) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permisos para crear registros'
            ], 403);
        }

        // Validación condicional: servicios no obligatorio si es Jefe Dpto/Gerente/Director (cargo >= 3)
        // El campo cargo está oculto en el formulario, se gestiona solo desde el organigrama
        $cargo = is_numeric($request->input('cargo')) ? (int)$request->input('cargo') : 0;
        $serviciosRequeridos = ($cargo >= 3) ? 'nullable' : 'required';

        // Determinar si se envía servicios_asignados (nuevo formato) o servicios (formato legacy)
        $tieneServiciosAsignados = $request->has('servicios_asignados') && is_array($request->servicios_asignados) && count($request->servicios_asignados) > 0;

        // DEBUG: Log para ver qué recibe el servidor
        \Log::info('DEBUG PERSONAL STORE:', [
            'tieneServiciosAsignados' => $tieneServiciosAsignados,
            'has_servicios_asignados' => $request->has('servicios_asignados'),
            'servicios_asignados_count' => is_array($request->servicios_asignados) ? count($request->servicios_asignados) : 'not array',
            'servicios_asignados' => $request->servicios_asignados,
            'certifica' => $request->certifica,
            'cargo' => $request->cargo,
        ]);

        // Validate request - Unificado con validaciones del cliente
        $request->validate([
            'legajo' => 'required|numeric|unique:empleados,Legajo',
            'apellido' => 'required|string|max:50',
            'nombre' => 'required|string|max:50',
            'dni' => 'required|numeric|digits_between:7,8|unique:empleados,DNI',
            'sexo' => 'required|in:M,F',
            'email' => 'nullable|email',
            'fecha_nacimiento' => 'required|date_format:Y-m-d',
            'fecha_alta' => 'required|date_format:Y-m-d',
            'fecha_adm_publica' => 'required|date_format:Y-m-d',
            // Datos laborales obligatorios
            'tipo_tarea' => 'required|integer',
            'relacion' => 'required|integer',
            'profesion' => 'required|integer',
            'categoria' => 'required|integer',
            'agrupamiento' => 'required|integer',
            // cargo puede ser 0 (sin cargo), usar nullable porque el campo está oculto
            'cargo' => 'nullable|integer|min:0',
            // certifica es requerido solo si NO hay servicios_asignados
            'certifica' => $tieneServiciosAsignados ? 'nullable|integer' : 'required|integer',
            // Jerarquía organizacional obligatoria
            'gerencia' => 'required|integer',
            'departamento' => 'required|integer',
            // Acepta ambos formatos: servicios (legacy) o servicios_asignados (nuevo)
            'servicios' => 'nullable|array',
            'servicios.*' => 'integer|exists:servicios,IdServicio',
            'servicios_asignados' => $tieneServiciosAsignados ? 'required|array' : 'nullable|array',
            'servicios_asignados.*.servicio_id' => $tieneServiciosAsignados ? 'required|integer' : 'nullable|integer',
            'servicios_asignados.*.certificador_id' => 'nullable|integer',
            'servicios_asignados.*.fecha_pase' => 'nullable|date',
            'servicios_asignados.*.sector_id' => 'nullable|integer',
            // Jornada
            'tipo_jornada' => 'required|integer',
            'f_jornada' => 'required|date_format:Y-m-d'
        ], [
            'legajo.required' => 'El legajo es requerido',
            'legajo.numeric' => 'El legajo debe ser un número válido',
            'apellido.required' => 'El apellido es requerido',
            'apellido.max' => 'El apellido no debe exceder 50 caracteres',
            'nombre.required' => 'El nombre es requerido',
            'nombre.max' => 'El nombre no debe exceder 50 caracteres',
            'dni.required' => 'El DNI es requerido',
            'dni.numeric' => 'El DNI debe ser un número válido',
            'dni.digits_between' => 'El DNI debe tener entre 7 y 8 dígitos',
            'sexo.required' => 'Seleccione el sexo',
            'email.email' => 'Ingrese un email válido',
            'fecha_nacimiento.required' => 'Ingrese la fecha de nacimiento',
            'fecha_alta.required' => 'Ingrese la fecha de alta',
            'fecha_adm_publica.required' => 'Ingrese la fecha de admisión pública',
            'tipo_tarea.required' => 'Seleccione el tipo de tarea',
            'relacion.required' => 'Seleccione la relación laboral',
            'profesion.required' => 'Seleccione la profesión',
            'categoria.required' => 'Seleccione la categoría',
            'agrupamiento.required' => 'Seleccione el agrupamiento',
            'cargo.required' => 'Seleccione el cargo',
            'certifica.required' => 'Seleccione quién certifica',
            'gerencia.required' => 'Seleccione la gerencia',
            'departamento.required' => 'Seleccione el departamento',
            'servicios.required' => 'Seleccione al menos un servicio',
            'servicios.min' => 'Seleccione al menos un servicio',
            'servicios_asignados.required' => 'Seleccione al menos un servicio',
            'tipo_jornada.required' => 'Seleccione el tipo de jornada',
            'f_jornada.required' => 'Ingrese la fecha de jornada'
        ]);

        // Validación personalizada para servicios_asignados - verificar que existan en la tabla servicio
        if ($tieneServiciosAsignados) {
            foreach ($request->servicios_asignados as $index => $servicioAsignado) {
                $servicioId = $servicioAsignado['servicio_id'] ?? null;
                if ($servicioId) {
                    $exists = DB::table('servicio')->where('IdServicio', $servicioId)->exists();
                    if (!$exists) {
                        return response()->json([
                            'success' => false,
                            'message' => "El servicio seleccionado en la posición " . ($index + 1) . " no es válido",
                            'errors' => ["servicios_asignados.{$index}.servicio_id" => ["El servicio seleccionado no existe"]]
                        ], 422);
                    }
                }
            }
        }

        try {
            DB::beginTransaction();

            // Create new employee record
            $empleado = new Empleado();
            $this->fillEmpleadoData($empleado, $request);
            $empleado->Creador_Id = $usuarioId;
            $empleado->FechaCreacion = now();
            $empleado->save();

            // Process historial de relaciones
            if ($request->has('relaciones') && is_array($request->relaciones)) {
                $this->processHistorialRelaciones($empleado->idEmpleado, $request->relaciones);
            }

            // Process documents
            if ($request->has('documentos') && is_array($request->documentos)) {
                $this->processDocuments($empleado->idEmpleado, $request->documentos);
            }

            // Asignación de servicios con información completa (certificador, fecha, sector)
            if ($request->has('servicios_asignados') && is_array($request->servicios_asignados)) {
                $serviciosAsignados = $request->servicios_asignados;

                if (!empty($serviciosAsignados)) {
                    // Pasar toda la información de servicios_asignados al método actualizarServicios
                    $empleado->actualizarServicios($serviciosAsignados, 'Alta de empleado');
                }
            }

            // Process deleted documents if any
            if ($request->has('imagenes_eliminadas') && !empty($request->imagenes_eliminadas)) {
                $this->processDeletedDocuments($request->imagenes_eliminadas);
            }

            // Process profile photo
            if ($request->has('foto') && !empty($request->foto)) {
                $this->processProfilePhoto($empleado, $request->foto);
            }

            // Check if we need to delete the photo
            if ($request->has('eliminar_foto') && $request->eliminar_foto) {
                $this->deleteProfilePhoto($empleado);
            }

            // Insert jornada if tipo_jornada is specified
            if ($request->has('tipo_jornada') && !empty($request->tipo_jornada)) {
                // Create jornada record
                JornadaXEmp::create([
                    'JornadaXEmp_Id' => $request->tipo_jornada,
                    'Emp_Id' => $empleado->idEmpleado,
                    'FechaJor' => now()->format('Y-m-d'),
                    'CreadorJor_Id' => $usuarioId
                ]);
            }

            // Log creation
            LogHelper::insertar('empleados', 'personal', $empleado->idEmpleado, "Se creó el empleado {$empleado->Legajo} - {$empleado->Apellido}, {$empleado->Nombre}");



            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Empleado creado correctamente',
                'data' => ['id' => $empleado->idEmpleado]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el empleado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a personnel record
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Get user ID from session
        $usuarioId = session('usuario_id');

        // Check if user has permission to edit
        $permisos = PermisoHelper::obtenerPermisos($usuarioId, 'personal');

        if (!($permisos['editar'] ?? false)) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permisos para editar registros'
            ], 403);
        }

        // Find the employee
        $empleado = Empleado::find($id);

        if (!$empleado) {
            return response()->json([
                'success' => false,
                'message' => 'Empleado no encontrado'
            ], 404);
        }

        // Validación condicional: servicios no obligatorio si es Jefe Dpto/Gerente/Director (cargo >= 3)
        // El campo cargo está oculto en el formulario, se gestiona solo desde el organigrama
        $cargo = is_numeric($request->input('cargo')) ? (int)$request->input('cargo') : 0;
        $serviciosRequeridos = ($cargo >= 3) ? 'nullable' : 'required';

        // Determinar si se envía servicios_asignados (nuevo formato) o servicios (formato legacy)
        $tieneServiciosAsignados = $request->has('servicios_asignados') && is_array($request->servicios_asignados) && count($request->servicios_asignados) > 0;

        // Validate request - Unificado con validaciones del cliente
        $request->validate([
            'legajo' => 'required|numeric|unique:empleados,Legajo,' . $id . ',idEmpleado',
            'apellido' => 'required|string|max:50',
            'nombre' => 'required|string|max:50',
            'dni' => 'required|numeric|digits_between:7,8|unique:empleados,DNI,' . $id . ',idEmpleado',
            'sexo' => 'required|in:M,F',
            'email' => 'nullable|email',
            'fecha_nacimiento' => 'required|date_format:Y-m-d',
            'fecha_alta' => 'required|date_format:Y-m-d',
            'fecha_adm_publica' => 'required|date_format:Y-m-d',
            // Datos laborales obligatorios
            'tipo_tarea' => 'required|integer',
            'relacion' => 'required|integer',
            'profesion' => 'required|integer',
            'categoria' => 'required|integer',
            'agrupamiento' => 'required|integer',
            // cargo puede ser 0 (sin cargo), usar nullable porque el campo está oculto
            'cargo' => 'nullable|integer|min:0',
            // certifica es requerido solo si NO hay servicios_asignados
            'certifica' => $tieneServiciosAsignados ? 'nullable|integer' : 'required|integer',
            // Jerarquía organizacional obligatoria
            'gerencia' => 'required|integer',
            'departamento' => 'required|integer',
            // Acepta ambos formatos: servicios (legacy) o servicios_asignados (nuevo)
            'servicios' => 'nullable|array',
            'servicios.*' => 'integer|exists:servicios,IdServicio',
            'servicios_asignados' => 'nullable|array',
            'servicios_asignados.*.servicio_id' => 'nullable|integer',
            'servicios_asignados.*.certificador_id' => 'nullable|integer',
            'servicios_asignados.*.fecha_pase' => 'nullable|date',
            'servicios_asignados.*.sector_id' => 'nullable|integer',
            // Jornada
            'tipo_jornada' => 'required|integer',
            'f_jornada' => 'required|date_format:Y-m-d'
        ], [
            'legajo.required' => 'El legajo es requerido',
            'legajo.numeric' => 'El legajo debe ser un número válido',
            'apellido.required' => 'El apellido es requerido',
            'apellido.max' => 'El apellido no debe exceder 50 caracteres',
            'nombre.required' => 'El nombre es requerido',
            'nombre.max' => 'El nombre no debe exceder 50 caracteres',
            'dni.required' => 'El DNI es requerido',
            'dni.numeric' => 'El DNI debe ser un número válido',
            'dni.digits_between' => 'El DNI debe tener entre 7 y 8 dígitos',
            'sexo.required' => 'Seleccione el sexo',
            'email.email' => 'Ingrese un email válido',
            'fecha_nacimiento.required' => 'Ingrese la fecha de nacimiento',
            'fecha_alta.required' => 'Ingrese la fecha de alta',
            'fecha_adm_publica.required' => 'Ingrese la fecha de admisión pública',
            'tipo_tarea.required' => 'Seleccione el tipo de tarea',
            'relacion.required' => 'Seleccione la relación laboral',
            'profesion.required' => 'Seleccione la profesión',
            'categoria.required' => 'Seleccione la categoría',
            'agrupamiento.required' => 'Seleccione el agrupamiento',
            'cargo.required' => 'Seleccione el cargo',
            'certifica.required' => 'Seleccione quién certifica',
            'gerencia.required' => 'Seleccione la gerencia',
            'departamento.required' => 'Seleccione el departamento',
            'servicios.required' => 'Seleccione al menos un servicio',
            'servicios.min' => 'Seleccione al menos un servicio',
            'servicios_asignados.required' => 'Seleccione al menos un servicio',
            'tipo_jornada.required' => 'Seleccione el tipo de jornada',
            'f_jornada.required' => 'Ingrese la fecha de jornada'
        ]);

        // Validación personalizada para servicios_asignados - verificar que existan en la tabla servicio
        if ($tieneServiciosAsignados) {
            foreach ($request->servicios_asignados as $index => $servicioAsignado) {
                $servicioId = $servicioAsignado['servicio_id'] ?? null;
                if ($servicioId) {
                    $exists = DB::table('servicio')->where('IdServicio', $servicioId)->exists();
                    if (!$exists) {
                        return response()->json([
                            'success' => false,
                            'message' => "El servicio seleccionado en la posición " . ($index + 1) . " no es válido",
                            'errors' => ["servicios_asignados.{$index}.servicio_id" => ["El servicio seleccionado no existe"]]
                        ], 422);
                    }
                }
            }
        }

        try {
            DB::beginTransaction();

            // Store original data for comparison
            $originalData = $empleado->toArray();
            $legajoOriginal = $request->input('legajoOriginal', $empleado->Legajo);

            // Update employee data
            $this->fillEmpleadoDataUpdate($empleado, $request);
            $empleado->Modificador_Id = $usuarioId;
            $empleado->save();

            // Asignación de servicios (Multi-servicio)
            $serviciosIds = $request->input('servicios', []);
            $servicioPrincipal = $request->input('idServicio');

            // Procesar servicios a dar de baja primero
            if ($request->has('servicios_dar_baja') && is_array($request->servicios_dar_baja)) {
                foreach ($request->servicios_dar_baja as $baja) {
                    DB::table('empleado_servicio')
                        ->where('empleado_id', $empleado->id)
                        ->where('servicio_id', $baja['servicio_id'])
                        ->where('activo', 1)
                        ->update([
                            'activo' => 0,
                            'fecha_fin' => $baja['fecha_baja'] ?? now()->format('Y-m-d'),
                            'motivo' => $baja['motivo'] ?? 'Baja de servicio'
                        ]);
                }
            }

            // Procesar servicios asignados con información completa
            if ($request->has('servicios_asignados') && is_array($request->servicios_asignados)) {
                $serviciosAsignados = $request->servicios_asignados;

                if (!empty($serviciosAsignados)) {
                    // Pasar toda la información de servicios_asignados al método actualizarServicios
                    $empleado->actualizarServicios($serviciosAsignados, 'Edición de empleado');
                }
            } elseif ($servicioPrincipal) {
                // Legacy: solo cambió el servicio principal via dropdown simple
                $empleado->actualizarServicios([$servicioPrincipal], 'Cambio servicio principal');
            }

            // Check if legajo has been changed and update related tables
            if ($request->legajo != $legajoOriginal) {
                $this->updateLegajoInRelatedTables($legajoOriginal, $request->legajo);
            }

            // Process historial de relaciones
            if ($request->has('relaciones') && is_array($request->relaciones)) {
                // Delete existing relations and create new ones

                // Check if work schedule type or date has changed and create new JornadaXEmp record

                HistorialRelacion::where('Personal_Id', $id)->delete();
                $this->processHistorialRelaciones($id, $request->relaciones);
            }

            // Process deleted documents if any
            if ($request->has('imagenes_eliminadas') && !empty($request->imagenes_eliminadas)) {
                $this->processDeletedDocuments($request->imagenes_eliminadas);
            }
            // Process documents
            if ($request->has('documentos')) {
                $this->processDocuments($id, $request->documentos);
            }



            // Check if we need to delete the photo
            if ($request->has('eliminar_foto') && $request->eliminar_foto) {
                $this->deleteProfilePhoto($empleado);
            }

            // Process profile photo
            if ($request->has('foto') && !empty($request->foto)) {
                $this->processProfilePhoto($empleado, $request->foto);
            }


            $fechaJornada = $request->input('f_jornada');
            $tipoJornadaActual = $request->input('tipo_jornada');
            $tipoJornadaOriginal = $request->input('JornadaOriginal_Id');
            $fechaJornadaOriginal = $request->input('FechaJornadaOri');

            /**
             * Actualiza o crea registros de jornada del empleado según los cambios en fecha y tipo de jornada.
             */

            if ($fechaJornada != $fechaJornadaOriginal && intval($tipoJornadaActual) == intval($tipoJornadaOriginal)) {
                // Obtener la última jornada (más reciente) del empleado y actualizar sólo la fecha
                $ultimaJornada = JornadaXEmp::where('Emp_Id', $id)
                    ->orderBy('FechaJor', 'desc')
                    ->first();

                if ($ultimaJornada) {
                    $ultimaJornada->FechaJor = $fechaJornada;
                    $ultimaJornada->save();
                }
            }

            if ($fechaJornada && !empty($tipoJornadaActual) && intval($tipoJornadaActual) != intval($tipoJornadaOriginal)) {
                JornadaXEmp::create([
                    'JornadaXEmp_Id' => $tipoJornadaActual,
                    'Emp_Id' => $id,
                    'FechaJor' => $fechaJornada,
                    'CreadorJor_Id' => $usuarioId
                ]);
            }

            /*
            // Generate modification log
            $changes = $this->getChanges($originalData, $empleado->toArray());
            */
            // Log update
            LogHelper::actualizar('empleados', 'personal', $id, "Se modificó el empleado {$empleado->Legajo} - {$empleado->Apellido}, {$empleado->Nombre}");

            // Guardar historial de modificaciones importantes
            $this->guardarHistorialModificaciones($originalData, $empleado->toArray(), $usuarioId);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Empleado actualizado correctamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el empleado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a personnel record (logical delete)
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Get user ID from session
        $usuarioId = session('usuario_id');

        // Check if user has permission to delete
        $permisos = PermisoHelper::obtenerPermisos($usuarioId, 'personal');

        if (!($permisos['eliminar'] ?? false)) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permisos para eliminar registros'
            ], 403);
        }

        // Find the employee
        $empleado = Empleado::find($id);

        if (!$empleado) {
            return response()->json([
                'success' => false,
                'message' => 'Empleado no encontrado'
            ], 404);
        }

        try {
            DB::beginTransaction();

            // Store employee info for logging
            $empleadoInfo = "{$empleado->Legajo} - {$empleado->Apellido}, {$empleado->Nombre}";

            // Soft delete the employee record (sets FechaEliminacion)
            $empleado->delete();

            // Log deletion
            LogHelper::eliminar('empleados', 'personal', $id, "Se eliminó el empleado {$empleadoInfo}");

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Empleado eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el empleado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update legajo in related tables
     */
    private function updateLegajoInRelatedTables($legajoOriginal, $legajoNuevo)
    {
        // Define the tables and fields that need to be updated
        $tablasModLeg = [
            ["tabla" => "licencias", "campo" => "LegajoPersonal"],
            ["tabla" => "config_lar", "campo" => "LegajoLarP"],
            ["tabla" => "conf_x_bien_inv", "campo" => "RespLegCXBI"],
            ["tabla" => "resp_x_invent", "campo" => "Resp_Leg"],
            ["tabla" => "pers_x_deposito", "campo" => "LegajoDep"],
        ];

        // Update each table
        foreach ($tablasModLeg as $tabla) {
            DB::table($tabla["tabla"])
                ->where($tabla["campo"], $legajoOriginal)
                ->update([$tabla["campo"] => $legajoNuevo]);
        }

        // Log the legajo change
        LogHelper::actualizar('empleados', 'personal', null, "Se cambió el legajo $legajoOriginal por $legajoNuevo en tablas relacionadas");
    }

    /**
     * Get departments by gerencia
     */
    public function getDepartamentos(Request $request)
    {
        $gerenciaId = $request->input('gerencia_id');

        if (!$gerenciaId) {
            return response()->json(['data' => []]);
        }

        $departamentos = Departamento::where('idGerencia', $gerenciaId)
            ->orderBy('departamento')
            ->get(['idDepartamento', 'departamento']);

        return response()->json(['data' => $departamentos]);
    }

    /**
     * Get servicios by departamento
     */
    public function getServicios(Request $request)
    {
        $departamentoId = $request->input('departamento_id');

        if (!$departamentoId) {
            return response()->json(['data' => []]);
        }

        $servicios = Servicio::where('idDepartamento', $departamentoId)
            ->orderBy('servicio')
            ->get(['idServicio', 'servicio']);

        return response()->json(['data' => $servicios]);
    }

    /**
     * Get sectores by servicio
     */
    public function getSectores(Request $request)
    {
        $servicioId = $request->input('servicio_id');

        if (!$servicioId) {
            return response()->json(['data' => []]);
        }

        $sectores = Sector::where('idservicio', $servicioId)
            ->orderBy('sector')
            ->get(['idSector', 'sector']);

        return response()->json(['data' => $sectores]);
    }

    /**
     * Obtener el jefe de un servicio específico.
     *
     * Dado un servicio, devuelve su jefe único (empleado con idCargo = 2).
     * El certificador se infiere del SERVICIO seleccionado.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getJefeServicio(Request $request)
    {
        $servicioId = $request->input('servicio_id');

        if (!$servicioId) {
            return response()->json(['jefe' => null]);
        }

        // Buscar el empleado activo con idCargo = 2 (Jefe de Servicio) asignado a este servicio.
        // Verifica en el campo idServicio (legacy) y en la tabla pivote empleado_servicio.
        // Solo devuelve UN jefe por servicio (first() garantiza unicidad).
        // Si no hay jefe asignado, devuelve null.
        $jefe = Empleado::where('Estado', 1)
            ->where('idCargo', 2)
            ->where(function($query) use ($servicioId) {
                $query->where('idServicio', $servicioId)
                      ->orWhereHas('serviciosActivos', function($q) use ($servicioId) {
                          $q->where('servicio_id', $servicioId)->where('activo', 1);
                      });
            })
            ->first(['idEmpleado', 'Apellido', 'Nombre', 'Legajo']);

        if ($jefe) {
            return response()->json([
                'jefe' => [
                    'id' => $jefe->idEmpleado,
                    'nombre' => $jefe->Apellido . ', ' . $jefe->Nombre . ' (Leg. ' . $jefe->Legajo . ')'
                ]
            ]);
        }

        return response()->json(['jefe' => null]);
    }

    /**
     * Obtener empleados activos para certificadores (solo los que tienen cargo asignado)
     */
    public function getEmpleadosActivos()
    {
        $empleados = Empleado::where('Estado', '1')
            ->whereNotNull('idCargo')
            ->where('idCargo', '!=', 0)
            ->orderBy('Apellido')
            ->orderBy('Nombre')
            ->get(['idEmpleado', 'Apellido', 'Nombre', 'Legajo'])
            ->map(function($emp) {
                return [
                    'id' => $emp->idEmpleado,
                    'nombre' => $emp->Apellido . ', ' . $emp->Nombre . ' (Leg. ' . $emp->Legajo . ')'
                ];
            });

        return response()->json(['empleados' => $empleados]);
    }

    /**
     * Get localidades by provincia
     */
    public function getLocalidades(Request $request)
    {
        $provinciaId = $request->input('provincia_id');

        if (!$provinciaId) {
            return response()->json(['data' => []]);
        }

        $localidades = Localidad::where('Provincia_Id', $provinciaId)
            ->orderBy('Localidad')
            ->get(['IdLocalidad', 'Localidad', 'CP']);

        return response()->json(['data' => $localidades]);
    }

    /**
     * Get all initial selectors for dropdowns
     */
    public function getSelectoresIniciales()
    {
        $data = [
            'gerencias' => Gerencia::orderBy('Gerencia')->get(['idGerencia', 'Gerencia']),
            'provincias' => Provincia::orderBy('Provincia')->get(['IdProvincia', 'Provincia']),
            'estados_civiles' => EstadoCivil::orderBy('EstadoCivil')->get(['idEstadoCivil', 'EstadoCivil']),
            'estados' => Estado::orderBy('estado')->get(['idEstado', 'estado']),
            'profesiones' => Profesion::orderBy('profesion')->get(['idprofesion', 'profesion']),
            'funciones' => Funcion::orderBy('Funcion')->get(['IdFuncion', 'Funcion']),
            'instrucciones' => Instruccion::orderBy('instruccion')->get(['idInstruccion', 'instruccion']),
            'tipos_relacion' => TipoRelacion::orderBy('Relacion')->get(['idRelacion', 'Relacion']),
            'tipos_jornada' => TipoJornada::orderBy('Jornada')->get(['IdTipoJornada', 'Jornada']),
            'motivos_baja' => MotivoBaja::orderBy('MotivoBaja')->get(['IdMotivoBaja', 'MotivoBaja']),
            'paises' => Pais::orderBy('Pais')->get(['IdPais', 'Pais']),
            // Nuevos selectores
            'empleados_con_cargo' => Empleado::where('idCargo', '!=', 0)
                ->whereNotNull('idCargo')
                ->orderBy('Apellido')
                ->orderBy('Nombre')
                ->get(['idEmpleado', 'Apellido', 'Nombre', 'Legajo']),
            // Selectores agregados
            'agrupamientos' => Agrupamiento::orderBy('agrupamiento')->get(['idAgrupamiento', 'agrupamiento']),
            'categorias' => Categoria::orderBy('categoria')->get(['idcategoria', 'categoria']),
            'cargos' => Cargo::orderBy('cargo')->get(['idCargo', 'cargo'])
        ];

        return response()->json(['data' => $data]);
    }

    /**
     * Check if DNI exists
     */
    public function checkDniExists(Request $request)
    {
        $dni = $request->input('dni');
        $excludeId = $request->input('exclude_id', null);

        $query = Empleado::where('DNI', $dni);

        if ($excludeId) {
            $query->where('idEmpleado', '!=', $excludeId);
        }

        $exists = $query->exists();

        return response()->json(['exists' => $exists]);
    }

    /**
     * Check if legajo exists
     */
    public function checkLegajoExists(Request $request)
    {
        $legajo = $request->input('legajo');
        $excludeId = $request->input('exclude_id', null);

        $query = Empleado::where('Legajo', $legajo)->where('Estado', '1');

        if ($excludeId) {
            $query->where('idEmpleado', '!=', $excludeId);
        }

        $exists = $query->exists();

        return response()->json(['exists' => $exists]);
    }

    /**
     * Get jornadas historial for an employee
     */
    public function getJornadas($id)
    {
        $jornadas = JornadaXEmp::with('tipoJornada')
            ->where('Emp_Id', $id)
            ->orderBy('FechaJor', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->IdJornadaXEmp,
                    'jornada' => $item->tipoJornada->Jornada ?? 'Sin jornada',
                    'fecha' => $item->FechaJor ? \Carbon\Carbon::parse($item->FechaJor)->format('d/m/Y') : ''
                ];
            });

        return response()->json(['data' => $jornadas]);
    }

    /**
     * Fill employee data from request
     */
    private function fillEmpleadoData($empleado, $request)
    {
        $empleado->Legajo = $request->legajo;
        $empleado->Apellido = $request->apellido;
        $empleado->Nombre = $request->nombre;
        $empleado->DNI = $request->dni;
        $empleado->cuit = $request->cuit ?? '';
        // Convertir M/F a 1/2 para la BD
        $sexoValue = $request->sexo ?? 1;
        $empleado->sexo = ($sexoValue === 'M') ? 1 : (($sexoValue === 'F') ? 2 : $sexoValue);
        $empleado->FecNac = $request->fecha_nacimiento; // Ya viene en formato YYYY-MM-DD
        $empleado->EstCiv = $request->estado_civil ?? 1;
        $empleado->Nacionalidad = $request->nacionalidad ?? 1;
        $empleado->Provincia = $request->provincia ?? 1;
        $empleado->Localidad = $request->localidad ?? 1;
        $empleado->CP = $request->cp ?? 0;
        $empleado->calle = $request->calle ?? '';
        $empleado->CalleNum = $request->num_calle ?? '';
        $empleado->Piso = $request->piso ?? '';
        $empleado->Departamento = $request->dto ?? '';
        $empleado->Barrio = $request->barrio ?? '';
        $empleado->Manzana = $request->manzana ?? '';
        $empleado->Casa = $request->casa ?? '';
        $empleado->Email = $request->email ?? '';
        $empleado->Telefono = $request->telefono ?? '';
        $empleado->Celular = $request->celular ?? '';
        $empleado->idProfesion = $request->profesion ?? 1;
        $empleado->Funcion = $request->funcion ?? 1;
        $empleado->idInstrucion = $request->instruccion ?? $request->tipo_tarea ?? 1;  // Aceptar ambos nombres
        $empleado->idTipoRelacion = $request->relacion_laboral ?? $request->relacion ?? 1;
        $empleado->idGerencia = $request->gerencia ?? 1;
        $empleado->idDepartamento = $request->departamento ?? 1;
        $empleado->idServicio = $request->servicio ?? 1;
        $empleado->idSector = $request->sector ?? 1;
        $empleado->categoria = $request->categoria ?? 0;
        $empleado->codigo = 0;
        // Guardar también en el campo idAgrupamiento (nuevo) para persistir el agrupamiento
        $empleado->idAgrupamiento = (int)($request->agrupamiento ?? 0);
        // Cargo se gestiona solo desde el organigrama (campo oculto en el formulario)
        $empleado->idCargo = is_numeric($request->cargo) ? (int)$request->cargo : 0;
        $empleado->IdEmpleado2 = (int)($request->certifica ?? 0);
        $empleado->Matricula = $request->num_matricula ?? '';
        $empleado->NroContrato = $request->nro_contrato ?? '';

        // Asumir que las fechas ya vienen en formato YYYY-MM-DD
        $empleado->FAlta = $request->fecha_alta;
        $empleado->Modificador_Id = 0;

        $empleado->Indicador = 0;
        $empleado->Foto = "";


        if (!empty($request->fecha_adm_publica)) {
            $empleado->FAltaAP = $request->fecha_adm_publica;
        }

        if (!empty($request->fecha_baja)) {
            $empleado->FBaja = $request->fecha_baja;
        } else {
            $empleado->FBaja = NULL;
        }

        $empleado->Estado = $request->estado ?? 1;
        $empleado->MotivoBaja_Id = $request->motivo_baja ?? 0;
        $empleado->DescripcionBaja = $request->descripcion_baja ?? $request->des_baja ?? '';
        $empleado->Observacion = $request->observacion ?? '';
        $empleado->Jornada_Id = $request->tipo_jornada ?? 1;
        $empleado->DobleFS = $request->has('f_doble') ? 1 : 0;
        $empleado->FE = $request->has('fe') ? 1 : 0;
        $empleado->Nocturno = 0;
        $empleado->Gremio = 0;
        $empleado->Convenios = '0';
    }

    /**
     * Fill employee data from request
     */

    //ALTER TABLE `empleados` CHANGE `FBaja` `FBaja` DATE NULL;
    //ALTER TABLE `historial_relacion` CHANGE `Hasta` `Hasta` DATE NULL;

    private function fillEmpleadoDataUpdate($empleado, $request)
    {
        $empleado->Legajo = $request->legajo;
        $empleado->Apellido = $request->apellido;
        $empleado->Nombre = $request->nombre;
        $empleado->DNI = $request->dni;
        $empleado->cuit = $request->cuit ?? '';
        // Convertir M/F a 1/2 para la BD
        $sexoValue = $request->sexo ?? 1;
        $empleado->sexo = ($sexoValue === 'M') ? 1 : (($sexoValue === 'F') ? 2 : $sexoValue);
        $empleado->FecNac = $request->fecha_nacimiento; // Ya viene en formato YYYY-MM-DD
        $empleado->EstCiv = $request->estado_civil ?? 1;
        $empleado->Nacionalidad = $request->nacionalidad ?? 1;
        $empleado->Provincia = $request->provincia ?? 1;
        $empleado->Localidad = $request->localidad ?? 1;
        $empleado->CP = $request->cp ?? 0;
        $empleado->calle = $request->calle ?? '';
        $empleado->CalleNum = $request->num_calle ?? '';
        $empleado->Piso = $request->piso ?? '';
        $empleado->Departamento = $request->dto ?? '';
        $empleado->Barrio = $request->barrio ?? '';
        $empleado->Manzana = $request->manzana ?? '';
        $empleado->Casa = $request->casa ?? '';
        $empleado->Email = $request->email ?? '';
        $empleado->Telefono = $request->telefono ?? '';
        $empleado->Celular = $request->celular ?? '';
        $empleado->idProfesion = $request->profesion ?? 1;
        $empleado->Funcion = $request->funcion ?? 1;
        $empleado->idInstrucion = $request->instruccion ?? $request->tipo_tarea ?? 1;  // Aceptar ambos nombres
        $empleado->idTipoRelacion = $request->relacion_laboral ?? $request->relacion ?? 1;
        $empleado->idGerencia = $request->gerencia ?? 1;
        $empleado->idDepartamento = $request->departamento ?? 1;
        $empleado->idServicio = $request->servicio ?? 1;
        $empleado->idSector = $request->sector ?? 1;
        $empleado->categoria = $request->categoria ?? 0;

        // Guardar también en el campo idAgrupamiento (nuevo) para persistir el agrupamiento
        $empleado->idAgrupamiento = (int)($request->agrupamiento ?? 0);
        // Cargo se gestiona solo desde el organigrama (campo oculto en el formulario)
        $empleado->idCargo = is_numeric($request->cargo) ? (int)$request->cargo : 0;
        $empleado->IdEmpleado2 = (int)($request->certifica ?? 0);
        $empleado->Matricula = $request->num_matricula ?? '';
        $empleado->NroContrato = $request->nro_contrato ?? '';

        // Asumir que las fechas ya vienen en formato YYYY-MM-DD
        $empleado->FAlta = $request->fecha_alta;



        if (!empty($request->fecha_adm_publica)) {
            $empleado->FAltaAP = $request->fecha_adm_publica;
        }

        if (!empty($request->fecha_baja)) {
            $empleado->FBaja = $request->fecha_baja;
        } else {
            $empleado->FBaja = NULL;
        }

        $empleado->Estado = $request->estado ?? 1;
        $empleado->MotivoBaja_Id = $request->motivo_baja ?? 0;
        $empleado->DescripcionBaja = $request->des_baja ?? '';
        $empleado->Observacion = $request->observacion ?? '';

        $empleado->DobleFS = $request->has('f_doble') ? 1 : 0;
        $empleado->FE = $request->has('fe') ? 1 : 0;
    }
    /**
     * Process historial de relaciones
     */
    private function processHistorialRelaciones($empleadoId, $historialData)
    {
        foreach ($historialData as $historial) {
            if (!empty($historial['relacion_id'])) {
                $hist = new HistorialRelacion();
                $hist->Personal_Id = $empleadoId;
                $hist->Relacion_Id = $historial['relacion_id'];

                // Las fechas ya deberían venir en formato YYYY-MM-DD
                $hist->Desde = !empty($historial['desde']) ? $historial['desde'] : null;
                $hist->Hasta = !empty($historial['hasta']) ? $historial['hasta'] : null;

                $hist->Observacion = $historial['observacion'] ?? '';
                $hist->save();
            }
        }
    }

    /**
     * Process documents
     */
    private function processDocuments($empleadoId, $documentsData)
    {
        foreach ($documentsData as $doc) {
            // Always create a new document entry
            $documento = new DocumentoEscaneado();
            $documento->Empleado_Id = $empleadoId;
            $documento->NombreDoc = $doc['nombre'] ?? 'Documento';
            $documento->FechaDoc = now();

            // Procesar imagen solo si hay datos de imagen
            if (!empty($doc['imagen_data'])) {
                $imageData = $doc['imagen_data'];

                // Si la longitud es mayor a 200 caracteres, es una imagen nueva (base64)
                if (strlen($imageData) > 200) {
                    // Extract base64 data if needed (remove data:image prefixes)
                    if (strpos($imageData, 'data:image') !== false) {
                        $imageData = substr($imageData, strpos($imageData, ',') + 1);
                    }
                    $hashmd5 = md5(time() . $empleadoId . rand());
                    $imageName = $hashmd5 . '.png';
                    $imagePath = 'empleados/documentos/' . $imageName;

                    // Decode base64 data and save to storage
                    Storage::disk('public')->put($imagePath, base64_decode($imageData));

                    $documento->Imagen = $hashmd5;
                    $documento->save();
                }
            }
        }
    }

    /**
     * Delete document by ID
     *
     * @param int $documentoId - The ID of the document to delete
     * @return bool - Whether the deletion was successful
     */
    private function deleteDocument($documentoId)
    {
        $documento = DocumentoEscaneado::find($documentoId);

        if ($documento) {
            // Delete the physical file first
            if (!empty($documento->Imagen)) {
                $imagePath = 'empleados/documentos/' . $documento->Imagen . '.png';
                if (Storage::disk('public')->exists($imagePath)) {
                    Storage::disk('public')->delete($imagePath);
                }
            }

            // Delete the database record
            return $documento->delete();
        }

        return false;
    }

    /**
     * Process documents to be deleted
     *
     * @param array $documentosIds - Array of document IDs to delete
     */
    private function processDeletedDocuments($documentosIds)
    {
        if (empty($documentosIds) || !is_array($documentosIds)) {
            return;
        }

        foreach ($documentosIds as $documentoId) {
            $this->deleteDocument($documentoId);
        }
    }

    /**
     * Process profile photo
     */
    /**
     * Process profile photo upload or deletion
     */
    /**
     * Process profile photo upload or deletion
     */
    private function processProfilePhoto($empleado, $fotoData)
    {
        // Si hay datos nuevos de foto
        if (!empty($fotoData)) {
            // Si la longitud es mayor a 200 caracteres, es una imagen nueva (base64)
            if (strlen($fotoData) > 200) {
                // Si ya existía una foto, eliminar la anterior
                if (!empty($empleado->Foto)) {
                    $oldImagePath = 'empleados/fotos/' . $empleado->Foto . '.png';
                    if (Storage::disk('public')->exists($oldImagePath)) {
                        Storage::disk('public')->delete($oldImagePath);
                    }
                }

                // Generate a unique filename based on time and employee ID
                $hashmd5 = md5(time() . $empleado->idEmpleado . rand());
                $imageName = $hashmd5 . '.png';
                $imagePath = 'empleados/fotos/' . $imageName;

                // Extract base64 data if needed (remove data:image prefixes)
                if (strpos($fotoData, 'data:image') !== false) {
                    $fotoData = substr($fotoData, strpos($fotoData, ',') + 1);
                }

                // Decode base64 data and save to storage
                Storage::disk('public')->put($imagePath, base64_decode($fotoData));


                // Update employee record with the new photo filename
                $empleado->Foto = $hashmd5;
                $empleado->save();
            }
        }
    }

    /**
     * Delete profile photo if requested
     */
    private function deleteProfilePhoto($empleado)
    {
        // Si el empleado tiene una foto guardada, eliminarla

        if (!empty($empleado->Foto)) {
            $oldImagePath = 'empleados/fotos/' . $empleado->Foto . ".png";


            // Eliminar la imagen del almacenamiento
            if (Storage::disk('public')->exists($oldImagePath)) {
                Storage::disk('public')->delete($oldImagePath);
            }
            // Vaciar el campo de foto en la base de datos
            $empleado->Foto = "";
            $empleado->save();

            return true;
        }

        return false;
    }
    /**
     * Get changes between original and updated data
     */
    private function getChanges($original, $updated)
    {
        $changes = [];
        $fieldsToCheck = ['Legajo', 'Apellido', 'Nombre', 'DNI', 'Estado'];

        foreach ($fieldsToCheck as $field) {
            if (isset($original[$field]) && isset($updated[$field]) && $original[$field] != $updated[$field]) {
                $changes[] = "{$field}: {$original[$field]} -> {$updated[$field]}";
            }
        }

        return $changes;
    }

    /**
     * Buscar jefe según la jerarquía organizacional
     */
    public function buscarJefe(Request $request)
    {
        try {
            $nivel = $request->get('nivel');
            $nivelId = $request->get('nivel_id');
            $cargoJefe = $request->get('cargo_jefe');

            // Construir la consulta base
            $query = Empleado::select('empleados.*')
                ->where('empleados.Estado', 1) // Solo empleados activos
                ->where('empleados.idCargo', $cargoJefe); // Con el cargo de jefe correspondiente

            // Filtrar según el nivel organizacional
            switch ($nivel) {
                case 'gerencia':
                    $query->where('empleados.idGerencia', $nivelId);
                    break;
                case 'departamento':
                    $query->where('empleados.idDepartamento', $nivelId);
                    break;
                case 'servicio':
                    $query->where('empleados.idServicio', $nivelId);
                    break;
                case 'sector':
                    $query->where('empleados.idSector', $nivelId);
                    break;
                default:
                    return response()->json(['jefe' => null]);
            }

            $jefe = $query->first();

            if ($jefe) {
                return response()->json([
                    'jefe' => [
                        'idEmpleado' => $jefe->idEmpleado,
                        'legajo' => $jefe->Legajo,
                        'nombres' => $jefe->Nombre,
                        'apellidos' => $jefe->Apellido,
                        'cargo' => $jefe->Cargo
                    ]
                ]);
            } else {
                return response()->json(['jefe' => null]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al buscar jefe: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Buscar personal por nombre o legajo (para typeahead)
     */
    public function buscar(Request $request)
    {
        try {
            $query = $request->get('query', '');

            if (empty($query)) {
                return response()->json([]);
            }

            $empleados = Empleado::select('idEmpleado', 'Legajo', 'Apellido', 'Nombre', 'DNI')
                ->where('Estado', 1)
                ->where(function($q) use ($query) {
                    $q->where('Apellido', 'like', '%' . $query . '%')
                      ->orWhere('Nombre', 'like', '%' . $query . '%')
                      ->orWhere('Legajo', 'like', '%' . $query . '%')
                      ->orWhereRaw("CONCAT(Apellido, ', ', Nombre) LIKE ?", ['%' . $query . '%']);
                })
                ->orderBy('Apellido')
                ->orderBy('Nombre')
                ->limit(20)
                ->get();

            $results = [];
            foreach ($empleados as $emp) {
                $results[] = [
                    'id' => $emp->idEmpleado,
                    'value' => $emp->Apellido . ', ' . $emp->Nombre . ' (Leg: ' . $emp->Legajo . ')',
                    'tokens' => [$emp->Apellido, $emp->Nombre, $emp->Legajo]
                ];
            }

            return response()->json($results);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al buscar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Helper methods para obtener nombres descriptivos
     */
    private function getAgrupamientoNombre($id)
    {
        if (!$id)
            return 'Sin asignar';

        $agrupamiento = DB::table('agrupamiento')->where('idAgrupamiento', $id)->first();
        return $agrupamiento ? $agrupamiento->agrupamiento : 'Sin definir';
    }

    private function getCategoriaNombre($id)
    {
        if (!$id)
            return 'Sin asignar';

        $categoria = DB::table('categoria')->where('idcategoria', $id)->first();
        return $categoria ? $categoria->categoria : 'Sin definir';
    }

    private function getCargoNombre($id)
    {
        if (!$id)
            return 'Sin asignar';

        $cargo = DB::table('cargo')->where('idCargo', $id)->first();
        return $cargo ? $cargo->cargo : 'Sin definir';
    }

    private function getCertificaNombre($id)
    {
        if (!$id)
            return 'Sin asignar';

        $empleado = Empleado::where('idEmpleado', $id)->first();
        return $empleado ? "{$empleado->Apellido}, {$empleado->Nombre} (Leg. {$empleado->Legajo})" : 'Sin definir';
    }

    private function obtenerServiciosAsignados($empleado)
    {
        // Intentar obtener servicios activos desde la tabla pivote con información completa
        if ($empleado->serviciosActivos && count($empleado->serviciosActivos) > 0) {
            return $empleado->serviciosActivos->map(function ($s) use ($empleado) {
                // Obtener datos del pivot si existen
                $pivot = $s->pivot;

                // Usar el certificador_id del pivot, si existe. Sino, buscar el jefe del servicio.
                $certificadorId = $pivot && $pivot->certificador_id ? $pivot->certificador_id : null;

                // Si no hay certificador en el pivot, buscar el jefe del servicio
                if (!$certificadorId) {
                    $jefe = Empleado::where('Estado', 1)
                        ->where('idCargo', 2)
                        ->where(function($query) use ($s) {
                            $query->where('idServicio', $s->idServicio)
                                  ->orWhereHas('serviciosActivos', function($q) use ($s) {
                                      $q->where('servicio_id', $s->idServicio);
                                  });
                        })
                        ->first(['idEmpleado']);

                    $certificadorId = $jefe ? $jefe->idEmpleado : $empleado->idCertifica;
                }

                return [
                    'servicio_id' => $s->idServicio,
                    'nombre' => $s->servicio,
                    'certificador_id' => $certificadorId,
                    'fecha_pase' => $pivot && $pivot->fecha_inicio ? date('d/m/Y', strtotime($pivot->fecha_inicio)) : '',
                    'sector_id' => null // El sector se seleccionará por servicio
                ];
            })->values()->toArray();
        }

        // Fallback: Si no hay servicios activos pero tiene idServicio, usar ese
        if ($empleado->idServicio) {
            $servicio = Servicio::find($empleado->idServicio);
            if ($servicio) {
                return [
                    [
                        'servicio_id' => $servicio->idServicio,
                        'nombre' => $servicio->servicio,
                        'certificador_id' => $empleado->idCertifica,
                        'fecha_pase' => '',
                        'sector_id' => $empleado->idSector ?: null
                    ]
                ];
            }
        }

        return [];
    }

    /**
     * Formatear un UPDATE SQL del sistema antiguo a texto descriptivo
     */
    private function formatearUpdateSql($sql)
    {
        // Buscar cambios importantes en el SQL
        $cambios = [];

        // Detectar cambio de cargo
        if (preg_match("/idCargo\s*=\s*'(\d+)'/", $sql, $matches)) {
            $cargoId = $matches[1];
            $cargoNombre = match($cargoId) {
                '1' => 'Jefe de Sector',
                '2' => 'Jefe de Servicio',
                '3' => 'Jefe de Departamento',
                '4' => 'Gerente',
                '5' => 'Director',
                default => 'Cargo ' . $cargoId
            };
            if ($cargoId === '0') {
                $cambios[] = 'Se quitó el cargo de Jefe';
            } else {
                $cambios[] = "Cargo actualizado a {$cargoNombre}";
            }
        }

        // Detectar cambio de certificador
        if (preg_match("/IdEmpleado2\s*=\s*'(\d+)'/", $sql, $matches)) {
            $certificadorId = $matches[1];
            $certificador = Empleado::find($certificadorId);
            if ($certificador) {
                $cambios[] = "Certificador actualizado a {$certificador->nombreCompleto}";
            }
        }

        // Detectar cambio de servicio
        if (preg_match("/idServicio\s*=\s*'(\d+)'/", $sql, $matches)) {
            $servicioId = $matches[1];
            $servicio = Servicio::find($servicioId);
            if ($servicio) {
                $cambios[] = "Servicio actualizado a {$servicio->servicio}";
            }
        }

        // Detectar cambio de departamento
        if (preg_match("/idDepartamento\s*=\s*'(\d+)'/", $sql, $matches)) {
            $deptoId = $matches[1];
            $depto = Departamento::find($deptoId);
            if ($depto) {
                $cambios[] = "Departamento actualizado a {$depto->departamento}";
            }
        }

        // Detectar cambio de estado (baja)
        if (preg_match("/Estado\s*=\s*'(\d+)'/", $sql, $matches)) {
            $estado = $matches[1];
            if ($estado === '3') {
                $cambios[] = "Estado: Baja";
            } elseif ($estado === '1') {
                $cambios[] = "Estado: Activo";
            } elseif ($estado === '2') {
                $cambios[] = "Estado: Licencia";
            }
        }

        // Detectar cambio de función
        if (preg_match("/Funcion\s*=\s*'(\d+)'/", $sql, $matches)) {
            $funcionId = $matches[1];
            $funcion = Funcion::find($funcionId);
            if ($funcion) {
                $cambios[] = "Función actualizada a {$funcion->Funcion}";
            }
        }

        // Si se detectaron cambios importantes, mostrarlos
        if (!empty($cambios)) {
            return "Edición de datos personales: " . implode(', ', $cambios);
        }

        // Si no se pudieron detectar cambios específicos, mostrar un mensaje genérico
        return "Edición de datos personales (modificación múltiple)";
    }

    /**
     * Formatear un INSERT SQL del sistema antiguo a texto descriptivo
     */
    private function formatearInsertSql($sql)
    {
        // Los INSERT antiguos tienen formato: INSERT INTO empleados (Apellido,Nombre,DNI,...) VALUES ('ACEVEDO','OLGA',...)
        // Intentar extraer nombre y DNI
        $nombre = '';
        $dni = '';

        // Extraer los valores de la cláusula VALUES
        if (preg_match("/VALUES\s*\(([^)]+)\)/", $sql, $matches)) {
            $values = $matches[1];

            // Separar por comas (teniendo cuidado con las comillas)
            preg_match_all("/'([^']*)'/", $values, $allValues);
            $valoresArray = $allValues[1] ?? [];

            // Apellido es el primer valor, DNI es el tercero (índice 2)
            if (!empty($valoresArray[0])) {
                $apellido = $valoresArray[0];
                $nombre = $valoresArray[1] ?? '';
                $nombreCompleto = trim($apellido . ' ' . $nombre);
            }
            if (!empty($valoresArray[2])) {
                $dni = $valoresArray[2];
            }
        }

        if ($nombreCompleto && $dni) {
            return "Alta de empleado: {$nombreCompleto} (DNI: {$dni})";
        } elseif ($nombreCompleto) {
            return "Alta de empleado: {$nombreCompleto}";
        } else {
            return "Alta de nuevo empleado";
        }
    }

    /**
     * Guardar historial de modificaciones importantes al editar un empleado
     */
    private function guardarHistorialModificaciones($datosOriginales, $datosNuevos, $usuarioId)
    {
        $cambiosOrganizacionales = [];
        $cambiosPersonales = [];

        // Campos organizacionales (cargo, jefe, ubicación)
        $camposOrganizacionales = [
            'idCargo', 'IdEmpleado2', 'idServicio', 'idDepartamento', 'idGerencia', 'idSector'
        ];

        // Mapeo de campos importantes a descripciones
        $camposImportantes = [
            'Apellido' => 'personal',
            'Nombre' => 'personal',
            'DNI' => 'personal',
            'Telefono' => 'personal',
            'Celular' => 'personal',
            'Email' => 'personal',
            'calle' => 'personal',
            'CalleNum' => 'personal',
            'Piso' => 'personal',
            'Departamento' => 'personal',
            'Barrio' => 'personal',
            'CP' => 'personal',
            'Localidad' => 'personal',
            'Provincia' => 'personal',
            'idCargo' => 'organizacional',
            'IdEmpleado2' => 'organizacional',
            'idServicio' => 'organizacional',
            'idDepartamento' => 'organizacional',
            'idGerencia' => 'organizacional',
            'idSector' => 'organizacional',
            'idProfesion' => 'personal',
            'Funcion' => 'personal',
            'categoria' => 'personal',
            'idAgrupamiento' => 'personal',
            'Estado' => 'organizacional',
            'FBaja' => 'organizacional',
            'Observacion' => 'personal',
            'Matricula' => 'personal',
            'NroContrato' => 'personal',
            'EstCiv' => 'personal',
            'Nacionalidad' => 'personal',
            'FecNac' => 'personal',
            'FAlta' => 'personal',
            'idInstrucion' => 'personal',
            'DobleFS' => 'personal',
            'FE' => 'personal',
            'idTipoRelacion' => 'personal',
        ];

        foreach ($camposImportantes as $campo => $tipoCambio) {
            // Obtener valores originales y nuevos
            $valorOriginal = $datosOriginales[$campo] ?? null;
            $valorNuevo = $datosNuevos[$campo] ?? null;

            // Normalizar valores para comparación
            $valorOriginal = $this->normalizarValor($valorOriginal);
            $valorNuevo = $this->normalizarValor($valorNuevo);

            // Si hubo cambio
            if ($valorOriginal !== $valorNuevo) {
                $descripcionCambio = $this->describirCambio($campo, $valorOriginal, $valorNuevo);
                if ($descripcionCambio) {
                    if ($tipoCambio === 'organizacional') {
                        $cambiosOrganizacionales[] = $descripcionCambio;
                    } else {
                        $cambiosPersonales[] = $descripcionCambio;
                    }
                }
            }
        }

        // Guardar cambios organizacionales si hay
        if (!empty($cambiosOrganizacionales)) {
            try {
                HistorialModPers::create([
                    'FechaMod' => now(),
                    'Modificaciones' => 'Cambio organizacional: ' . implode(', ', $cambiosOrganizacionales),
                    'Modificador_Id' => $usuarioId,
                    'EmpleadoMod_Id' => $datosNuevos['idEmpleado'],
                    'tipo_cambio' => 'organizacional'
                ]);
            } catch (\Exception $e) {
                Log::error("Error guardando historial organizacional: " . $e->getMessage());
            }
        }

        // Guardar cambios personales si hay
        if (!empty($cambiosPersonales)) {
            try {
                HistorialModPers::create([
                    'FechaMod' => now(),
                    'Modificaciones' => 'Datos personales: ' . implode(', ', $cambiosPersonales),
                    'Modificador_Id' => $usuarioId,
                    'EmpleadoMod_Id' => $datosNuevos['idEmpleado'],
                    'tipo_cambio' => 'personal'
                ]);
            } catch (\Exception $e) {
                Log::error("Error guardando historial personal: " . $e->getMessage());
            }
        }
    }

    /**
     * Normalizar valor para comparación
     */
    private function normalizarValor($valor)
    {
        // Convertir null a string vacío
        if ($valor === null) {
            return '';
        }

        // Convertir boolean a string
        if (is_bool($valor)) {
            return $valor ? '1' : '0';
        }

        // Fechas vacías
        if (in_array($valor, ['0000-00-00', '', 'null'])) {
            return '';
        }

        return $valor;
    }

    /**
     * Generar descripción legible del cambio
     */
    private function describirCambio($campo, $valorOriginal, $valorNuevo)
    {
        $nombreLegible = match($campo) {
            'Apellido' => 'apellido',
            'Nombre' => 'nombre',
            'DNI' => 'DNI',
            'Telefono' => 'teléfono',
            'Celular' => 'celular',
            'Email' => 'email',
            'calle' => 'calle',
            'CalleNum' => 'número',
            'Piso' => 'piso',
            'Departamento' => 'departamento (dir.)',
            'Barrio' => 'barrio',
            'CP' => 'CP',
            'Localidad' => 'localidad',
            'Provincia' => 'provincia',
            'idCargo' => 'cargo',
            'IdEmpleado2' => 'certificador',
            'idServicio' => 'servicio',
            'idDepartamento' => 'departamento',
            'idGerencia' => 'gerencia',
            'idSector' => 'sector',
            'idProfesion' => 'profesión',
            'Funcion' => 'función',
            'categoria' => 'categoría',
            'idAgrupamiento' => 'agrupamiento',
            'Estado' => 'estado',
            'FBaja' => 'fecha de baja',
            'Observacion' => 'observación',
            'Matricula' => 'matrícula',
            'NroContrato' => 'nro. contrato',
            'EstCiv' => 'estado civil',
            'Nacionalidad' => 'nacionalidad',
            'FecNac' => 'fecha de nacimiento',
            'FAlta' => 'fecha de alta',
            'idInstrucion' => 'instrucción',
            'DobleFS' => 'doble FS',
            'FE' => 'franco electivo',
            'idTipoRelacion' => 'relación',
            default => $campo
        };

        // Manejo especial para ciertos campos
        return match($campo) {
            'idCargo' => $this->describirCambioCargo($valorOriginal, $valorNuevo),
            'IdEmpleado2' => $this->describirCambioCertificador($valorOriginal, $valorNuevo),
            'idServicio' => $this->describirCambioServicio($valorOriginal, $valorNuevo),
            'idDepartamento' => $this->describirCambioDepartamento($valorOriginal, $valorNuevo),
            'idGerencia' => $this->describirCambioGerencia($valorOriginal, $valorNuevo),
            'idProfesion' => $this->describirCambioProfesion($valorOriginal, $valorNuevo),
            'Funcion' => $this->describirCambioFuncion($valorOriginal, $valorNuevo),
            'categoria' => $this->describirCambioCategoria($valorOriginal, $valorNuevo),
            'idAgrupamiento' => $this->describirCambioAgrupamiento($valorOriginal, $valorNuevo),
            'Estado' => $this->describirCambioEstado($valorOriginal, $valorNuevo),
            'FBaja' => $this->describirCambioBaja($valorOriginal, $valorNuevo),
            'EstCiv' => $this->describirCambioEstadoCivil($valorOriginal, $valorNuevo),
            'Nacionalidad' => $this->describirCambioNacionalidad($valorOriginal, $valorNuevo),
            'idInstrucion' => $this->describirCambioInstruccion($valorOriginal, $valorNuevo),
            'Provincia' => $this->describirCambioProvincia($valorOriginal, $valorNuevo),
            'Localidad' => $this->describirCambioLocalidad($valorOriginal, $valorNuevo),
            'idTipoRelacion' => $this->describirCambioRelacion($valorOriginal, $valorNuevo),
            'DobleFS', 'FE' => $this->describirCambioBooleano($nombreLegible, $valorOriginal, $valorNuevo),
            default => "{$nombreLegible}: '{$valorOriginal}' → '{$valorNuevo}'"
        };
    }

    private function describirCambioCargo($original, $nuevo)
    {
        $nombreOriginal = match($original) {
            '1', 1 => 'Jefe de Sector',
            '2', 2 => 'Jefe de Servicio',
            '3', 3 => 'Jefe de Departamento',
            '4', 4 => 'Gerente',
            '5', 5 => 'Director',
            '0', 0, '' => 'Sin cargo',
            default => 'Cargo ' . $original
        };

        $nombreNuevo = match($nuevo) {
            '1', 1 => 'Jefe de Sector',
            '2', 2 => 'Jefe de Servicio',
            '3', 3 => 'Jefe de Departamento',
            '4', 4 => 'Gerente',
            '5', 5 => 'Director',
            '0', 0, '' => 'Sin cargo',
            default => 'Cargo ' . $nuevo
        };

        return "cargo: {$nombreOriginal} → {$nombreNuevo}";
    }

    private function describirCambioCertificador($original, $nuevo)
    {
        if ($nuevo && $certificador = Empleado::find($nuevo)) {
            $nombreNuevo = $certificador->nombreCompleto;
        } else {
            $nombreNuevo = 'Sin certificador';
        }

        if ($original && $certificadorOrig = Empleado::find($original)) {
            $nombreOriginal = $certificadorOrig->nombreCompleto;
        } else {
            $nombreOriginal = 'Sin certificador';
        }

        return "certificador: {$nombreOriginal} → {$nombreNuevo}";
    }

    private function describirCambioServicio($original, $nuevo)
    {
        if ($nuevo && $servicio = Servicio::find($nuevo)) {
            $nombreNuevo = $servicio->servicio;
        } else {
            $nombreNuevo = 'Sin servicio';
        }

        if ($original && $servicioOrig = Servicio::find($original)) {
            $nombreOriginal = $servicioOrig->servicio;
        } else {
            $nombreOriginal = 'Sin servicio';
        }

        return "servicio: {$nombreOriginal} → {$nombreNuevo}";
    }

    private function describirCambioDepartamento($original, $nuevo)
    {
        if ($nuevo && $depto = Departamento::find($nuevo)) {
            $nombreNuevo = $depto->departamento;
        } else {
            $nombreNuevo = 'Sin departamento';
        }

        if ($original && $deptoOrig = Departamento::find($original)) {
            $nombreOriginal = $deptoOrig->departamento;
        } else {
            $nombreOriginal = 'Sin departamento';
        }

        return "departamento: {$nombreOriginal} → {$nombreNuevo}";
    }

    private function describirCambioGerencia($original, $nuevo)
    {
        if ($nuevo && $gerencia = Gerencia::find($nuevo)) {
            $nombreNuevo = $gerencia->Gerencia;
        } else {
            $nombreNuevo = 'Sin gerencia';
        }

        if ($original && $gerenciaOrig = Gerencia::find($original)) {
            $nombreOriginal = $gerenciaOrig->Gerencia;
        } else {
            $nombreOriginal = 'Sin gerencia';
        }

        return "gerencia: {$nombreOriginal} → {$nombreNuevo}";
    }

    private function describirCambioProfesion($original, $nuevo)
    {
        if ($nuevo && $prof = Profesion::find($nuevo)) {
            return "profesión: {$prof->profesion}";
        }
        return null;
    }

    private function describirCambioFuncion($original, $nuevo)
    {
        if ($nuevo && $func = Funcion::find($nuevo)) {
            return "función: {$func->Funcion}";
        }
        return null;
    }

    private function describirCambioCategoria($original, $nuevo)
    {
        if ($nuevo && $cat = Categoria::find($nuevo)) {
            return "categoría: {$cat->categoria}";
        }
        return null;
    }

    private function describirCambioAgrupamiento($original, $nuevo)
    {
        if ($nuevo && $agrup = Agrupamiento::find($nuevo)) {
            return "agrupamiento: {$agrup->agrupamiento}";
        }
        return null;
    }

    private function describirCambioEstado($original, $nuevo)
    {
        $estados = [
            1 => 'Activo',
            2 => 'Licencia',
            3 => 'Baja',
            0 => 'Inactivo'
        ];

        $nombreOriginal = $estados[$original] ?? 'Desconocido';
        $nombreNuevo = $estados[$nuevo] ?? 'Desconocido';

        if ($nuevo == 3) {
            return "EMPLEADO DADO DE BAJA (era: {$nombreOriginal})";
        }

        return "estado: {$nombreOriginal} → {$nombreNuevo}";
    }

    private function describirCambioBaja($original, $nuevo)
    {
        if ($nuevo && $nuevo !== '0000-00-00' && $nuevo !== '') {
            return "fecha de baja establecida: {$nuevo}";
        }
        return null;
    }

    private function describirCambioEstadoCivil($original, $nuevo)
    {
        if ($nuevo && $est = EstadoCivil::find($nuevo)) {
            return "estado civil: {$est->EstadoCivil}";
        }
        return null;
    }

    private function describirCambioNacionalidad($original, $nuevo)
    {
        if ($nuevo && $nac = Pais::find($nuevo)) {
            return "nacionalidad: {$nac->Pais}";
        }
        return null;
    }

    private function describirCambioInstruccion($original, $nuevo)
    {
        if ($nuevo && $inst = Instruccion::find($nuevo)) {
            return "instrucción: {$inst->instruccion}";
        }
        return null;
    }

    private function describirCambioProvincia($original, $nuevo)
    {
        if ($nuevo && $prov = Provincia::find($nuevo)) {
            return "provincia: {$prov->Provincia}";
        }
        return null;
    }

    private function describirCambioLocalidad($original, $nuevo)
    {
        if ($nuevo && $loc = Localidad::find($nuevo)) {
            return "localidad: {$loc->Localidad}";
        }
        return null;
    }

    private function describirCambioRelacion($original, $nuevo)
    {
        if ($nuevo && $rel = TipoRelacion::find($nuevo)) {
            return "relación laboral: {$rel->TipoRelacion}";
        }
        return null;
    }

    private function describirCambioBooleano($campo, $original, $nuevo)
    {
        if ($nuevo == 1 && $original != 1) {
            return "{$campo} activado";
        } elseif ($nuevo != 1 && $original == 1) {
            return "{$campo} desactivado";
        }
        return null;
    }

    /**
     * Formatear fecha de manera segura, retornando vacío si la fecha es inválida
     * Maneja casos como 0000-00-00, 0001-11-30, fechas negativas, etc.
     */
    private function formatearFechaSegura($fecha)
    {
        if (!$fecha) {
            return '';
        }

        // Si es vacía o es la fecha nula de MySQL
        if ($fecha === '0000-00-00' || trim($fecha) === '') {
            return '';
        }

        try {
            $parsed = \Carbon\Carbon::parse($fecha);
            $anio = $parsed->year;

            // Si el año es inválido (menor a 1900 o mayor a 2100), retornar vacío
            // Esto maneja casos como 0001-11-30 que produce año -1
            if ($anio < 1900 || $anio > 2100) {
                return '';
            }

            return $parsed->format('d/m/Y');
        } catch (\Exception $e) {
            return '';
        }
    }
}

