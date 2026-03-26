# URL analysis: removing boss status in person view

**Session ID:** ses_4484c3fa5ffe5t31MVpdUpmemX
**Created:** 13/1/2026, 11:12:49
**Updated:** 13/1/2026, 11:26:29

---

## User

http://127.0.0.1:8003/personal analizame este url, que pasa cuando en la vista a una persona que era jefe, se le quita el estatus?

---

## Assistant (Plan · glm-4.7-free · 38.6s)

_Thinking:_

El usuario me pide analizar el URL http://127.0.0.1:8003/personal y quiere saber qué pasa cuando en la vista a una persona que era jefe, se le quita el estatus.

Estoy en modo plan, así que debo:
1. Analizar el código relacionado con la vista /personal
2. Entender cómo funciona el estatus de jefe
3. Determinar qué ocurre cuando se quita este estatus
4. NO hacer ninguna modificación

Primero necesito explorar el códigobase para entender esta ruta. Debería buscar:
- Rutas que definan /personal
- Controladores relacionados con personal
- Vistas de personal
- Modelos de personal con estatus de jefe

Empezaré buscando archivos relacionados con "personal" y "jefe".

Voy a analizar el código relacionado con la ruta `/personal` y el estatus de jefe. Déjame explorar el códigobase para entender cómo funciona.

```
Tool: grep

**Input:**
```json
{
  "pattern": "/personal",
  "include": "*.php"
}
```
**Output:**
```
Found 37 matches
D:\laragon\www\zona_capital_laravel\storage\framework\views\c075e84c9616a45a03b167c9b9ec4d6ea39de5f0.php:
  Line 821:                 const url = `/personal/${id}`;
  Line 993:                 apiLaravel('/personal/selectores-iniciales', 'GET')
  Line 1042:                     apiLaravel('/personal/selectores-iniciales', 'GET')
  Line 1049:                                 return apiLaravel(`/personal/departamentos?gerencia_id=${empleado.gerencia}`, 'GET');
  Line 1059:                                     return apiLaravel(`/personal/servicios?departamento_id=${empleado.departamento}`, 'GET');
  Line 1070:                                     return apiLaravel(`/personal/sectores?servicio_id=${empleado.servicio}`, 'GET');
  Line 1087:                     apiLaravel('/personal/selectores-iniciales', 'GET')
  Line 1093:                                 return apiLaravel(`/personal/localidades?provincia_id=${empleado.provincia}`, 'GET');
  Line 1310:                             apiLaravel(`/personal/${id}`, 'DELETE')
  Line 1328:                         apiLaravel(`/personal/${id}`, 'DELETE')
  Line 1346: <?php echo $__env->make('layouts.main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\laragon\www\zona_capital_laravel\resources\views/personal-ver.blade.php ENDPATH**/ ?>

D:\laragon\www\zona_capital_laravel\storage\framework\views\c2645c1a8283544d2b2b9c3b5063eef25fc7b93b.php:
  Line 835:     <script src="<?php echo e(asset('js/personal.js')); ?>"></script>
  Line 915: <?php echo $__env->make('layouts.main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\laragon\www\zona_capital_laravel\resources\views/personal.blade.php ENDPATH**/ ?>

D:\laragon\www\zona_capital_laravel\resources\views\personal.blade.php:
  Line 829:     <script src="{{ asset('js/personal.js') }}"></script>

D:\laragon\www\zona_capital_laravel\resources\views\personal-ver.blade.php:
  Line 816:                 const url = `/personal/${id}`;
  Line 988:                 apiLaravel('/personal/selectores-iniciales', 'GET')
  Line 1037:                     apiLaravel('/personal/selectores-iniciales', 'GET')
  Line 1044:                                 return apiLaravel(`/personal/departamentos?gerencia_id=${empleado.gerencia}`, 'GET');
  Line 1054:                                     return apiLaravel(`/personal/servicios?departamento_id=${empleado.departamento}`, 'GET');
  Line 1065:                                     return apiLaravel(`/personal/sectores?servicio_id=${empleado.servicio}`, 'GET');
  Line 1082:                     apiLaravel('/personal/selectores-iniciales', 'GET')
  Line 1088:                                 return apiLaravel(`/personal/localidades?provincia_id=${empleado.provincia}`, 'GET');
  Line 1305:                             apiLaravel(`/personal/${id}`, 'DELETE')
  Line 1323:                         apiLaravel(`/personal/${id}`, 'DELETE')

D:\laragon\www\zona_capital_laravel\public\debug_session.php:
  Line 104:                 <a href="/personal" class="btn">👥 Ir a Personal</a>
  Line 105:                 <a href="/personal/1/ver" class="btn">👁️ Ver Personal (Test)</a>
  Line 182:         <a href="<?= url('/personal') ?>" class="btn">👥 Módulo Personal</a>

D:\laragon\www\zona_capital_laravel\public\debug_permisos_ruta.php:
  Line 195:         <p><a href="/personal">👥 Ir a Personal</a></p>
  Line 196:         <p><a href="/personal/1/ver">👁️ Ver Personal ID 1</a></p>

D:\laragon\www\zona_capital_laravel\resources\views\old_version_personal.blade.php:
  Line 850:     <script src="{{ asset('js/personal.js') }}?v={{ time() }}"></script>

D:\laragon\www\zona_capital_laravel\zona_capital\zonaCapital\php\Model\ABMPersonalModel.php:
  Line 411:             $targetPath = "../img/personal/" . $personalId . ".png";

D:\laragon\www\zona_capital_laravel\zona_capital\zonacapital_old\php\clases\cl_personal.php:
  Line 17062:                 $url  = "img/personal/".$nombre_img_personal.".png";
  Line 17149:             unlink("img/personal/".$imagen.".png");
  Line 17381:             $url  = "img/personal/".$nombre_img_personal.".png";

D:\laragon\www\zona_capital_laravel\zona_capital\zonacapital_old\personal_20.php:
  Line 71: 		<script type="text/javascript" language="javascript" src="js/mod/personal20.js?rand=<?php echo time()?>"></script>

D:\laragon\www\zona_capital_laravel\zona_capital\zonacapital_old\cbu.php:
  Line 32: 		<script type="text/javascript" language="javascript" src="js/mod/personal_2.js?v=5"></script>

D:\laragon\www\zona_capital_laravel\zona_capital\zonacapital_old\FEjs\personal.php:
  Line 25: 		<script type="text/javascript" src="js/Soporte/personal.js"></script>	
```
```

```
Tool: glob

**Input:**
```json
{
  "pattern": "**/personal*.php"
}
```
**Output:**
```
D:\laragon\www\zona_capital_laravel\resources\views\personal.blade.php
D:\laragon\www\zona_capital_laravel\resources\views\personal-ver.blade.php
D:\laragon\www\zona_capital_laravel\zona_capital\zonacapital_old\personal_20.php
D:\laragon\www\zona_capital_laravel\zona_capital\zonacapital_old\php\servicios\personal_acc.php
D:\laragon\www\zona_capital_laravel\zona_capital\zonacapital_old\FEjs\personal.php
```
```

```
Tool: grep

**Input:**
```json
{
  "pattern": "jefe",
  "include": "*.php"
}
```
**Output:**
```
Found 100 matches
D:\laragon\www\zona_capital_laravel\storage\framework\views\396240fd5a0e27c3ddffdadf0f9135555bffb64e.php:
  Line 241:                                         <label for="jefes_fil">Jefes:</label>
  Line 242:                                         <select class="form-control select2" name="jefes_fil" id="jefes_fil">

D:\laragon\www\zona_capital_laravel\storage\framework\views\2281918f861da7b5031e5d61a9832b1130774afd.php:
  Line 146:                                     <input type="text" id="certifica" class="form-control" placeholder="Buscar jefe...">
  Line 147:                                     <small class="text-muted">Seleccionar jefe certificador</small>
  Line 185:         jefesFull: <?php echo json_encode($jefesFull, 15, 512) ?>,
  Line 186:         jefesLimit: <?php echo json_encode($jefesLimit, 15, 512) ?>,

D:\laragon\www\zona_capital_laravel\routes\web.php:
  Line 126:         Route::get('/jefe-servicio', [PersonalController::class, 'getJefeServicio'])->name('personal.jefe-servicio');
  Line 130:         Route::get('/buscar-jefe', [PersonalController::class, 'buscarJefe'])->name('personal.buscar-jefe');
  Line 536:             // Obtener jefe de gerencia (idCargo = 4 o 5 como en el original)
  Line 537:             $jefeGerencia = \App\Models\Empleado::where('idGerencia', $gerencia->idGerencia)
  Line 554:             if($jefeGerencia) {
  Line 555:                 $html .= '<div class="org-info">👤 ' . htmlspecialchars($jefeGerencia->Apellido . ', ' . $jefeGerencia->Nombre) . '</div>';
  Line 575:                     // Obtener jefe de departamento (idCargo = 3 como en el original)
  Line 576:                     $jefeDept = \App\Models\Empleado::where('idDepartamento', $dept->idDepartamento)
  Line 590:                     if($jefeDept) {
  Line 591:                         $html .= '<div class="org-info">👤 ' . htmlspecialchars($jefeDept->Apellido . ', ' . $jefeDept->Nombre) . '</div>';
  Line 610:                             // Obtener jefe de servicio (idCargo = 2 como en el original)
  Line 611:                             $jefeServ = \App\Models\Empleado::where('idServicio', $serv->idServicio)
  Line 625:                             if($jefeServ) {
  Line 626:                                 $html .= '<div class="org-info">👤 ' . htmlspecialchars($jefeServ->Apellido . ', ' . $jefeServ->Nombre) . '</div>';
  Line 645:                                     // Obtener jefe de sector (idCargo = 1 como en el original)
  Line 646:                                     $jefeSector = \App\Models\Empleado::where('idSector', $sector->idSector)
  Line 660:                                     if($jefeSector) {
  Line 661:                                         $html .= '<div class="org-info">👤 ' . htmlspecialchars($jefeSector->Apellido . ', ' . $jefeSector->Nombre) . '</div>';
  Line 739:     // Gestión de jefes
  Line 740:     Route::get('/jefes', [App\Http\Controllers\ProgramacionPersonalController::class, 'obtenerJefesApi']);

D:\laragon\www\zona_capital_laravel\storage\framework\views\c2645c1a8283544d2b2b9c3b5063eef25fc7b93b.php:
  Line 222:                                     <label for="filtro_jefe" class="form-label">Jefe</label>
  Line 223:                                     <select class="form-select select2" id="filtro_jefe" name="jefe">
  Line 225:                                         <?php $__currentLoopData = $jefes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jefe): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  Line 226:                                             <option value="<?php echo e($jefe->idEmpleado); ?>">
  Line 227:                                                 <?php echo e($jefe->Apellido); ?>, <?php echo e($jefe->Nombre); ?> (<?php echo e($jefe->Legajo); ?>)
  Line 595:                                         onchange="CargaSelectDto()" data-id_jefe="4">>
  Line 608:                                         onchange="CargaSelectServ()" data-id_jefe="3">>

D:\laragon\www\zona_capital_laravel\app\Http\Controllers\PersonalController.php:
  Line 58:         $jefes = Empleado::where('idCargo', '!=', 0)
  Line 72:             'jefes' => $jefes,
  Line 139:         $jefe = $request->input('jefe', 0);
  Line 205:         if ($jefe > 0) {
  Line 206:             $query->where('IdEmpleado2', $jefe);
  Line 1034:      * Get jefe del servicio (empleado con idCargo = 2 en ese servicio)
  Line 1041:             return response()->json(['jefe' => null]);
  Line 1046:         $jefe = Empleado::where('Estado', 1)
  Line 1056:         if ($jefe) {
  Line 1058:                 'jefe' => [
  Line 1059:                     'id' => $jefe->idEmpleado,
  Line 1060:                     'nombre' => $jefe->Apellido . ', ' . $jefe->Nombre . ' (Leg. ' . $jefe->Legajo . ')'
  Line 1065:         return response()->json(['jefe' => null]);
  Line 1531:      * Buscar jefe según la jerarquía organizacional
  Line 1538:             $cargoJefe = $request->get('cargo_jefe');
  Line 1543:                 ->where('empleados.idCargo', $cargoJefe); // Con el cargo de jefe correspondiente
  Line 1560:                     return response()->json(['jefe' => null]);
  Line 1563:             $jefe = $query->first();
  Line 1565:             if ($jefe) {
  Line 1567:                     'jefe' => [
  Line 1568:                         'idEmpleado' => $jefe->idEmpleado,
  Line 1569:                         'legajo' => $jefe->Legajo,
  Line 1570:                         'nombres' => $jefe->Nombre,
  Line 1571:                         'apellidos' => $jefe->Apellido,
  Line 1572:                         'cargo' => $jefe->Cargo
  Line 1576:                 return response()->json(['jefe' => null]);
  Line 1579:             return response()->json(['error' => 'Error al buscar jefe: ' . $e->getMessage()], 500);
  Line 1630:                 // Usar el certificador_id del pivot, si existe. Sino, buscar el jefe del servicio.
  Line 1633:                 // Si no hay certificador en el pivot, buscar el jefe del servicio
  Line 1635:                     $jefe = Empleado::where('Estado', 1)
  Line 1645:                     $certificadorId = $jefe ? $jefe->idEmpleado : $empleado->idCertifica;

D:\laragon\www\zona_capital_laravel\app\Models\Empleado.php:
  Line 88:         'IdEmpleado2', // Campo que certifica (jefe)

D:\laragon\www\zona_capital_laravel\resources\views\personal.blade.php:
  Line 220:                                     <label for="filtro_jefe" class="form-label">Jefe</label>
  Line 221:                                     <select class="form-select select2" id="filtro_jefe" name="jefe">
  Line 223:                                         @foreach ($jefes as $jefe)
  Line 224:                                             <option value="{{ $jefe->idEmpleado }}">
  Line 225:                                                 {{ $jefe->Apellido }}, {{ $jefe->Nombre }} ({{ $jefe->Legajo }})
  Line 591:                                         onchange="CargaSelectDto()" data-id_jefe="4">>
  Line 603:                                         onchange="CargaSelectServ()" data-id_jefe="3">>

D:\laragon\www\zona_capital_laravel\app\Http\Controllers\ProgramacionPersonalController.php:
  Line 24:         // Obtener jefes disponibles
  Line 25:         $jefes = $this->obtenerJefes($usuarioId, $permisos);
  Line 26:         // dd($jefes);
  Line 29:             'jefes' => $jefes
  Line 45:             $jefeId = $request->get('jefe_id', 0);
  Line 59:             $empleados = $this->obtenerEmpleadosPorJefe($jefeId, $usuarioId);
  Line 63:                 'jefeId' => $jefeId,
  Line 79:                     'message' => 'No se encontraron empleados para el jefe seleccionado'
  Line 136:             // NOTA AGENTE: Para cumplir estrictamente "solo debe verle asignado en su horario", necesitariamos el servicio_id del jefe.
  Line 284:             $jefeActual = DB::table('empleados')->where('idEmpleado', $usuarioId)->first();
  Line 285:             $servicioId = $jefeActual ? $jefeActual->idServicio : null;
  Line 807:             Log::error('Error al obtener jefes: ' . $e->getMessage());
  Line 812:     private function obtenerEmpleadosPorJefe($jefeId, $usuarioId)
  Line 832:             if ($jefeId > 0) {
  Line 833:                 // Obtener el servicio del jefe
  Line 834:                 $jefe = DB::table('empleados')->where('idEmpleado', $jefeId)->first();
  Line 835:                 // Si el jefe tiene servicio asignado, filtramos por la tabla pivote
  Line 836:                 if ($jefe && $jefe->idServicio) {
  Line 838:                         ->where('es.servicio_id', $jefe->idServicio)
  Line 843:                     $query->where('e.IdEmpleado2', $jefeId);
  Line 846:                 // Si jefe_id es 0, verificar permisos del usuario
  Line 850:                     // Usuario normal (jefe logueado): filtrar por su servicio
  Line 851:                     $jefe = DB::table('empleados')->where('idEmpleado', $usuarioId)->first();
  Line 852:                     if ($jefe && $jefe->idServicio) {
  Line 854:                             ->where('es.servicio_id', $jefe->idServicio)
  Line 905:             // Obtener jefes disponibles
  Line 906:             $jefes = $this->obtenerJefes($usuarioId, $permisos);
  Line 910:                 'jefes' => $jefes

(Results are truncated. Consider using a more specific path or pattern.)
```
```

---

## Assistant (Plan · glm-4.7-free · 6.2s)

_Thinking:_

Bien, ahora necesito leer los archivos principales para entender la lógica. Voy a leer:

1. El controlador PersonalController.php - que maneja la vista de personal
2. La vista personal.blade.php - que es la interfaz
3. La vista personal-ver.blade.php - que parece ser la vista de detalle
4. El modelo Empleado.php - para entender los campos y relaciones

Empecemos leyendo estos archivos.

```
Tool: read

**Input:**
```json
{
  "filePath": "D:\\laragon\\www\\zona_capital_laravel\\app\\Http\\Controllers\\PersonalController.php"
}
```
**Output:**
```
<file>
00001| <?php
00002| 
00003| namespace App\Http\Controllers;
00004| 
00005| use Illuminate\Http\Request;
00006| use Illuminate\Support\Facades\Log;
00007| use App\Models\Empleado;
00008| use App\Models\Departamento;
00009| use App\Models\Servicio;
00010| use App\Models\Sector;
00011| use App\Models\Gerencia;
00012| use App\Models\Provincia;
00013| use App\Models\Localidad;
00014| use App\Models\EstadoCivil;
00015| use App\Models\Estado;
00016| use App\Models\Profesion;
00017| use App\Models\Funcion;
00018| use App\Models\Instruccion;
00019| use App\Models\TipoRelacion;
00020| use App\Models\TipoJornada;
00021| use App\Models\MotivoBaja;
00022| use App\Models\Pais;
00023| use App\Models\DocumentoEscaneado;
00024| use App\Models\HistorialRelacion;
00025| use App\Models\HistorialModPers;
00026| use App\Models\JornadaXEmp;
00027| use App\Models\Agrupamiento;
00028| use App\Models\Categoria;
00029| use App\Models\Cargo;
00030| use App\Helpers\PermisoHelper;
00031| use App\Helpers\LogHelper;
00032| use Illuminate\Support\Facades\DB;
00033| use Illuminate\Support\Facades\Storage;
00034| 
00035| class PersonalController extends Controller
00036| {
00037|     /**
00038|      * Display a listing of the personnel records.
00039|      *
00040|      * @param  \Illuminate\Http\Request  $request
00041|      * @return \Illuminate\Http\Response
00042|      */
00043|     public function index(Request $request)
00044|     {
00045|         // Get the user ID from session
00046|         $usuarioId = session('usuario_id');
00047| 
00048|         // Get permissions for this view
00049|         $permisos = PermisoHelper::obtenerPermisos($usuarioId, 'personal');
00050| 
00051|         // Get all data for dropdown filters
00052|         $gerencias = Gerencia::orderBy('Gerencia')->get();
00053|         $profesiones = Profesion::orderBy('profesion')->get();
00054|         $funciones = Funcion::orderBy('Funcion')->get();
00055|         $estados = Estado::orderBy('estado')->get();
00056| 
00057|         // Get additional filter data
00058|         $jefes = Empleado::where('idCargo', '!=', 0)
00059|             ->whereNotNull('idCargo')
00060|             ->orderBy('Apellido')
00061|             ->orderBy('Nombre')
00062|             ->get(['idEmpleado', 'Apellido', 'Nombre', 'Legajo']);
00063| 
00064|         $cargos = Cargo::orderBy('cargo')->get(['idCargo', 'cargo']);
00065| 
00066|         return view('personal', [
00067|             'permisos' => $permisos,
00068|             'gerencias' => $gerencias,
00069|             'profesiones' => $profesiones,
00070|             'funciones' => $funciones,
00071|             'estados' => $estados,
00072|             'jefes' => $jefes,
00073|             'cargos' => $cargos
00074|         ]);
00075|     }
00076| 
00077|     /**
00078|      * Show a single personnel record in a read-only view
00079|      *
00080|      * @param  int  $id
00081|      * @return \Illuminate\Http\Response
00082|      */
00083|     public function show($id)
00084|     {
00085|         // Get the user ID from session
00086| 
00087|         $usuarioId = session('usuario_id');
00088| 
00089|         // Get permissions for this view - use 'personal' instead of the full path
00090|         $permisos = PermisoHelper::obtenerPermisos($usuarioId, 'personal');
00091| 
00092|         // Check if user has permission to read
00093|         if (!($permisos['leer'] ?? false)) {
00094|             return response('No tiene permisos para ver este contenido.', 403);
00095|         }
00096| 
00097|         // Get the employee data directly
00098|         $empleadoData = $this->getById($id);
00099| 
00100|         $empleado = null;
00101| 
00102|         if ($empleadoData instanceof \Illuminate\Http\JsonResponse) {
00103|             $responseData = $empleadoData->getData(true);
00104|             if ($responseData['success'] && isset($responseData['data'])) {
00105|                 $empleado = $responseData['data'];
00106|             }
00107|         }
00108| 
00109| 
00110|         return view('personal-ver', [
00111|             'permisos' => $permisos,
00112|             'empleadoId' => $id,
00113|             'empleado' => $empleado
00114|         ]);
00115|     }
00116| 
00117|     /**
00118|      * Get filtered personnel data with pagination
00119|      *
00120|      * @param  \Illuminate\Http\Request  $request
00121|      * @return \Illuminate\Http\Response
00122|      */
00123|     public function getPersonal(Request $request)
00124|     {
00125|         // Get filter parameters
00126|         $apellidoNombre = $request->input('apellido_nombre', '');
00127|         $legajo = $request->input('legajo', '');
00128|         $dni = $request->input('dni', '');
00129|         $sexo = $request->input('sexo', 0);
00130|         $edad = $request->input('edad', '');
00131|         $profesion = $request->input('profesion', 0);
00132|         $funcion = $request->input('funcion', 0);
00133|         $gerencia = $request->input('gerencia', 0);
00134|         $departamento = $request->input('departamento', 0);
00135|         $servicio = $request->input('servicio', 0);
00136|         $sector = $request->input('sector', 0);
00137|         // Por defecto mostrar activos (id 1). Si el cliente envía 0 significa 'Todos'.
00138|         $estado = $request->input('estado', 1);
00139|         $jefe = $request->input('jefe', 0);
00140|         $cargo = $request->input('cargo', 0);
00141|         $pagina = $request->input('pagina', 1);
00142|         $porPagina = $request->input('porPagina', 10);
00143| 
00144|         // Build query with relationships
00145|         $query = Empleado::with([
00146|             'gerencia',
00147|             'departamento',
00148|             'servicio',
00149|             'sector'
00150|         ]);
00151| 
00152|         // Apply filters
00153|         if (!empty($apellidoNombre)) {
00154|             $query->where(function ($q) use ($apellidoNombre) {
00155|                 $q->where('Apellido', 'LIKE', "%{$apellidoNombre}%")
00156|                     ->orWhere('Nombre', 'LIKE', "%{$apellidoNombre}%")
00157|                     ->orWhereRaw("CONCAT(Apellido, ' ', Nombre) LIKE ?", ["%{$apellidoNombre}%"]);
00158|             });
00159|         }
00160| 
00161|         if (!empty($legajo)) {
00162|             $query->where('Legajo', 'LIKE', "%{$legajo}%");
00163|         }
00164| 
00165|         if (!empty($dni)) {
00166|             $query->where('DNI', 'LIKE', "%{$dni}%");
00167|         }
00168| 
00169|         if ($sexo > 0) {
00170|             $query->where('sexo', $sexo);
00171|         }
00172| 
00173|         if (!empty($edad)) {
00174|             $query->whereRaw('TIMESTAMPDIFF(YEAR, FecNac, CURDATE()) = ?', [$edad]);
00175|         }
00176| 
00177|         if ($profesion > 0) {
00178|             $query->where('idProfesion', $profesion);
00179|         }
00180| 
00181|         if ($funcion > 0) {
00182|             $query->where('Funcion', $funcion);
00183|         }
00184| 
00185|         if ($gerencia > 0) {
00186|             $query->where('idGerencia', $gerencia);
00187|         }
00188| 
00189|         if ($departamento > 0) {
00190|             $query->where('idDepartamento', $departamento);
00191|         }
00192| 
00193|         if ($servicio > 0) {
00194|             $query->where('idServicio', $servicio);
00195|         }
00196| 
00197|         if ($sector > 0) {
00198|             $query->where('idSector', $sector);
00199|         }
00200| 
00201|         if ($estado > 0) {
00202|             $query->where('Estado', $estado);
00203|         }
00204| 
00205|         if ($jefe > 0) {
00206|             $query->where('IdEmpleado2', $jefe);
00207|         }
00208| 
00209|         if ($cargo > 0) {
00210|             $query->where('idCargo', $cargo);
00211|         }
00212| 
00213|         // Order by apellido and nombre
00214|         $query->orderBy('Apellido')->orderBy('Nombre');
00215| 
00216|         // Count total records (before pagination)
00217|         $totalRegistros = $query->count();
00218| 
00219|         // Apply pagination
00220|         $offset = ($pagina - 1) * $porPagina;
00221|         $empleados = $query->skip($offset)->take($porPagina)->get();
00222| 
00223|         // Calculate total pages
00224|         $totalPaginas = ceil($totalRegistros / $porPagina);
00225| 
00226|         // Format the data
00227|         $formattedEmpleados = $empleados->map(function ($item) {
00228|             return [
00229|                 'idEmpleado' => $item->idEmpleado,
00230|                 'legajo' => $item->Legajo,
00231|                 'nombre_completo' => $item->Apellido . ', ' . $item->Nombre,
00232|                 'dni' => $item->DNI,
00233|                 'sexo' => $item->sexo == 1 ? 'M' : 'F',
00234|                 'edad' => $item->FecNac ? \Carbon\Carbon::parse($item->FecNac)->age : '-',
00235|                 'gerencia' => $item->gerencia->Gerencia ?? '-',
00236|                 'departamento' => $item->departamento->departamento ?? '-',
00237|                 'servicio' => $item->servicio->servicio ?? '-',
00238|                 'sector' => $item->sector->sector ?? '-',
00239|                 'estado' => $item->Estado == 1 ? 'Activo' : 'Inactivo',
00240|                 'fecha_alta' => $item->FAlta ? \Carbon\Carbon::parse($item->FAlta)->format('d/m/Y') : '-'
00241|             ];
00242|         });
00243| 
00244|         return response()->json([
00245|             'data' => $formattedEmpleados,
00246|             'totalRegistros' => $totalRegistros,
00247|             'totalPaginas' => $totalPaginas,
00248|             'paginaActual' => $pagina,
00249|             'porPagina' => $porPagina
00250|         ]);
00251|     }
00252| 
00253|     /**
00254|      * Get a single personnel record
00255|      *
00256|      * @param  int  $id
00257|      * @return \Illuminate\Http\Response
00258|      */
00259|     public function getById($id)
00260|     {
00261|         // Find the record with all relationships
00262|         $empleado = Empleado::with([
00263|             'gerencia',
00264|             'departamento',
00265|             'servicio',
00266|             'sector',
00267|             'provincia',
00268|             'localidad',
00269|             'estadoCivil',
00270|             'profesion',
00271|             'funcion',
00272|             'instruccion',
00273|             'tipoRelacion',
00274|             'tipoJornada',
00275|             'motivoBaja',
00276|             'pais'
00277|         ])->find($id);
00278| 
00279|         if (!$empleado) {
00280|             return response()->json([
00281|                 'success' => false,
00282|                 'message' => 'Empleado no encontrado'
00283|             ], 404);
00284|         }
00285| 
00286|         // Get historial de relaciones
00287|         $historialRelaciones = HistorialRelacion::with('tipoRelacion')
00288|             ->where('Personal_Id', $id)
00289|             ->orderBy('Desde', 'desc')
00290|             ->get();
00291| 
00292|         // Get documentos escaneados
00293|         $documentos = DocumentoEscaneado::where('Empleado_Id', $id)->get();
00294| 
00295|         // Get jornadas
00296|         $jornadas = JornadaXEmp::with('tipoJornada')
00297|             ->where('Emp_Id', $id)
00298|             ->orderBy('FechaJor', 'desc')
00299|             ->get();
00300| 
00301|         // Format the data with descriptive names
00302|         $data = [
00303|             'idEmpleado' => $empleado->idEmpleado,
00304|             'legajo' => $empleado->Legajo,
00305|             'apellido' => $empleado->Apellido,
00306|             'nombre' => $empleado->Nombre,
00307|             'dni' => $empleado->DNI,
00308|             'cuit' => $empleado->cuit,
00309|             'sexo' => $empleado->sexo,
00310|             'fecha_nacimiento' => $empleado->FecNac ? \Carbon\Carbon::parse($empleado->FecNac)->format('d/m/Y') : '',
00311| 
00312|             // Datos con nombres descriptivos en lugar de IDs
00313|             'estado_civil' => $empleado->estadoCivil->EstadoCivil ?? 'Sin definir',
00314|             'estado_civil_id' => $empleado->EstCiv,
00315|             'nacionalidad' => $empleado->pais->Pais ?? 'Sin definir',
00316|             'nacionalidad_id' => $empleado->Nacionalidad,
00317|             'provincia' => $empleado->provincia->Provincia ?? 'Sin definir',
00318|             'provincia_id' => $empleado->Provincia,
00319|             'localidad' => $empleado->localidad->Localidad ?? 'Sin definir',
00320|             'localidad_id' => $empleado->Localidad,
00321|             'instruccion' => $empleado->instruccion->instruccion ?? 'Sin definir',
00322|             'instruccion_id' => $empleado->idInstrucion,
00323| 
00324|             'cp' => $empleado->CP,
00325|             'calle' => $empleado->calle,
00326|             'calle_num' => $empleado->CalleNum,
00327|             'piso' => $empleado->Piso,
00328|             'departamento_dir' => $empleado->Departamento,
00329|             'barrio' => $empleado->Barrio,
00330|             'manzana' => $empleado->Manzana,
00331|             'casa' => $empleado->Casa,
00332|             'email' => $empleado->Email,
00333|             'telefono' => $empleado->Telefono,
00334|             'celular' => $empleado->Celular,
00335| 
00336|             // Información laboral con nombres descriptivos
00337|             'profesion' => $empleado->profesion->profesion ?? 'Sin definir',
00338|             'profesion_id' => $empleado->idProfesion,
00339|             'funcion' => $empleado->funcion->Funcion ?? 'Sin definir',
00340|             'funcion_id' => $empleado->Funcion,
00341|             'tipo_tarea' => $empleado->instruccion->instruccion ?? 'Sin definir',
00342|             'tipo_tarea_id' => $empleado->idInstrucion,
00343|             'tipo_relacion' => $empleado->tipoRelacion->Relacion ?? 'Sin definir',
00344|             'tipo_relacion_id' => $empleado->idTipoRelacion,
00345| 
00346|             // Jerarquía organizacional con nombres descriptivos
00347|             'gerencia' => $empleado->gerencia->Gerencia ?? 'Sin asignar',
00348|             'gerencia_id' => $empleado->idGerencia,
00349|             'departamento' => $empleado->departamento->departamento ?? 'Sin asignar',
00350|             'departamento_id' => $empleado->idDepartamento,
00351|             'servicio' => $empleado->servicio->servicio ?? 'Sin asignar',
00352|             'servicio_id' => $empleado->idServicio,
00353|             'sector' => $empleado->sector->Sector ?? 'Sin asignar',
00354|             'sector_id' => $empleado->idSector,
00355| 
00356|             'fecha_alta' => $empleado->FAlta ? \Carbon\Carbon::parse($empleado->FAlta)->format('d/m/Y') : '',
00357|             'fecha_baja' => $empleado->FBaja && $empleado->FBaja != '0000-00-00' ? \Carbon\Carbon::parse($empleado->FBaja)->format('d/m/Y') : '',
00358|             'fecha_adm_publica' => $empleado->FAltaAP ? \Carbon\Carbon::parse($empleado->FAltaAP)->format('d/m/Y') : '',
00359|             'estado' => $empleado->Estado,
00360|             'descripcion_baja' => $empleado->DescripcionBaja,
00361|             'observacion' => $empleado->Observacion,
00362|             // Jornada con nombre descriptivo
00363|             'tipo_jornada' => $empleado->tipoJornada->Jornada ?? 'Sin definir',
00364|             'tipo_jornada_id' => $empleado->Jornada_Id,
00365|             'motivo_baja' => $empleado->motivoBaja->MotivoBaja ?? 'Sin definir',
00366|             'motivo_baja_id' => $empleado->MotivoBaja_Id,
00367| 
00368|             'doble_fs' => $empleado->DobleFS,
00369|             'nocturno' => $empleado->Nocturno,
00370|             'fe' => $empleado->FE,
00371|             'gremio' => $empleado->Gremio,
00372|             'nro_contrato' => $empleado->NroContrato,
00373|             'matricula' => $empleado->Matricula,
00374| 
00375|             // Campos que necesitan consultas adicionales
00376|             'agrupamiento' => $this->getAgrupamientoNombre($empleado->idAgrupamiento),
00377|             'agrupamiento_id' => $empleado->idAgrupamiento,
00378|             'categoria' => $this->getCategoriaNombre($empleado->categoria),
00379|             'categoria_id' => $empleado->categoria,
00380|             'cargo' => $this->getCargoNombre($empleado->idCargo),
00381|             'cargo_id' => $empleado->idCargo,
00382|             'certifica' => $this->getCertificaNombre($empleado->IdEmpleado2),
00383|             'certifica_id' => $empleado->IdEmpleado2,
00384|             'convenios' => $empleado->Convenios,
00385|             'foto' => $empleado->Foto,
00386|             'servicios_asignados' => $this->obtenerServiciosAsignados($empleado),
00387|             'historial_servicios' => $empleado->servicios->map(function ($s) {
00388|                 return [
00389|                     'id' => $s->idServicio,
00390|                     'nombre' => $s->servicio,
00391|                     'fecha_inicio' => $s->pivot->fecha_inicio ? \Carbon\Carbon::parse($s->pivot->fecha_inicio)->format('d/m/Y') : '',
00392|                     'fecha_fin' => $s->pivot->fecha_fin ? \Carbon\Carbon::parse($s->pivot->fecha_fin)->format('d/m/Y') : '-',
00393|                     'activo' => $s->pivot->activo,
00394|                     'motivo' => $s->pivot->motivo
00395|                 ];
00396|             })->sortByDesc('fecha_inicio')->values(),
00397|             'historial_relaciones' => $historialRelaciones->map(function ($item) {
00398|                 return [
00399|                     'IdHisRelacion' => $item->IdHisRelacion,
00400|                     'relacion_id' => $item->Relacion_Id,
00401|                     'relacion_nombre' => $item->tipoRelacion->Relacion ?? '',
00402|                     'desde' => $item->Desde ? \Carbon\Carbon::parse($item->Desde)->format('d/m/Y') : '',
00403|                     'hasta' => $item->Hasta ? \Carbon\Carbon::parse($item->Hasta)->format('d/m/Y') : '',
00404|                     'observacion' => $item->Observacion
00405|                 ];
00406|             }),
00407|             'documentos' => $documentos->map(function ($item) {
00408|                 return [
00409|                     'IdDocumento' => $item->IdDocumento,
00410|                     'nombre' => $item->NombreDoc,
00411|                     'imagen' => $item->Imagen,
00412|                     'fecha' => $item->FechaDoc ? \Carbon\Carbon::parse($item->FechaDoc)->format('d/m/Y H:i') : ''
00413|                 ];
00414|             }),
00415|             'jornadas' => $jornadas->map(function ($item) {
00416|                 return [
00417|                     'IdJornadaXEmp' => $item->IdJornadaXEmp,
00418|                     'jornada_id' => $item->JornadaXEmp_Id,
00419|                     'jornada_nombre' => $item->tipoJornada->Jornada ?? '',
00420|                     'fecha' => $item->FechaJor ? \Carbon\Carbon::parse($item->FechaJor)->format('d/m/Y') : '',
00421|                     'fechaSinFormato' => $item->FechaJor ? \Carbon\Carbon::parse($item->FechaJor)->format('Y-m-d') : '',
00422|                 ];
00423|             })
00424|         ];
00425| 
00426| 
00427| 
00428| 
00429| 
00430|         return response()->json([
00431|             'success' => true,
00432|             'data' => $data
00433|         ]);
00434|     }
00435| 
00436|     /**
00437|      * Store a new personnel record
00438|      *
00439|      * @param  \Illuminate\Http\Request  $request
00440|      * @return \Illuminate\Http\Response
00441|      */
00442|     public function store(Request $request)
00443|     {
00444|         // Get user ID from session
00445|         $usuarioId = session('usuario_id');
00446| 
00447|         // Check if user has permission to create
00448|         $permisos = PermisoHelper::obtenerPermisos($usuarioId, 'personal');
00449| 
00450|         if (!($permisos['crear'] ?? false)) {
00451|             return response()->json([
00452|                 'success' => false,
00453|                 'message' => 'No tiene permisos para crear registros'
00454|             ], 403);
00455|         }
00456| 
00457|         // Validación condicional: servicios no obligatorio si es Jefe Dpto/Gerente/Director (cargo >= 3)
00458|         $cargo = $request->input('cargo', 0);
00459|         $serviciosRequeridos = ($cargo >= 3) ? 'nullable' : 'required';
00460| 
00461|         // Determinar si se envía servicios_asignados (nuevo formato) o servicios (formato legacy)
00462|         $tieneServiciosAsignados = $request->has('servicios_asignados') && is_array($request->servicios_asignados) && count($request->servicios_asignados) > 0;
00463| 
00464|         // Validate request - Unificado con validaciones del cliente
00465|         $request->validate([
00466|             'legajo' => 'required|numeric|unique:empleados,Legajo',
00467|             'apellido' => 'required|string|max:50',
00468|             'nombre' => 'required|string|max:50',
00469|             'dni' => 'required|numeric|digits_between:7,8|unique:empleados,DNI',
00470|             'sexo' => 'required|in:M,F',
00471|             'email' => 'nullable|email',
00472|             'fecha_nacimiento' => 'required|date_format:Y-m-d',
00473|             'fecha_alta' => 'required|date_format:Y-m-d',
00474|             'fecha_adm_publica' => 'required|date_format:Y-m-d',
00475|             // Datos laborales obligatorios
00476|             'tipo_tarea' => 'required|integer',
00477|             'relacion' => 'required|integer',
00478|             'profesion' => 'required|integer',
00479|             'categoria' => 'required|integer',
00480|             'agrupamiento' => 'required|integer',
00481|             // cargo puede ser 0 (sin cargo), usar present|integer|min:0
00482|             'cargo' => 'present|integer|min:0',
00483|             // certifica es requerido solo si NO hay servicios_asignados
00484|             'certifica' => $tieneServiciosAsignados ? 'nullable|integer' : 'required|integer',
00485|             // Jerarquía organizacional obligatoria
00486|             'gerencia' => 'required|integer',
00487|             'departamento' => 'required|integer',
00488|             // Acepta ambos formatos: servicios (legacy) o servicios_asignados (nuevo)
00489|             'servicios' => 'nullable|array',
00490|             'servicios.*' => 'integer|exists:servicios,IdServicio',
00491|             'servicios_asignados' => $tieneServiciosAsignados ? 'required|array' : 'nullable|array',
00492|             'servicios_asignados.*.servicio_id' => $tieneServiciosAsignados ? 'required|integer' : 'nullable|integer',
00493|             'servicios_asignados.*.certificador_id' => 'nullable|integer',
00494|             'servicios_asignados.*.fecha_pase' => 'nullable|date',
00495|             'servicios_asignados.*.sector_id' => 'nullable|integer',
00496|             // Jornada
00497|             'tipo_jornada' => 'required|integer',
00498|             'f_jornada' => 'required|date_format:Y-m-d'
00499|         ], [
00500|             'legajo.required' => 'El legajo es requerido',
00501|             'legajo.numeric' => 'El legajo debe ser un número válido',
00502|             'apellido.required' => 'El apellido es requerido',
00503|             'apellido.max' => 'El apellido no debe exceder 50 caracteres',
00504|             'nombre.required' => 'El nombre es requerido',
00505|             'nombre.max' => 'El nombre no debe exceder 50 caracteres',
00506|             'dni.required' => 'El DNI es requerido',
00507|             'dni.numeric' => 'El DNI debe ser un número válido',
00508|             'dni.digits_between' => 'El DNI debe tener entre 7 y 8 dígitos',
00509|             'sexo.required' => 'Seleccione el sexo',
00510|             'email.email' => 'Ingrese un email válido',
00511|             'fecha_nacimiento.required' => 'Ingrese la fecha de nacimiento',
00512|             'fecha_alta.required' => 'Ingrese la fecha de alta',
00513|             'fecha_adm_publica.required' => 'Ingrese la fecha de admisión pública',
00514|             'tipo_tarea.required' => 'Seleccione el tipo de tarea',
00515|             'relacion.required' => 'Seleccione la relación laboral',
00516|             'profesion.required' => 'Seleccione la profesión',
00517|             'categoria.required' => 'Seleccione la categoría',
00518|             'agrupamiento.required' => 'Seleccione el agrupamiento',
00519|             'cargo.required' => 'Seleccione el cargo',
00520|             'certifica.required' => 'Seleccione quién certifica',
00521|             'gerencia.required' => 'Seleccione la gerencia',
00522|             'departamento.required' => 'Seleccione el departamento',
00523|             'servicios.required' => 'Seleccione al menos un servicio',
00524|             'servicios.min' => 'Seleccione al menos un servicio',
00525|             'servicios_asignados.required' => 'Seleccione al menos un servicio',
00526|             'tipo_jornada.required' => 'Seleccione el tipo de jornada',
00527|             'f_jornada.required' => 'Ingrese la fecha de jornada'
00528|         ]);
00529| 
00530|         // Validación personalizada para servicios_asignados - verificar que existan en la tabla servicio
00531|         if ($tieneServiciosAsignados) {
00532|             foreach ($request->servicios_asignados as $index => $servicioAsignado) {
00533|                 $servicioId = $servicioAsignado['servicio_id'] ?? null;
00534|                 if ($servicioId) {
00535|                     $exists = DB::table('servicio')->where('IdServicio', $servicioId)->exists();
00536|                     if (!$exists) {
00537|                         return response()->json([
00538|                             'success' => false,
00539|                             'message' => "El servicio seleccionado en la posición " . ($index + 1) . " no es válido",
00540|                             'errors' => ["servicios_asignados.{$index}.servicio_id" => ["El servicio seleccionado no existe"]]
00541|                         ], 422);
00542|                     }
00543|                 }
00544|             }
00545|         }
00546| 
00547|         try {
00548|             DB::beginTransaction();
00549| 
00550|             // Create new employee record
00551|             $empleado = new Empleado();
00552|             $this->fillEmpleadoData($empleado, $request);
00553|             $empleado->Creador_Id = $usuarioId;
00554|             $empleado->FechaCreacion = now();
00555|             $empleado->save();
00556| 
00557|             // Process historial de relaciones
00558|             if ($request->has('relaciones') && is_array($request->relaciones)) {
00559|                 $this->processHistorialRelaciones($empleado->idEmpleado, $request->relaciones);
00560|             }
00561| 
00562|             // Process documents
00563|             if ($request->has('documentos') && is_array($request->documentos)) {
00564|                 $this->processDocuments($empleado->idEmpleado, $request->documentos);
00565|             }
00566| 
00567|             // Asignación de servicios con información completa (certificador, fecha, sector)
00568|             if ($request->has('servicios_asignados') && is_array($request->servicios_asignados)) {
00569|                 $serviciosAsignados = $request->servicios_asignados;
00570| 
00571|                 if (!empty($serviciosAsignados)) {
00572|                     // Pasar toda la información de servicios_asignados al método actualizarServicios
00573|                     $empleado->actualizarServicios($serviciosAsignados, 'Alta de empleado');
00574|                 }
00575|             }
00576| 
00577|             // Process deleted documents if any
00578|             if ($request->has('imagenes_eliminadas') && !empty($request->imagenes_eliminadas)) {
00579|                 $this->processDeletedDocuments($request->imagenes_eliminadas);
00580|             }
00581| 
00582|             // Process profile photo
00583|             if ($request->has('foto') && !empty($request->foto)) {
00584|                 $this->processProfilePhoto($empleado, $request->foto);
00585|             }
00586| 
00587|             // Check if we need to delete the photo
00588|             if ($request->has('eliminar_foto') && $request->eliminar_foto) {
00589|                 $this->deleteProfilePhoto($empleado);
00590|             }
00591| 
00592|             // Insert jornada if tipo_jornada is specified
00593|             if ($request->has('tipo_jornada') && !empty($request->tipo_jornada)) {
00594|                 // Create jornada record
00595|                 JornadaXEmp::create([
00596|                     'JornadaXEmp_Id' => $request->tipo_jornada,
00597|                     'Emp_Id' => $empleado->idEmpleado,
00598|                     'FechaJor' => now()->format('Y-m-d'),
00599|                     'CreadorJor_Id' => $usuarioId
00600|                 ]);
00601|             }
00602| 
00603|             // Log creation
00604|             LogHelper::insertar('empleados', 'personal', $empleado->idEmpleado, "Se creó el empleado {$empleado->Legajo} - {$empleado->Apellido}, {$empleado->Nombre}");
00605| 
00606| 
00607| 
00608|             DB::commit();
00609| 
00610|             return response()->json([
00611|                 'success' => true,
00612|                 'message' => 'Empleado creado correctamente',
00613|                 'data' => ['id' => $empleado->idEmpleado]
00614|             ]);
00615|         } catch (\Exception $e) {
00616|             DB::rollBack();
00617|             return response()->json([
00618|                 'success' => false,
00619|                 'message' => 'Error al crear el empleado: ' . $e->getMessage()
00620|             ], 500);
00621|         }
00622|     }
00623| 
00624|     /**
00625|      * Update a personnel record
00626|      *
00627|      * @param  \Illuminate\Http\Request  $request
00628|      * @param  int  $id
00629|      * @return \Illuminate\Http\Response
00630|      */
00631|     public function update(Request $request, $id)
00632|     {
00633|         // Get user ID from session
00634|         $usuarioId = session('usuario_id');
00635| 
00636|         // Check if user has permission to edit
00637|         $permisos = PermisoHelper::obtenerPermisos($usuarioId, 'personal');
00638| 
00639|         if (!($permisos['editar'] ?? false)) {
00640|             return response()->json([
00641|                 'success' => false,
00642|                 'message' => 'No tiene permisos para editar registros'
00643|             ], 403);
00644|         }
00645| 
00646|         // Find the employee
00647|         $empleado = Empleado::find($id);
00648| 
00649|         if (!$empleado) {
00650|             return response()->json([
00651|                 'success' => false,
00652|                 'message' => 'Empleado no encontrado'
00653|             ], 404);
00654|         }
00655| 
00656|         // Validación condicional: servicios no obligatorio si es Jefe Dpto/Gerente/Director (cargo >= 3)
00657|         $cargo = $request->input('cargo', 0);
00658|         $serviciosRequeridos = ($cargo >= 3) ? 'nullable' : 'required';
00659| 
00660|         // Determinar si se envía servicios_asignados (nuevo formato) o servicios (formato legacy)
00661|         $tieneServiciosAsignados = $request->has('servicios_asignados') && is_array($request->servicios_asignados) && count($request->servicios_asignados) > 0;
00662| 
00663|         // Validate request - Unificado con validaciones del cliente
00664|         $request->validate([
00665|             'legajo' => 'required|numeric|unique:empleados,Legajo,' . $id . ',idEmpleado',
00666|             'apellido' => 'required|string|max:50',
00667|             'nombre' => 'required|string|max:50',
00668|             'dni' => 'required|numeric|digits_between:7,8|unique:empleados,DNI,' . $id . ',idEmpleado',
00669|             'sexo' => 'required|in:M,F',
00670|             'email' => 'nullable|email',
00671|             'fecha_nacimiento' => 'required|date_format:Y-m-d',
00672|             'fecha_alta' => 'required|date_format:Y-m-d',
00673|             'fecha_adm_publica' => 'required|date_format:Y-m-d',
00674|             // Datos laborales obligatorios
00675|             'tipo_tarea' => 'required|integer',
00676|             'relacion' => 'required|integer',
00677|             'profesion' => 'required|integer',
00678|             'categoria' => 'required|integer',
00679|             'agrupamiento' => 'required|integer',
00680|             // cargo puede ser 0 (sin cargo), usar present|integer|min:0
00681|             'cargo' => 'present|integer|min:0',
00682|             // certifica es requerido solo si NO hay servicios_asignados
00683|             'certifica' => $tieneServiciosAsignados ? 'nullable|integer' : 'required|integer',
00684|             // Jerarquía organizacional obligatoria
00685|             'gerencia' => 'required|integer',
00686|             'departamento' => 'required|integer',
00687|             // Acepta ambos formatos: servicios (legacy) o servicios_asignados (nuevo)
00688|             'servicios' => 'nullable|array',
00689|             'servicios.*' => 'integer|exists:servicios,IdServicio',
00690|             'servicios_asignados' => 'nullable|array',
00691|             'servicios_asignados.*.servicio_id' => 'nullable|integer',
00692|             'servicios_asignados.*.certificador_id' => 'nullable|integer',
00693|             'servicios_asignados.*.fecha_pase' => 'nullable|date',
00694|             'servicios_asignados.*.sector_id' => 'nullable|integer',
00695|             // Jornada
00696|             'tipo_jornada' => 'required|integer',
00697|             'f_jornada' => 'required|date_format:Y-m-d'
00698|         ], [
00699|             'legajo.required' => 'El legajo es requerido',
00700|             'legajo.numeric' => 'El legajo debe ser un número válido',
00701|             'apellido.required' => 'El apellido es requerido',
00702|             'apellido.max' => 'El apellido no debe exceder 50 caracteres',
00703|             'nombre.required' => 'El nombre es requerido',
00704|             'nombre.max' => 'El nombre no debe exceder 50 caracteres',
00705|             'dni.required' => 'El DNI es requerido',
00706|             'dni.numeric' => 'El DNI debe ser un número válido',
00707|             'dni.digits_between' => 'El DNI debe tener entre 7 y 8 dígitos',
00708|             'sexo.required' => 'Seleccione el sexo',
00709|             'email.email' => 'Ingrese un email válido',
00710|             'fecha_nacimiento.required' => 'Ingrese la fecha de nacimiento',
00711|             'fecha_alta.required' => 'Ingrese la fecha de alta',
00712|             'fecha_adm_publica.required' => 'Ingrese la fecha de admisión pública',
00713|             'tipo_tarea.required' => 'Seleccione el tipo de tarea',
00714|             'relacion.required' => 'Seleccione la relación laboral',
00715|             'profesion.required' => 'Seleccione la profesión',
00716|             'categoria.required' => 'Seleccione la categoría',
00717|             'agrupamiento.required' => 'Seleccione el agrupamiento',
00718|             'cargo.required' => 'Seleccione el cargo',
00719|             'certifica.required' => 'Seleccione quién certifica',
00720|             'gerencia.required' => 'Seleccione la gerencia',
00721|             'departamento.required' => 'Seleccione el departamento',
00722|             'servicios.required' => 'Seleccione al menos un servicio',
00723|             'servicios.min' => 'Seleccione al menos un servicio',
00724|             'servicios_asignados.required' => 'Seleccione al menos un servicio',
00725|             'tipo_jornada.required' => 'Seleccione el tipo de jornada',
00726|             'f_jornada.required' => 'Ingrese la fecha de jornada'
00727|         ]);
00728| 
00729|         // Validación personalizada para servicios_asignados - verificar que existan en la tabla servicio
00730|         if ($tieneServiciosAsignados) {
00731|             foreach ($request->servicios_asignados as $index => $servicioAsignado) {
00732|                 $servicioId = $servicioAsignado['servicio_id'] ?? null;
00733|                 if ($servicioId) {
00734|                     $exists = DB::table('servicio')->where('IdServicio', $servicioId)->exists();
00735|                     if (!$exists) {
00736|                         return response()->json([
00737|                             'success' => false,
00738|                             'message' => "El servicio seleccionado en la posición " . ($index + 1) . " no es válido",
00739|                             'errors' => ["servicios_asignados.{$index}.servicio_id" => ["El servicio seleccionado no existe"]]
00740|                         ], 422);
00741|                     }
00742|                 }
00743|             }
00744|         }
00745| 
00746|         try {
00747|             DB::beginTransaction();
00748| 
00749|             // Store original data for comparison
00750|             $originalData = $empleado->toArray();
00751|             $legajoOriginal = $request->input('legajoOriginal', $empleado->Legajo);
00752| 
00753|             // Update employee data
00754|             $this->fillEmpleadoDataUpdate($empleado, $request);
00755|             $empleado->Modificador_Id = $usuarioId;
00756|             $empleado->save();
00757| 
00758|             // Asignación de servicios (Multi-servicio)
00759|             $serviciosIds = $request->input('servicios', []);
00760|             $servicioPrincipal = $request->input('idServicio');
00761| 
00762|             // Procesar servicios a dar de baja primero
00763|             if ($request->has('servicios_dar_baja') && is_array($request->servicios_dar_baja)) {
00764|                 foreach ($request->servicios_dar_baja as $baja) {
00765|                     DB::table('empleado_servicio')
00766|                         ->where('empleado_id', $empleado->id)
00767|                         ->where('servicio_id', $baja['servicio_id'])
00768|                         ->where('activo', 1)
00769|                         ->update([
00770|                             'activo' => 0,
00771|                             'fecha_fin' => $baja['fecha_baja'] ?? now()->format('Y-m-d'),
00772|                             'motivo' => $baja['motivo'] ?? 'Baja de servicio'
00773|                         ]);
00774|                 }
00775|             }
00776| 
00777|             // Procesar servicios asignados con información completa
00778|             if ($request->has('servicios_asignados') && is_array($request->servicios_asignados)) {
00779|                 $serviciosAsignados = $request->servicios_asignados;
00780| 
00781|                 if (!empty($serviciosAsignados)) {
00782|                     // Pasar toda la información de servicios_asignados al método actualizarServicios
00783|                     $empleado->actualizarServicios($serviciosAsignados, 'Edición de empleado');
00784|                 }
00785|             } elseif ($servicioPrincipal) {
00786|                 // Legacy: solo cambió el servicio principal via dropdown simple
00787|                 $empleado->actualizarServicios([$servicioPrincipal], 'Cambio servicio principal');
00788|             }
00789| 
00790|             // Check if legajo has been changed and update related tables
00791|             if ($request->legajo != $legajoOriginal) {
00792|                 $this->updateLegajoInRelatedTables($legajoOriginal, $request->legajo);
00793|             }
00794| 
00795|             // Process historial de relaciones
00796|             if ($request->has('relaciones') && is_array($request->relaciones)) {
00797|                 // Delete existing relations and create new ones
00798| 
00799|                 // Check if work schedule type or date has changed and create new JornadaXEmp record
00800| 
00801|                 HistorialRelacion::where('Personal_Id', $id)->delete();
00802|                 $this->processHistorialRelaciones($id, $request->relaciones);
00803|             }
00804| 
00805|             // Process deleted documents if any
00806|             if ($request->has('imagenes_eliminadas') && !empty($request->imagenes_eliminadas)) {
00807|                 $this->processDeletedDocuments($request->imagenes_eliminadas);
00808|             }
00809|             // Process documents
00810|             if ($request->has('documentos')) {
00811|                 $this->processDocuments($id, $request->documentos);
00812|             }
00813| 
00814| 
00815| 
00816|             // Check if we need to delete the photo
00817|             if ($request->has('eliminar_foto') && $request->eliminar_foto) {
00818|                 $this->deleteProfilePhoto($empleado);
00819|             }
00820| 
00821|             // Process profile photo
00822|             if ($request->has('foto') && !empty($request->foto)) {
00823|                 $this->processProfilePhoto($empleado, $request->foto);
00824|             }
00825| 
00826| 
00827|             $fechaJornada = $request->input('f_jornada');
00828|             $tipoJornadaActual = $request->input('tipo_jornada');
00829|             $tipoJornadaOriginal = $request->input('JornadaOriginal_Id');
00830|             $fechaJornadaOriginal = $request->input('FechaJornadaOri');
00831| 
00832|             /**
00833|              * Actualiza o crea registros de jornada del empleado según los cambios en fecha y tipo de jornada.
00834|              */
00835| 
00836|             if ($fechaJornada != $fechaJornadaOriginal && intval($tipoJornadaActual) == intval($tipoJornadaOriginal)) {
00837|                 // Obtener la última jornada (más reciente) del empleado y actualizar sólo la fecha
00838|                 $ultimaJornada = JornadaXEmp::where('Emp_Id', $id)
00839|                     ->orderBy('FechaJor', 'desc')
00840|                     ->first();
00841| 
00842|                 if ($ultimaJornada) {
00843|                     $ultimaJornada->FechaJor = $fechaJornada;
00844|                     $ultimaJornada->save();
00845|                 }
00846|             }
00847| 
00848|             if ($fechaJornada && !empty($tipoJornadaActual) && intval($tipoJornadaActual) != intval($tipoJornadaOriginal)) {
00849|                 JornadaXEmp::create([
00850|                     'JornadaXEmp_Id' => $tipoJornadaActual,
00851|                     'Emp_Id' => $id,
00852|                     'FechaJor' => $fechaJornada,
00853|                     'CreadorJor_Id' => $usuarioId
00854|                 ]);
00855|             }
00856| 
00857|             /*
00858|             // Generate modification log
00859|             $changes = $this->getChanges($originalData, $empleado->toArray());
00860|             */
00861|             // Log update
00862|             LogHelper::actualizar('empleados', 'personal', $id, "Se modificó el empleado {$empleado->Legajo} - {$empleado->Apellido}, {$empleado->Nombre}");
00863| 
00864| 
00865| 
00866|             DB::commit();
00867| 
00868|             return response()->json([
00869|                 'success' => true,
00870|                 'message' => 'Empleado actualizado correctamente'
00871|             ]);
00872|         } catch (\Exception $e) {
00873|             DB::rollBack();
00874|             return response()->json([
00875|                 'success' => false,
00876|                 'message' => 'Error al actualizar el empleado: ' . $e->getMessage()
00877|             ], 500);
00878|         }
00879|     }
00880| 
00881|     /**
00882|      * Delete a personnel record (logical delete)
00883|      *
00884|      * @param  int  $id
00885|      * @return \Illuminate\Http\Response
00886|      */
00887|     public function destroy($id)
00888|     {
00889|         // Get user ID from session
00890|         $usuarioId = session('usuario_id');
00891| 
00892|         // Check if user has permission to delete
00893|         $permisos = PermisoHelper::obtenerPermisos($usuarioId, 'personal');
00894| 
00895|         if (!($permisos['eliminar'] ?? false)) {
00896|             return response()->json([
00897|                 'success' => false,
00898|                 'message' => 'No tiene permisos para eliminar registros'
00899|             ], 403);
00900|         }
00901| 
00902|         // Find the employee
00903|         $empleado = Empleado::find($id);
00904| 
00905|         if (!$empleado) {
00906|             return response()->json([
00907|                 'success' => false,
00908|                 'message' => 'Empleado no encontrado'
00909|             ], 404);
00910|         }
00911| 
00912|         try {
00913|             DB::beginTransaction();
00914| 
00915|             // Store employee info for logging
00916|             $empleadoInfo = "{$empleado->Legajo} - {$empleado->Apellido}, {$empleado->Nombre}";
00917| 
00918|             // 1. Delete profile photo if exists
00919|             $this->deleteProfilePhoto($empleado);
00920| 
00921|             // 2. Delete all associated documents
00922|             $documentos = DocumentoEscaneado::where('Empleado_Id', $id)->get();
00923|             foreach ($documentos as $documento) {
00924|                 $this->deleteDocument($documento->IdDocumento);
00925|             }
00926| 
00927|             // 3. Delete relationship history records
00928|             HistorialRelacion::where('Personal_Id', $id)->delete();
00929| 
00930|             // 4. Delete work schedule records (jornadas)
00931|             JornadaXEmp::where('Emp_Id', $id)->delete();
00932| 
00933|             // 5. Delete the employee record itself
00934|             $empleado->delete();
00935| 
00936|             // Log deletion
00937|             LogHelper::eliminar('empleados', 'personal', $id, "Se eliminó físicamente el empleado {$empleadoInfo}");
00938| 
00939|             DB::commit();
00940| 
00941|             return response()->json([
00942|                 'success' => true,
00943|                 'message' => 'Empleado eliminado correctamente'
00944|             ]);
00945|         } catch (\Exception $e) {
00946|             DB::rollBack();
00947|             return response()->json([
00948|                 'success' => false,
00949|                 'message' => 'Error al eliminar el empleado: ' . $e->getMessage()
00950|             ], 500);
00951|         }
00952|     }
00953| 
00954|     /**
00955|      * Update legajo in related tables
00956|      */
00957|     private function updateLegajoInRelatedTables($legajoOriginal, $legajoNuevo)
00958|     {
00959|         // Define the tables and fields that need to be updated
00960|         $tablasModLeg = [
00961|             ["tabla" => "licencias", "campo" => "LegajoPersonal"],
00962|             ["tabla" => "config_lar", "campo" => "LegajoLarP"],
00963|             ["tabla" => "conf_x_bien_inv", "campo" => "RespLegCXBI"],
00964|             ["tabla" => "resp_x_invent", "campo" => "Resp_Leg"],
00965|             ["tabla" => "pers_x_deposito", "campo" => "LegajoDep"],
00966|         ];
00967| 
00968|         // Update each table
00969|         foreach ($tablasModLeg as $tabla) {
00970|             DB::table($tabla["tabla"])
00971|                 ->where($tabla["campo"], $legajoOriginal)
00972|                 ->update([$tabla["campo"] => $legajoNuevo]);
00973|         }
00974| 
00975|         // Log the legajo change
00976|         LogHelper::actualizar('empleados', 'personal', null, "Se cambió el legajo $legajoOriginal por $legajoNuevo en tablas relacionadas");
00977|     }
00978| 
00979|     /**
00980|      * Get departments by gerencia
00981|      */
00982|     public function getDepartamentos(Request $request)
00983|     {
00984|         $gerenciaId = $request->input('gerencia_id');
00985| 
00986|         if (!$gerenciaId) {
00987|             return response()->json(['data' => []]);
00988|         }
00989| 
00990|         $departamentos = Departamento::where('idGerencia', $gerenciaId)
00991|             ->orderBy('departamento')
00992|             ->get(['idDepartamento', 'departamento']);
00993| 
00994|         return response()->json(['data' => $departamentos]);
00995|     }
00996| 
00997|     /**
00998|      * Get servicios by departamento
00999|      */
01000|     public function getServicios(Request $request)
01001|     {
01002|         $departamentoId = $request->input('departamento_id');
01003| 
01004|         if (!$departamentoId) {
01005|             return response()->json(['data' => []]);
01006|         }
01007| 
01008|         $servicios = Servicio::where('idDepartamento', $departamentoId)
01009|             ->orderBy('servicio')
01010|             ->get(['idServicio', 'servicio']);
01011| 
01012|         return response()->json(['data' => $servicios]);
01013|     }
01014| 
01015|     /**
01016|      * Get sectores by servicio
01017|      */
01018|     public function getSectores(Request $request)
01019|     {
01020|         $servicioId = $request->input('servicio_id');
01021| 
01022|         if (!$servicioId) {
01023|             return response()->json(['data' => []]);
01024|         }
01025| 
01026|         $sectores = Sector::where('idservicio', $servicioId)
01027|             ->orderBy('sector')
01028|             ->get(['idSector', 'sector']);
01029| 
01030|         return response()->json(['data' => $sectores]);
01031|     }
01032| 
01033|     /**
01034|      * Get jefe del servicio (empleado con idCargo = 2 en ese servicio)
01035|      */
01036|     public function getJefeServicio(Request $request)
01037|     {
01038|         $servicioId = $request->input('servicio_id');
01039| 
01040|         if (!$servicioId) {
01041|             return response()->json(['jefe' => null]);
01042|         }
01043| 
01044|         // Buscar empleado activo con idCargo = 2 (Jefe Servicio) asignado a este servicio
01045|         // Verificar tanto en el campo idServicio (legacy) como en la tabla pivote
01046|         $jefe = Empleado::where('Estado', 1)
01047|             ->where('idCargo', 2)
01048|             ->where(function($query) use ($servicioId) {
01049|                 $query->where('idServicio', $servicioId)
01050|                       ->orWhereHas('serviciosActivos', function($q) use ($servicioId) {
01051|                           $q->where('servicio_id', $servicioId);
01052|                       });
01053|             })
01054|             ->first(['idEmpleado', 'Apellido', 'Nombre', 'Legajo']);
01055| 
01056|         if ($jefe) {
01057|             return response()->json([
01058|                 'jefe' => [
01059|                     'id' => $jefe->idEmpleado,
01060|                     'nombre' => $jefe->Apellido . ', ' . $jefe->Nombre . ' (Leg. ' . $jefe->Legajo . ')'
01061|                 ]
01062|             ]);
01063|         }
01064| 
01065|         return response()->json(['jefe' => null]);
01066|     }
01067| 
01068|     /**
01069|      * Obtener empleados activos para certificadores (solo los que tienen cargo asignado)
01070|      */
01071|     public function getEmpleadosActivos()
01072|     {
01073|         $empleados = Empleado::where('Estado', '1')
01074|             ->whereNotNull('idCargo')
01075|             ->where('idCargo', '!=', 0)
01076|             ->orderBy('Apellido')
01077|             ->orderBy('Nombre')
01078|             ->get(['idEmpleado', 'Apellido', 'Nombre', 'Legajo'])
01079|             ->map(function($emp) {
01080|                 return [
01081|                     'id' => $emp->idEmpleado,
01082|                     'nombre' => $emp->Apellido . ', ' . $emp->Nombre . ' (Leg. ' . $emp->Legajo . ')'
01083|                 ];
01084|             });
01085| 
01086|         return response()->json(['empleados' => $empleados]);
01087|     }
01088| 
01089|     /**
01090|      * Get localidades by provincia
01091|      */
01092|     public function getLocalidades(Request $request)
01093|     {
01094|         $provinciaId = $request->input('provincia_id');
01095| 
01096|         if (!$provinciaId) {
01097|             return response()->json(['data' => []]);
01098|         }
01099| 
01100|         $localidades = Localidad::where('Provincia_Id', $provinciaId)
01101|             ->orderBy('Localidad')
01102|             ->get(['IdLocalidad', 'Localidad', 'CP']);
01103| 
01104|         return response()->json(['data' => $localidades]);
01105|     }
01106| 
01107|     /**
01108|      * Get all initial selectors for dropdowns
01109|      */
01110|     public function getSelectoresIniciales()
01111|     {
01112|         $data = [
01113|             'gerencias' => Gerencia::orderBy('Gerencia')->get(['idGerencia', 'Gerencia']),
01114|             'provincias' => Provincia::orderBy('Provincia')->get(['IdProvincia', 'Provincia']),
01115|             'estados_civiles' => EstadoCivil::orderBy('EstadoCivil')->get(['idEstadoCivil', 'EstadoCivil']),
01116|             'estados' => Estado::orderBy('estado')->get(['idEstado', 'estado']),
01117|             'profesiones' => Profesion::orderBy('profesion')->get(['idprofesion', 'profesion']),
01118|             'funciones' => Funcion::orderBy('Funcion')->get(['IdFuncion', 'Funcion']),
01119|             'instrucciones' => Instruccion::orderBy('instruccion')->get(['idInstruccion', 'instruccion']),
01120|             'tipos_relacion' => TipoRelacion::orderBy('Relacion')->get(['idRelacion', 'Relacion']),
01121|             'tipos_jornada' => TipoJornada::orderBy('Jornada')->get(['IdTipoJornada', 'Jornada']),
01122|             'motivos_baja' => MotivoBaja::orderBy('MotivoBaja')->get(['IdMotivoBaja', 'MotivoBaja']),
01123|             'paises' => Pais::orderBy('Pais')->get(['IdPais', 'Pais']),
01124|             // Nuevos selectores
01125|             'empleados_con_cargo' => Empleado::where('idCargo', '!=', 0)
01126|                 ->whereNotNull('idCargo')
01127|                 ->orderBy('Apellido')
01128|                 ->orderBy('Nombre')
01129|                 ->get(['idEmpleado', 'Apellido', 'Nombre', 'Legajo']),
01130|             // Selectores agregados
01131|             'agrupamientos' => Agrupamiento::orderBy('agrupamiento')->get(['idAgrupamiento', 'agrupamiento']),
01132|             'categorias' => Categoria::orderBy('categoria')->get(['idcategoria', 'categoria']),
01133|             'cargos' => Cargo::orderBy('cargo')->get(['idCargo', 'cargo'])
01134|         ];
01135| 
01136|         return response()->json(['data' => $data]);
01137|     }
01138| 
01139|     /**
01140|      * Check if DNI exists
01141|      */
01142|     public function checkDniExists(Request $request)
01143|     {
01144|         $dni = $request->input('dni');
01145|         $excludeId = $request->input('exclude_id', null);
01146| 
01147|         $query = Empleado::where('DNI', $dni);
01148| 
01149|         if ($excludeId) {
01150|             $query->where('idEmpleado', '!=', $excludeId);
01151|         }
01152| 
01153|         $exists = $query->exists();
01154| 
01155|         return response()->json(['exists' => $exists]);
01156|     }
01157| 
01158|     /**
01159|      * Check if legajo exists
01160|      */
01161|     public function checkLegajoExists(Request $request)
01162|     {
01163|         $legajo = $request->input('legajo');
01164|         $excludeId = $request->input('exclude_id', null);
01165| 
01166|         $query = Empleado::where('Legajo', $legajo)->where('Estado', '1');
01167| 
01168|         if ($excludeId) {
01169|             $query->where('idEmpleado', '!=', $excludeId);
01170|         }
01171| 
01172|         $exists = $query->exists();
01173| 
01174|         return response()->json(['exists' => $exists]);
01175|     }
01176| 
01177|     /**
01178|      * Get jornadas historial for an employee
01179|      */
01180|     public function getJornadas($id)
01181|     {
01182|         $jornadas = JornadaXEmp::with('tipoJornada')
01183|             ->where('Emp_Id', $id)
01184|             ->orderBy('FechaJor', 'desc')
01185|             ->get()
01186|             ->map(function ($item) {
01187|                 return [
01188|                     'id' => $item->IdJornadaXEmp,
01189|                     'jornada' => $item->tipoJornada->Jornada ?? 'Sin jornada',
01190|                     'fecha' => $item->FechaJor ? \Carbon\Carbon::parse($item->FechaJor)->format('d/m/Y') : ''
01191|                 ];
01192|             });
01193| 
01194|         return response()->json(['data' => $jornadas]);
01195|     }
01196| 
01197|     /**
01198|      * Fill employee data from request
01199|      */
01200|     private function fillEmpleadoData($empleado, $request)
01201|     {
01202|         $empleado->Legajo = $request->legajo;
01203|         $empleado->Apellido = $request->apellido;
01204|         $empleado->Nombre = $request->nombre;
01205|         $empleado->DNI = $request->dni;
01206|         $empleado->cuit = $request->cuit ?? '';
01207|         // Convertir M/F a 1/2 para la BD
01208|         $sexoValue = $request->sexo ?? 1;
01209|         $empleado->sexo = ($sexoValue === 'M') ? 1 : (($sexoValue === 'F') ? 2 : $sexoValue);
01210|         $empleado->FecNac = $request->fecha_nacimiento; // Ya viene en formato YYYY-MM-DD
01211|         $empleado->EstCiv = $request->estado_civil ?? 1;
01212|         $empleado->Nacionalidad = $request->nacionalidad ?? 1;
01213|         $empleado->Provincia = $request->provincia ?? 1;
01214|         $empleado->Localidad = $request->localidad ?? 1;
01215|         $empleado->CP = $request->cp ?? 0;
01216|         $empleado->calle = $request->calle ?? '';
01217|         $empleado->CalleNum = $request->num_calle ?? '';
01218|         $empleado->Piso = $request->piso ?? '';
01219|         $empleado->Departamento = $request->dto ?? '';
01220|         $empleado->Barrio = $request->barrio ?? '';
01221|         $empleado->Manzana = $request->manzana ?? '';
01222|         $empleado->Casa = $request->casa ?? '';
01223|         $empleado->Email = $request->email ?? '';
01224|         $empleado->Telefono = $request->telefono ?? '';
01225|         $empleado->Celular = $request->celular ?? '';
01226|         $empleado->idProfesion = $request->profesion ?? 1;
01227|         $empleado->Funcion = $request->funcion ?? 1;
01228|         $empleado->idInstrucion = $request->instruccion ?? $request->tipo_tarea ?? 1;  // Aceptar ambos nombres
01229|         $empleado->idTipoRelacion = $request->relacion_laboral ?? $request->relacion ?? 1;
01230|         $empleado->idGerencia = $request->gerencia ?? 1;
01231|         $empleado->idDepartamento = $request->departamento ?? 1;
01232|         $empleado->idServicio = $request->servicio ?? 1;
01233|         $empleado->idSector = $request->sector ?? 1;
01234|         $empleado->categoria = $request->categoria ?? 0;
01235|         $empleado->codigo = 0;
01236|         // Guardar también en el campo idAgrupamiento (nuevo) para persistir el agrupamiento
01237|         $empleado->idAgrupamiento = (int)($request->agrupamiento ?? 0);
01238|         $empleado->idCargo = (int)($request->cargo ?? 0);
01239|         $empleado->IdEmpleado2 = (int)($request->certifica ?? 0);
01240|         $empleado->Matricula = $request->num_matricula ?? '';
01241|         $empleado->NroContrato = $request->nro_contrato ?? '';
01242| 
01243|         // Asumir que las fechas ya vienen en formato YYYY-MM-DD
01244|         $empleado->FAlta = $request->fecha_alta;
01245|         $empleado->Modificador_Id = 0;
01246| 
01247|         $empleado->Indicador = 0;
01248|         $empleado->Foto = "";
01249| 
01250| 
01251|         if (!empty($request->fecha_adm_publica)) {
01252|             $empleado->FAltaAP = $request->fecha_adm_publica;
01253|         }
01254| 
01255|         if (!empty($request->fecha_baja)) {
01256|             $empleado->FBaja = $request->fecha_baja;
01257|         } else {
01258|             $empleado->FBaja = '0000-00-00';
01259|         }
01260| 

(Output truncated at 51200 bytes. Use 'offset' parameter to read beyond line 1260)
</file>
```
```

```
Tool: read

**Input:**
```json
{
  "filePath": "D:\\laragon\\www\\zona_capital_laravel\\resources\\views\\personal.blade.php"
}
```
**Output:**
```
<file>
00001| @extends('layouts.main')
00002| 
00003| @section('title', 'Gestión de Personal | ZonaCapital')
00004| 
00005| @section('header-title', 'Gestión de Personal')
00006| 
00007| @section('breadcrumb')
00008|     <li class="breadcrumb-item active" aria-current="page">Personal</li>
00009| @endsection
00010| 
00011| @section('content')
00012|     <!-- Hidden permissions inputs for JavaScript -->
00013|     <input type="hidden" id="permiso_crear" value="{{ $permisos['crear'] ? 1 : 0 }}">
00014|     <input type="hidden" id="permiso_leer" value="{{ $permisos['leer'] ? 1 : 0 }}">
00015|     <input type="hidden" id="permiso_editar" value="{{ $permisos['editar'] ? 1 : 0 }}">
00016|     <input type="hidden" id="permiso_eliminar" value="{{ $permisos['eliminar'] ? 1 : 0 }}">
00017|     <!-- CSRF Token for AJAX -->
00018|     <meta name="csrf-token" content="{{ csrf_token() }}">
00019| 
00020| 
00021| 
00022| 
00023|     <!-- Modal para webcam -->
00024|     <div class="modal fade" id="modal_foto">
00025|         <div class="modal-dialog">
00026|             <div class="modal-content">
00027|                 <div class="modal-header bg-primary">
00028|                     <h4 class="modal-title">Cámara</h4>
00029|                     <button type="button" class="close" data-dismiss="modal" aria-label="Close">
00030|                         <span aria-hidden="true">&times;</span>
00031|                     </button>
00032|                 </div>
00033|                 <div class="modal-body">
00034|                     <div id="my_camera" style="width:320px; height:240px;"></div>
00035|                 </div>
00036|                 <div class="modal-footer justify-content-between">
00037|                     <button type="button" id="btn_capturar" class="btn btn-primary">Tomar foto</button>
00038|                 </div>
00039|             </div>
00040|         </div>
00041|     </div>
00042| 
00043|     <!-- Modal de historial de jornadas -->
00044|     <div class="modal fade" id="histo_modal" tabindex="-1" role="dialog">
00045|         <div class="modal-dialog modal-lg">
00046|             <div class="modal-content">
00047|                 <div class="modal-header bg-primary">
00048|                     <h4 class="modal-title">Historial de jornadas</h4>
00049|                     <button type="button" class="close" data-dismiss="modal" aria-label="Close">
00050|                         <span aria-hidden="true">&times;</span>
00051|                     </button>
00052|                 </div>
00053|                 <div class="modal-body">
00054|                     <table class="table table-striped">
00055|                         <thead>
00056|                             <tr>
00057|                                 <th>Jornada</th>
00058|                                 <th>Fecha</th>
00059|                                 <th>Acciones</th>
00060|                             </tr>
00061|                         </thead>
00062|                         <tbody id="tabla_his">
00063|                             <!-- Contenido dinámico de historial -->
00064|                         </tbody>
00065|                     </table>
00066|                 </div>
00067|             </div>
00068|         </div>
00069|     </div>
00070| 
00071|     <!-- List Section - Initially visible -->
00072|     <div id="seccion-listado" class="@if ($permisos['leer'] ?? false) d-block @else d-none @endif">
00073|         <div class="card">
00074|             <div class="card-header d-flex justify-content-between align-items-center">
00075|                 <h3 class="card-title">Listado de Personal</h3>
00076|                 <div>
00077|                     @if ($permisos['crear'] ?? false)
00078|                         <button type="button" id="btnAgregar" class="btn btn-primary">
00079|                             <i class="bi bi-plus-lg me-1"></i> Agregar Personal
00080|                         </button>
00081|                     @endif
00082|                 </div>
00083|             </div>
00084|             <div class="card-body">
00085|                 <!-- Filters -->
00086|                 <form id="formFiltros" action="{{ route('personal.filtrar') }}" method="GET">
00087|                     <!-- Filtros Principales (Siempre visibles) -->
00088|                     <div class="row mb-3 align-items-end">
00089|                         <div class="col-md-3">
00090|                             <div class="form-group">
00091|                                 <label for="filtro_apellido_nombre" class="form-label">Apellido y Nombre</label>
00092|                                 <input type="text" class="form-control" id="filtro_apellido_nombre" name="apellido_nombre"
00093|                                     placeholder="Buscar por apellido o nombre">
00094|                             </div>
00095|                         </div>
00096|                         <div class="col-md-2">
00097|                             <div class="form-group">
00098|                                 <label for="filtro_legajo" class="form-label">Legajo</label>
00099|                                 <input type="text" class="form-control" id="filtro_legajo" name="legajo"
00100|                                     placeholder="Legajo">
00101|                             </div>
00102|                         </div>
00103|                         <div class="col-md-2">
00104|                             <div class="form-group">
00105|                                 <label for="filtro_dni" class="form-label">DNI</label>
00106|                                 <input type="text" class="form-control" id="filtro_dni" name="dni" placeholder="DNI">
00107|                             </div>
00108|                         </div>
00109|                         <div class="col-md-5 d-flex gap-2">
00110|                             <button type="submit" class="btn btn-primary">
00111|                                 <i class="bi bi-search me-1"></i> Buscar
00112|                             </button>
00113|                             <button type="button" id="btnToggleFiltros" class="btn btn-outline-secondary">
00114|                                 <i class="bi bi-chevron-down me-1"></i> Más filtros
00115|                             </button>
00116|                             <button type="button" id="btnLimpiarFiltros" class="btn btn-secondary" title="Limpiar filtros">
00117|                                 <i class="bi bi-eraser"></i>
00118|                             </button>
00119|                         </div>
00120|                     </div>
00121| 
00122|                     <!-- Filtros Secundarios (Ocultos por defecto) -->
00123|                     <div id="contenedor-filtros" class="d-none border-top pt-3 mt-3">
00124|                         <div class="row mb-3">
00125|                             <div class="col-md-3">
00126|                                 <div class="form-group">
00127|                                     <label for="filtro_sexo" class="form-label">Sexo</label>
00128|                                     <select class="form-select" id="filtro_sexo" name="sexo">
00129|                                         <option value="0" selected>Todos</option>
00130|                                         <option value="1">Masculino</option>
00131|                                         <option value="2">Femenino</option>
00132|                                     </select>
00133|                                 </div>
00134|                             </div>
00135|                             <div class="col-md-3">
00136|                                 <div class="form-group">
00137|                                     <label for="filtro_profesion" class="form-label">Profesión</label>
00138|                                     <select class="form-select" id="filtro_profesion" name="profesion">
00139|                                         <option value="0" selected>Todas</option>
00140|                                         @foreach ($profesiones as $profesion)
00141|                                             <option value="{{ $profesion->idprofesion }}">
00142|                                                 {{ $profesion->profesion }}
00143|                                             </option>
00144|                                         @endforeach
00145|                                     </select>
00146|                                 </div>
00147|                             </div>
00148|                             <div class="col-md-3">
00149|                                 <div class="form-group">
00150|                                     <label for="filtro_estado" class="form-label">Estado</label>
00151|                                     <select class="form-select" id="filtro_estado" name="estado">
00152|                                         @foreach ($estados as $estado)
00153|                                             <option value="{{ $estado->idEstado }}" {{ $estado->idEstado == 1 ? 'selected' : '' }}>
00154|                                                 {{ $estado->estado }}
00155|                                             </option>
00156|                                         @endforeach
00157|                                     </select>
00158|                                 </div>
00159|                             </div>
00160|                             <div class="col-md-3">
00161|                                 <div class="form-group">
00162|                                     <label for="filtro_cargo" class="form-label">Cargo</label>
00163|                                     <select class="form-select" id="filtro_cargo" name="cargo">
00164|                                         <option value="0" selected>Todos</option>
00165|                                         @foreach ($cargos as $cargo)
00166|                                             <option value="{{ $cargo->idCargo }}">
00167|                                                 {{ $cargo->cargo }}
00168|                                             </option>
00169|                                         @endforeach
00170|                                     </select>
00171|                                 </div>
00172|                             </div>
00173|                         </div>
00174| 
00175|                         <div class="row mb-3">
00176|                             <div class="col-md-3">
00177|                                 <div class="form-group">
00178|                                     <label for="filtro_gerencia" class="form-label">Gerencia</label>
00179|                                     <select class="form-select" id="filtro_gerencia" name="gerencia"
00180|                                         onchange="CargaSelectDtoFiltro()">
00181|                                         <option value="0" selected>Todas</option>
00182|                                         @foreach ($gerencias as $gerencia)
00183|                                             <option value="{{ $gerencia->idGerencia }}">
00184|                                                 {{ $gerencia->Gerencia }}
00185|                                             </option>
00186|                                         @endforeach
00187|                                     </select>
00188|                                 </div>
00189|                             </div>
00190|                             <div class="col-md-3">
00191|                                 <div class="form-group">
00192|                                     <label for="filtro_departamento" class="form-label">Departamento</label>
00193|                                     <select class="form-select" id="filtro_departamento" name="departamento"
00194|                                         onchange="CargaSelectServFiltro()">
00195|                                         <option value="0" selected>Todos</option>
00196|                                     </select>
00197|                                 </div>
00198|                             </div>
00199|                             <div class="col-md-3">
00200|                                 <div class="form-group">
00201|                                     <label for="filtro_servicio" class="form-label">Servicio</label>
00202|                                     <select class="form-select" id="filtro_servicio" name="servicio"
00203|                                         onchange="CargaSelectSectFiltro()">
00204|                                         <option value="0" selected>Todos</option>
00205|                                     </select>
00206|                                 </div>
00207|                             </div>
00208|                             <div class="col-md-3">
00209|                                 <div class="form-group">
00210|                                     <label for="filtro_sector" class="form-label">Sector</label>
00211|                                     <select class="form-select" id="filtro_sector" name="sector">
00212|                                         <option value="0" selected>Todos</option>
00213|                                     </select>
00214|                                 </div>
00215|                             </div>
00216|                         </div>
00217|                         <div class="row mb-3">
00218|                             <div class="col-md-6">
00219|                                 <div class="form-group">
00220|                                     <label for="filtro_jefe" class="form-label">Jefe</label>
00221|                                     <select class="form-select select2" id="filtro_jefe" name="jefe">
00222|                                         <option value="0" selected>Todos</option>
00223|                                         @foreach ($jefes as $jefe)
00224|                                             <option value="{{ $jefe->idEmpleado }}">
00225|                                                 {{ $jefe->Apellido }}, {{ $jefe->Nombre }} ({{ $jefe->Legajo }})
00226|                                             </option>
00227|                                         @endforeach
00228|                                     </select>
00229|                                 </div>
00230|                             </div>
00231|                         </div>
00232|                     </div>
00233|                 </form>
00234| 
00235|                 <!-- Loading indicator -->
00236|                 <div class="progress" id="load" style="display:none;">
00237|                     <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuemin="100"
00238|                         aria-valuemax="100" style="width: 100%;color:#000;">
00239|                         Cargando...
00240|                     </div>
00241|                 </div>
00242| 
00243|                 <!-- Table -->
00244|                 <div class="table-responsive">
00245|                     <table class="table table-bordered table-striped">
00246|                         <thead>
00247|                             <tr>
00248|                                 <th style="width: 50px">Legajo</th>
00249|                                 <th>Apellido y Nombre</th>
00250|                                 <th style="width: 80px">DNI</th>
00251| 
00252|                                 <th>Servicio</th>
00253|                                 <th style="width: 100px">Acciones</th>
00254|                             </tr>
00255|                         </thead>
00256|                         <tbody id="tabla-personal">
00257| 
00258|                         </tbody>
00259|                     </table>
00260|                 </div>
00261| 
00262|                 <!-- Total info and pagination -->
00263|                 <div id="total_info" class="info-pagination mt-3"></div>
00264|                 <div id="paginacion-contenedor" class="mt-3"></div>
00265|             </div>
00266|         </div>
00267|     </div>
00268| 
00269|     <!-- Form Section - Initially hidden -->
00270|     <div id="seccion-formulario" class="d-none">
00271|         <div class="card">
00272|             <div class="card-header">
00273|                 <h3 class="card-title" id="titulo-formulario">Formulario de Personal</h3>
00274|                 <div class="card-tools">
00275|                     <button type="button" id="btnVolver" class="btn btn-secondary">
00276|                         <i class="bi bi-arrow-left me-1"></i> Volver
00277|                     </button>
00278|                 </div>
00279|             </div>
00280|             <div class="card-body">
00281|                 <form role="form" id="formPersonal">
00282|                     <input type="hidden" id="idEmpleado" name="idEmpleado" value="">
00283| 
00284|                     <!-- Datos Personales -->
00285|                     <div class="card card-secondary">
00286|                         <div class="card-header">
00287|                             <h3 class="card-title">Datos Personales</h3>
00288|                         </div>
00289|                         <div class="card-body">
00290|                             <div class="row">
00291|                                 <!-- Columna Izquierda: Inputs -->
00292|                                 <div class="col-md-9">
00293|                                     <div class="row">
00294|                                         <div class="form-group col-md-3"> <!-- Antes col-md-2 -->
00295|                                             <label for="legajo">Legajo:</label>
00296|                                             <div class="input-group">
00297|                                                 <input type="text" id="legajo" name="legajo" class="form-control" required>
00298|                                                 <button type="button" id="btnActLegajo" class="btn btn-outline-secondary"
00299|                                                     title="Editar legajo" onclick="actLegajo()">
00300|                                                     <i class="fas fa-lock"></i>
00301|                                                 </button>
00302|                                             </div>
00303|                                         </div>
00304|                                         <div class="form-group col-md-4"> <!-- Antes col-md-3 -->
00305|                                             <label for="apellido">Apellido:</label>
00306|                                             <input type="text" id="apellido" name="apellido" class="form-control" required>
00307|                                         </div>
00308|                                         <div class="form-group col-md-5"> <!-- Antes col-md-3 -->
00309|                                             <label for="nombre">Nombre:</label>
00310|                                             <input type="text" id="nombre" name="nombre" class="form-control" required>
00311|                                         </div>
00312|                                     </div>
00313| 
00314|                                     <div class="row">
00315|                                         <div class="form-group col-md-3"> <!-- Antes col-md-2 -->
00316|                                             <label for="dni">DNI:</label>
00317|                                             <input type="text" id="dni" name="dni" class="form-control" required
00318|                                                 onchange="getCuit()">
00319|                                         </div>
00320|                                         <div class="form-group col-md-3"> <!-- Antes col-md-2 -->
00321|                                             <label for="sexo">Sexo:</label>
00322|                                             <select class="form-select" id="sexo" name="sexo" required onchange="getCuit()">
00323|                                                 <option value="">- Seleccionar -</option>
00324|                                                 <option value="1">Masculino</option>
00325|                                                 <option value="2">Femenino</option>
00326|                                             </select>
00327|                                         </div>
00328|                                         <div class="form-group col-md-3"> <!-- Antes col-md-2 -->
00329|                                             <label for="cuit">CUIT:</label>
00330|                                             <input type="text" id="cuit" name="cuit" class="form-control">
00331|                                         </div>
00332|                                         <div class="form-group col-md-3"> <!-- Antes col-md-3 -->
00333|                                             <label for="fecha_nacimiento">Fecha Nac.:</label>
00334|                                             <div class="input-group date" id="fecha_nacimiento_picker"
00335|                                                 data-td-target-input="nearest">
00336|                                                 <input type="text" name="fecha_nacimiento" required id="fecha_nacimiento"
00337|                                                     class="form-control datetimepicker-input"
00338|                                                     data-td-target="#fecha_nacimiento_picker" />
00339|                                                 <div class="input-group-text" data-td-target="#fecha_nacimiento_picker"
00340|                                                     data-td-toggle="datetimepicker">
00341|                                                     <i class="fa fa-calendar"></i>
00342|                                                 </div>
00343|                                             </div>
00344|                                         </div>
00345|                                     </div>
00346| 
00347|                                     <div class="row">
00348|                                         <div class="form-group col-md-4">
00349|                                             <label for="nacionalidad">Nacionalidad:</label>
00350|                                             <select class="form-select select2" id="nacionalidad" name="nacionalidad">
00351|                                                 <option value="">- Seleccionar -</option>
00352|                                             </select>
00353|                                         </div>
00354|                                         <div class="form-group col-md-4">
00355|                                             <label for="estado_civil">Estado Civil:</label>
00356|                                             <select class="form-select select2" id="estado_civil" name="estado_civil">
00357|                                                 <option value="">- Seleccionar -</option>
00358|                                             </select>
00359|                                         </div>
00360|                                     </div>
00361| 
00362|                                     <div class="row">
00363|                                         <div class="form-group col-md-4">
00364|                                             <label for="telefono">Teléfono:</label>
00365|                                             <input type="text" id="telefono" name="telefono" class="form-control">
00366|                                         </div>
00367|                                         <div class="form-group col-md-4">
00368|                                             <label for="celular">Celular:</label>
00369|                                             <input type="text" id="celular" name="celular" class="form-control">
00370|                                         </div>
00371|                                         <div class="form-group col-md-4">
00372|                                             <label for="email">Email:</label>
00373|                                             <input type="email" id="email" name="email" class="form-control">
00374|                                         </div>
00375|                                     </div>
00376|                                 </div>
00377| 
00378|                                 <!-- Columna Derecha: Foto -->
00379|                                 <div class="col-md-3 d-flex flex-column align-items-center justify-content-start pt-3">
00380|                                     <label class="mb-2">Foto del empleado:</label>
00381| 
00382|                                     <div id="crop_content" style="display: none; margin-bottom: 10px;">
00383|                                         <img id="img_foto" class="img-thumbnail"
00384|                                             style="width: 200px; height: 200px; object-fit: cover;"
00385|                                             src="{{ asset('img/dummy.png') }}">
00386|                                     </div>
00387|                                     <img id="img_crop" class="img-thumbnail"
00388|                                         style="width: 200px; height: 200px; display: block; cursor: pointer; object-fit: cover; margin-bottom: 15px;"
00389|                                         src="{{ asset('img/dummy.png') }}" title="Haz clic para cambiar la imagen">
00390| 
00391|                                     <div class="d-grid gap-2 col-10 mx-auto">
00392|                                         <input type="file" id="foto_file" accept="image/*" style="display: none;"
00393|                                             data-prev="img_foto"
00394|                                             onchange="cargarImg(this, 200, 200,onBase64ResizeFotoPerfil)">
00395| 
00396|                                         <button type="button" class="btn btn-primary btn-sm"
00397|                                             onclick="$('#foto_file').click()">
00398|                                             <i class="fas fa-upload me-1"></i> Seleccionar
00399|                                         </button>
00400|                                         <button type="button" class="btn btn-info btn-sm"
00401|                                             onclick="initCamera(); $('#modal_foto').modal('show')">
00402|                                             <i class="fas fa-camera me-1"></i> Cámara
00403|                                         </button>
00404|                                         <button type="button" id="btn_eliminar_foto" class="btn btn-danger btn-sm"
00405|                                             style="display: none;" onclick="eliminarFoto()">
00406|                                             <i class="fas fa-trash me-1"></i> Eliminar
00407|                                         </button>
00408|                                     </div>
00409|                                 </div>
00410|                             </div>
00411|                         </div>
00412|                     </div>
00413| 
00414|                     <!-- Domicilio -->
00415|                     <div class="card card-secondary">
00416|                         <div class="card-header">
00417|                             <h3 class="card-title">Domicilio</h3>
00418|                         </div>
00419|                         <div class="card-body">
00420|                             <div class="row">
00421|                                 <div class="form-group col-md-3">
00422|                                     <label for="calle">Calle:</label>
00423|                                     <input type="text" id="calle" name="calle" class="form-control">
00424|                                 </div>
00425|                                 <div class="form-group col-md-2">
00426|                                     <label for="num_calle">Número:</label>
00427|                                     <input type="text" id="num_calle" name="num_calle" class="form-control">
00428|                                 </div>
00429|                                 <div class="form-group col-md-1">
00430|                                     <label for="piso">Piso:</label>
00431|                                     <input type="text" id="piso" name="piso" class="form-control">
00432|                                 </div>
00433|                                 <div class="form-group col-md-2">
00434|                                     <label for="dto">Departamento:</label>
00435|                                     <input type="text" id="dto" name="dto" class="form-control">
00436|                                 </div>
00437|                                 <div class="form-group col-md-2">
00438|                                     <label for="manzana">Manzana:</label>
00439|                                     <input type="text" id="manzana" name="manzana" class="form-control">
00440|                                 </div>
00441|                                 <div class="form-group col-md-2">
00442|                                     <label for="casa">Casa:</label>
00443|                                     <input type="text" id="casa" name="casa" class="form-control">
00444|                                 </div>
00445|                             </div>
00446|                             <div class="row">
00447|                                 <div class="form-group col-md-2">
00448|                                     <label for="cp">Código Postal:</label>
00449|                                     <input type="text" id="cp" name="cp" class="form-control" required>
00450|                                 </div>
00451|                                 <div class="form-group col-md-4">
00452|                                     <label for="provincia">Provincia:</label>
00453|                                     <select class="form-select select2" id="provincia" name="provincia"
00454|                                         onchange="getLocalidades()">
00455|                                         <option value="">- Seleccionar -</option>
00456|                                     </select>
00457|                                 </div>
00458|                                 <div class="form-group col-md-4">
00459|                                     <label for="localidad">Localidad:</label>
00460|                                     <select class="form-select select2" id="localidad" name="localidad" onchange="getCP()">
00461|                                         <option value="">- Seleccionar -</option>
00462|                                     </select>
00463|                                 </div>
00464|                                 <div class="form-group col-md-4">
00465|                                     <label for="barrio">Barrio:</label>
00466|                                     <input type="text" id="barrio" name="barrio" class="form-control">
00467|                                 </div>
00468|                             </div>
00469|                         </div>
00470|                     </div>
00471| 
00472| 
00473|                     <!-- Datos Profesionales -->
00474|                     <div class="card card-secondary">
00475|                         <div class="card-header">
00476|                             <h3 class="card-title">Datos Profesionales</h3>
00477|                         </div>
00478|                         <div class="card-body">
00479|                             <div class="row">
00480|                                 <div class="form-group col-md-3">
00481|                                     <label for="profesion">Profesión:</label>
00482|                                     <select class="form-select select2" id="profesion" name="profesion">
00483|                                         <option value="">- Seleccionar -</option>
00484|                                         @foreach ($profesiones as $profesion)
00485|                                             <option value="{{ $profesion->idprofesion }}">
00486|                                                 {{ $profesion->profesion }}
00487|                                             </option>
00488|                                         @endforeach
00489|                                     </select>
00490|                                 </div>
00491|                                 <div class="form-group col-md-3">
00492|                                     <label for="funcion">Función:</label>
00493|                                     <select class="form-select select2" id="funcion" name="funcion">
00494|                                         <option value="">- Seleccionar -</option>
00495|                                         @foreach ($funciones as $funcion)
00496|                                             <option value="{{ $funcion->IdFuncion }}">
00497|                                                 {{ $funcion->Funcion }}
00498|                                             </option>
00499|                                         @endforeach
00500|                                     </select>
00501|                                 </div>
00502|                                 <div class="form-group col-md-3">
00503|                                     <label for="tipo_tarea">Tipo de Tarea:</label>
00504|                                     <select class="form-select select2" id="tipo_tarea" name="tipo_tarea" required>
00505|                                         <option value="">- Seleccionar -</option>
00506|                                     </select>
00507|                                 </div>
00508|                                 <div class="form-group col-md-3">
00509|                                     <label for="num_matricula">Matrícula:</label>
00510|                                     <input type="text" maxlength="50" id="num_matricula" name="num_matricula"
00511|                                         class="form-control">
00512|                                 </div>
00513|                             </div>
00514|                         </div>
00515|                     </div>
00516| 
00517|                     <!-- Datos Laborales -->
00518|                     <div class="card card-secondary">
00519|                         <div class="card-header">
00520|                             <h3 class="card-title">Datos Laborales</h3>
00521|                         </div>
00522|                         <div class="card-body">
00523|                             <!-- Fechas y Contrato -->
00524|                             <div class="row">
00525|                                 <div class="form-group col-md-3">
00526|                                     <label for="fecha_alta">Fecha Alta en Zona:</label>
00527|                                     <div class="input-group date" id="fecha_alta_picker" data-td-target-input="nearest">
00528|                                         <input type="text" name="fecha_alta" required id="fecha_alta"
00529|                                             class="form-control datetimepicker-input" data-td-target="#fecha_alta_picker" />
00530|                                         <div class="input-group-text" data-td-target="#fecha_alta_picker"
00531|                                             data-td-toggle="datetimepicker">
00532|                                             <i class="fa fa-calendar"></i>
00533|                                         </div>
00534|                                     </div>
00535|                                 </div>
00536|                                 <div class="form-group col-md-3">
00537|                                     <label for="fecha_adm_publica">Fecha Alta Adm. Publ.:</label>
00538|                                     <div class="input-group date" id="fecha_adm_publica_picker"
00539|                                         data-td-target-input="nearest">
00540|                                         <input type="text" name="fecha_adm_publica" required id="fecha_adm_publica"
00541|                                             class="form-control datetimepicker-input"
00542|                                             data-td-target="#fecha_adm_publica_picker" />
00543|                                         <div class="input-group-text" data-td-target="#fecha_adm_publica_picker"
00544|                                             data-td-toggle="datetimepicker">
00545|                                             <i class="fa fa-calendar"></i>
00546|                                         </div>
00547|                                     </div>
00548|                                 </div>
00549|                                 <div class="form-group col-md-3">
00550|                                     <label for="nro_contrato">N° Contrato:</label>
00551|                                     <input type="text" maxlength="10" id="nro_contrato" name="nro_contrato"
00552|                                         class="form-control">
00553|                                 </div>
00554|                                 <div class="form-group col-md-3">
00555|                                     <label for="relacion">Relación Laboral:</label>
00556|                                     <select class="form-select select2" id="relacion" name="relacion" required>
00557|                                         <option value="">- Seleccionar -</option>
00558|                                     </select>
00559|                                 </div>
00560|                             </div>
00561| 
00562|                             <!-- Categorización -->
00563|                             <div class="row">
00564|                                 <div class="form-group col-md-4">
00565|                                     <label for="agrupamiento">Grado/Agrupamiento:</label>
00566|                                     <select class="form-select select2" id="agrupamiento" name="agrupamiento" required>
00567|                                         <option value="">- Seleccionar -</option>
00568|                                     </select>
00569|                                 </div>
00570|                                 <div class="form-group col-md-4">
00571|                                     <label for="categoria">Categoría:</label>
00572|                                     <select class="form-select select2" id="categoria" name="categoria">
00573|                                         <option value="">- Seleccionar -</option>
00574|                                     </select>
00575|                                 </div>
00576|                                 <div class="form-group col-md-4">
00577|                                     <label for="cargo">Cargo:</label>
00578|                                     <select class="form-select select2" id="cargo" name="cargo">
00579|                                         <option value="">- Seleccionar -</option>
00580|                                     </select>
00581|                                 </div>
00582|                             </div>
00583| 
00584|                             <!-- Asignación Organizacional -->
00585|                             <hr class="my-4">
00586|                             <h5 class="mb-3 text-secondary">Ubicación Organizacional</h5>
00587|                             <div class="row">
00588|                                 <div class="form-group col-md-3">
00589|                                     <label for="gerencia">Gerencia:</label>
00590|                                     <select class="form-select select2" id="gerencia" name="gerencia"
00591|                                         onchange="CargaSelectDto()" data-id_jefe="4">>
00592|                                         <option value="">- Seleccionar -</option>
00593|                                         @foreach ($gerencias as $gerencia)
00594|                                             <option value="{{ $gerencia->idGerencia }}">
00595|                                                 {{ $gerencia->Gerencia }}
00596|                                             </option>
00597|                                         @endforeach
00598|                                     </select>
00599|                                 </div>
00600|                                 <div class="form-group col-md-3">
00601|                                     <label for="departamento">Departamento:</label>
00602|                                     <select class="form-select select2" id="departamento" name="departamento"
00603|                                         onchange="CargaSelectServ()" data-id_jefe="3">>
00604|                                         <option value="">- Seleccionar -</option>
00605|                                     </select>
00606|                                 </div>
00607|                             </div>
00608| 
00609|                             <!-- Asignación de Servicios -->
00610|                             <hr class="my-4">
00611|                             <div class="row">
00612|                                 <div class="col-12">
00613|                                     <h5 class="mb-3 text-secondary">Asignación de Servicios</h5>
00614|                                 </div>
00615|                             </div>
00616|                             <div class="row mb-3">
00617|                                 <div class="form-group col-md-4">
00618|                                     <label for="servicio">Servicio:</label>
00619|                                     <select class="form-select select2" id="servicio" name="servicio">
00620|                                         <option value="">- Seleccionar -</option>
00621|                                     </select>
00622|                                 </div>
00623|                                 <div class="form-group col-md-2 d-flex align-items-end">
00624|                                     <button type="button" class="btn btn-primary w-100" onclick="agregarServicio()">
00625|                                         <i class="fas fa-plus"></i> Agregar
00626|                                     </button>
00627|                                 </div>
00628|                             </div>
00629| 
00630|                             <!-- Lista de servicios asignados -->
00631|                             <div class="row">
00632|                                 <div class="col-12">
00633|                                     <div id="servicios-asignados-container" class="border rounded p-3 bg-light">
00634|                                         <p class="text-muted text-center" id="sin-servicios-msg">No hay servicios asignados</p>
00635|                                     </div>
00636|                                 </div>
00637|                             </div>
00638| 
00639|                             <!-- Historial de Servicios -->
00640|                             <hr class="my-4">
00641|                             <div class="row">
00642|                                 <div class="col-12">
00643|                                     <h5 class="mb-3 text-secondary">Historial de Servicios</h5>
00644|                                     <div class="table-responsive">
00645|                                         <table class="table table-bordered table-striped table-sm text-center">
00646|                                             <thead class="bg-light">
00647|                                                 <tr>
00648|                                                     <th>Servicio</th>
00649|                                                     <th>Fecha Inicio</th>
00650|                                                     <th>Fecha Fin</th>
00651|                                                     <th>Estado</th>
00652|                                                     <th>Motivo</th>
00653|                                                 </tr>
00654|                                             </thead>
00655|                                             <tbody id="tabla_historial_servicios">
00656|                                                 <!-- Se llena via JS -->
00657|                                                 <tr>
00658|                                                     <td colspan="5">Sin historial</td>
00659|                                                 </tr>
00660|                                             </tbody>
00661|                                         </table>
00662|                                     </div>
00663|                                 </div>
00664|                             </div>
00665| 
00666|                             <!-- Estado y Baja -->
00667|                             <hr class="my-4">
00668|                             <div class="row">
00669|                                 <div class="form-group col-md-6">
00670|                                     <label for="estado">Estado del Personal:</label>
00671|                                     <select class="form-select" id="estado" name="estado" onchange="changeEstado()">
00672|                                         <option value="">- Seleccionar -</option>
00673|                                         @foreach ($estados as $estado)
00674|                                             <option value="{{ $estado->idEstado }}">
00675|                                                 {{ $estado->estado }}
00676|                                             </option>
00677|                                         @endforeach
00678|                                     </select>
00679|                                 </div>
00680| 
00681|                                 <div class="form-group col-md-3" id="fecha_baja_group" style="display: none;">
00682|                                     <label for="fecha_baja">Fecha de Baja:</label>
00683|                                     <div class="input-group date" id="fecha_baja_picker" data-td-target-input="nearest">
00684|                                         <input type="text" name="fecha_baja" id="fecha_baja"
00685|                                             class="form-control datetimepicker-input" data-td-target="#fecha_baja_picker" />
00686|                                         <div class="input-group-text" data-td-target="#fecha_baja_picker"
00687|                                             data-td-toggle="datetimepicker">
00688|                                             <i class="fa fa-calendar"></i>
00689|                                         </div>
00690|                                     </div>
00691|                                 </div>
00692|                                 <div class="form-group col-md-3" id="motivo_baja_group" style="display: none;">
00693|                                     <label for="motivo_baja">Motivo de Baja:</label>
00694|                                     <select class="form-select select2" id="motivo_baja" name="motivo_baja">
00695|                                         <option value="">- Seleccionar -</option>
00696|                                     </select>
00697|                                 </div>
00698|                                 <div class="form-group col-12" id="des_baja_group" style="display: none;">
00699|                                     <label for="des_baja">Descripción de Baja:</label>
00700|                                     <textarea id="des_baja" name="des_baja" class="form-control" rows="2"></textarea>
00701|                                 </div>
00702|                             </div>
00703|                             <div class="row">
00704|                                 <div class="form-group col-md-3">
00705|                                     <div class="custom-control custom-checkbox">
00706|                                         <input type="checkbox" class="custom-control-input" id="f_doble" name="f_doble"
00707|                                             checked>
00708|                                         <label class="custom-control-label" for="f_doble">F doble</label>
00709|                                     </div>
00710|                                 </div>
00711|                                 <div class="form-group col-md-3">
00712|                                     <div class="custom-control custom-checkbox">
00713|                                         <input type="checkbox" class="custom-control-input" id="fe" name="fe">
00714|                                         <label class="custom-control-label" for="fe">NO FE</label>
00715|                                     </div>
00716|                                 </div>
00717|                             </div>
00718|                             <div class="row">
00719|                                 <div class="form-group col-md-3">
00720|                                     <label for="tipo_jornada">Tipo de Jornada:</label>
00721|                                     <select class="form-select select2" required id="tipo_jornada" name="tipo_jornada"
00722|                                         onchange="changJornada()">
00723|                                         <option value="">- Seleccionar -</option>
00724|                                     </select>
00725|                                 </div>
00726| 
00727| 
00728|                                 <div class="form-group col-md-3">
00729|                                     <label for="f_jornada">Jornada a partir de:</label>
00730|                                     <div class="input-group date" id="f_jornada_picker" data-target-input="nearest">
00731|                                         <input type="text" name="f_jornada" required id="f_jornada"
00732|                                             class="form-control datetimepicker-input" data-target="#f_jornada_picker" />
00733|                                         <div class="input-group-append" data-target="#f_jornada_picker"
00734|                                             data-toggle="datetimepicker">
00735|                                             <div class="input-group-text"><i class="fa fa-calendar"></i></div>
00736|                                         </div>
00737|                                     </div>
00738|                                 </div>
00739|                                 <div class="form-group col-md-3">
00740|                                     <button type="button" class="btn btn-info" onclick="verJornadas()"
00741|                                         style="margin-top: 32px;">
00742|                                         <i class="fas fa-history"></i> Ver Historial de Jornadas
00743|                                     </button>
00744|                                 </div>
00745| 
00746|                             </div>
00747|                             <div class="row">
00748|                                 <div class="form-group col-md-12">
00749|                                     <label for="observacion">Observaciones:</label>
00750|                                     <textarea id="observacion" name="observacion" class="form-control" rows="3"></textarea>
00751|                                 </div>
00752|                             </div>
00753|                         </div>
00754|                     </div>
00755| 
00756|                     <!-- Historial de Relaciones -->
00757|                     <div class="card card-secondary">
00758|                         <div class="card-header">
00759|                             <h3 class="card-title">Historial de Relaciones</h3>
00760|                             <div class="card-tools">
00761|                                 <button type="button" class="btn btn-success btn-sm" onclick="addHistorialRel()">
00762|                                     <i class="fas fa-plus"></i> Agregar Relación
00763|                                 </button>
00764|                             </div>
00765|                         </div>
00766|                         <div class="card-body">
00767|                             <div id="container_relaciones">
00768|                                 <!-- Relaciones dinámicas se agregan aquí -->
00769|                             </div>
00770|                         </div>
00771|                     </div>
00772| 
00773|                     <!-- Documentos Escaneados -->
00774|                     <div class="card card-secondary">
00775|                         <div class="card-header">
00776|                             <h3 class="card-title">Documentos Escaneados</h3>
00777|                             <div class="card-tools">
00778|                                 <button type="button" class="btn btn-success btn-sm" onclick="addDoc()">
00779|                                     <i class="fas fa-plus"></i> Agregar Documento
00780|                                 </button>
00781|                             </div>
00782|                         </div>
00783|                         <div class="card-body">
00784|                             <div id="container_doc">
00785|                                 <!-- Documentos dinámicos se agregan aquí -->
00786|                             </div>
00787|                         </div>
00788|                     </div>
00789|                 </form>
00790|             </div>
00791|             <div class="card-footer">
00792|                 @if ($permisos['crear'] || $permisos['editar'])
00793|                     <button type="button" id="btnGuardar" class="btn btn-primary">
00794|                         <i class="fas fa-save"></i> Guardar
00795|                     </button>
00796|                 @endif
00797|                 <button type="button" id="btnLimpiar" class="btn btn-warning">
00798|                     <i class="fas fa-times"></i> Limpiar
00799|                 </button>
00800|                 <button type="button" id="btnImprimir" class="btn btn-info">
00801|                     <i class="fas fa-print"></i> Imprimir
00802|                 </button>
00803|                 @if ($permisos['eliminar'])
00804|                     <button type="button" id="btnEliminar" class="btn btn-danger" onclick="modalEliminar()">
00805|                         <i class="fa fa-trash"></i> Eliminar
00806|                     </button>
00807|                 @endif
00808|                 <!-- Botón para llenar con datos de prueba (solo en desarrollo) -->
00809|                 <button type="button" id="btnDatosPrueba" class="btn btn-secondary" onclick="fillPersonalFormWithTestData()"
00810|                     title="Llenar con datos de prueba">
00811|                     <i class="fas fa-flask"></i> Datos de Prueba
00812|                 </button>
00813|             </div>
00814|         </div>
00815|     </div>
00816| 
00817|     @if (!($permisos['leer'] ?? false))
00818|         <div class="alert alert-warning">
00819|             No tiene permisos para ver el listado de personal.
00820|         </div>
00821|     @endif
00822| @endsection
00823| 
00824| @push('scripts')
00825|     <!-- Include custom JavaScript file -->
00826|     <script src="{{ asset('js/croppie.js') }}"></script>
00827|     <script src="{{ asset('js/webcam.min.js') }}"></script>
00828|     <script src="{{ asset('js/form-filler.js') }}"></script>
00829|     <script src="{{ asset('js/personal.js') }}"></script>
00830| @endpush
00831| 
00832| @push('styles')
00833|     <style>
00834|         /* Estilos adicionales para el módulo de personal */
00835|         .table th {
00836|             background-color: #f4f6f9;
00837|         }
00838| 
00839|         .btn-group-sm>.btn {
00840|             padding: 0.25rem 0.5rem;
00841|         }
00842| 
00843|         /* Estilos responsivos */
00844|         @media (max-width: 767.98px) {
00845|             .table-responsive {
00846|                 font-size: 0.9rem;
00847|             }
00848|         }
00849| 
00850|         .typeahead {
00851|             background-color: #fff;
00852|         }
00853| 
00854|         .tt-menu {
00855|             width: 100%;
00856|             margin: 2px 0;
00857|             padding: 8px 0;
00858|             background-color: #fff;
00859|             border: 1px solid #ccc;
00860|             border: 1px solid rgba(0, 0, 0, 0.2);
00861|             border-radius: 8px;
00862|             box-shadow: 0 5px 10px rgba(0, 0, 0, .2);
00863|         }
00864| 
00865|         .tt-suggestion {
00866|             padding: 3px 20px;
00867|             line-height: 24px;
00868|         }
00869| 
00870|         .tt-suggestion:hover {
00871|             cursor: pointer;
00872|             color: #fff;
00873|             background-color: #0097cf;
00874|         }
00875| 
00876|         .tt-suggestion.tt-cursor {
00877|             color: #fff;
00878|             background-color: #0097cf;
00879|         }
00880| 
00881|         .info-pagination {
00882|             font-size: 0.9rem;
00883|             color: #666;
00884|         }
00885| 

(Output truncated at 51200 bytes. Use 'offset' parameter to read beyond line 885)
</file>
```
```

```
Tool: read

**Input:**
```json
{
  "filePath": "D:\\laragon\\www\\zona_capital_laravel\\app\\Models\\Empleado.php"
}
```
**Output:**
```
<file>
00001| <?php
00002| 
00003| namespace App\Models;
00004| 
00005| use Illuminate\Database\Eloquent\Factories\HasFactory;
00006| use Illuminate\Database\Eloquent\Model;
00007| use Illuminate\Support\Facades\DB;
00008| 
00009| class Empleado extends Model
00010| {
00011|     use HasFactory;
00012| 
00013|     /**
00014|      * The table associated with the model.
00015|      *
00016|      * @var string
00017|      */
00018|     protected $table = 'empleados';
00019| 
00020|     /**
00021|      * The primary key for the model.
00022|      *
00023|      * @var string
00024|      */
00025|     protected $primaryKey = 'idEmpleado';
00026| 
00027|     /**
00028|      * Indicates if the model should be timestamped.
00029|      *
00030|      * @var bool
00031|      */
00032|     public $timestamps = false;
00033| 
00034|     /**
00035|      * The attributes that are mass assignable.
00036|      *
00037|      * @var array
00038|      */
00039|     protected $fillable = [
00040|         'Legajo',
00041|         'Apellido',
00042|         'Nombre',
00043|         'DNI',
00044|         'cuit',
00045|         'sexo',
00046|         'FecNac',
00047|         'EstCiv',
00048|         'Nacionalidad',
00049|         'Provincia',
00050|         'Localidad',
00051|         'CP',
00052|         'calle',
00053|         'CalleNum',
00054|         'Piso',
00055|         'Departamento',
00056|         'Barrio',
00057|         'Manzana',
00058|         'Casa',
00059|         'Email',
00060|         'Telefono',
00061|         'Celular',
00062|         'idProfesion',
00063|         'Funcion',
00064|         'idInstrucion',
00065|         'idTipoRelacion',
00066|         'idGerencia',
00067|         'idDepartamento',
00068|         'idServicio',
00069|         'idSector',
00070|         'FAlta',
00071|         'FBaja',
00072|         'FAltaAP',
00073|         'Estado',
00074|         'MotivoBaja_Id',
00075|         'DescripcionBaja',
00076|         'Observacion',
00077|         'Jornada_Id',
00078|         'DobleFS',
00079|         'Nocturno',
00080|         'FE',
00081|         'Gremio',
00082|         'NroContrato',
00083|         'Matricula',
00084|         'codigo',
00085|         'idAgrupamiento',
00086|         'categoria',
00087|         'idCargo',
00088|         'IdEmpleado2', // Campo que certifica (jefe)
00089|         'Convenios',
00090|         'Foto'
00091|     ];
00092| 
00093|     /**
00094|      * The attributes that should be cast.
00095|      *
00096|      * @var array
00097|      */
00098|     protected $casts = [
00099|         'FecNac' => 'date',
00100|         'FAlta' => 'date',
00101|         'FBaja' => 'date',
00102|         'FAltaAP' => 'date',
00103|         'FechaCreacion' => 'date',
00104|         'FechaEliminacion' => 'date',
00105|         'DobleFS' => 'boolean',
00106| 
00107|         'FE' => 'boolean',
00108| 
00109| 
00110|     ];
00111| 
00112|     /**
00113|      * Get the full name of the employee
00114|      *
00115|      * @return string
00116|      */
00117|     public function getNombreCompletoAttribute()
00118|     {
00119|         return $this->Apellido . ' ' . $this->Nombre;
00120|     }
00121| 
00122|     /**
00123|      * Get the gerencia that owns the employee.
00124|      */
00125|     public function gerencia()
00126|     {
00127|         return $this->belongsTo(Gerencia::class, 'idGerencia', 'idGerencia');
00128|     }
00129| 
00130|     /**
00131|      * Get the departamento that owns the employee.
00132|      */
00133|     public function departamento()
00134|     {
00135|         return $this->belongsTo(Departamento::class, 'idDepartamento', 'idDepartamento');
00136|     }
00137| 
00138|     /**
00139|      * Get the servicio that owns the employee.
00140|      */
00141|     public function servicio()
00142|     {
00143|         return $this->belongsTo(Servicio::class, 'idServicio', 'idServicio');
00144|     }
00145| 
00146|     /**
00147|      * Get the sector that owns the employee.
00148|      */
00149|     public function sector()
00150|     {
00151|         return $this->belongsTo(Sector::class, 'idSector', 'idSector');
00152|     }
00153| 
00154|     /**
00155|      * Get the provincia that owns the employee.
00156|      */
00157|     public function provincia()
00158|     {
00159|         return $this->belongsTo(Provincia::class, 'Provincia', 'IdProvincia');
00160|     }
00161| 
00162|     /**
00163|      * Get the localidad that owns the employee.
00164|      */
00165|     public function localidad()
00166|     {
00167|         return $this->belongsTo(Localidad::class, 'Localidad', 'IdLocalidad');
00168|     }
00169| 
00170|     /**
00171|      * Get the estado civil that owns the employee.
00172|      */
00173|     public function estadoCivil()
00174|     {
00175|         return $this->belongsTo(EstadoCivil::class, 'EstCiv', 'idEstadoCivil');
00176|     }
00177| 
00178|     /**
00179|      * Get the profesion that owns the employee.
00180|      */
00181|     public function profesion()
00182|     {
00183|         return $this->belongsTo(Profesion::class, 'idProfesion', 'idprofesion');
00184|     }
00185| 
00186|     /**
00187|      * Get the funcion that owns the employee.
00188|      */
00189|     public function funcion()
00190|     {
00191|         return $this->belongsTo(Funcion::class, 'Funcion', 'IdFuncion');
00192|     }
00193| 
00194|     /**
00195|      * Get the instruccion that owns the employee.
00196|      */
00197|     public function instruccion()
00198|     {
00199|         return $this->belongsTo(Instruccion::class, 'idInstrucion', 'idInstruccion');
00200|     }
00201| 
00202|     /**
00203|      * Get the tipo relacion that owns the employee.
00204|      */
00205|     public function tipoRelacion()
00206|     {
00207|         return $this->belongsTo(TipoRelacion::class, 'idTipoRelacion', 'idRelacion');
00208|     }
00209| 
00210|     /**
00211|      * Get the tipo jornada that owns the employee.
00212|      */
00213|     public function tipoJornada()
00214|     {
00215|         return $this->belongsTo(TipoJornada::class, 'Jornada_Id', 'IdTipoJornada');
00216|     }
00217| 
00218|     /**
00219|      * Get the motivo baja that owns the employee.
00220|      */
00221|     public function motivoBaja()
00222|     {
00223|         return $this->belongsTo(MotivoBaja::class, 'MotivoBaja_Id', 'IdMotivoBaja');
00224|     }
00225| 
00226|     /**
00227|      * Get the pais (nacionalidad) that owns the employee.
00228|      */
00229|     public function pais()
00230|     {
00231|         return $this->belongsTo(Pais::class, 'Nacionalidad', 'IdPais');
00232|     }
00233| 
00234|     /**
00235|      * Get the productivity records for the employee.
00236|      */
00237|     public function productividades()
00238|     {
00239|         return $this->hasMany(Productividad::class, 'Personal_Id', 'idEmpleado');
00240|     }
00241| 
00242|     /**
00243|      * Get the documentos escaneados for the employee.
00244|      */
00245|     public function documentosEscaneados()
00246|     {
00247|         return $this->hasMany(DocumentoEscaneado::class, 'Empleado_Id', 'idEmpleado');
00248|     }
00249| 
00250|     /**
00251|      * Get the historial relaciones for the employee.
00252|      */
00253|     public function historialRelaciones()
00254|     {
00255|         return $this->hasMany(HistorialRelacion::class, 'Personal_Id', 'idEmpleado');
00256|     }
00257| 
00258|     /**
00259|      * Get the historial modificaciones for the employee.
00260|      */
00261|     public function historialModificaciones()
00262|     {
00263|         return $this->hasMany(HistorialModPers::class, 'EmpleadoMod_Id', 'idEmpleado');
00264|     }
00265| 
00266|     /**
00267|      * Get the jornadas for the employee.
00268|      */
00269|     public function jornadas()
00270|     {
00271|         return $this->hasMany(JornadaXEmp::class, 'Emp_Id', 'idEmpleado');
00272|     }
00273| 
00274|     /**
00275|      * Scope a query to only include active employees.
00276|      *
00277|      * @param  \Illuminate\Database\Eloquent\Builder  $query
00278|      * @return \Illuminate\Database\Eloquent\Builder
00279|      */
00280|     public function scopeActivos($query)
00281|     {
00282|         return $query->where('Estado', 1)->where('FBaja', '0000-00-00');
00283|     }
00284| 
00285|     /**
00286|      * Scope a query to only include inactive employees.
00287|      *
00288|      * @param  \Illuminate\Database\Eloquent\Builder  $query
00289|      * @return \Illuminate\Database\Eloquent\Builder
00290|      */
00291|     public function scopeInactivos($query)
00292|     {
00293|         return $query->where('Estado', 0)->orWhere('FBaja', '!=', '0000-00-00');
00294|     }
00295| 
00296|     /**
00297|      * Get the employee's age.
00298|      *
00299|      * @return int|null
00300|      */
00301|     public function getEdadAttribute()
00302|     {
00303|         return $this->FecNac ? $this->FecNac->age : null;
00304|     }
00305| 
00306|     /**
00307|      * Get the employee's status text.
00308|      *
00309|      * @return string
00310|      */
00311|     public function getEstadoTextoAttribute()
00312|     {
00313|         return $this->Estado == 1 ? 'Activo' : 'Inactivo';
00314|     }
00315| 
00316|     /**
00317|      * Get the employee's sex text.
00318|      *
00319|      * @return string
00320|      */
00321|     public function getSexoTextoAttribute()
00322|     {
00323|         return $this->sexo == 1 ? 'Masculino' : 'Femenino';
00324|     }
00325| 
00326|     /**
00327|      * Get the services that are assigned to the employee.
00328|      */
00329|     public function servicios()
00330|     {
00331|         return $this->belongsToMany(Servicio::class, 'empleado_servicio', 'empleado_id', 'servicio_id', 'idEmpleado', 'idServicio')
00332|             ->withPivot('fecha_inicio', 'fecha_fin', 'activo', 'motivo', 'certificador_id')
00333|             ->withTimestamps();
00334|     }
00335| 
00336|     /**
00337|      * Get active services only.
00338|      */
00339|     public function serviciosActivos()
00340|     {
00341|         return $this->servicios()->wherePivot('activo', true);
00342|     }
00343| 
00344|     /**
00345|      * Sync services with history tracking.
00346|      *
00347|      * @param array $serviciosConCertificadores Array of servicios con formato:
00348|      *        [
00349|      *          ['servicio_id' => X, 'certificador_id' => Y, 'fecha_pase' => 'Z', 'sector_id' => W],
00350|      *          ...
00351|      *        ]
00352|      *        O formato legacy: [servicio_id_1, servicio_id_2, ...]
00353|      * @param string|null $motivo Optional reason for changes.
00354|      */
00355|     public function actualizarServicios(array $serviciosConCertificadores, $motivo = null, $fechaMovimiento = null)
00356|     {
00357|         // Convertir formato legacy a nuevo si es necesario
00358|         $nuevosServicios = [];
00359|         foreach ($serviciosConCertificadores as $serv) {
00360|             if (is_array($serv)) {
00361|                 // Formato nuevo con detalles
00362|                 $nuevosServicios[] = [
00363|                     'servicio_id' => $serv['servicio_id'],
00364|                     'certificador_id' => $serv['certificador_id'] ?? null,
00365|                     'fecha_pase' => $serv['fecha_pase'] ?? $fechaMovimiento ?? now(),
00366|                     'sector_id' => $serv['sector_id'] ?? null
00367|                 ];
00368|             } else {
00369|                 // Formato legacy (solo ID)
00370|                 $nuevosServicios[] = [
00371|                     'servicio_id' => $serv,
00372|                     'certificador_id' => null,
00373|                     'fecha_pase' => $fechaMovimiento ?? now(),
00374|                     'sector_id' => null
00375|                 ];
00376|             }
00377|         }
00378| 
00379|         // Obtener servicios activos actuales con sus certificadores
00380|         $actuales = DB::table('empleado_servicio')
00381|             ->where('empleado_id', $this->idEmpleado)
00382|             ->where('activo', true)
00383|             ->get(['servicio_id', 'certificador_id']);
00384| 
00385|         $actualesMap = [];
00386|         foreach ($actuales as $a) {
00387|             $actualesMap[$a->servicio_id] = $a->certificador_id;
00388|         }
00389| 
00390|         $fecha = $fechaMovimiento ? $fechaMovimiento : now();
00391| 
00392|         // Calcular cambios
00393|         $nuevosIds = array_column($nuevosServicios, 'servicio_id');
00394|         $actualesIds = array_keys($actualesMap);
00395|         $paraAgregar = array_diff($nuevosIds, $actualesIds);
00396|         $paraQuitar = array_diff($actualesIds, $nuevosIds);
00397|         $paraActualizar = array_intersect($nuevosIds, $actualesIds);
00398| 
00399|         // Desactivar los que ya no están
00400|         if (!empty($paraQuitar)) {
00401|             DB::table('empleado_servicio')
00402|                 ->where('empleado_id', $this->idEmpleado)
00403|                 ->where('activo', true)
00404|                 ->whereIn('servicio_id', $paraQuitar)
00405|                 ->update([
00406|                     'activo' => false,
00407|                     'fecha_fin' => $fecha,
00408|                     'motivo' => $motivo ? $motivo . ' (Baja)' : 'Actualización'
00409|                 ]);
00410|         }
00411| 
00412|         // Agregar los nuevos
00413|         foreach ($nuevosServicios as $nuevo) {
00414|             if (in_array($nuevo['servicio_id'], $paraAgregar)) {
00415|                 DB::table('empleado_servicio')->insert([
00416|                     'empleado_id' => $this->idEmpleado,
00417|                     'servicio_id' => $nuevo['servicio_id'],
00418|                     'certificador_id' => $nuevo['certificador_id'],
00419|                     'fecha_inicio' => $nuevo['fecha_pase'],
00420|                     'activo' => true,
00421|                     'motivo' => $motivo ? $motivo . ' (Alta)' : 'Nuevo Servicio',
00422|                     'created_at' => now(),
00423|                     'updated_at' => now()
00424|                 ]);
00425|             }
00426|         }
00427| 
00428|         // Actualizar los existentes si cambió el certificador
00429|         foreach ($nuevosServicios as $nuevo) {
00430|             if (in_array($nuevo['servicio_id'], $paraActualizar)) {
00431|                 $certificadorActual = $actualesMap[$nuevo['servicio_id']];
00432|                 // Si cambió el certificador, actualizar
00433|                 if ($certificadorActual != $nuevo['certificador_id']) {
00434|                     DB::table('empleado_servicio')
00435|                         ->where('empleado_id', $this->idEmpleado)
00436|                         ->where('servicio_id', $nuevo['servicio_id'])
00437|                         ->where('activo', true)
00438|                         ->update([
00439|                             'certificador_id' => $nuevo['certificador_id'],
00440|                             'updated_at' => now()
00441|                         ]);
00442|                 }
00443|             }
00444|         }
00445|     }
00446| 
00447| 
00448| }
00449| 

(End of file - total 449 lines)
</file>
```
```

---

## Assistant (Plan · glm-4.7-free · 7.8s)

_Thinking:_

Ahora necesito ver el archivo JavaScript que maneja la lógica de la vista personal para entender qué pasa cuando se quita el estatus de jefe a una persona. Voy a buscar el archivo personal.js.

```
Tool: read

**Input:**
```json
{
  "filePath": "D:\\laragon\\www\\zona_capital_laravel\\public\\js\\personal.js"
}
```
**Output:**
```
<file>
00001| /**
00002|  * Módulo JavaScript para la gestión de Personal
00003|  * -----------------------------------------------
00004|  *
00005|  * DESCRIPCIÓN GENERAL:
00006|  * Este archivo contiene la lógica completa del frontend para el módulo de Personal,
00007|  * implementando un patrón de Single Page Application (SPA) con carga dinámica de datos
00008|  * mediante AJAX y manejo de formularios con validación.
00009|  *
00010|  * ESTRUCTURA DEL CÓDIGO:
00011|  * - Inicialización y variables globales: Configuración inicial, permisos y variables de estado
00012|  * - Funciones de inicialización: Configuración de UI, inicialización de plugins y event listeners
00013|  * - Funciones de paginación y carga: Gestión de paginación y carga de datos mediante AJAX
00014|  * - Funciones de UI: Mostrar/ocultar secciones (listado vs formulario)
00015|  * - Funciones CRUD: Operaciones sobre registros de personal
00016|  * - Funciones auxiliares: Manejo de imágenes, validaciones y utilidades
00017|  */
00018| 
00019| document.addEventListener('DOMContentLoaded', function () {
00020|     // ========================================
00021|     // INICIALIZACIÓN Y VARIABLES GLOBALES
00022|     // ========================================
00023| 
00024|     // Obtener permisos desde los inputs hidden
00025|     const permisos = {
00026|         crear: document.getElementById('permiso_crear')?.value === '1',
00027|         leer: document.getElementById('permiso_leer')?.value === '1',
00028|         editar: document.getElementById('permiso_editar')?.value === '1',
00029|         eliminar: document.getElementById('permiso_eliminar')?.value === '1'
00030|     };
00031| 
00032|     // Variables de estado
00033|     let modoFormulario = 'crear'; // 'crear', 'editar', 'ver'
00034|     let empleadoEditando = null;
00035|     let cargandoDatos = false; // Variable para controlar cuando estamos cargando datos iniciales
00036|     let cropImg = null;
00037|     let selectoresIniciales = {};
00038|     let num_relaciones = 0;
00039|     let num_doc = 0;
00040|     let arrImgsDel = [];
00041|     let del_jor = [];
00042|     let data_local = [];
00043|     let initCameraModal = false;
00044|     let Toast;
00045|     let JornadaOriginal_Id = 0;
00046|     let eliminarFotoFlag = false; // Bandera para indicar si se debe eliminar la foto
00047|     let FechaJornadaOri = "";
00048| 
00049|     // Funciones para validar DNI y legajo
00050|     function validarDniExistente(dni) {
00051|         if (!dni || dni.trim() === '') return;
00052| 
00053|         const idEmpleado = document.getElementById('idEmpleado')?.value;
00054| 
00055|         apiLaravel('/personal/check-dni', 'POST', {
00056|             dni: dni,
00057|             exclude_id: idEmpleado || null
00058|         })
00059|             .then(response => {
00060|                 if (response.exists) {
00061|                     Toast.fire({
00062|                         icon: "error",
00063|                         title: "Este DNI ya está registrado en el sistema"
00064|                     });
00065|                     document.getElementById('dni').value = '';
00066|                     document.getElementById('dni').focus();
00067|                 }
00068|             })
00069|             .catch(error => {
00070|                 console.error('Error al validar DNI:', error);
00071|             });
00072|     }
00073| 
00074|     function validarLegajoExistente(legajo) {
00075|         if (!legajo || legajo.trim() === '') return;
00076| 
00077|         const idEmpleado = document.getElementById('idEmpleado')?.value;
00078| 
00079|         apiLaravel('/personal/check-legajo', 'POST', {
00080|             legajo: legajo,
00081|             exclude_id: idEmpleado || null
00082|         })
00083|             .then(response => {
00084|                 if (response.exists) {
00085|                     Toast.fire({
00086|                         icon: "error",
00087|                         title: "Este Legajo ya está registrado en el sistema"
00088|                     });
00089|                     document.getElementById('legajo').value = '';
00090|                     document.getElementById('legajo').focus();
00091|                 }
00092|             })
00093|             .catch(error => {
00094|                 console.error('Error al validar Legajo:', error);
00095|             });
00096|     }
00097| 
00098|     // Variables de paginación
00099|     const paginacionContenedor = document.getElementById('paginacion-contenedor');
00100|     let paginacion;
00101| 
00102|     // Contenedores de secciones
00103|     const seccionListado = document.getElementById('seccion-listado');
00104|     const seccionFormulario = document.getElementById('seccion-formulario');
00105| 
00106| 
00107| 
00108|     // Cargar selectores iniciales
00109|     const selectoresPromise = cargarSelectoresIniciales();
00110| 
00111|     // Inicializar componentes UI
00112|     initUI();
00113| 
00114|     // Agregar event listeners
00115|     setupEventListeners();
00116| 
00117|     // Cargar datos iniciales
00118|     if (document.getElementById('formFiltros')) {
00119|         cargarPersonal(1);
00120|     }
00121| 
00122|     // Verificar si hay parámetro de edición en la URL
00123|     const urlParams = new URLSearchParams(window.location.search);
00124|     const editarId = urlParams.get('editar');
00125|     if (editarId && !isNaN(editarId)) {
00126|         // Esperar a que se carguen los selectores antes de editar
00127|         selectoresPromise.then(() => {
00128|             setTimeout(() => {
00129|                 editarPersonal(editarId);
00130|             }, 300);
00131|         }).catch(error => {
00132|             console.error('Error cargando selectores para edición:', error);
00133|             // Intentar editar de todos modos
00134|             setTimeout(() => {
00135|                 editarPersonal(editarId);
00136|             }, 500);
00137|         });
00138|     }
00139| 
00140|     // ========================================
00141|     // FUNCIONES DE INICIALIZACIÓN
00142|     // ========================================
00143| 
00144|     /**
00145|      * Cargar todos los selectores iniciales desde el backend
00146|      */
00147|     function cargarSelectoresIniciales() {
00148|         return apiLaravel('/personal/selectores-iniciales', 'GET')
00149|             .then(response => {
00150|                 const data = response.data;
00151| 
00152|                 // Cargar provincias
00153|                 let provinciaOptions = '<option value="">- Seleccionar -</option>';
00154|                 data.provincias.forEach(prov => {
00155|                     provinciaOptions += `<option value="${prov.IdProvincia}" ${prov.IdProvincia == 14 ? 'selected' : ''}>${prov.Provincia}</option>`;
00156|                 });
00157|                 $('#provincia').html(provinciaOptions);
00158| 
00159|                 // Cargar estados civiles
00160|                 let estadoCivilOptions = '<option value="">- Seleccionar -</option>';
00161|                 data.estados_civiles.forEach(ec => {
00162|                     estadoCivilOptions += `<option value="${ec.idEstadoCivil}">${ec.EstadoCivil}</option>`;
00163|                 });
00164|                 $('#estado_civil').html(estadoCivilOptions);
00165| 
00166|                 // Cargar países/nacionalidades
00167|                 let nacionalidadOptions = '<option value="">- Seleccionar -</option>';
00168|                 data.paises.forEach(pais => {
00169| 
00170|                     nacionalidadOptions += `<option value="${pais.IdPais}" ${pais.IdPais == 80 ? 'selected' : ''}>${pais.Pais}</option>`;
00171|                 });
00172|                 $('#nacionalidad').html(nacionalidadOptions);
00173| 
00174|                 // Cargar instrucciones
00175|                 let instruccionOptions = '<option value="">- Seleccionar -</option>';
00176|                 data.instrucciones.forEach(inst => {
00177|                     instruccionOptions += `<option value="${inst.idInstruccion}">${inst.instruccion}</option>`;
00178|                 });
00179|                 $('#instruccion').html(instruccionOptions);
00180| 
00181|                 // Cargar tipos de relación (para historial)
00182|                 let relacionOptions = '<option value="">- Seleccionar -</option>';
00183|                 data.tipos_relacion.forEach(rel => {
00184|                     relacionOptions += `<option value="${rel.idRelacion}">${rel.Relacion}</option>`;
00185|                 });
00186|                 $('#relacion').html(relacionOptions);
00187| 
00188|                 // Cargar tipos de jornada
00189|                 let jornadaOptions = '<option value="">- Seleccionar -</option>';
00190|                 data.tipos_jornada.forEach(jor => {
00191|                     jornadaOptions += `<option value="${jor.IdTipoJornada}">${jor.Jornada}</option>`;
00192|                 });
00193|                 $('#tipo_jornada').html(jornadaOptions);
00194| 
00195|                 // Cargar motivos de baja
00196|                 let motivoBajaOptions = '<option value="">- Seleccionar -</option>';
00197|                 data.motivos_baja.forEach(mb => {
00198|                     motivoBajaOptions += `<option value="${mb.IdMotivoBaja}">${mb.MotivoBaja}</option>`;
00199|                 });
00200|                 $('#motivo_baja').html(motivoBajaOptions);
00201| 
00202|                 // Cargar instrucciones para "Tipo de Tarea"
00203|                 let tipoTareaOptions = '<option value="">- Seleccionar -</option>';
00204|                 data.instrucciones.forEach(inst => {
00205|                     tipoTareaOptions += `<option value="${inst.idInstruccion}">${inst.instruccion}</option>`;
00206|                 });
00207|                 $('#tipo_tarea').html(tipoTareaOptions);
00208| 
00209|                 // Cargar empleados con cargo para "Certifica"
00210|                 let certificaOptions = '<option value="">- Seleccionar -</option>';
00211|                 data.empleados_con_cargo.forEach(emp => {
00212|                     certificaOptions += `<option value="${emp.idEmpleado}">${emp.Apellido}, ${emp.Nombre} (${emp.Legajo})</option>`;
00213|                 });
00214|                 $('#certifica').html(certificaOptions);
00215| 
00216|                 // Cargar agrupamientos
00217|                 let agrupamientoOptions = '<option value="">- Seleccionar -</option>';
00218|                 data.agrupamientos.forEach(agr => {
00219|                     agrupamientoOptions += `<option value="${agr.idAgrupamiento}">${agr.agrupamiento}</option>`;
00220|                 });
00221|                 $('#agrupamiento').html(agrupamientoOptions);
00222| 
00223|                 // Cargar categorías
00224|                 let categoriaOptions = '<option value="">- Seleccionar -</option>';
00225|                 data.categorias.forEach(cat => {
00226|                     categoriaOptions += `<option value="${cat.idcategoria}">${cat.categoria}</option>`;
00227|                 });
00228|                 $('#categoria').html(categoriaOptions);
00229| 
00230|                 // Cargar cargos
00231|                 let cargoOptions = '<option value="">- Seleccionar -</option>';
00232|                 data.cargos.forEach(cargo => {
00233|                     cargoOptions += `<option value="${cargo.idCargo}">${cargo.cargo}</option>`;
00234|                 });
00235|                 $('#cargo').html(cargoOptions);
00236| 
00237|                 selectoresIniciales = data; // Guardar para uso posterior
00238|             })
00239|             .catch(error => {
00240|                 console.error('Error cargando selectores iniciales:', error);
00241|             });
00242|     }
00243| 
00244|     /**
00245|      * Inicializar componentes de la interfaz
00246|      */
00247|     function initUI() {
00248|         // Inicializar Select2
00249|         if (typeof $.fn.select2 !== 'undefined') {
00250|             $('.select2').select2({
00251|                 theme: 'bootstrap-5',
00252|                 width: '100%',
00253| 
00254|             });
00255|         }
00256| 
00257|         // Inicializar DateTimePickers con Tempus Dominus 6 (Bootstrap 5)
00258|         if (typeof tempusDominus !== 'undefined') {
00259|             // Configurar idioma
00260|             tempusDominus.DefaultOptions.localization.locale = 'es';
00261| 
00262|             // Inicializar los date pickers
00263|             ['#fecha_nacimiento_picker', '#fecha_alta_picker', '#fecha_adm_publica_picker', '#fecha_baja_picker', '#fecha_movimiento_picker'].forEach(selector => {
00264|                 const element = document.querySelector(selector);
00265|                 if (element) {
00266|                     // Check for invalid values in input before initializing
00267|                     const input = element.querySelector('input');
00268|                     if (input && input.value) {
00269|                        // Simple validation: must contain '/'
00270|                        if (!input.value.includes('/')) {
00271|                            console.warn(`Saneamiento: Limpiando valor inválido "${input.value}" en ${selector}`);
00272|                            input.value = '';
00273|                        }
00274|                     }
00275| 
00276|                     try {
00277|                         new tempusDominus.TempusDominus(element, {
00278|                             display: {
00279|                                 components: {
00280|                                     calendar: true,
00281|                                     date: true,
00282|                                     month: true,
00283|                                     year: true,
00284|                                     decades: true,
00285|                                     clock: false,
00286|                                     hours: false,
00287|                                     minutes: false,
00288|                                     seconds: false
00289|                                 }
00290|                             },
00291|                             localization: {
00292|                                 locale: 'es',
00293|                                 format: 'dd/MM/yyyy'
00294|                             }
00295|                         });
00296|                     } catch (err) {
00297|                         console.error(`Error initializing DatePicker for ${selector}:`, err);
00298|                     }
00299|                 }
00300|             });
00301|         } else if (typeof $.fn.datetimepicker !== 'undefined') {
00302|             // Fallback para Tempus Dominus Bootstrap 4
00303|             if (typeof moment !== 'undefined') {
00304|                 moment.locale('es');
00305|             }
00306| 
00307|             $('#fecha_nacimiento_picker, #fecha_alta_picker, #fecha_adm_publica_picker, #fecha_baja_picker').datetimepicker({
00308|                 format: 'DD/MM/YYYY',
00309|                 locale: 'es',
00310|                 useCurrent: false,
00311|                 icons: {
00312|                     time: 'fas fa-clock',
00313|                     date: 'fas fa-calendar',
00314|                     up: 'fas fa-chevron-up',
00315|                     down: 'fas fa-chevron-down',
00316|                     previous: 'fas fa-chevron-left',
00317|                     next: 'fas fa-chevron-right',
00318|                     today: 'fas fa-calendar-check',
00319|                     clear: 'fas fa-trash',
00320|                     close: 'fas fa-times'
00321|                 }
00322|             });
00323|         } else if (typeof flatpickr !== 'undefined') {
00324|             // Fallback usando Flatpickr si está disponible
00325|             flatpickr('#fecha_nacimiento, #fecha_alta, #fecha_adm_publica, #fecha_baja', {
00326|                 locale: 'es',
00327|                 dateFormat: "d/m/Y",
00328|                 allowInput: true
00329|             });
00330|         } else {
00331|             // Fallback: usar inputs nativos HTML5
00332|             console.log('DatePicker: usando inputs nativos HTML5');
00333|             $('#fecha_nacimiento, #fecha_alta, #fecha_adm_publica, #fecha_baja, #fecha_movimiento').attr('type', 'date');
00334|         }
00335| 
00336| 
00337|         // Inicializar validación de formulario
00338|         if (typeof $.fn.validate !== 'undefined') {
00339|             $('#formPersonal').validate({
00340|                 ignore: [],
00341|                 rules: {
00342|                     legajo: {
00343|                         required: true,
00344|                         number: true,
00345|                         remote: {
00346|                             url: '/personal/check-legajo',
00347|                             type: 'POST',
00348|                             headers: {
00349|                                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
00350|                             },
00351|                             data: {
00352|                                 legajo: function () {
00353|                                     return $('#legajo').val();
00354|                                 },
00355|                                 exclude_id: function () {
00356|                                     return $('#idEmpleado').val() || null;
00357|                                 }
00358|                             },
00359|                             dataFilter: function (data) {
00360|                                 var json = JSON.parse(data);
00361|                                 return json.exists ? '"El legajo ya existe"' : 'true';
00362|                             }
00363|                         }
00364|                     },
00365|                     apellido: {
00366|                         required: true,
00367|                         maxlength: 50
00368|                     },
00369|                     nombre: {
00370|                         required: true,
00371|                         maxlength: 50
00372|                     },
00373|                     dni: {
00374|                         required: true,
00375|                         number: true,
00376|                         minlength: 7,
00377|                         maxlength: 8,
00378|                         remote: {
00379|                             url: '/personal/check-dni',
00380|                             type: 'POST',
00381|                             headers: {
00382|                                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
00383|                             },
00384|                             data: {
00385|                                 dni: function () {
00386|                                     return $('#dni').val();
00387|                                 },
00388|                                 exclude_id: function () {
00389|                                     return $('#idEmpleado').val() || null;
00390|                                 }
00391|                             },
00392|                             dataFilter: function (data) {
00393|                                 var json = JSON.parse(data);
00394|                                 return json.exists ? '"El DNI ya existe"' : 'true';
00395|                             }
00396|                         }
00397|                     },
00398|                     sexo: 'required',
00399|                     email: {
00400|                         email: true
00401|                     },
00402|                     // Datos laborales obligatorios
00403|                     tipo_tarea: 'required',
00404|                     relacion: 'required',
00405|                     profesion: 'required',
00406|                     categoria: 'required',
00407|                     agrupamiento: 'required',
00408|                     cargo: 'required',
00409|                     certifica: 'required',
00410|                     // Jerarquía organizacional obligatoria
00411|                     gerencia: 'required',
00412|                     departamento: 'required',
00413|                     'servicios[]': 'required',
00414|                     // Fechas obligatorias
00415|                     fecha_alta: 'required',
00416|                     fecha_nacimiento: 'required',
00417|                     fecha_adm_publica: 'required',
00418|                     // Campos de baja (solo requeridos si están visibles)
00419|                     fecha_baja: {
00420|                         required: function() {
00421|                             return $('#fecha_baja_group').is(':visible');
00422|                         }
00423|                     },
00424|                     motivo_baja: {
00425|                         required: function() {
00426|                             return $('#motivo_baja_group').is(':visible');
00427|                         }
00428|                     },
00429|                     // Jornada
00430|                     tipo_jornada: 'required',
00431|                     f_jornada: 'required'
00432|                 },
00433|                 messages: {
00434|                     legajo: {
00435|                         required: 'El legajo es requerido',
00436|                         number: 'Debe ser un número válido'
00437|                     },
00438|                     apellido: {
00439|                         required: 'El apellido es requerido',
00440|                         maxlength: 'Máximo 50 caracteres'
00441|                     },
00442|                     nombre: {
00443|                         required: 'El nombre es requerido',
00444|                         maxlength: 'Máximo 50 caracteres'
00445|                     },
00446|                     dni: {
00447|                         required: 'El DNI es requerido',
00448|                         number: 'Debe ser un número válido',
00449|                         minlength: 'Mínimo 7 dígitos',
00450|                         maxlength: 'Máximo 8 dígitos'
00451|                     },
00452|                     sexo: 'Seleccione el sexo',
00453|                     email: 'Ingrese un email válido',
00454|                     // Mensajes para datos laborales
00455|                     tipo_tarea: 'Seleccione el tipo de tarea',
00456|                     relacion: 'Seleccione la relación laboral',
00457|                     profesion: 'Seleccione la profesión',
00458|                     categoria: 'Seleccione la categoría',
00459|                     agrupamiento: 'Seleccione el agrupamiento',
00460|                     cargo: 'Seleccione el cargo',
00461|                     certifica: 'Seleccione quién certifica',
00462|                     // Mensajes para jerarquía organizacional
00463|                     gerencia: 'Seleccione la gerencia',
00464|                     departamento: 'Seleccione el departamento',
00465|                     'servicios[]': 'Seleccione al menos un servicio',
00466|                     // Mensajes para fechas
00467|                     fecha_alta: 'Ingrese la fecha de alta',
00468|                     fecha_nacimiento: 'Ingrese la fecha de nacimiento',
00469|                     fecha_adm_publica: 'Ingrese la fecha de admisión pública',
00470|                     // Campos de baja
00471|                     fecha_baja: 'Ingrese la fecha de baja',
00472|                     motivo_baja: 'Seleccione el motivo de baja',
00473|                     // Jornada
00474|                     tipo_jornada: 'Seleccione el tipo de jornada',
00475|                     f_jornada: 'Ingrese la fecha de jornada'
00476|                 },
00477|                 errorElement: 'span',
00478|                 errorClass: 'invalid-feedback',
00479|                 errorPlacement: function(error, element) {
00480|                     // Para Select2
00481|                     if (element.hasClass('select2-hidden-accessible')) {
00482|                         error.insertAfter(element.next('.select2-container'));
00483|                     } else {
00484|                         error.insertAfter(element);
00485|                     }
00486|                 },
00487|                 highlight: function(element, errorClass, validClass) {
00488|                     $(element).addClass('is-invalid').removeClass('is-valid');
00489|                     // Marcar también el contenedor Select2
00490|                     if ($(element).hasClass('select2-hidden-accessible')) {
00491|                         $(element).next('.select2-container').find('.select2-selection').addClass('is-invalid');
00492|                     }
00493|                 },
00494|                 unhighlight: function(element, errorClass, validClass) {
00495|                     $(element).removeClass('is-invalid').addClass('is-valid');
00496|                     // Limpiar también el contenedor Select2
00497|                     if ($(element).hasClass('select2-hidden-accessible')) {
00498|                         $(element).next('.select2-container').find('.select2-selection').removeClass('is-invalid');
00499|                     }
00500|                 },
00501|                 invalidHandler: function(event, validator) {
00502|                     // Mostrar mensaje de error general
00503|                     var errors = validator.numberOfInvalids();
00504|                     if (errors) {
00505|                         var message = errors === 1
00506|                             ? 'Hay 1 campo con error. Por favor revíselo.'
00507|                             : 'Hay ' + errors + ' campos con errores. Por favor revíselos.';
00508| 
00509|                         mostrarMensaje('error', message);
00510| 
00511|                         // Scroll al primer campo con error
00512|                         var firstError = $(validator.errorList[0].element);
00513|                         $('html, body').animate({
00514|                             scrollTop: firstError.offset().top - 100
00515|                         }, 500);
00516| 
00517|                         // Si el campo está en un tab oculto, activar ese tab
00518|                         var tabPane = firstError.closest('.tab-pane');
00519|                         if (tabPane.length && !tabPane.hasClass('show')) {
00520|                             var tabId = tabPane.attr('id');
00521|                             $('button[data-bs-target="#' + tabId + '"]').tab('show');
00522|                         }
00523|                     }
00524|                 },
00525|                 submitHandler: function (form) {
00526|                     guardarPersonal();
00527|                     return false;
00528|                 }
00529|             });
00530|         }
00531| 
00532|         // Inicializar typeahead para jefes
00533|         if (typeof $.fn.typeahead !== 'undefined' && typeof listJefes !== 'undefined') {
00534|             $('#jefe').typeahead({
00535|                 hint: true,
00536|                 highlight: true,
00537|                 minLength: 1
00538|             }, {
00539|                 name: 'jefes',
00540|                 source: listJefes,
00541|                 display: function (item) {
00542|                     return item.apellido + ', ' + item.nombre;
00543|                 },
00544|                 templates: {
00545|                     suggestion: function (item) {
00546|                         return '<div><strong>' + item.apellido + ', ' + item.nombre + '</strong> - Legajo: ' + item.legajo + '</div>';
00547|                     }
00548|                 }
00549|             }).bind('typeahead:select', function (ev, suggestion) {
00550|                 $('#jefe_id').val(suggestion.id);
00551|             });
00552|         }
00553|         Toast = Swal.mixin({
00554|             toast: true,
00555|             position: "top-end",
00556|             showConfirmButton: false,
00557|             timer: 10000,
00558|         });
00559|     }
00560| 
00561|     /**
00562|      * Configurar event listeners
00563|      */
00564|     function setupEventListeners() {
00565|         // Event listener para el formulario de filtros
00566|         const formFiltros = document.getElementById('formFiltros');
00567|         if (formFiltros) {
00568|             formFiltros.addEventListener('submit', function (e) {
00569|                 e.preventDefault();
00570|                 cargarPersonal(1);
00571|             });
00572|         }
00573| 
00574|         // Event listener para el botón de agregar
00575|         const btnAgregar = document.getElementById('btnAgregar');
00576|         if (btnAgregar) {
00577|             btnAgregar.addEventListener('click', function () {
00578|                 modoFormulario = 'crear';
00579|                 limpiarFormulario();
00580|                 mostrarFormulario();
00581|             });
00582|         }
00583| 
00584|         // Event listener para los botones de acción en la tabla
00585|         const tablaPersonal = document.getElementById('tabla-personal');
00586|         if (tablaPersonal) {
00587|             tablaPersonal.addEventListener('click', function (e) {
00588|                 const btn = e.target.closest('button');
00589|                 if (!btn) return;
00590| 
00591|                 const id = btn.dataset.id;
00592|                 const action = btn.dataset.action;
00593| 
00594|                 console.log('=== CLICK EN TABLA PERSONAL ===');
00595|                 console.log('Botón clickeado:', btn);
00596|                 console.log('ID extraído:', id);
00597|                 console.log('Acción:', action);
00598|                 console.log('Tipo de ID:', typeof id);
00599| 
00600|                 switch (action) {
00601|                     case 'ver':
00602|                         console.log('Llamando verPersonal con ID:', id);
00603|                         verPersonal(id);
00604|                         break;
00605|                     case 'editar':
00606|                         editarPersonal(id);
00607|                         break;
00608|                     case 'eliminar':
00609|                         eliminarPersonal(id);
00610|                         break;
00611|                 }
00612|             });
00613|         }
00614| 
00615|         // Event listener para el botón guardar
00616|         const btnGuardar = document.getElementById('btnGuardar');
00617|         if (btnGuardar) {
00618|             btnGuardar.addEventListener('click', function () {
00619|                 $('#formPersonal').submit();
00620|             });
00621|         }
00622| 
00623|         // Event listener para el botón de limpiar
00624|         const btnLimpiar = document.getElementById('btnLimpiar');
00625|         if (btnLimpiar) {
00626|             btnLimpiar.addEventListener('click', function () {
00627|                 limpiarFormulario();
00628|             });
00629|         }
00630| 
00631|         // Event listener para el botón de volver
00632|         const btnVolver = document.getElementById('btnVolver');
00633|         if (btnVolver) {
00634|             btnVolver.addEventListener('click', function () {
00635|                 mostrarListado();
00636|             });
00637|         }
00638| 
00639|         // Event listeners para validación de DNI y legajo
00640|         const dniInput = document.getElementById('dni');
00641|         if (dniInput) {
00642|             dniInput.addEventListener('blur', function () {
00643|                 validarDniExistente(this.value);
00644|             });
00645|         }
00646| 
00647|         const legajoInput = document.getElementById('legajo');
00648|         if (legajoInput) {
00649|             legajoInput.addEventListener('blur', function () {
00650|                 validarLegajoExistente(this.value);
00651|             });
00652|         }
00653| 
00654|         // Event listener para el botón de eliminar en el formulario
00655|         const btnEliminar = document.getElementById('btnEliminar');
00656|         if (btnEliminar) {
00657|             btnEliminar.addEventListener('click', function () {
00658|                 if (empleadoEditando) {
00659|                     eliminarPersonal(empleadoEditando.idEmpleado);
00660|                 }
00661|             });
00662|         }
00663| 
00664| 
00665| 
00666|         // Event listener para el botón de limpiar filtros
00667|         const btnLimpiarFiltros = document.getElementById('btnLimpiarFiltros');
00668|         if (btnLimpiarFiltros) {
00669|             btnLimpiarFiltros.addEventListener('click', function () {
00670|                 document.getElementById('formFiltros').reset();
00671|                 // Reponer filtro de estado a '1' (activo) por defecto
00672|                 const filtroEstado = document.getElementById('filtro_estado');
00673|                 if (filtroEstado) {
00674|                     filtroEstado.value = '1';
00675|                 }
00676|                 cargarPersonal(1);
00677|             });
00678|         }
00679| 
00680|         // Event listener para el botón de mostrar/ocultar filtros
00681|         const btnToggleFiltros = document.getElementById('btnToggleFiltros');
00682|         if (btnToggleFiltros) {
00683|             btnToggleFiltros.addEventListener('click', function () {
00684|                 const contenedorFiltros = document.getElementById('contenedor-filtros');
00685|                 const icon = this.querySelector('i');
00686| 
00687|                 if (contenedorFiltros.classList.contains('d-none')) {
00688|                     // Mostrar filtros
00689|                     contenedorFiltros.classList.remove('d-none');
00690|                     this.innerHTML = '<i class="bi bi-chevron-up me-1"></i> Ocultar filtros';
00691|                 } else {
00692|                     // Ocultar filtros
00693|                     contenedorFiltros.classList.add('d-none');
00694|                     this.innerHTML = '<i class="bi bi-chevron-down me-1"></i> Mostrar filtros';
00695|                 }
00696|             });
00697|         }
00698| 
00699|         // Event listener para el botón de capturar foto
00700|         const btnCapturar = document.getElementById('btn_capturar');
00701|         if (btnCapturar) {
00702|             btnCapturar.addEventListener('click', function () {
00703|                 takeSnapshot();
00704|             });
00705|         }
00706| 
00707|         // Event listener para cambio de fecha de nacimiento (calcular edad)
00708|         const fechaNacimiento = document.getElementById('fecha_nacimiento');
00709|         if (fechaNacimiento) {
00710|             fechaNacimiento.addEventListener('change', function () {
00711|                 calcularEdad();
00712|             });
00713|         }
00714| 
00715|         // Event listener para click en la imagen de perfil (cargar nueva imagen)
00716|         const imgCrop = document.getElementById('img_crop');
00717|         if (imgCrop) {
00718|             imgCrop.addEventListener('click', function () {
00719|                 // Solo permitir cambiar imagen si no estamos en modo solo lectura
00720|                 if (modoFormulario !== 'ver') {
00721|                     document.getElementById('foto_file').click();
00722|                 }
00723|             });
00724|             // Agregar cursor pointer para indicar que es clickeable
00725|             imgCrop.style.cursor = 'pointer';
00726|         }
00727|         const imgCargada = document.getElementById('img_foto');
00728|         if (imgCargada) {
00729|             imgCargada.addEventListener('click', function () {
00730|                 // Solo permitir cambiar imagen si no estamos en modo solo lectura
00731|                 if (modoFormulario !== 'ver') {
00732|                     document.getElementById('foto_file').click();
00733|                 }
00734|             });
00735|             // Agregar cursor pointer para indicar que es clickeable
00736|             imgCargada.style.cursor = 'pointer';
00737|         }
00738| 
00739| 
00740|     }
00741| 
00742|     // ========================================
00743|     // FUNCIONES DE PAGINACIÓN Y CARGA DE DATOS
00744|     // ========================================
00745| 
00746|     /**
00747|      * Inicializar o actualizar la paginación
00748|      */
00749|     function initPaginacion(totalRegistros, porPagina, paginaActual) {
00750|         totalRegistros = totalRegistros || 0;
00751|         porPagina = porPagina || 10;
00752|         paginaActual = paginaActual || 1;
00753| 
00754|         if (paginacion) {
00755|             paginacion.setPaginationData(totalRegistros, porPagina, paginaActual);
00756|         } else if (typeof CustomPagination !== 'undefined') {
00757|             paginacion = new CustomPagination(paginacionContenedor, {
00758|                 totalRecords: totalRegistros,
00759|                 recordsPerPage: porPagina,
00760|                 currentPage: paginaActual,
00761|                 onPageChange: cargarPersonal
00762|             });
00763| 
00764|         }
00765|     }
00766| 
00767|     /**
00768|      * Cargar datos de personal con AJAX
00769|      */
00770|     function cargarPersonal(pagina = 1) {
00771|         console.log('Cargando personal, página:', pagina);
00772|         // Obtener datos del formulario de filtros
00773|         const formData = new FormData(document.getElementById('formFiltros'));
00774|         const params = new URLSearchParams(formData);
00775|         params.append('page', pagina);
00776| 
00777|         apiLaravel('/personal/filtrar?' + params.toString(), 'GET')
00778|             .then(response => {
00779|                 // Estructura esperada del backend: { data: [...], total, pagina, porPagina, totalPaginas }
00780|                 const resp = response;
00781|                 const datos = resp.data || [];
00782| console.log(datos)
00783|                 actualizarTabla(datos);
00784| 
00785|                 // Inicializar/actualizar paginación con los valores que devuelve el servidor.
00786|                 const total = parseInt(resp.total || 0, 10);
00787|                 const paginaResp = parseInt(resp.pagina || pagina, 10);
00788|                 const porPagina = parseInt(resp.porPagina || 10, 10);
00789| 
00790|                 initPaginacion(total, porPagina, paginaResp);
00791|             })
00792|             .catch(error => {
00793|                 console.error('Error cargando personal:', error);
00794|                 mostrarMensaje('error', 'Error al cargar los datos del personal');
00795|             });
00796|     }
00797| 
00798|     /**
00799|      * Actualizar la tabla de personal
00800|      */
00801|     function actualizarTabla(datos) {
00802|         const tbody = document.getElementById('tabla-personal');
00803|         console.log(tbody)
00804|         console.log(datos)
00805|         if (!tbody) return;
00806| 
00807|         if (datos.length === 0) {
00808|             tbody.innerHTML = '<tr><td colspan="8" class="text-center">No se encontraron registros</td></tr>';
00809|             return;
00810|         }
00811| 
00812|         let html = '';
00813|         datos.forEach(empleado => {
00814|             // Construir ruta jerárquica incluyendo sólo los segmentos existentes (no concatenar '-' literales)
00815|             let rutaJerarquica = [];
00816|             if (empleado.gerencia && empleado.gerencia.toString().trim() !== '' && empleado.gerencia !== '-') {
00817|                 rutaJerarquica.push(empleado.gerencia);
00818|             }
00819|             const deptValor = (empleado.Dto && empleado.Dto.toString().trim() !== '') ? empleado.Dto : ((empleado.departamento && empleado.departamento.toString().trim() !== '') ? empleado.departamento : null);
00820|             if (deptValor && deptValor !== '-') {
00821|                 rutaJerarquica.push(deptValor);
00822|             }
00823|             if (empleado.servicio && empleado.servicio.toString().trim() !== '' && empleado.servicio !== '-') {
00824|                 rutaJerarquica.push(empleado.servicio);
00825|             }
00826|             if (empleado.sector && empleado.sector.toString().trim() !== '' && empleado.sector !== '-') {
00827|                 rutaJerarquica.push(empleado.sector);
00828|             }
00829| 
00830|             const jerarquiaTexto = rutaJerarquica.length > 0 ? rutaJerarquica.join(' / ') : '-';
00831| 
00832|             // Debug: Verificar el ID del empleado antes de generar botones
00833|             console.log(`Generando botones para empleado:`, {
00834|                 idEmpleado: empleado.idEmpleado,
00835|                 nombre: empleado.nombre_completo,
00836|                 legajo: empleado.legajo,
00837|                 dni: empleado.dni
00838|             });
00839| 
00840|             html += `
00841|                 <tr>
00842|                     <td>${empleado.legajo || ''}</td>
00843|                     <td>${empleado.nombre_completo || ''}</td>
00844|                     <td>${empleado.dni || ''}</td>
00845|                     <td><small class="text-secondary">${jerarquiaTexto}</small></td>
00846|                     <td>
00847|                         <div class="btn-group btn-group-sm" role="group">
00848|                             ${permisos.leer ? `<button type="button" class="btn btn-info btn-sm" data-id="${empleado.idEmpleado}" data-action="ver" title="Ver" onclick="console.log('Click Ver ID:', ${empleado.idEmpleado})"><i class="bi bi-eye"></i></button>` : ''}
00849|                             ${permisos.editar ? `<button type="button" class="btn btn-warning btn-sm" data-id="${empleado.idEmpleado}" data-action="editar" title="Editar"><i class="bi bi-pencil"></i></button>` : ''}
00850|                             ${permisos.eliminar ? `<button type="button" class="btn btn-danger btn-sm" data-id="${empleado.idEmpleado}" data-action="eliminar" title="Eliminar"><i class="bi bi-trash"></i></button>` : ''}
00851|                         </div>
00852|                     </td>
00853|                 </tr>
00854|             `;
00855|         });
00856| 
00857|         tbody.innerHTML = html;
00858|     }
00859| 
00860|     // ========================================
00861|     // FUNCIONES DE MOSTRAR/OCULTAR SECCIONES
00862|     // ========================================
00863| 
00864|     /**
00865|      * Mostrar el formulario y ocultar el listado
00866|      */
00867|     function mostrarFormulario() {
00868|         seccionListado.classList.add('d-none');
00869|         seccionFormulario.classList.remove('d-none');
00870|         configurarFormulario();
00871|     }
00872| 
00873|     /**
00874|      * Mostrar el listado y ocultar el formulario
00875|      */
00876|     function mostrarListado() {
00877|         seccionFormulario.classList.add('d-none');
00878|         seccionListado.classList.remove('d-none');
00879|         cargarPersonal(1);
00880|     }
00881| 
00882|     // ========================================
00883|     // FUNCIONES DEL FORMULARIO DE PERSONAL
00884|     // ========================================
00885| 
00886|     /**
00887|      * Configurar el formulario según el modo
00888|      */
00889|     function configurarFormulario() {
00890|         const titulo = document.getElementById('titulo-formulario');
00891|         const btnGuardar = document.getElementById('btnGuardar');
00892|         const btnEliminar = document.getElementById('btnEliminar');
00893|         const btnLimpiar = document.getElementById('btnLimpiar');
00894| 
00895|         // Configurar título y botones según el modo
00896|         switch (modoFormulario) {
00897|             case 'crear':
00898|                 titulo.textContent = 'Agregar Personal';
00899|                 if (btnGuardar) btnGuardar.style.display = permisos.crear ? 'inline-block' : 'none';
00900|                 if (btnEliminar) btnEliminar.style.display = 'none';
00901|                 if (btnLimpiar) btnLimpiar.style.display = 'inline-block';
00902|                 habilitarFormulario(true);
00903|                 // Asegurar que el campo legajo esté habilitado en creación
00904|                 if (document.getElementById('legajo')) document.getElementById('legajo').disabled = false;
00905|                 if (document.getElementById('btnActLegajo')) {
00906|                     document.getElementById('btnActLegajo').classList.remove('btn-outline-success');
00907|                     document.getElementById('btnActLegajo').classList.add('btn-outline-secondary');
00908|                     document.getElementById('btnActLegajo').innerHTML = '<i class="fas fa-lock"></i>';
00909|                 }
00910|                 break;
00911|             case 'editar':
00912|                 titulo.textContent = 'Editar Personal';
00913|                 if (btnGuardar) btnGuardar.style.display = permisos.editar ? 'inline-block' : 'none';
00914|                 if (btnEliminar) btnEliminar.style.display = permisos.eliminar ? 'inline-block' : 'none';
00915|                 if (btnLimpiar) btnLimpiar.style.display = 'none';
00916|                 habilitarFormulario(true);
00917|                 // En edición, bloquear el campo legajo por defecto
00918|                 if (document.getElementById('legajo')) document.getElementById('legajo').disabled = true;
00919|                 if (document.getElementById('btnActLegajo')) {
00920|                     document.getElementById('btnActLegajo').classList.remove('btn-outline-success');
00921|                     document.getElementById('btnActLegajo').classList.add('btn-outline-secondary');
00922|                     document.getElementById('btnActLegajo').innerHTML = '<i class="fas fa-lock"></i>';
00923|                 }
00924|                 break;
00925|             case 'ver':
00926|                 titulo.textContent = 'Ver Personal';
00927|                 if (btnGuardar) btnGuardar.style.display = 'none';
00928|                 if (btnEliminar) btnEliminar.style.display = 'none';
00929|                 if (btnLimpiar) btnLimpiar.style.display = 'none';
00930|                 habilitarFormulario(false);
00931|                 // En visualización, bloquear legajo
00932|                 if (document.getElementById('legajo')) document.getElementById('legajo').disabled = true;
00933|                 if (document.getElementById('btnActLegajo')) {
00934|                     document.getElementById('btnActLegajo').classList.remove('btn-outline-success');
00935|                     document.getElementById('btnActLegajo').classList.add('btn-outline-secondary');
00936|                     document.getElementById('btnActLegajo').innerHTML = '<i class="fas fa-lock"></i>';
00937|                 }
00938|                 break;
00939|         }
00940|     }
00941| 
00942|     /**
00943|      * Habilitar o deshabilitar campos del formulario
00944|      */
00945|     function habilitarFormulario(habilitar) {
00946|         const form = document.getElementById('formPersonal');
00947|         if (!form) return;
00948| 
00949|         const inputs = form.querySelectorAll('input, select, textarea');
00950|         inputs.forEach(input => {
00951|             if (habilitar) {
00952|                 input.removeAttribute('disabled');
00953|                 input.removeAttribute('readonly');
00954|             } else {
00955|                 input.setAttribute('readonly', 'true');
00956|                 if (input.tagName === 'SELECT') {
00957|                     input.setAttribute('disabled', 'true');
00958|                 }
00959|             }
00960|         });
00961| 
00962|         // Deshabilitar botones especiales en modo solo lectura
00963|         if (!habilitar) {
00964|             const botonesEspeciales = form.querySelectorAll('button[onclick], .btn');
00965|             botonesEspeciales.forEach(btn => {
00966|                 if (!btn.id || btn.id !== 'btnVolver') {
00967|                     btn.style.display = 'none';
00968|                 }
00969|             });
00970|         }
00971|     }
00972| 
00973|     /**
00974|      * Limpiar el formulario
00975|      */
00976|     function limpiarFormulario() {
00977|         const form = document.getElementById('formPersonal');
00978|         if (form) {
00979|             form.reset();
00980|             $('#idEmpleado').val('');
00981|             $('#cuil').val('');
00982| 
00983| 
00984|             // Limpiar Select2
00985|             $('.select2').val(null).trigger('change');
00986| 
00987|             // Limpiar contenedores dinámicos
00988|             $('#container_relaciones').empty();
00989|             $('#container_doc').empty();
00990| 
00991|             // Resetear contadores
00992|             num_relaciones = 0;
00993|             num_doc = 0;
00994|             arrImgsDel = [];
00995|             del_jor = [];
00996|             JornadaOriginal_Id = 0;
00997|             FechaJornadaOri = "";
00998|             // Resetear imagen
00999|             resetearImagen();
01000| 
01001|             // Resetear servicios
01002|             serviciosAsignados = [];
01003|             serviciosParaDarBaja = [];
01004|             contadorServicios = 0;
01005| 
01006|             // Ocultar campos condicionales
01007|             $('#fecha_baja_group').hide();
01008|             $('#motivo_baja_group').hide();
01009|             $('#des_baja_group').hide();
01010| 
01011| 
01012|             // Resetear checkboxes a valores por defecto
01013|             $('#f_doble').prop('checked', true); // Checked por defecto
01014|             $('#fe').prop('checked', false);
01015| 
01016| 
01017|             $('#nacionalidad').val(80).trigger("change");
01018|             $('#provincia').val(14).trigger("change");
01019|             $('#cp').val(3300);
01020| 
01021| 
01022|             $('#estado').val(1);
01023|             empleadoEditando = null;
01024|             eliminarFotoFlag = false
01025|         }
01026|     }
01027| 
01028|     /**
01029|      * Ver personal (solo lectura) - Redirige a la nueva vista elegante
01030|      */
01031|     function verPersonal(id) {
01032|         console.log('=== FUNCIÓN verPersonal ===');
01033|         console.log('ID recibido:', id);
01034|         console.log('Tipo de ID:', typeof id);
01035|         console.log('ID como número:', Number(id));
01036|         console.log('ID es válido:', !isNaN(id) && id != null && id !== '');
01037| 
01038|         if (!id || isNaN(id)) {
01039|             console.error('❌ ID inválido para verPersonal:', id);
01040|             alert('Error: ID de empleado inválido: ' + id);
01041|             return;
01042|         }
01043| 
01044|         const url = `${window.location.origin}/personal/${id}/ver`;
01045|         console.log('URL de redirección:', url);
01046|         console.log('Redirigiendo...');
01047| 
01048|         window.location.href = url;
01049|     }
01050| 
01051|     /**
01052|      * Editar personal
01053|      */
01054|     function editarPersonal(id) {
01055|         limpiarFormulario();
01056|         modoFormulario = 'editar';
01057|         mostrarFormulario();
01058|         cargarDatosPersonal(id, false);
01059|     }
01060| 
01061|     /**
01062|      * Cargar datos de personal para ver o editar
01063|      */
01064|     function cargarDatosPersonal(id, soloLectura) {
01065|         // Resetear la bandera de eliminar foto al cargar datos
01066|         eliminarFotoFlag = false;
01067| 
01068|         apiLaravel(`/personal/${id}`, 'GET')
01069|             .then(response => {
01070|                 console.log('=== RESPONSE FROM /personal/${id} ===', response);
01071|                 // La respuesta del controlador tiene formato: { success: true, data: {...} }
01072|                 if (!response.success) {
01073|                     mostrarMensaje('error', response.message || 'Error al cargar los datos del personal');
01074|                     return;
01075|                 }
01076| 
01077|                 const empleado = response.data;
01078|                 console.log('=== EMPLEADO DATA ===', empleado);
01079|                 empleadoEditando = empleado;
01080| 
01081|                 // Llenar campos básicos
01082|                 $('#idEmpleado').val(empleado.idEmpleado);
01083|                 $('#legajo').val(empleado.legajo);
01084|                 // Store original legajo value as a data attribute for comparing on update
01085|                 $('#legajo').attr('data-original-legajo', empleado.legajo);
01086|                 $('#apellido').val(empleado.apellido);
01087|                 $('#nombre').val(empleado.nombre);
01088|                 $('#dni').val(empleado.dni);
01089|                 $('#sexo').val(empleado.sexo);
01090|                 $('#cuit').val(empleado.cuit);
01091|                 $('#fecha_nacimiento').val(empleado.fecha_nacimiento); // Ya viene formateado
01092| 
01093|                 // Calcular edad a partir de la fecha de nacimiento
01094|                 calcularEdad();
01095| 
01096|                 $('#telefono').val(empleado.telefono);
01097|                 $('#celular').val(empleado.celular);
01098|                 $('#email').val(empleado.email);
01099| 
01100|                 // Campos de domicilio
01101|                 $('#calle').val(empleado.calle);
01102|                 $('#num_calle').val(empleado.calle_num);
01103|                 $('#piso').val(empleado.piso);
01104|                 $('#dto').val(empleado.departamento_dir);
01105|                 $('#cp').val(empleado.cp);
01106|                 $('#barrio').val(empleado.barrio);
01107| 
01108|                 // Campos adicionales de domicilio si existen
01109|                 if ($('#manzana').length) $('#manzana').val(empleado.manzana);
01110|                 if ($('#casa').length) $('#casa').val(empleado.casa);                // Campos laborales
01111|                 $('#fecha_alta').val(empleado.fecha_alta);
01112|                 $('#fecha_baja').val(empleado.fecha_baja);
01113|                 if ($('#fecha_adm_publica').length) $('#fecha_adm_publica').val(empleado.fecha_adm_publica);
01114|                 if ($('#observacion').length) $('#observacion').val(empleado.observacion);
01115|                 if ($('#des_baja').length) $('#des_baja').val(empleado.descripcion_baja);
01116|                 if ($('#matricula').length) $('#matricula').val(empleado.matricula);
01117|                 if ($('#num_matricula').length) $('#num_matricula').val(empleado.matricula);
01118|                 if ($('#nro_contrato').length) $('#nro_contrato').val(empleado.nro_contrato);
01119|                 if ($('#categoria').length) $('#categoria').val(empleado.categoria_id ?? '').trigger("change");
01120|                 if ($('#agrupamiento').length) $('#agrupamiento').val(empleado.agrupamiento_id ?? '').trigger("change");
01121| 
01122|                 // Cargo - si cargo_id es 0, mostrar "- Seleccionar -" pero enviar 0 al guardar
01123|                 // Para lograr esto, necesitamos agregar una opción con value="0" si no existe
01124|                 const cargoId = empleado.cargo_id ?? 0;
01125|                 if ($('#cargo').length) {
01126|                     // Si cargo_id es 0, asegurarse de que existe una opción con value="0"
01127|                     if (cargoId === 0 && $('#cargo option[value="0"]').length === 0) {
01128|                         // Agregar opción "Sin cargo" con value="0"
01129|                         $('#cargo').append('<option value="0">Sin cargo</option>');
01130|                     }
01131|                     $('#cargo').val(cargoId).trigger("change");
01132|                 }
01133| 
01134|                 // Certifica (cargar el valor del certificador)
01135|                 if ($('#certifica').length) {
01136|                     $('#certifica').val(empleado.certifica_id ?? '').trigger("change");
01137|                 }
01138| 
01139| 
01140|                 // Checkboxes
01141|                 if ($('#f_doble').length) $('#f_doble').prop('checked', empleado.doble_fs == 1);
01142| 
01143|                 if ($('#fe').length) $('#fe').prop('checked', empleado.fe == 1);
01144| 
01145| 
01146|                 // Selects
01147|                 $('#nacionalidad').val(empleado.nacionalidad_id).trigger('change');
01148|                 $('#estado_civil').val(empleado.estado_civil_id).trigger('change');
01149|                 $('#provincia').val(empleado.provincia_id).trigger('change');
01150|                 $('#profesion').val(empleado.profesion_id).trigger('change');
01151|                 $('#funcion').val(empleado.funcion_id).trigger('change');
01152|                 $('#instruccion').val(empleado.instruccion_id).trigger('change');
01153|                 $('#gerencia').val(empleado.gerencia_id).trigger('change');
01154|                 $('#estado').val(empleado.estado).trigger('change');
01155|                 // Cargar jornadas - usar la última jornada si existe
01156|                 if (empleado.jornadas && empleado.jornadas.length != 0) {
01157|                     $("#tipo_jornada").val(empleado.jornadas[0].jornada_id).trigger('change');
01158|                     $("#f_jornada").val(empleado.jornadas[0].fecha);
01159|                     JornadaOriginal_Id = empleado.jornadas[0].jornada_id || 0;
01160|                     FechaJornadaOri = empleado.jornadas[0].fechaSinFormato
01161|                 } else {
01162|                     $("#f_jornada").val("");
01163|                     $("#tipo_jornada").val("").trigger('change');
01164|                     JornadaOriginal_Id = 0;
01165|                     FechaJornadaOri = ""
01166|                 }
01167|                 $('#motivo_baja').val(empleado.motivo_baja_id).trigger('change');
01168|                 // Nuevos selects - usar IDs correctos
01169|                 if ($('#relacion').length) $('#relacion').val(empleado.tipo_relacion_id).trigger('change');
01170|                 if ($('#tipo_tarea').length) $('#tipo_tarea').val(empleado.tipo_tarea_id).trigger('change');
01171| 
01172|                 // Cargar localidades si hay provincia
01173|                 if (empleado.provincia) {
01174|                     setTimeout(() => {
01175|                         getLocalidades(empleado.localidad);
01176|                     }, 500);
01177|                 }
01178| 
01179|                 cargandoDatos = true;
01180|                 CargaSelectDto()
01181|                     .then(() => {
01182|                         $('#departamento').val(empleado.departamento_id).trigger('change');
01183|                         return CargaSelectServ();
01184|                     })
01185|                     .then(async () => {
01186|                         // Cargar servicios asignados desde el backend
01187|                         serviciosAsignados = [];
01188| 
01189|                         console.log('Datos de empleado recibidos:', empleado);
01190|                         console.log('servicios_asignados:', empleado.servicios_asignados);

(Output truncated at 51200 bytes. Use 'offset' parameter to read beyond line 1190)
</file>
```
```

---

## Assistant (Plan · glm-4.7-free · 9.5s)

_Thinking:_

El archivo es muy largo. Necesito ver más sobre la lógica del cargo de jefe. Voy a buscar en el archivo JavaScript específicamente lógica relacionada con el cambio de estatus de jefe, y también buscar en el controlador cómo se maneja.

Déjame leer más del archivo personal.js para encontrar la lógica relacionada con el cargo.

```
Tool: grep

**Input:**
```json
{
  "include": "*.js",
  "pattern": "cargo|jefe|idCargo",
  "path": "D:\\laragon\\www\\zona_capital_laravel\\public\\js"
}
```
**Output:**
```
Found 72 matches
D:\laragon\www\zona_capital_laravel\public\js\personal.js:
  Line 209:                 // Cargar empleados con cargo para "Certifica"
  Line 211:                 data.empleados_con_cargo.forEach(emp => {
  Line 230:                 // Cargar cargos
  Line 231:                 let cargoOptions = '<option value="">- Seleccionar -</option>';
  Line 232:                 data.cargos.forEach(cargo => {
  Line 233:                     cargoOptions += `<option value="${cargo.idCargo}">${cargo.cargo}</option>`;
  Line 235:                 $('#cargo').html(cargoOptions);
  Line 408:                     cargo: 'required',
  Line 460:                     cargo: 'Seleccione el cargo',
  Line 532:         // Inicializar typeahead para jefes
  Line 534:             $('#jefe').typeahead({
  Line 539:                 name: 'jefes',
  Line 550:                 $('#jefe_id').val(suggestion.id);
  Line 1122:                 // Cargo - si cargo_id es 0, mostrar "- Seleccionar -" pero enviar 0 al guardar
  Line 1124:                 const cargoId = empleado.cargo_id ?? 0;
  Line 1125:                 if ($('#cargo').length) {
  Line 1126:                     // Si cargo_id es 0, asegurarse de que existe una opción con value="0"
  Line 1127:                     if (cargoId === 0 && $('#cargo option[value="0"]').length === 0) {
  Line 1128:                         // Agregar opción "Sin cargo" con value="0"
  Line 1129:                         $('#cargo').append('<option value="0">Sin cargo</option>');
  Line 1131:                     $('#cargo').val(cargoId).trigger("change");
  Line 1637:      * Cargar el jefe correspondiente según la jerarquía organizacional
  Line 1653:         let cargoJefe = null;
  Line 1659:             cargoJefe = 1; // Jefe de sector
  Line 1663:             cargoJefe = 2; // Jefe de servicio
  Line 1667:             cargoJefe = 3; // Jefe de departamento
  Line 1671:             cargoJefe = 4; // Jefe de gerencia
  Line 1676:         // Buscar el jefe correspondiente
  Line 1678:         apiLaravel(`/personal/buscar-jefe?nivel=${tipoNivel}&nivel_id=${nivelId}&cargo_jefe=${cargoJefe}`, 'GET')
  Line 1681:                 if (response.jefe) {
  Line 1683:                     const jefeId = response.jefe.idEmpleado;
  Line 1684:                     $('#certifica').val(jefeId).trigger('change');
  Line 1686:                     const option = $(`#certifica option[value="${jefeId}"]`);
  Line 1690:                         $('#certifica').val(jefeId).trigger('change');
  Line 1694:                             `${response.jefe.apellidos}, ${response.jefe.nombres} (${response.jefe.legajo})`,
  Line 1695:                             jefeId,
  Line 1702:                     // No hay jefe asignado, limpiar selección
  Line 1708:                 console.error('Error cargando jefe:', error);
  Line 1710:                 mostrarMensaje('error', 'Error cargando jefe:' + error);
  Line 1763:                 // Cargar jefe correspondiente solo si se solicita
  Line 2645:     // Función para buscar jefe de un servicio
  Line 2647:         console.log('Buscando jefe para servicio ID:', servicioId);
  Line 2648:         return apiLaravel('/personal/jefe-servicio?servicio_id=' + servicioId, 'GET')
  Line 2650:                 console.log('Respuesta jefe servicio:', response);
  Line 2651:                 if (response.jefe) {
  Line 2652:                     console.log('Jefe encontrado:', response.jefe.nombre, 'ID:', response.jefe.id);
  Line 2654:                     console.log('No se encontró jefe para el servicio');
  Line 2656:                 return response.jefe || null;
  Line 2659:                 console.error('Error buscando jefe:', error);
  Line 2698:         // Buscar jefe automáticamente
  Line 2699:         const jefe = await buscarJefeServicio(servicioId);
  Line 2700:         const certificadorId = jefe ? jefe.id : '';
  Line 2727:         if (jefe) {
  Line 2731:                 html: `<strong>${nombreServicio}</strong><br>Certificador: ${jefe.nombre}`,
  Line 2742:                 html: `<strong>${nombreServicio}</strong><br><small>No se encontró jefe asignado. Por favor, seleccione un certificador manualmente.</small>`,

D:\laragon\www\zona_capital_laravel\public\js\control-horarios.js:
  Line 13:     const jefesData = (todoPersonal === "1") ? (config.jefesFull || []) : (config.jefesLimit || []);
  Line 351:         setupInputAutocomplete('certifica', jefesData, (datum) => {

D:\laragon\www\zona_capital_laravel\public\js\programacion_horarios.js:
  Line 57:     $('#jefes_fil').on('change', function() {
  Line 119:         jefe_id: $('#jefes_fil').val(),
  Line 165:  * Cargar lista de jefes
  Line 168:     return apiLaravel('/api/programacion-personal/jefes', 'GET')
  Line 171:                 const select = $('#jefes_fil');
  Line 175:                 response.jefes.forEach(jefe => {
  Line 177:                     if (select.find(`option[value="${jefe.idEmpleado}"]`).length === 0) {
  Line 178:                         select.append(`<option value="${jefe.idEmpleado}" data-uo="${jefe.Servicio || ''}">${jefe.Apellido}, ${jefe.Nombre}${jefe.Servicio ? ' - ' + jefe.Servicio : ''}</option>`);
  Line 184:                 console.error('Error al cargar jefes desde API');
  Line 189:             console.error('Error al cargar jefes:', error);
  Line 959:         idJefe: $('#jefes_fil').val(),
  Line 994:         idJefe: $('#jefes_fil').val(),
  Line 1413:         $('#jefes_fil').select2({
  Line 1414:             placeholder: 'Seleccionar jefe',

D:\laragon\www\zona_capital_laravel\public\js\productividad.js:
  Line 54:         jefe: document.getElementById('permiso_jefe')?.value === '1'
```
```

---

## Assistant (Plan · glm-4.7-free · 4.7s)

_Thinking:_

Ahora necesito ver el código específico del controlador personal.js que maneja la lógica de certificación y ver qué pasa cuando el cargo cambia de jefe a no-jefe. Voy a leer más del archivo JavaScript.

```
Tool: read

**Input:**
```json
{
  "filePath": "D:\\laragon\\www\\zona_capital_laravel\\public\\js\\personal.js",
  "offset": 1620,
  "limit": 200
}
```
**Output:**
```
<file>
01621|         if (typeof Swal !== 'undefined') {
01622|             Toast.fire({
01623|                 icon: tipo,
01624|                 title: mensaje
01625|             });
01626|         } else {
01627|             // Fallback a alert
01628|             alert(mensaje);
01629|         }
01630|     }
01631| 
01632|     // ========================================
01633|     // FUNCIONES ESPECÍFICAS DEL MÓDULO
01634|     // ========================================
01635| 
01636|     /**
01637|      * Cargar el jefe correspondiente según la jerarquía organizacional
01638|      */
01639|     function cargarCertifica() {
01640| 
01641|         // Determinar el nivel más específico seleccionado
01642|         const sector = $('#sector').val();
01643|         let servicio = $('#servicio').val();
01644|          // Si es array (multiple), usamos el primero si es único, o null si son varios
01645|          if (Array.isArray(servicio)) {
01646|              servicio = servicio.length === 1 ? servicio[0] : "";
01647|          }
01648|         const departamento = $('#departamento').val();
01649|         const gerencia = $('#gerencia').val();
01650| 
01651|         let nivelId = null;
01652|         let tipoNivel = null;
01653|         let cargoJefe = null;
01654| 
01655|         // Determinar el nivel más específico con valor
01656|         if (sector && sector !== "") {
01657|             nivelId = sector;
01658|             tipoNivel = 'sector';
01659|             cargoJefe = 1; // Jefe de sector
01660|         } else if (servicio && servicio !== "") {
01661|             nivelId = servicio;
01662|             tipoNivel = 'servicio';
01663|             cargoJefe = 2; // Jefe de servicio
01664|         } else if (departamento && departamento !== "") {
01665|             nivelId = departamento;
01666|             tipoNivel = 'departamento';
01667|             cargoJefe = 3; // Jefe de departamento
01668|         } else if (gerencia && gerencia !== "") {
01669|             nivelId = gerencia;
01670|             tipoNivel = 'gerencia';
01671|             cargoJefe = 4; // Jefe de gerencia
01672|         }
01673| 
01674| 
01675| 
01676|         // Buscar el jefe correspondiente
01677| 
01678|         apiLaravel(`/personal/buscar-jefe?nivel=${tipoNivel}&nivel_id=${nivelId}&cargo_jefe=${cargoJefe}`, 'GET')
01679|             .then(response => {
01680| 
01681|                 if (response.jefe) {
01682|                     // Buscar en las opciones del select si existe el empleado
01683|                     const jefeId = response.jefe.idEmpleado;
01684|                     $('#certifica').val(jefeId).trigger('change');
01685|                     /*
01686|                     const option = $(`#certifica option[value="${jefeId}"]`);
01687| 
01688|                     if (option.length > 0) {
01689|                         // Si existe en las opciones, seleccionarlo
01690|                         $('#certifica').val(jefeId).trigger('change');
01691|                     } else {
01692|                         // Si no existe, agregarlo como nueva opción y seleccionarlo
01693|                         const newOption = new Option(
01694|                             `${response.jefe.apellidos}, ${response.jefe.nombres} (${response.jefe.legajo})`,
01695|                             jefeId,
01696|                             true,
01697|                             true
01698|                         );
01699|                         $('#certifica').append(newOption).trigger('change');
01700|                     }*/
01701|                 } else {
01702|                     // No hay jefe asignado, limpiar selección
01703|                     $('#certifica').val('').trigger('change');
01704|                 }
01705|             })
01706|             .catch(error => {
01707| 
01708|                 console.error('Error cargando jefe:', error);
01709|                 $('#certifica').val('').trigger('change');
01710|                 mostrarMensaje('error', 'Error cargando jefe:' + error);
01711|             });
01712|     }
01713| 
01714|     /**
01715|      * Cargar departamentos según gerencia seleccionada
01716|      */
01717|     function CargaSelectDto() {
01718|         const gerenciaId = $('#gerencia').val();
01719|         if (!gerenciaId || gerenciaId == "0") {
01720|             $('#departamento').html('<option value="">- Seleccionar -</option>').trigger('change');
01721|             return Promise.resolve();
01722|         }
01723| 
01724|         return apiLaravel('/personal/departamentos?gerencia_id=' + gerenciaId, 'GET')
01725|             .then(response => {
01726|                 let options = '<option value="">- Seleccionar -</option>';
01727|                 response.data.forEach(dept => {
01728|                     options += `<option value="${dept.idDepartamento}">${dept.departamento}</option>`;
01729|                 });
01730|                 // Destruir Select2 antes de manipular el DOM
01731|                 if (typeof $.fn.select2 !== 'undefined') {
01732|                     if ($('#departamento').data('select2')) { try { $('#departamento').select2('destroy'); } catch (e) { } }
01733|                     if ($('#servicio').data('select2')) { try { $('#servicio').select2('destroy'); } catch (e) { } }
01734|                     if ($('#sector').data('select2')) { try { $('#sector').select2('destroy'); } catch (e) { } }
01735|                 }
01736| 
01737|                 $('#departamento').html(options);
01738| 
01739|                 // Limpiar selectores dependientes (sin trigger hasta reinit)
01740|                 $('#servicio').html('<option value="" selected>- Seleccionar -</option>');
01741|                 $('#sector').html('<option value="" selected>- Seleccionar -</option>');
01742| 
01743|                 // Volver a inicializar Select2 en los selects afectados
01744|                 if (typeof $.fn.select2 !== 'undefined') {
01745|                     $('#departamento').select2({
01746|                         theme: 'bootstrap-5',
01747|                         width: '100%',
01748| 
01749|                     });
01750| 
01751|                     $('#servicio').select2({
01752|                         theme: 'bootstrap-5',
01753|                         width: '100%',
01754|                         placeholder: "- Seleccionar -"
01755|                     });
01756|                     $('#sector').select2({
01757|                         theme: 'bootstrap-5',
01758|                         width: '100%',
01759| 
01760|                     });
01761|                 }
01762| 
01763|                 // Cargar jefe correspondiente solo si se solicita
01764|                 if (!cargandoDatos) {
01765|                     cargarCertifica();
01766|                 }
01767|             })
01768|             .catch(error => {
01769|                 console.error('Error cargando departamentos:', error);
01770|             });
01771|     }
01772| 
01773|     /**
01774|      * Cargar servicios según departamento seleccionado
01775|      */
01776|     function CargaSelectServ() {
01777|         const departamentoId = $('#departamento').val();
01778|         if (!departamentoId || departamentoId == "0") {
01779|             $('#servicio').html('<option value="">- Seleccionar -</option>').trigger('change');
01780|             return Promise.resolve();
01781|         }
01782| 
01783|         return apiLaravel('/personal/servicios?departamento_id=' + departamentoId, 'GET')
01784|             .then(response => {
01785|                 let options = '<option value="">- Seleccionar -</option>';
01786|                 response.data.forEach(serv => {
01787|                     options += `<option value="${serv.idServicio}">${serv.servicio}</option>`;
01788|                 });
01789|                 // Destruir Select2 antes de manipular el DOM
01790|                 if (typeof $.fn.select2 !== 'undefined') {
01791|                     if ($('#servicio').data('select2')) { try { $('#servicio').select2('destroy'); } catch (e) { } }
01792|                 }
01793| 
01794|                 $('#servicio').html(options);
01795| 
01796|                 // Volver a inicializar Select2 (SIMPLE, no múltiple)
01797|                 if (typeof $.fn.select2 !== 'undefined') {
01798|                     $('#servicio').select2({
01799|                         theme: 'bootstrap-5',
01800|                         width: '100%',
01801|                         placeholder: "- Seleccionar -"
01802|                     });
01803|                 }
01804|             })
01805|             .catch(error => {
01806|                 console.error('Error cargando servicios:', error);
01807|             });
01808|     }
01809| 
01810|     /**
01811|      * Cargar sectores según servicio seleccionado
01812|      */
01813|     function CargaSelectSect() {
01814|         let servicioId = $('#servicio').val();
01815| 
01816|         // Si es array (multiple), tomamos decisión:
01817|         // Opción A: No cargar sectores (disable) si hay más de 1.
01818|         // Opción B: Tomar el primero para cargar sectores (comportamiento "principal").
01819|         // Comportamiento actual: Validar si es array
01820|         if (Array.isArray(servicioId)) {

(File has more lines. Use 'offset' parameter to read beyond line 1820)
</file>
```
```

---

## Assistant (Plan · glm-4.7-free · 10.0s)

_Thinking:_

Ahora necesito entender qué pasa específicamente cuando se cambia el estatus de jefe a alguien. Buscaré en el código de personal.js la lógica de guardar, y también revisaré el controlador PHP para ver cómo se maneja el campo idCargo cuando se actualiza un empleado.

Déjame buscar en el código de JavaScript donde se envían los datos al backend.

```
Tool: grep

**Input:**
```json
{
  "pattern": "guardarPersonal|servicios_asignados",
  "path": "D:\\laragon\\www\\zona_capital_laravel\\public\\js",
  "include": "personal.js"
}
```
**Output:**
```
Found 12 matches
D:\laragon\www\zona_capital_laravel\public\js\personal.js:
  Line 526:                     guardarPersonal();
  Line 1190:                         console.log('servicios_asignados:', empleado.servicios_asignados);
  Line 1193:                         // Intentar cargar desde servicios_asignados o historial_servicios (activos)
  Line 1196:                         if (empleado.servicios_asignados && empleado.servicios_asignados.length > 0) {
  Line 1197:                             serviciosParaCargar = empleado.servicios_asignados;
  Line 1360:     async function guardarPersonal() {
  Line 1389:         datos.servicios_asignados = serviciosAsignados.map(s => ({
  Line 1398:         if (datos.servicios_asignados && datos.servicios_asignados.length > 0) {
  Line 1399:             const primerCertificador = datos.servicios_asignados[0].certificador_id;
  Line 1436:                     return; // Detener la ejecución de guardarPersonal
  Line 1457:                     return; // Detener la ejecución de guardarPersonal
  Line 1461:                     return; // Detener la ejecución de guardarPersonal
```
```

---

## Assistant (Plan · glm-4.7-free · 11.0s)

_Thinking:_

Necesito leer la función guardarPersonal para ver cómo maneja los datos del empleado.

```
Tool: read

**Input:**
```json
{
  "filePath": "D:\\laragon\\www\\zona_capital_laravel\\public\\js\\personal.js",
  "limit": 200,
  "offset": 1350
}
```
**Output:**
```
<file>
01351|         const d = parseInt(parts[0], 10);
01352|         const m = parseInt(parts[1], 10);
01353|         const y = parseInt(parts[2], 10);
01354|         const dt = new Date(y, m - 1, d);
01355|         return dt.getFullYear() === y && dt.getMonth() === m - 1 && dt.getDate() === d;
01356|     }
01357|     /**
01358|      * Guardar personal (crear o actualizar)
01359|      */
01360|     async function guardarPersonal() {
01361|         const form = document.getElementById('formPersonal');
01362| 
01363|         // Crear objeto de datos en lugar de FormData
01364|         const datos = {};
01365| 
01366|         // Obtener datos básicos del formulario
01367|         const campos = form.querySelectorAll('input, select, textarea');
01368|         campos.forEach(campo => {
01369|             if (campo.name) {
01370|                 // Ignorar campos de archivos, se manejarán por separado
01371|                 if (campo.type !== 'file' && campo.name !== 'servicios[]') {
01372|                     // Incluir siempre el campo, incluso si está vacío (excepto checkboxes)
01373|                     if (campo.type !== 'checkbox' && campo.type !== 'radio') {
01374|                         datos[campo.name] = campo.value;
01375|                     } else if (campo.checked) {
01376|                         // Para checkboxes, solo incluir si está marcado
01377|                         datos[campo.name] = campo.value;
01378|                     }
01379|                 }
01380|             }
01381|         });
01382| 
01383|         // Convertir sexo de 1/2 a M/F para validación del servidor
01384|         if (datos.sexo) {
01385|             datos.sexo = datos.sexo === '1' ? 'M' : 'F';
01386|         }
01387| 
01388|         // Agregar servicios asignados con toda su información
01389|         datos.servicios_asignados = serviciosAsignados.map(s => ({
01390|             servicio_id: s.servicio_id,
01391|             certificador_id: s.certificador_id || null,
01392|             fecha_pase: s.fecha_pase ? formatDate(s.fecha_pase) : null,
01393|             sector_id: s.sector_id || null
01394|         }));
01395| 
01396|         // Si hay servicios asignados, usar el certificador del primer servicio como certifica principal
01397|         // Esto es para compatibilidad con el backend que requiere certifica
01398|         if (datos.servicios_asignados && datos.servicios_asignados.length > 0) {
01399|             const primerCertificador = datos.servicios_asignados[0].certificador_id;
01400|             if (primerCertificador) {
01401|                 datos.certifica = primerCertificador;
01402|             }
01403|         }
01404| 
01405|         // Agregar servicios a dar de baja (solo en modo editar)
01406|         if (modoFormulario === 'editar' && serviciosParaDarBaja.length > 0) {
01407|             datos.servicios_dar_baja = serviciosParaDarBaja;
01408|         }
01409| 
01410|         // Si estamos editando y el legajo cambió, incluir el legajo original
01411|         if (modoFormulario === 'editar') {
01412|             const legajoInput = document.getElementById('legajo');
01413|             const legajoOriginal = legajoInput.getAttribute('data-original-legajo');
01414| 
01415|             if (legajoInput.value !== legajoOriginal) {
01416|                 datos.legajoOriginal = legajoOriginal;
01417|             }
01418|         }
01419| 
01420|         // Convertir todas las fechas al formato YYYY-MM-DD
01421| 
01422| 
01423|         const fechaCampos = {
01424|             fecha_nacimiento: 'fecha de nacimiento',
01425|             fecha_alta: 'fecha de alta',
01426|             fecha_baja: 'fecha de baja',
01427|             fecha_adm_publica: 'fecha de admisión pública',
01428|             f_jornada: 'fecha de jornada'
01429|         };
01430| 
01431|         // Comprobar y convertir cada fecha; si alguna no cumple el formato, mostrar error y detener
01432|         for (const campo in fechaCampos) {
01433|             if (Object.prototype.hasOwnProperty.call(datos, campo) && datos[campo]) {
01434|                 if (!isValidDDMMYYYY(datos[campo])) {
01435|                     mostrarMensaje('error', `La ${fechaCampos[campo]} debe tener formato DD/MM/AAAA`);
01436|                     return; // Detener la ejecución de guardarPersonal
01437|                 }
01438|                 // Si es válida, convertir a YYYY-MM-DD
01439|                 datos[campo] = formatDate(datos[campo]);
01440|             }
01441|         }
01442|         datos.JornadaOriginal_Id = JornadaOriginal_Id
01443|         datos.FechaJornadaOri = FechaJornadaOri
01444| 
01445|         // Convertir fechas en historial de relaciones
01446|         datos.relaciones = [];
01447|         for (let i = 0; i < num_relaciones; i++) {
01448|             const relacion = $(`#relacion_${i}`).val();
01449|             let desde = $(`#desde_rel_${i}`).val();
01450|             let hasta = $(`#hasta_rel_${i}`).val();
01451|             const obs = $(`#obs_rel_${i}`).val();
01452| 
01453|             if (relacion) {
01454|                 // Si hay fecha, validar formato DD/MM/YYYY antes de convertir
01455|                 if (desde && !isValidDDMMYYYY(desde)) {
01456|                     mostrarMensaje('error', `La fecha "Desde" en la relación ${i + 1} debe tener formato DD/MM/AAAA`);
01457|                     return; // Detener la ejecución de guardarPersonal
01458|                 }
01459|                 if (hasta && !isValidDDMMYYYY(hasta)) {
01460|                     mostrarMensaje('error', `La fecha "Hasta" en la relación ${i + 1} debe tener formato DD/MM/AAAA`);
01461|                     return; // Detener la ejecución de guardarPersonal
01462|                 }
01463| 
01464|                 // Convertir fechas a formato YYYY-MM-DD (si existen)
01465|                 desde = desde ? formatDate(desde) : '';
01466|                 hasta = hasta ? formatDate(hasta) : '';
01467| 
01468|                 datos.relaciones.push({
01469|                     relacion_id: relacion,
01470|                     desde: desde,
01471|                     hasta: hasta,
01472|                     observacion: obs
01473|                 });
01474|             }
01475|         }
01476|         // Agregar estados de checkbox (sin usar formData.has)
01477|         datos.f_doble = $('#f_doble').is(':checked') ? 1 : 0;
01478|         datos.fe = $('#fe').is(':checked') ? 1 : 0;
01479| 
01480| 
01481| 
01482|         // Agregar foto de perfil
01483|         // Prioridad: 1. Input hidden de recorte (nueva) 2. Imagen recortada mostrada 3. Imagen original (si no hubo cambios)
01484|         const fotoBase64 = $('#foto_base64').val();
01485|         if (fotoBase64) {
01486|              datos.foto = fotoBase64;
01487|         } else {
01488|              // Fallback a lo que se esté mostrando
01489|              if ($('#img_crop').is(':visible')) {
01490|                  const cropSrc = $('#img_crop').attr('src');
01491|                  if (cropSrc && cropSrc.length > 200) { // Check if it's base64
01492|                      datos.foto = cropSrc;
01493|                  }
01494|              } else if ($('#img_foto').length > 0) {
01495|                  const imgSrc = $('#img_foto').attr('src');
01496|                  if (imgSrc) {
01497|                      datos.foto = imgSrc;
01498|                  }
01499|              }
01500|         }
01501| 
01502|         // Agregar indicador separado para eliminación de foto (usando booleano simple)
01503|         datos.eliminar_foto = eliminarFotoFlag;        // Agregar datos de documentos como array de objetos con imágenes en base64
01504|         datos.documentos = [];
01505| 
01506|         // Procesar archivos de forma asíncrona para convertirlos a base64
01507| 
01508|         for (let i = 0; i < num_doc; i++) {
01509|             const nombre = $(`#doc_nombre_${i}`).val();
01510| 
01511| 
01512|             if ($(`#doc_nombre_${i}`).length != 0) {
01513|                 const doc = {
01514|                     nombre: nombre,
01515|                 };
01516| 
01517|                 // Si hay una imagen previa ya cargada pero no hay nuevo archivo
01518|                 const imgSrc = $(`#img_prev_${i}_big`).attr('src');
01519| 
01520|                 doc.imagen_data = imgSrc;
01521| 
01522|                 datos.documentos.push(doc);
01523|             }
01524|         }
01525| 
01526|         // Agregar jornadas eliminadas como array
01527|         if (del_jor.length > 0) {
01528|             datos.jornadas_eliminadas = del_jor;
01529|         }
01530| 
01531|         // Agregar imágenes eliminadas como array
01532|         if (arrImgsDel.length > 0) {
01533|             datos.imagenes_eliminadas = arrImgsDel;
01534|         }
01535| 
01536|         const url = modoFormulario === 'crear' ? '/personal' : `/personal/${empleadoEditando.idEmpleado}`;
01537|         const method = modoFormulario === 'crear' ? 'POST' : 'PUT';
01538| 
01539| 
01540|         apiLaravel(url, method, datos)
01541|             .then(response => {
01542|                 mostrarMensaje('success', response.message || 'Personal guardado correctamente');
01543|                 mostrarListado();
01544|             })
01545|             .catch(error => {
01546|                 console.error('Error guardando personal:', error);
01547|                 mostrarMensaje('error', error);
01548|             });
01549|     }
01550| 

(File has more lines. Use 'offset' parameter to read beyond line 1550)
</file>
```
```

---

## Assistant (Plan · glm-4.7-free · 5.8s)

_Thinking:_

Ahora tengo una buena comprensión del código. Déjame analizar qué sucede cuando se quita el estatus de jefe a una persona. Necesito verificar también si hay alguna lógica específica en el controlador para manejar este caso.

Déjame buscar en el controlador PHP para ver si hay alguna validación o lógica específica cuando se cambia el idCargo.

```
Tool: grep

**Input:**
```json
{
  "include": "PersonalController.php",
  "path": "D:\\laragon\\www\\zona_capital_laravel\\app\\Http\\Controllers",
  "pattern": "idCargo|cargo|certifica|IdEmpleado2"
}
```
**Output:**
```
Found 59 matches
D:\laragon\www\zona_capital_laravel\app\Http\Controllers\PersonalController.php:
  Line 58:         $jefes = Empleado::where('idCargo', '!=', 0)
  Line 59:             ->whereNotNull('idCargo')
  Line 64:         $cargos = Cargo::orderBy('cargo')->get(['idCargo', 'cargo']);
  Line 73:             'cargos' => $cargos
  Line 140:         $cargo = $request->input('cargo', 0);
  Line 206:             $query->where('IdEmpleado2', $jefe);
  Line 209:         if ($cargo > 0) {
  Line 210:             $query->where('idCargo', $cargo);
  Line 380:             'cargo' => $this->getCargoNombre($empleado->idCargo),
  Line 381:             'cargo_id' => $empleado->idCargo,
  Line 382:             'certifica' => $this->getCertificaNombre($empleado->IdEmpleado2),
  Line 383:             'certifica_id' => $empleado->IdEmpleado2,
  Line 457:         // Validación condicional: servicios no obligatorio si es Jefe Dpto/Gerente/Director (cargo >= 3)
  Line 458:         $cargo = $request->input('cargo', 0);
  Line 459:         $serviciosRequeridos = ($cargo >= 3) ? 'nullable' : 'required';
  Line 481:             // cargo puede ser 0 (sin cargo), usar present|integer|min:0
  Line 482:             'cargo' => 'present|integer|min:0',
  Line 483:             // certifica es requerido solo si NO hay servicios_asignados
  Line 484:             'certifica' => $tieneServiciosAsignados ? 'nullable|integer' : 'required|integer',
  Line 493:             'servicios_asignados.*.certificador_id' => 'nullable|integer',
  Line 519:             'cargo.required' => 'Seleccione el cargo',
  Line 520:             'certifica.required' => 'Seleccione quién certifica',
  Line 567:             // Asignación de servicios con información completa (certificador, fecha, sector)
  Line 656:         // Validación condicional: servicios no obligatorio si es Jefe Dpto/Gerente/Director (cargo >= 3)
  Line 657:         $cargo = $request->input('cargo', 0);
  Line 658:         $serviciosRequeridos = ($cargo >= 3) ? 'nullable' : 'required';
  Line 680:             // cargo puede ser 0 (sin cargo), usar present|integer|min:0
  Line 681:             'cargo' => 'present|integer|min:0',
  Line 682:             // certifica es requerido solo si NO hay servicios_asignados
  Line 683:             'certifica' => $tieneServiciosAsignados ? 'nullable|integer' : 'required|integer',
  Line 692:             'servicios_asignados.*.certificador_id' => 'nullable|integer',
  Line 718:             'cargo.required' => 'Seleccione el cargo',
  Line 719:             'certifica.required' => 'Seleccione quién certifica',
  Line 1034:      * Get jefe del servicio (empleado con idCargo = 2 en ese servicio)
  Line 1044:         // Buscar empleado activo con idCargo = 2 (Jefe Servicio) asignado a este servicio
  Line 1047:             ->where('idCargo', 2)
  Line 1069:      * Obtener empleados activos para certificadores (solo los que tienen cargo asignado)
  Line 1074:             ->whereNotNull('idCargo')
  Line 1075:             ->where('idCargo', '!=', 0)
  Line 1125:             'empleados_con_cargo' => Empleado::where('idCargo', '!=', 0)
  Line 1126:                 ->whereNotNull('idCargo')
  Line 1133:             'cargos' => Cargo::orderBy('cargo')->get(['idCargo', 'cargo'])
  Line 1238:         $empleado->idCargo = (int)($request->cargo ?? 0);
  Line 1239:         $empleado->IdEmpleado2 = (int)($request->certifica ?? 0);
  Line 1318:         $empleado->idCargo = (int)($request->cargo ?? 0);
  Line 1319:         $empleado->IdEmpleado2 = (int)($request->certifica ?? 0);
  Line 1538:             $cargoJefe = $request->get('cargo_jefe');
  Line 1543:                 ->where('empleados.idCargo', $cargoJefe); // Con el cargo de jefe correspondiente
  Line 1572:                         'cargo' => $jefe->Cargo
  Line 1609:         $cargo = DB::table('cargo')->where('idCargo', $id)->first();
  Line 1610:         return $cargo ? $cargo->cargo : 'Sin definir';
  Line 1630:                 // Usar el certificador_id del pivot, si existe. Sino, buscar el jefe del servicio.
  Line 1631:                 $certificadorId = $pivot && $pivot->certificador_id ? $pivot->certificador_id : null;
  Line 1633:                 // Si no hay certificador en el pivot, buscar el jefe del servicio
  Line 1634:                 if (!$certificadorId) {
  Line 1636:                         ->where('idCargo', 2)
  Line 1645:                     $certificadorId = $jefe ? $jefe->idEmpleado : $empleado->idCertifica;
  Line 1651:                     'certificador_id' => $certificadorId,
  Line 1666:                         'certificador_id' => $empleado->idCertifica,
```
```

---

## Assistant (Plan · glm-4.7-free · 10.2s)

_Thinking:_

Ahora necesito buscar específicamente en la base de datos o en el código qué pasa cuando un empleado deja de ser jefe. Necesito verificar si hay lógica para eliminar sus relaciones de servicios o manejar a los empleados que dependían de él como certificador.

Déjame buscar en el código donde se usan los jefes para certificar.

```
Tool: bash

**Input:**
```json
{
  "command": "rg \"certifica|IdEmpleado2\" app/Http/Controllers/ --type php -A 3 -B 3",
  "description": "Search for certifica field usage"
}
```
**Output:**
```
/usr/bin/bash: line 1: rg: command not found

```
```

---

## Assistant (Plan · glm-4.7-free · 17.6s)

_Thinking:_

El comando rg no está disponible. Déjame usar grep en su lugar.

```
Tool: grep

**Input:**
```json
{
  "include": "*.php",
  "path": "D:\\laragon\\www\\zona_capital_laravel\\app",
  "pattern": "IdEmpleado2|certifica"
}
```
**Output:**
```
Found 84 matches
D:\laragon\www\zona_capital_laravel\app\Http\Controllers\PersonalController.php:
  Line 206:             $query->where('IdEmpleado2', $jefe);
  Line 382:             'certifica' => $this->getCertificaNombre($empleado->IdEmpleado2),
  Line 383:             'certifica_id' => $empleado->IdEmpleado2,
  Line 483:             // certifica es requerido solo si NO hay servicios_asignados
  Line 484:             'certifica' => $tieneServiciosAsignados ? 'nullable|integer' : 'required|integer',
  Line 493:             'servicios_asignados.*.certificador_id' => 'nullable|integer',
  Line 520:             'certifica.required' => 'Seleccione quién certifica',
  Line 567:             // Asignación de servicios con información completa (certificador, fecha, sector)
  Line 682:             // certifica es requerido solo si NO hay servicios_asignados
  Line 683:             'certifica' => $tieneServiciosAsignados ? 'nullable|integer' : 'required|integer',
  Line 692:             'servicios_asignados.*.certificador_id' => 'nullable|integer',
  Line 719:             'certifica.required' => 'Seleccione quién certifica',
  Line 1069:      * Obtener empleados activos para certificadores (solo los que tienen cargo asignado)
  Line 1239:         $empleado->IdEmpleado2 = (int)($request->certifica ?? 0);
  Line 1319:         $empleado->IdEmpleado2 = (int)($request->certifica ?? 0);
  Line 1630:                 // Usar el certificador_id del pivot, si existe. Sino, buscar el jefe del servicio.
  Line 1631:                 $certificadorId = $pivot && $pivot->certificador_id ? $pivot->certificador_id : null;
  Line 1633:                 // Si no hay certificador en el pivot, buscar el jefe del servicio
  Line 1634:                 if (!$certificadorId) {
  Line 1645:                     $certificadorId = $jefe ? $jefe->idEmpleado : $empleado->idCertifica;
  Line 1651:                     'certificador_id' => $certificadorId,
  Line 1666:                         'certificador_id' => $empleado->idCertifica,

D:\laragon\www\zona_capital_laravel\app\Models\Empleado.php:
  Line 88:         'IdEmpleado2', // Campo que certifica (jefe)
  Line 332:             ->withPivot('fecha_inicio', 'fecha_fin', 'activo', 'motivo', 'certificador_id')
  Line 349:      *          ['servicio_id' => X, 'certificador_id' => Y, 'fecha_pase' => 'Z', 'sector_id' => W],
  Line 364:                     'certificador_id' => $serv['certificador_id'] ?? null,
  Line 372:                     'certificador_id' => null,
  Line 379:         // Obtener servicios activos actuales con sus certificadores
  Line 383:             ->get(['servicio_id', 'certificador_id']);
  Line 387:             $actualesMap[$a->servicio_id] = $a->certificador_id;
  Line 418:                     'certificador_id' => $nuevo['certificador_id'],
  Line 428:         // Actualizar los existentes si cambió el certificador
  Line 431:                 $certificadorActual = $actualesMap[$nuevo['servicio_id']];
  Line 432:                 // Si cambió el certificador, actualizar
  Line 433:                 if ($certificadorActual != $nuevo['certificador_id']) {
  Line 439:                             'certificador_id' => $nuevo['certificador_id'],

D:\laragon\www\zona_capital_laravel\app\Http\Controllers\ProgramacionPersonalController.php:
  Line 842:                     // Fallback legacy: filtrar por IdEmpleado2
  Line 843:                     $query->where('e.IdEmpleado2', $jefeId);
  Line 858:                         $query->where('e.IdEmpleado2', $usuarioId);

D:\laragon\www\zona_capital_laravel\app\Http\Controllers\ControlHorariosController.php:
  Line 262:                 'IdEmpleado2',
  Line 271:                 $query->where('IdEmpleado2', $usuario->Personal_Id);
  Line 341:                 // Cuando no hay jefe, los datos se indexan por idEmpleado, no por IdEmpleado2
  Line 342:                 $indiceEmpleado = ($data['idJefe'] == 0) ? $empleado->idEmpleado : ($empleado->IdEmpleado2 ?? $empleado->idEmpleado);
  Line 458:                 $resultado[$legajoKey][] = $empleado->IdEmpleado2 ?? $empleado->idEmpleado;
  Line 482:         $sql = "SELECT mr.Estado, emp.Legajo, emp.IdEmpleado2, emp.idEmpleado, Entrada, EXtra, emp.Apellido, emp.Nombre, Salida, FechaMarca, Dif
  Line 486:             LEFT JOIN empleados jef ON jef.idEmpleado = emp.IdEmpleado2
  Line 491:             $sql .= ' AND emp.IdEmpleado2 = ?';
  Line 518:                 $reg['IdEmpleado2'] = $reg['idEmpleado'];
  Line 520:             $dataQuery[$reg['FechaMarca']][(int) $reg['IdEmpleado2']][(int) $reg['Legajo']][] = $reg;
  Line 528:         $sql = "SELECT emp.Legajo, emp.IdEmpleado2, AnioLar, emp.idEmpleado, Dia, Motivo, DATE_FORMAT(Dia, '%d/%m/%Y') AS DF
  Line 533:             INNER JOIN empleados jef ON jef.idEmpleado = emp.IdEmpleado2
  Line 534:             WHERE Dia BETWEEN ? AND ? AND emp.IdEmpleado2 != 0
  Line 541:             $reg['IdEmpleado2'] = $reg['idEmpleado'];
  Line 542:             $dataQuery[$reg['Dia']][(int) $reg['IdEmpleado2']][(int) $reg['Legajo']][] = $reg;
  Line 550:         $sql = "SELECT emp.Legajo, emp.IdEmpleado2, emp.idEmpleado, hr.FechaRot, Horario_Id, DATE_FORMAT(h.entrada, '%H:%i') AS Entrada, DATE_FORMAT(h.Salida, '%H:%i') AS Salida
  Line 553:             INNER JOIN empleados jef ON jef.idEmpleado = emp.IdEmpleado2
  Line 560:             $sql .= ' AND emp.IdEmpleado2 = ?';
  Line 587:                 $reg['IdEmpleado2'] = $reg['idEmpleado'];
  Line 589:             $dataQuery[$reg['FechaRot']][(int) $reg['IdEmpleado2']][(int) $reg['Legajo']][] = $reg;
  Line 612:         $sql = "SELECT emp.Legajo, emp.IdEmpleado2, emp.idEmpleado, FechaGuard, DATE_FORMAT(h.entrada, '%H:%i') AS Entrada, DATE_FORMAT(h.Salida, '%H:%i') AS Salida
  Line 616:             INNER JOIN empleados jef ON jef.idEmpleado = emp.IdEmpleado2
  Line 617:             WHERE FechaGuard BETWEEN ? AND ? AND emp.IdEmpleado2 != 0
  Line 624:             $reg['IdEmpleado2'] = $reg['idEmpleado'];
  Line 625:             $dataQuery[$reg['FechaGuard']][(int) $reg['IdEmpleado2']][(int) $reg['Legajo']][] = $reg;
  Line 633:         $query = Empleado::query()->where('Estado', 1)->select('idEmpleado', 'Apellido', 'Nombre', 'Legajo', 'IdEmpleado2');
  Line 636:             $query->where('IdEmpleado2', $idJefe);

D:\laragon\www\zona_capital_laravel\app\Http\Controllers\OrdenMedicaController.php:
  Line 79:             $directorio = public_path('img/certificados');
  Line 89:             return 'img/certificados/' . $nombreArchivo;
  Line 274:                 'certificado' => 'nullable|integer',
  Line 321:                 $imagen->move(public_path('img/certificados'), $nombreImagen);
  Line 322:                 $imagenPath = 'img/certificados/' . $nombreImagen;
  Line 338:                 'CertMedico' => $validated['certificado'] ?? 0,
  Line 401:                 'certificado' => 'nullable|integer',
  Line 459:                 $imagen->move(public_path('img/certificados'), $nombreImagen);
  Line 460:                 $imagenPath = 'img/certificados/' . $nombreImagen;
  Line 476:                 'CertMedico' => $validated['certificado'] ?? 0,

D:\laragon\www\zona_capital_laravel\app\Http\Controllers\LicenciasController.php:
  Line 59:                 'message' => 'Error al obtener próximo número de certificado médico'
  Line 435:     // Medical certificate generation
  Line 449:             // Get the next medical certificate number
  Line 450:             $ultimoCertificado = DB::table('certificados_medicos')
  Line 457:             // Save the new certificate number
  Line 458:             DB::table('certificados_medicos')->insert([

D:\laragon\www\zona_capital_laravel\app\Models\OrdenMedica.php:
  Line 27:         'certificado',

D:\laragon\www\zona_capital_laravel\app\Models\Personal.php:
  Line 66:         'IdEmpleado2',
```
```

---

## Assistant (Plan · glm-4.7-free · 27.1s)

_Thinking:_

Ahora tengo un buen entendimiento del código. Déjame resumir lo que he encontrado:

1. **Campo idCargo**: Determina si una persona es jefe o no. Los valores de cargo tienen jerarquía:
   - 0: Sin cargo
   - 1: Jefe de sector
   - 2: Jefe de servicio
   - 3: Jefe de departamento
   - 4: Jefe de gerencia

2. **Campo IdEmpleado2**: Es el campo que indica quién certifica al empleado (su jefe directo).

3. **Tabla empleado_servicio**: Tiene un campo `certificador_id` en el pivot que puede ser diferente de `IdEmpleado2`, permitiendo que un empleado tenga certificadores específicos para cada servicio.

4. **Filtrado de jefes**: En PersonalController.php línea 58-62, los jefes se cargan como:
   ```php
   $jefes = Empleado::where('idCargo', '!=', 0)
       ->whereNotNull('idCargo')
       ->orderBy('Apellido')
       ->orderBy('Nombre')
       ->get(['idEmpleado', 'Apellido', 'Nombre', 'Legajo']);
   ```

5. **Búsqueda de jefes**: En personal.js línea 1636-1712, hay una función `cargarCertifica()` que busca el jefe correspondiente según la jerarquía organizacional.

Ahora, el punto clave es entender qué pasa cuando se quita el estatus de jefe (se cambia idCargo de un valor 1-4 a 0). Déjame verificar si hay alguna lógica para:

1. Eliminar al ex-jefe de la lista de jefes disponibles
2. Actualizar los empleados que tenían a esta persona como certificador


3. Probaré la eliminación del certificador del sistema, verificando si existen mecanismos automáticos para esta acción. Investigaré los procedimientos de actualización y las posibles dependencias que puedan impedir una eliminación completa.

```
Tool: webfetch

**Input:**
```json
{
  "url": "http://127.0.0.1:8003/personal",
  "format": "text"
}
```
**Output:**
```
Login - Zona Capital
    



    
        
            
                
                    Iniciar sesión
                    
                                                
                            Usuario
                            
                        
                        
                            Clave
                            
                        
                        
                        Ingresar
                    
                                    
            
        
    
    
        
            Si no tenés usuario, solicitá acceso al administrador.
        
    
}
```
```

---

## Assistant (Plan · glm-4.7-free · 648.9s)

_Thinking:_

La página de personal requiere autenticación. Necesito analizar el código para entender qué sucede en el backend cuando se quita el estatus de jefe.

Déjame ahora buscar en el código si hay alguna lógica específica para manejar cuando un jefe deja de ser jefe, como actualizar los empleados que dependen de él.

```
Tool: task

**Input:**
```json
{
  "subagent_type": "explore",
  "description": "Analyze jefe status removal",
  "prompt": "I need to analyze what happens in this Laravel application when a person's \"jefe\" (boss) status is removed.\n\nContext:\n- The application has an Employee model with fields: idCargo (position/role), IdEmpleado2 (who certifies/their boss)\n- Positions (idCargo) determine hierarchy: 1=Jefe de sector, 2=Jefe de servicio, 3=Jefe de departamento, 4=Jefe de gerencia\n- When idCargo is 0, the person has no boss position\n- The IdEmpleado2 field in empleados table stores who certifies each employee\n- There's also an empleado_servicio pivot table with a certificador_id field\n\nQuestions to investigate:\n1. When a boss's idCargo is changed from 1-4 to 0, does the system automatically update employees who had this person as their IdEmpleado2 (certifier)?\n2. Is there any validation or constraint preventing removing boss status if there are employees depending on them?\n3. Is there any cascading logic in Empleado model or Employee controller to handle this scenario?\n4. Look for any database triggers, foreign key constraints, or model events that handle when a boss is removed\n5. Check if there are any functions that clean up references when idCargo changes\n\nSearch in:\n- app/Models/Empleado.php\n- app/Http/Controllers/PersonalController.php\n- migrations or database schema files if they exist\n- Any service classes that handle employee relationships\n\nBe thorough and look for any automatic handling or cascading operations when a boss loses their status."
}
```
**Output:**
```
Based on my thorough analysis of the Laravel application, here are my findings regarding what happens when a boss's status is removed:

## Summary

**CRITICAL FINDING: There is NO automatic handling, validation, or cascading logic when a boss loses their status.**

---

## Detailed Findings:

### 1. Automatic Updates When idCargo Changes (Answer: NO)

**Result:** The system does **NOT** automatically update employees who had this person as their IdEmpleado2 (certifier) when idCargo changes from 1-4 to 0.

**Evidence:**
- In `PersonalController.php`, the `update()` method (lines 631-879) simply saves the employee record with:
  ```php
  $empleado->idCargo = (int)($request->cargo ?? 0);  // Line 1318
  $empleado->save();  // Line 756
  ```
- There is **no code** that checks if the employee being updated had subordinates, nor any code that updates those subordinates' `IdEmpleado2` or `certificador_id` fields.

- In the `Empleado` model, there is **no `boot()` method** or model events (like `updating`, `updated`, `saving`) that would trigger cascading updates.

---

### 2. Validation Preventing Removal (Answer: NO)

**Result:** There is **NO validation or constraint** preventing removal of boss status if there are employees depending on them.

**Evidence:**
- In `PersonalController.php` `update()` method, the validation rules (lines 664-698) are:
  ```php
  'cargo' => 'present|integer|min:0',
  ```
- This only requires cargo to be an integer >= 0 (which includes 0).
- There is **no custom validation** checking if employees have `IdEmpleado2` pointing to this employee before allowing `cargo` to be set to 0.

---

### 3. Cascading Logic in Model or Controller (Answer: NO)

**Result:** There is **NO cascading logic** in either the Empleado model or PersonalController to handle this scenario.

**Evidence:**
- The `Empleado` model has relationships but no cascading logic:
  - `IdEmpleado2` is in the `$fillable` array (line 88)
  - No relationship method defines `IdEmpleado2` as a relationship with cascade behavior
  
- The only cascading method found is `actualizarServicios()` in the Empleado model (lines 355-445), which handles service assignments but **does not** handle when a boss loses status.

---

### 4. Database Triggers, Foreign Key Constraints, or Model Events (Answer: NONE)

**Result:** There are **NO database triggers, foreign key constraints, or model events** handling this scenario.

**Evidence:**

**Foreign Key Constraints:**
- From the database schema (`zoncap_zonacap(1).sql`):
  - `empleados` table has `IdEmpleado2 int NOT NULL` with **NO foreign key constraint**
  - `empleado_servicio` table has `certificador_id` (added via migration) but also **NO foreign key constraint**

**No CASCADE Operations:**
```sql
CREATE TABLE `empleados` (
  ...
  `IdEmpleado2` int NOT NULL,
  ...
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
-- No FOREIGN KEY constraint defined on IdEmpleado2
```

**No Model Events:**
- The `Empleado` model has **no `boot()` method**
- No event listeners for `updating`, `updated`, `saving`, or `deleted` events

**No Database Triggers:**
- No SQL triggers found in the migrations or schema files

---

### 5. Functions to Clean Up References (Answer: NONE)

**Result:** There are **NO functions** that clean up references when `idCargo` changes.

**Evidence:**
- The `PersonalController` has a `updateLegajoInRelatedTables()` method (lines 957-977) that handles cascading legajo changes, but **nothing similar for cargo changes**

- No methods exist that:
  - Find employees with `IdEmpleado2 = [boss_id]`
  - Update them when the boss's `idCargo` becomes 0
  - Clear their `certificador_id` in `empleado_servicio` pivot table

---

## Potential Data Integrity Issues

When a boss's `idCargo` is changed from 1-4 to 0, the following problems can occur:

1. **Orphaned References:** Employees with `IdEmpleado2` pointing to the former boss will have invalid/obsolete certifiers.

2. **Broken Hierarchy:** Queries that filter by boss status or hierarchy will produce incorrect results:
   ```php
   // Line 58-62 in PersonalController.php
   $jefes = Empleado::where('idCargo', '!=', 0)  // Bosses
       ->whereNotNull('idCargo')
       ->get();
   // Employees pointing to a boss with idCargo=0 won't be filtered correctly
   ```

3. **Data Inconsistency in Pivot Table:** The `empleado_servicio` table's `certificador_id` field may point to employees who no longer have boss status.

---

## Recommended Solutions

To properly handle boss status removal, you should implement:

1. **Validation Rule:** Check if the employee has subordinates before allowing removal of boss status.

2. **Cascading Update:** Automatically update or nullify `IdEmpleado2` and `certificador_id` fields when a boss loses status.

3. **Database Constraint:** Add foreign key constraints with `ON DELETE SET NULL` or `ON UPDATE CASCADE`.

4. **Model Event:** Use Laravel model events (e.g., `updated`) to trigger the cascading logic automatically.

---

## File Paths Referenced

- `D:\laragon\www\zona_capital_laravel\app\Models\Empleado.php`
- `D:\laragon\www\zona_capital_laravel\app\Http\Controllers\PersonalController.php`
- `D:\laragon\www\zona_capital_laravel\database\migrations\2025_12_26_130132_create_empleado_servicio_table.php`
- `D:\laragon\www\zona_capital_laravel\database\migrations\2025_12_29_203044_add_certificador_id_to_empleado_servicio_table.php`
- `D:\laragon\www\zona_capital_laravel\database\zoncap_zonacap(1).sql`

<task_metadata>
session_id: ses_448499ec7ffeQmjwCva8IbD1Yi
</task_metadata>
```
```

---

## User

que paso

---

## Assistant (Plan · glm-4.7-free · 22.7s)

_Thinking:_

El usuario me está preguntando "qué pasó" en el contexto del análisis que acabo de completar sobre qué sucede cuando se le quita el estatus de jefe a una persona en el sistema.

El análisis que completó el agente exploratorio reveló que:

1. **NO hay manejo automático** cuando se cambia el idCargo de 1-4 a 0
2. **NO hay validación** que impida quitar el estatus de jefe si hay empleados dependientes
3. **NO hay lógica en cascada** en el modelo o controlador
4. **NO hay restricciones de base de datos** ni eventos del modelo
5. **NO hay funciones** que limpien referencias cuando cambia el cargo

El usuario probablemente quiere entender el resumen de los hallazgos. Debo dar un resumen claro y conciso en español.

Además, debo recordar que estoy en modo PLAN - solo lectura, así que no puedo hacer cambios, solo analizar y presentar un plan si el usuario lo solicita.

Basado en el análisis del código de tu aplicación Laravel, **aquí está lo que pasa** cuando se le quita el estatus de jefe a una persona:

## 🚨 PROBLEMA CRÍTICO: **NO HAY MANEJO AUTOMÁTICO**

### ¿Qué ocurre exactamente?

1. **Referencias huérfanas**: Los empleados que tenían a esa persona como su `IdEmpleado2` (certificador) quedan apuntando a alguien que ya no es jefe

2. **Jerarquía rota**: Las consultas que filtran por jefes activos arrojarán resultados incorrectos

3. **Inconsistencia en tabla pivote**: El campo `certificador_id` en `empleado_servicio` puede apuntar a alguien sin estatus de jefe

### ¿Por qué sucede esto?

En el código que analicé:

**PersonalController.php:**
```php
// Línea 1318 - Solo guarda el valor, sin validación ni cascada
$empleado->idCargo = (int)($request->cargo ?? 0);
$empleado->save();
```

No existe:
- ❌ Validación que cheque si hay empleados dependientes
- ❌ Código que actualice los `IdEmpleado2` de los subordinados
- ❌ Eventos del modelo que manejen esto
- ❌ Restricciones de base de datos con CASCADE

### ¿Quieres que diseñe una solución?

Puedo implementar cualquiera de estas opciones:

1. **Validación**: Bloquear el cambio si hay empleados dependientes
2. **Cascada automática**: Actualizar automáticamente los empleados subordinados
3. **Asignación de nuevo jefe**: Forzar a seleccionar un nuevo jefe antes de quitar el estatus

¿Cuál prefieres?

---

