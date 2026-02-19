<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Http\Controllers\GatewayController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ModeloEjemploController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductividadController;
use App\Http\Controllers\SaludMentalController;
use App\Http\Controllers\PersonalController;
use App\Http\Controllers\InformePersonalController;
use App\Http\Controllers\InformeNovedadesController;
use App\Http\Controllers\PapController;
use App\Http\Controllers\RelojController;
use App\Http\Controllers\BienesController;
use App\Http\Controllers\CategoriasController;
use App\Http\Controllers\DepositosController;
use App\Http\Controllers\LicenciasController;
use App\Http\Controllers\ImportHorariosController;
use App\Http\Controllers\FeriadoController;
use App\Http\Controllers\DisposicionController;
use App\Http\Controllers\OrganigramaController;
use App\Http\Controllers\OrdenMedicaController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\MovConsumosController;
use App\Http\Controllers\RegistrosDengueController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\PedidoInternoController;
use App\Http\Controllers\ActaRecepcionController;
use App\Http\Controllers\OrdenCompraController;
use App\Http\Controllers\OrdenPagoController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\DeudaController;
// use App\Http\Controllers\NotaCreditoController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\PasswordSetupController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\HijoController;
use App\Http\Controllers\MotivoLicenciaController;
use Illuminate\Support\Facades\DB;

// Ruta raíz que redirige al login (fuera de middleware)
Route::get('/', function () {
    return redirect('/login');
});

Route::get('/clear-all-cache', function () {
    if (app()->runningInConsole()) {
        return "Esta ruta debe ser accedida desde el navegador, no desde la consola.";
    }

    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('config:cache');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('event:clear');

    return "✅ Todas las cachés fueron limpiadas y regeneradas.";
});

// Página de login (fuera de middleware)
Route::get('/login', function () {
    return view('login');
})->name('login');

// Home route

Route::get('/gateway/login', [GatewayController::class, 'showLoginForm'])->name('gateway.login.form');
Route::post('/gateway/login', [GatewayController::class, 'login'])->name('gateway.login');

// Flujo de configuración de nueva contraseña (fuera de middleware de sesión)
Route::get('/password/setup', [PasswordSetupController::class, 'show'])->name('password.setup');
Route::post('/password/setup', [PasswordSetupController::class, 'store'])->name('password.setup.store');


Route::middleware(['check.session'])->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    // Rutas que reciben la sesión iniciada
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Rutas para Modelo Ejemplo (agrupadas)
    Route::prefix('modelo-ejemplo')->middleware('puede.ver')->group(function () {
        Route::get('/', [ModeloEjemploController::class, 'index'])->name('modelo-ejemplo');
        Route::get('/buscar', [ModeloEjemploController::class, 'buscar'])->name('modelo-ejemplo.buscar');
    });

    // Ejemplo de módulo migrado con sistema de permisos
    Route::get('/ejemplo-modulo', [App\Http\Controllers\EjemploModuloController::class, 'index'])->name('ejemplo-modulo');

    // Rutas para Productividad (agrupadas)
    Route::prefix('productividad')->middleware('puede.ver')->group(function () {
        Route::get('/', [ProductividadController::class, 'index'])->name('productividad');
        Route::get('/filtrar', [ProductividadController::class, 'getProductividad'])->name('productividad.filtrar');
        Route::get('/exportar', [ProductividadController::class, 'exportarExcel'])->name('productividad.exportar');
        Route::delete('/eliminar/{id}', [ProductividadController::class, 'eliminar'])->name('productividad.eliminar');
        Route::get('/{id}', [ProductividadController::class, 'getById'])->name('productividad.get');
        Route::post('/', [ProductividadController::class, 'store'])->name('productividad.store');
        Route::put('/{id}', [ProductividadController::class, 'update'])->name('productividad.update');
        Route::get('/meses-cerrados', [ProductividadController::class, 'getMesesCerrados'])->name('productividad.meses-cerrados');
        Route::post('/cerrar-mes', [ProductividadController::class, 'cerrarMes'])->name('productividad.cerrar-mes');
    });

    // Rutas para Salud Mental (agrupadas)
    Route::prefix('salud-mental')->middleware('puede.ver')->group(function () {
        Route::get('/', [SaludMentalController::class, 'index'])->name('salud-mental');
        Route::get('/registros', [SaludMentalController::class, 'getRegistros'])->name('salud-mental.filtrar');
        Route::get('/exportar', [SaludMentalController::class, 'exportarExcel'])->name('salud-mental.exportar');
        Route::get('/buscar-paciente', [SaludMentalController::class, 'buscarPaciente'])->name('salud-mental.buscar-paciente');
        Route::get('/{id}', [SaludMentalController::class, 'getById'])->name('salud-mental.get');
        Route::post('/', [SaludMentalController::class, 'store'])->name('salud-mental.store');
        Route::put('/{id}', [SaludMentalController::class, 'update'])->name('salud-mental.update');
        Route::delete('/{id}', [SaludMentalController::class, 'destroy'])->name('salud-mental.eliminar');
    });

    // Rutas para Personal (agrupadas)
    Route::prefix('personal')->middleware('puede.ver')->group(function () {
        Route::get('/', [PersonalController::class, 'index'])->name('personal');
        Route::get('/filtrar', [PersonalController::class, 'getPersonal'])->name('personal.filtrar');
        Route::get('/departamentos', [PersonalController::class, 'getDepartamentos'])->name('personal.departamentos');
        Route::get('/servicios', [PersonalController::class, 'getServicios'])->name('personal.servicios');
        Route::get('/sectores', [PersonalController::class, 'getSectores'])->name('personal.sectores');
        Route::get('/jefe-servicio', [PersonalController::class, 'getJefeServicio'])->name('personal.jefe-servicio');
        Route::get('/empleados-activos', [PersonalController::class, 'getEmpleadosActivos'])->name('personal.empleados-activos');
        Route::get('/localidades', [PersonalController::class, 'getLocalidades'])->name('personal.localidades');
        Route::get('/selectores-iniciales', [PersonalController::class, 'getSelectoresIniciales'])->name('personal.selectores');
        Route::get('/buscar-jefe', [PersonalController::class, 'buscarJefe'])->name('personal.buscar-jefe');
        Route::post('/check-dni', [PersonalController::class, 'checkDniExists'])->name('personal.check-dni');
    });

    // Ruta de búsqueda de personal accesible para typeahead
    Route::get('/personal/buscar', [PersonalController::class, 'buscar'])->name('personal.buscar');

    // Rutas para Personal (continuación)
    Route::prefix('personal')->middleware('puede.ver')->group(function () {
        Route::post('/check-legajo', [PersonalController::class, 'checkLegajoExists'])->name('personal.check-legajo');
        Route::get('/{id}/jornadas', [PersonalController::class, 'getJornadas'])->name('personal.jornadas');
        Route::get('/{id}/ver', [PersonalController::class, 'show'])->name('personal.show');
        // Rutas con parámetros dinámicos al final
        Route::get('/{id}', [PersonalController::class, 'getById'])->name('personal.get');
        Route::post('/', [PersonalController::class, 'store'])->name('personal.store');
        Route::put('/{id}', [PersonalController::class, 'update'])->name('personal.update');
        Route::delete('/{id}', [PersonalController::class, 'destroy'])->name('personal.destroy');
    });

    // Rutas para Hijos (dentro del grupo de Personal)
    Route::prefix('personal/{empleadoId}/hijos')->middleware(['check.session', 'puede.ver'])->group(function () {
        Route::get('/gestionar', [HijoController::class, 'gestionar'])->name('hijos.gestionar');
        Route::get('/', [HijoController::class, 'index'])->name('hijos.index');
        Route::get('/{id}', [HijoController::class, 'show'])->name('hijos.show');
        Route::post('/', [HijoController::class, 'store'])->name('hijos.store');
        Route::put('/{id}', [HijoController::class, 'update'])->name('hijos.update');
        Route::delete('/{id}', [HijoController::class, 'destroy'])->name('hijos.destroy');
        Route::get('/formulario/subsidio-familiar/pdf', [HijoController::class, 'generarFormularioSubsidio'])->name('hijos.formulario.subsidio.pdf');
    });

    // Rutas para Pap (agrupadas)
    Route::prefix('pap')->middleware('puede.ver')->group(function () {
        Route::get('/', [PapController::class, 'index'])->name('pap');
        Route::get('/filtrar', [PapController::class, 'getRegistros'])->name('pap.filtrar');
        Route::get('/exportar', [PapController::class, 'exportarExcel'])->name('pap.exportar');
        Route::get('/buscar-paciente', [PapController::class, 'buscarPaciente'])->name('pap.buscar-paciente');
        Route::get('/{id}', [PapController::class, 'getById'])->name('pap.get');
        Route::post('/', [PapController::class, 'store'])->name('pap.store');
        Route::put('/{id}', [PapController::class, 'update'])->name('pap.update');
        Route::delete('/{id}', [PapController::class, 'destroy'])->name('pap.destroy');
    });

    // Rutas para Informe Personal (agrupadas)
    Route::prefix('informe-personal')->middleware('puede.ver')->group(function () {
        Route::get('/', [InformePersonalController::class, 'index'])->name('informe-personal');
        Route::get('/filtrar', [InformePersonalController::class, 'getPersonasInforme'])->name('informe-personal.filtrar');
        Route::get('/exportar', [InformePersonalController::class, 'exportarInforme'])->name('informe-personal.exportar');
        Route::get('/departamentos', [InformePersonalController::class, 'getDepartamentosXGer'])->name('informe-personal.departamentos');
        Route::get('/servicios', [InformePersonalController::class, 'getServiciosXDep'])->name('informe-personal.servicios');
        Route::get('/sectores', [InformePersonalController::class, 'getSectoresXServ'])->name('informe-personal.sectores');
    });

    // Rutas para Informe de Novedades (agrupadas)
    Route::prefix('informe-novedades')->middleware('puede.ver')->group(function () {
        Route::get('/', [InformeNovedadesController::class, 'index'])->name('informe-novedades');
        Route::get('/exportar', [InformeNovedadesController::class, 'exportar'])->name('informe-novedades.exportar');
        Route::get('/departamentos', [InformeNovedadesController::class, 'getDepartamentosXGer'])->name('informe-novedades.departamentos');
        Route::get('/servicios', [InformeNovedadesController::class, 'getServiciosXDep'])->name('informe-novedades.servicios');
    });

    // Rutas para Reloj (agrupadas)
    Route::prefix('reloj')->middleware('puede.ver')->group(function () {
        Route::get('/', [RelojController::class, 'index'])->name('reloj');
        Route::get('/filtrar', [RelojController::class, 'filtrar'])->name('reloj.filtrar');
        Route::get('/{id}', [RelojController::class, 'getById'])->name('reloj.get');
        Route::post('/', [RelojController::class, 'store'])->name('reloj.store');
        Route::put('/{id}', [RelojController::class, 'update'])->name('reloj.update');
        Route::delete('/{id}', [RelojController::class, 'destroy'])->name('reloj.destroy');
    });

    // Rutas para Bienes (agrupadas)
    Route::prefix('bienes')->middleware('puede.ver')->group(function () {
        Route::get('/', [BienesController::class, 'index'])->name('bienes');
        Route::get('/filtrar', [BienesController::class, 'filtrar'])->name('bienes.filtrar');
        Route::get('/{id}', [BienesController::class, 'getById'])->name('bienes.get');
        Route::post('/', [BienesController::class, 'store'])->name('bienes.store');
        Route::put('/{id}', [BienesController::class, 'update'])->name('bienes.update');
        Route::delete('/{id}', [BienesController::class, 'destroy'])->name('bienes.destroy');
    });

    // Rutas para Categorias (agrupadas)
    Route::prefix('categorias')->middleware('puede.ver')->group(function () {
        Route::get('/', [CategoriasController::class, 'index'])->name('categorias');
        Route::post('/', [CategoriasController::class, 'store'])->name('categorias.store');
        Route::put('/{id}', [CategoriasController::class, 'update'])->name('categorias.update');
        Route::delete('/{id}', [CategoriasController::class, 'destroy'])->name('categorias.destroy');
    });

    // Rutas para Proveedores (agrupadas)
    Route::prefix('proveedores')->middleware('puede.ver')->group(function () {
        Route::get('/', [ProveedorController::class, 'index'])->name('proveedores');
        Route::get('/get-proveedores', [ProveedorController::class, 'getProveedores'])->name('proveedores.get-proveedores');
        Route::get('/get-condiciones-iva', [ProveedorController::class, 'getCondicionesIva'])->name('proveedores.condiciones-iva');
        Route::get('/get-tipo-empresas', [ProveedorController::class, 'getTipoEmpresas'])->name('proveedores.tipo-empresas');
        Route::get('/get-provincias', [ProveedorController::class, 'getProvincias'])->name('proveedores.provincias');
        Route::get('/get-todos', [ProveedorController::class, 'getTodos'])->name('proveedores.todos');
        Route::get('/{id}', [ProveedorController::class, 'getById'])->name('proveedores.get');
        Route::post('/', [ProveedorController::class, 'store'])->name('proveedores.store');
        Route::post('/add-obs', [ProveedorController::class, 'addObs'])->name('proveedores.add-obs');
        Route::post('/change-sts-msj', [ProveedorController::class, 'changeStsMsj'])->name('proveedores.change-sts-msj');
        Route::put('/{id}', [ProveedorController::class, 'update'])->name('proveedores.update');
        Route::delete('/{id}', [ProveedorController::class, 'destroy'])->name('proveedores.destroy');
    });

    // Rutas para Cotizaciones (agrupadas)
    Route::prefix('cotizaciones')->middleware('puede.ver')->group(function () {
        Route::get('/', [CotizacionController::class, 'index'])->name('cotizaciones');
        Route::get('/bienes-por-pedido-interno/{id}', [CotizacionController::class, 'getBienesPorPedidoInterno'])->name('cotizaciones.bienes-por-pedido-interno');
        Route::get('/info-pedido-interno/{id}', [CotizacionController::class, 'getInfoPedidoInterno'])->name('cotizaciones.info-pedido-interno');
        Route::get('/get-cotizacion/{id}', [CotizacionController::class, 'getCotizacion'])->name('cotizaciones.get-cotizacion');
        Route::get('/get-proveedores', [CotizacionController::class, 'getProveedores'])->name('cotizaciones.get-proveedores');
        Route::post('/', [CotizacionController::class, 'store'])->name('cotizaciones.store');
        Route::put('/{id}', [CotizacionController::class, 'update'])->name('cotizaciones.update');
        Route::put('/update-premiado/{id}', [CotizacionController::class, 'updatePremiado'])->name('cotizaciones.update-premiado');
        Route::delete('/{id}', [CotizacionController::class, 'destroy'])->name('cotizaciones.destroy');
        Route::delete('/proveedor/{pixbId}/{proveedorId}', [CotizacionController::class, 'deleteProveedor'])->name('cotizaciones.delete-proveedor');
        Route::post('/update-estado', [CotizacionController::class, 'updateEstado'])->name('cotizaciones.update-estado');
        Route::post('/marcar-ganadores', [CotizacionController::class, 'marcarGanadores'])->name('cotizaciones.marcar-ganadores');
        Route::post('/actualizar-observacion', [CotizacionController::class, 'actualizarObservacion'])->name('cotizaciones.actualizar-observacion');
    });

    // Rutas para Pedidos Internos (agrupadas)
    Route::prefix('pedidos-internos')->middleware('puede.ver')->group(function () {
        Route::get('/', [PedidoInternoController::class, 'index'])->name('pedidos-internos');
        Route::get('/get-pedidos-internos', [PedidoInternoController::class, 'getPedidosInternos'])->name('pedidos-internos.get-pedidos-internos');
        Route::get('/get-bienes', [PedidoInternoController::class, 'getBienes'])->name('pedidos-internos.get-bienes');
        Route::get('/get-bienes-inventario', [PedidoInternoController::class, 'getBienesInventario'])->name('pedidos-internos.get-bienes-inventario');
        Route::get('/get-servicios', [PedidoInternoController::class, 'getServicios'])->name('pedidos-internos.get-servicios');
        Route::get('/{id}', [PedidoInternoController::class, 'getById'])->name('pedidos-internos.get');
        Route::post('/', [PedidoInternoController::class, 'store'])->name('pedidos-internos.store');
        Route::post('/agregar-bien', [PedidoInternoController::class, 'agregarBien'])->name('pedidos-internos.agregar-bien');
        Route::put('/{id}', [PedidoInternoController::class, 'update'])->name('pedidos-internos.update');
        Route::delete('/{id}', [PedidoInternoController::class, 'destroy'])->name('pedidos-internos.destroy');
        Route::delete('/bien/{id}', [PedidoInternoController::class, 'eliminarBien'])->name('pedidos-internos.eliminar-bien');
    });

    // Rutas para Actas de Recepción (agrupadas)
    Route::prefix('actas-recepcion')->middleware('puede.ver')->group(function () {
        Route::get('/', [ActaRecepcionController::class, 'index'])->name('actas-recepcion');
        Route::get('/get-actas', [ActaRecepcionController::class, 'getActas'])->name('actas-recepcion.get-actas');
        Route::get('/get-proveedores', [ActaRecepcionController::class, 'getProveedores'])->name('actas-recepcion.get-proveedores');
        Route::get('/get-bienes', [ActaRecepcionController::class, 'getBienes'])->name('actas-recepcion.get-bienes');
        Route::get('/get-orden-compra/{id}', [ActaRecepcionController::class, 'getOrdenCompra'])->name('actas-recepcion.get-orden-compra');
        Route::get('/{id}', [ActaRecepcionController::class, 'getById'])->name('actas-recepcion.get');
        Route::post('/', [ActaRecepcionController::class, 'store'])->name('actas-recepcion.store');
        Route::post('/agregar-bien', [ActaRecepcionController::class, 'agregarBien'])->name('actas-recepcion.agregar-bien');
        Route::put('/{id}', [ActaRecepcionController::class, 'update'])->name('actas-recepcion.update');
        Route::delete('/{id}', [ActaRecepcionController::class, 'destroy'])->name('actas-recepcion.destroy');
        Route::delete('/bien/{id}', [ActaRecepcionController::class, 'eliminarBien'])->name('actas-recepcion.eliminar-bien');
    });

    // Rutas para Órdenes de Compra (agrupadas)
    Route::prefix('ordenes-compra')->middleware('puede.ver')->group(function () {
        Route::get('/', [OrdenCompraController::class, 'index'])->name('ordenes-compra');
        Route::get('/get-ordenes-compra', [OrdenCompraController::class, 'getOrdenesCompra'])->name('ordenes-compra.get-ordenes-compra');
        Route::get('/buscar-por-pedido-interno/{id}', [OrdenCompraController::class, 'buscarPorPedidoInterno'])->name('ordenes-compra.buscar-por-pedido-interno');
        Route::get('/get-proveedores', [OrdenCompraController::class, 'getProveedores'])->name('ordenes-compra.get-proveedores');
        Route::get('/get-observaciones', [OrdenCompraController::class, 'getObservaciones'])->name('ordenes-compra.get-observaciones');
        Route::post('/agregar-observacion', [OrdenCompraController::class, 'agregarObservacion'])->name('ordenes-compra.agregar-observacion');
        Route::get('/{id}', [OrdenCompraController::class, 'getById'])->name('ordenes-compra.get');
        Route::post('/', [OrdenCompraController::class, 'store'])->name('ordenes-compra.store');
        Route::put('/{id}', [OrdenCompraController::class, 'update'])->name('ordenes-compra.update');
        Route::delete('/{id}', [OrdenCompraController::class, 'destroy'])->name('ordenes-compra.destroy');
        Route::delete('/eliminar-bien/{id}', [OrdenCompraController::class, 'eliminarBienOrden'])->name('ordenes-compra.eliminar-bien');
    });

    // Rutas para Pagos (agrupadas)
    Route::prefix('pagos')->middleware('puede.ver')->group(function () {
        Route::get('/', [PagoController::class, 'index'])->name('pagos');
        Route::get('/get-pagos', [PagoController::class, 'getPagos'])->name('pagos.get-pagos');
        Route::get('/get-bienes', [PagoController::class, 'getBienes'])->name('pagos.get-bienes');
        Route::get('/get-proveedores', [PagoController::class, 'getProveedores'])->name('pagos.get-proveedores');
        Route::get('/get-expedientes', [PagoController::class, 'getExpedientes'])->name('pagos.get-expedientes');
        Route::get('/generar-numero-letra', [PagoController::class, 'generarNumeroLetra'])->name('pagos.generar-numero-letra');
        Route::get('/{id}', [PagoController::class, 'getById'])->name('pagos.get');
        Route::get('/bienes/{id}', [PagoController::class, 'getBienesPago'])->name('pagos.get-bienes-pago');
        Route::post('/', [PagoController::class, 'store'])->name('pagos.store');
        Route::post('/agregar-bien-pago', [PagoController::class, 'agregarBienPago'])->name('pagos.agregar-bien-pago');
        Route::put('/{id}', [PagoController::class, 'update'])->name('pagos.update');
        Route::delete('/{id}', [PagoController::class, 'destroy'])->name('pagos.destroy');
        Route::delete('/bien/{id}', [PagoController::class, 'eliminarBienPago'])->name('pagos.eliminar-bien-pago');
    });

    Route::prefix('ordenes-pago')->middleware('puede.ver')->group(function () {
        Route::get('/', [OrdenPagoController::class, 'index'])->name('ordenes-pago');
        Route::get('/get-ordenes-pago', [OrdenPagoController::class, 'getOrdenesPago'])->name('ordenes-pago.get-ordenes-pago');
        Route::get('/get-bienes', [OrdenPagoController::class, 'getBienes'])->name('ordenes-pago.get-bienes');
        Route::get('/get-proveedores', [OrdenPagoController::class, 'getProveedores'])->name('ordenes-pago.get-proveedores');
        Route::get('/get-expedientes', [OrdenPagoController::class, 'getExpedientes'])->name('ordenes-pago.get-expedientes');
        Route::get('/generar-numero', [OrdenPagoController::class, 'generarNumero'])->name('ordenes-pago.generar-numero');
        Route::get('/{id}', [OrdenPagoController::class, 'getById'])->name('ordenes-pago.get');
        Route::get('/bienes/{id}', [OrdenPagoController::class, 'getBienesOrden'])->name('ordenes-pago.get-bienes-orden');
        Route::get('/remitos/{id}', [OrdenPagoController::class, 'getRemitosOrden'])->name('ordenes-pago.get-remitos-orden');
        Route::post('/', [OrdenPagoController::class, 'store'])->name('ordenes-pago.store');
        Route::post('/agregar-bien-orden', [OrdenPagoController::class, 'agregarBienOrden'])->name('ordenes-pago.agregar-bien-orden');
        Route::post('/agregar-remito-orden', [OrdenPagoController::class, 'agregarRemitoOrden'])->name('ordenes-pago.agregar-remito-orden');
        Route::put('/{id}', [OrdenPagoController::class, 'update'])->name('ordenes-pago.update');
        Route::put('/actualizar-total-orden/{id}', [OrdenPagoController::class, 'actualizarTotalOrden'])->name('ordenes-pago.actualizar-total-orden');
        Route::delete('/{id}', [OrdenPagoController::class, 'destroy'])->name('ordenes-pago.destroy');
        Route::delete('/bien/{id}', [OrdenPagoController::class, 'eliminarBienOrden'])->name('ordenes-pago.eliminar-bien-orden');
        Route::delete('/remito/{id}', [OrdenPagoController::class, 'eliminarRemitoOrden'])->name('ordenes-pago.eliminar-remito-orden');
    });

    Route::prefix('deuda')->middleware('puede.ver')->group(function () {
        Route::get('/', [DeudaController::class, 'index'])->name('deuda');
        Route::get('/get-ingresos', [DeudaController::class, 'getIngresos'])->name('deuda.get-ingresos');
        Route::get('/get-ingresos-meses', [DeudaController::class, 'getIngresosMeses'])->name('deuda.get-ingresos-meses');
        Route::get('/get-ingresos-reserva', [DeudaController::class, 'getIngresosReserva'])->name('deuda.get-ingresos-reserva');
        Route::get('/get-reservas', [DeudaController::class, 'getReservas'])->name('deuda.get-reservas');
        Route::get('/get-saldos', [DeudaController::class, 'getSaldos'])->name('deuda.get-saldos');
        Route::post('/store-ingreso', [DeudaController::class, 'storeIngreso'])->name('deuda.store-ingreso');
        Route::post('/store-saldo-inicial', [DeudaController::class, 'storeSaldoInicial'])->name('deuda.store-saldo-inicial');
        Route::post('/store-reserva', [DeudaController::class, 'storeReserva'])->name('deuda.store-reserva');
        Route::post('/store-saldo', [DeudaController::class, 'storeSaldo'])->name('deuda.store-saldo');
        Route::post('/update-saldo-ingreso', [DeudaController::class, 'updateSaldoIngreso'])->name('deuda.update-saldo-ingreso');
        Route::delete('/delete-ingreso/{id}', [DeudaController::class, 'deleteIngreso'])->name('deuda.delete-ingreso');
        Route::delete('/delete-reserva/{id}', [DeudaController::class, 'deleteReserva'])->name('deuda.delete-reserva');
    });

    // Rutas para Depositos (agrupadas)
    Route::prefix('depositos')->middleware('puede.ver')->group(function () {
        Route::get('/', [DepositosController::class, 'index'])->name('depositos');
        Route::get('/filtrar', [DepositosController::class, 'filtrar'])->name('depositos.filtrar');
        Route::get('/{id}', [DepositosController::class, 'getById'])->name('depositos.get');
        Route::post('/', [DepositosController::class, 'store'])->name('depositos.store');
        Route::put('/{id}', [DepositosController::class, 'update'])->name('depositos.update');
        Route::delete('/{id}', [DepositosController::class, 'destroy'])->name('depositos.destroy');
    });

    // Rutas para Licencias (agrupadas)
    Route::prefix('licencias')->middleware('puede.ver')->group(function () {
        Route::get('/', [LicenciasController::class, 'index'])->name('licencias');
        Route::get('/lar', [LicenciasController::class, 'indexLar'])->name('licencias.lar');
        Route::get('/lar-lista', [LicenciasController::class, 'listarLar'])->name('lar-lista');
        Route::get('/lar-lista/filtrar', [LicenciasController::class, 'filtrarLar'])->name('lar-lista.filtrar');
        // Ruta para editar desde la lista (carga el formulario LAR con datos)
        Route::get('/lar/editar/{id}', [LicenciasController::class, 'editarLar'])->name('lar.editar');
        Route::get('/legajo/{legajo}', [LicenciasController::class, 'getLicenciasXLegajo'])->name('licencias.legajo');
        Route::post('/', [LicenciasController::class, 'store'])->name('licencias.store');
        Route::put('/{id}', [LicenciasController::class, 'update'])->name('licencias.update');
        Route::delete('/{id}', [LicenciasController::class, 'destroy'])->name('licencias.destroy');

        // Print routes
        Route::get('/imprimir/lar/{id}', [LicenciasController::class, 'imprimirLar'])->name('licencias.imprimir.lar');
        Route::get('/imprimir/cd/{id}', [LicenciasController::class, 'imprimirCD'])->name('licencias.imprimir.cd');
        Route::get('/imprimir/articulo30/{id}', [LicenciasController::class, 'imprimirArticulo30'])->name('licencias.imprimir.articulo30');
        Route::get('/imprimir/articulo43/{id}', [LicenciasController::class, 'imprimirArticulo43'])->name('licencias.imprimir.articulo43');

        // Date calculation routes
        Route::post('/calcular-fecha', [LicenciasController::class, 'calcularFecha'])->name('licencias.calcular.fecha');
        Route::post('/calcular-dias', [LicenciasController::class, 'calcularDias'])->name('licencias.calcular.dias');

        // LAR operations
        Route::post('/dias-lar', [LicenciasController::class, 'getDiasLar'])->name('licencias.dias.lar');

        // Medical certificate
        Route::post('/certificado-medico', [LicenciasController::class, 'getCertificadoMedico'])->name('licencias.certificado.medico');

        // Motivo operations
        Route::get('/dias-motivo', [LicenciasController::class, 'getDiasMotivoXLegajo'])->name('licencias.dias.motivo');

        // LAR parameters operations
        Route::get('/parametros/{legajo}', [LicenciasController::class, 'getParametrosLar'])->name('licencias.parametros.get');
        Route::post('/parametro', [LicenciasController::class, 'createParam'])->name('licencias.parametro.create');
        Route::delete('/parametro/{id}', [LicenciasController::class, 'deleteParam'])->name('licencias.parametro.delete');

        // History
        Route::get('/historial/{legajo}', [LicenciasController::class, 'getHistorial'])->name('licencias.historial');
        Route::get('/historial-personal/{personalId}', [LicenciasController::class, 'getHistorialByPersonalId'])->name('licencias.historial.personal');
    });

    // Rutas para Feriados (agrupadas)
    Route::prefix('feriados')->middleware('puede.ver')->group(function () {
        Route::get('/', [FeriadoController::class, 'index'])->name('feriados');
        Route::get('/listar', [FeriadoController::class, 'listar'])->name('feriados.listar');
        Route::post('/generar-fijos', [FeriadoController::class, 'generarFijos'])->name('feriados.generar-fijos');

        // Rutas para gestión de feriados fijos (DEBEN IR ANTES de /{id})
        Route::get('/fijos', [FeriadoController::class, 'feriadosFijos'])->name('feriados.fijos');
        Route::post('/fijos', [FeriadoController::class, 'storeFeriadoFijo'])->name('feriados.fijos.store');
        Route::patch('/fijos/{id}/toggle', [FeriadoController::class, 'toggleFeriadoFijo'])->name('feriados.fijos.toggle');
        Route::delete('/fijos/{id}', [FeriadoController::class, 'destroyFeriadoFijo'])->name('feriados.fijos.destroy');

        // Ruta para vista de calendario
        Route::get('/mes', [FeriadoController::class, 'feriadosDelMes'])->name('feriados.mes');

        // Rutas con parámetros (DEBEN IR AL FINAL)
        Route::get('/{id}', [FeriadoController::class, 'show'])->name('feriados.show');
        Route::post('/', [FeriadoController::class, 'store'])->name('feriados.store');
        Route::put('/{id}', [FeriadoController::class, 'update'])->name('feriados.update');
        Route::delete('/{id}', [FeriadoController::class, 'destroy'])->name('feriados.destroy');
    });

    // Rutas para Disposiciones (agrupadas)
    Route::prefix('disposiciones')->middleware('puede.ver')->group(function () {
        Route::get('/', [DisposicionController::class, 'index'])->name('disposiciones');
        Route::get('/listar', [DisposicionController::class, 'listar'])->name('disposiciones.listar');
        Route::get('/buscar', [DisposicionController::class, 'buscar'])->name('disposiciones.buscar');
        Route::get('/proximo-numero', [DisposicionController::class, 'getProximoNumero'])->name('disposiciones.proximo-numero');
        Route::get('/estadisticas', [DisposicionController::class, 'getEstadisticas'])->name('disposiciones.estadisticas');
        Route::get('/{id}', [DisposicionController::class, 'show'])->name('disposiciones.show');
        Route::post('/', [DisposicionController::class, 'store'])->name('disposiciones.store');
        Route::put('/{id}', [DisposicionController::class, 'update'])->name('disposiciones.update');
        Route::delete('/{id}', [DisposicionController::class, 'destroy'])->name('disposiciones.destroy');
        // Creación rápida desde otros módulos
        Route::post('/rapida', [DisposicionController::class, 'storeRapido'])->name('disposiciones.rapida');
    });

    // Rutas para Organigrama (agrupadas)
    Route::prefix('organigrama')->middleware('puede.ver')->group(function () {
        Route::get('/', [OrganigramaController::class, 'index'])->name('organigrama');
        Route::get('/get', [OrganigramaController::class, 'getOrganigrama'])->name('organigrama.get');
        Route::post('/add', [OrganigramaController::class, 'addNodo'])->name('organigrama.add');
        Route::post('/modificar', [OrganigramaController::class, 'modificarNodo'])->name('organigrama.modificar');
        Route::post('/eliminar', [OrganigramaController::class, 'eliminarNodo'])->name('organigrama.eliminar');
        Route::get('/empleados-disponibles', [OrganigramaController::class, 'getEmpleadosDisponibles'])->name('organigrama.empleados-disponibles');
        Route::post('/asignar-jefe', [OrganigramaController::class, 'asignarJefe'])->name('organigrama.asignar-jefe');
    });

    // Rutas para Orden Médicas (agrupadas)
    Route::prefix('orden-medicas')->middleware('puede.ver')->group(function () {
        Route::get('/', [OrdenMedicaController::class, 'index'])->name('orden-medicas');
        Route::get('/filtrar', [OrdenMedicaController::class, 'filtrar'])->name('orden-medicas.filtrar');
        Route::get('/ultimo-numero', [OrdenMedicaController::class, 'ultimoNumero'])->name('orden-medicas.ultimo-numero');
        Route::get('/proximo-certificado', [LicenciasController::class, 'obtenerProximoCertificadoMedico'])->name('orden-medicas.proximo-certificado');
        Route::get('/{licencia}', [OrdenMedicaController::class, 'show'])->name('orden-medicas.get');
        Route::get('/{licencia}/imprimir', [OrdenMedicaController::class, 'imprimir'])->name('orden-medicas.imprimir');
        // CRUD de Orden Médicas
        Route::post('/', [OrdenMedicaController::class, 'store'])->name('orden-medicas.store');
        Route::put('/{licencia}', [OrdenMedicaController::class, 'update'])->name('orden-medicas.update');
        Route::delete('/{licencia}', [OrdenMedicaController::class, 'destroy'])->name('orden-medicas.destroy');
    });

    // Rutas para Log (agrupadas)
    Route::prefix('log')->middleware('puede.ver')->group(function () {
        Route::get('/', [LogController::class, 'index'])->name('log');
        Route::get('/filtrar', [LogController::class, 'filtrar'])->name('log.filtrar');
    });

    // Rutas para Mov. Consumos (agrupadas)
    Route::prefix('mov-consumos')->middleware('puede.ver')->group(function () {
        Route::get('/', [MovConsumosController::class, 'index'])->name('mov-consumos');
        Route::get('/filtrar', [MovConsumosController::class, 'filtrar'])->name('mov-consumos.filtrar');
        Route::get('/{id}', [MovConsumosController::class, 'getById'])->name('mov-consumos.get');
        Route::post('/', [MovConsumosController::class, 'store'])->name('mov-consumos.store');
        Route::put('/{id}', [MovConsumosController::class, 'update'])->name('mov-consumos.update');
        Route::delete('/{id}', [MovConsumosController::class, 'destroy'])->name('mov-consumos.destroy');
    });

    // Rutas para Nota Crédito (agrupadas) - Temporalmente comentadas
    /*Route::prefix('nota-credito')->middleware('puede.ver')->group(function () {
        Route::get('/', [NotaCreditoController::class, 'index'])->name('nota-credito');
        Route::get('/filtrar', [NotaCreditoController::class, 'filtrar'])->name('nota-credito.filtrar');
        Route::get('/ultimo-numero', [NotaCreditoController::class, 'getUltimoNumero'])->name('nota-credito.ultimo-numero');
        Route::get('/buscar-doc', [NotaCreditoController::class, 'buscarDoc'])->name('nota-credito.buscar-doc');
        Route::get('/{id}', [NotaCreditoController::class, 'getById'])->name('nota-credito.get');
        Route::post('/', [NotaCreditoController::class, 'store'])->name('nota-credito.store');
        Route::put('/{id}', [NotaCreditoController::class, 'update'])->name('nota-credito.update');
        Route::delete('/{id}', [NotaCreditoController::class, 'destroy'])->name('nota-credito.destroy');
    });*/
});

// Registros de Dengue
Route::middleware(['check.session'])->group(function () {
    // Registros de Dengue (agrupadas)
    Route::prefix('registros-dengue')->middleware('puede.ver')->group(function () {
        Route::get('/', [RegistrosDengueController::class, 'index'])->name('registros-dengue.index');
        Route::post('/filtrar', [RegistrosDengueController::class, 'filtrar'])->name('registros-dengue.filtrar');
        Route::get('/exportar', [RegistrosDengueController::class, 'exportarCSV'])->name('registros-dengue.exportar');
        Route::get('/exportar-csv', [RegistrosDengueController::class, 'exportarCSV'])->name('registros-dengue.exportar-csv');
        Route::get('/force-csv', function() {
            $content = "SE;FIS;FC;APELLIDO\n1;01/01/2024;02/01/2024;Juan Perez\n2;03/01/2024;04/01/2024;Maria Lopez\n";
            return response($content, 200, [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="test_force.csv"',
                'Cache-Control' => 'no-cache'
            ]);
        });
        Route::get('/buscar-dni', [RegistrosDengueController::class, 'buscarDni'])->name('registros-dengue.buscar-dni');
        Route::get('/informe', [RegistrosDengueController::class, 'informe'])->name('registros-dengue.informe');

        // Rutas para gestión de pacientes
        Route::post('/paciente', [RegistrosDengueController::class, 'storePaciente'])->name('registros-dengue.store-paciente');
        Route::get('/paciente/{id}', [RegistrosDengueController::class, 'getPaciente'])->name('registros-dengue.get-paciente');
        Route::put('/paciente/{id}', [RegistrosDengueController::class, 'updatePaciente'])->name('registros-dengue.update-paciente');

        Route::get('/{id}', [RegistrosDengueController::class, 'getById'])->name('registros-dengue.get');
        Route::post('/', [RegistrosDengueController::class, 'store'])->name('registros-dengue.store');
        Route::put('/{id}', [RegistrosDengueController::class, 'update'])->name('registros-dengue.update');
        Route::delete('/{id}', [RegistrosDengueController::class, 'destroy'])->name('registros-dengue.destroy');
    });
});

// Informe de Dengue (vista)
Route::middleware(['check.session'])->group(function () {
    Route::get('/informe-dengue', [RegistrosDengueController::class, 'informeView'])->name('informe-dengue')->middleware('puede.ver');
});

// Ruta de prueba temporal para debuggear registros dengue (SIN middleware)
Route::get('/test-dengue', function() {
    session(['usuario_id' => 1]); // Simular sesión
    return app(App\Http\Controllers\RegistrosDengueController::class)->index();
})->name('test-dengue');

Route::post('/test-dengue-store', function(Illuminate\Http\Request $request) {
    session(['usuario_id' => 1]); // Simular sesión
    return app(App\Http\Controllers\RegistrosDengueController::class)->store($request);
})->name('test-dengue-store');

// Simular login para pruebas
Route::get('/simular-login/{usuario_id}', function($usuario_id) {
    session(['usuario_id' => intval($usuario_id)]);
    return redirect('/registros-dengue');
})->name('simular-login');

// Rutas para Usuarios (agrupadas)
Route::middleware(['check.session'])->group(function () {
    Route::prefix('usuarios')->middleware('puede.ver')->group(function () {
        Route::get('/', [\App\Http\Controllers\UsuarioController::class, 'index'])->name('usuarios');
        Route::get('/filtrar', [\App\Http\Controllers\UsuarioController::class, 'getUsuarios'])->name('usuarios.filtrar');
        Route::get('/autocomplete', [\App\Http\Controllers\UsuarioController::class, 'usuariosAutocomplete'])->name('usuarios.autocomplete');
        Route::get('/empleados-autocomplete', [\App\Http\Controllers\UsuarioController::class, 'empleadosAutocomplete'])->name('usuarios.empleados-autocomplete');
        Route::get('/por-personal/{id}', [\App\Http\Controllers\UsuarioController::class, 'getByPersonalId'])->name('usuarios.get-by-personal');
        Route::get('/permisos-tipos', [\App\Http\Controllers\UsuarioController::class, 'getPermisosXTiposUsuarios'])->name('usuarios.permisos-tipos');
        Route::get('/tipos-usuarios', [\App\Http\Controllers\UsuarioController::class, 'getTiposUsuarios'])->name('usuarios.tipos-usuarios');
        Route::get('/modulos', [\App\Http\Controllers\UsuarioController::class, 'getModulos'])->name('usuarios.modulos');
        Route::get('/permisos-extras', [\App\Http\Controllers\UsuarioController::class, 'getPermisosExtras'])->name('usuarios.permisos-extras');
        Route::post('/{id}/permiso-modulo', [\App\Http\Controllers\UsuarioController::class, 'togglePermisoModulo'])->name('usuarios.permiso-modulo');
        Route::post('/{id}/permiso-extra', [\App\Http\Controllers\UsuarioController::class, 'togglePermisoExtra'])->name('usuarios.permiso-extra');
        Route::post('/eliminar-masivo', [\App\Http\Controllers\UsuarioController::class, 'eliminarMasivo'])->name('usuarios.eliminar-masivo');
        Route::get('/{id}', [\App\Http\Controllers\UsuarioController::class, 'getById'])->name('usuarios.get');
        Route::post('/', [\App\Http\Controllers\UsuarioController::class, 'store'])->name('usuarios.store');
        Route::put('/{id}', [\App\Http\Controllers\UsuarioController::class, 'update'])->name('usuarios.update');
        Route::delete('/{id}', [\App\Http\Controllers\UsuarioController::class, 'destroy'])->name('usuarios.destroy');
    });

    // Rutas para Roles (RBAC) protegidas por permiso de ver
    Route::prefix('roles')->middleware('puede.ver')->group(function () {
        Route::get('/', [\App\Http\Controllers\RolesController::class, 'index'])->name('roles');
        Route::get('/list', [\App\Http\Controllers\RolesController::class, 'getRoles'])->name('roles.list');
        Route::post('/', [\App\Http\Controllers\RolesController::class, 'store'])->name('roles.store');
        Route::put('/{id}', [\App\Http\Controllers\RolesController::class, 'update'])->name('roles.update');
        Route::delete('/{id}', [\App\Http\Controllers\RolesController::class, 'destroy'])->name('roles.destroy');
        Route::get('/modulos', [\App\Http\Controllers\RolesController::class, 'getModulos'])->name('roles.modulos');
        Route::get('/{id}/permisos', [\App\Http\Controllers\RolesController::class, 'getPermisos'])->name('roles.permisos');
        Route::post('/{id}/toggle-permiso-modulo', [\App\Http\Controllers\RolesController::class, 'togglePermisoModulo'])->name('roles.toggle-permiso-modulo');
        Route::get('/{id}/permisos-extras', [\App\Http\Controllers\RolesController::class, 'getPermisosExtras'])->name('roles.permisos-extras');
        Route::post('/{id}/toggle-permiso-extra', [\App\Http\Controllers\RolesController::class, 'togglePermisoExtra'])->name('roles.toggle-permiso-extra');
    });

    // Rutas para Configuración del sistema
    Route::prefix('configuracion')->middleware('puede.ver')->group(function () {
        Route::get('/', [ConfiguracionController::class, 'index'])->name('configuracion.index');
        Route::put('/', [ConfiguracionController::class, 'update'])->name('configuracion.update');
        Route::post('/leyenda', [ConfiguracionController::class, 'guardarLeyenda'])->name('configuracion.leyenda.guardar');
        Route::get('/leyenda/{id}', [ConfiguracionController::class, 'getLeyenda'])->name('configuracion.leyenda.get');
        Route::delete('/leyenda/{id}', [ConfiguracionController::class, 'eliminarLeyenda'])->name('configuracion.leyenda.eliminar');
    });

    // Rutas para Motivos de Licencia (parámetros)
    Route::prefix('motivos-licencia')->middleware('puede.ver')->group(function () {
        Route::get('/', [MotivoLicenciaController::class, 'index'])->name('motivos-licencia.index');
        Route::post('/', [MotivoLicenciaController::class, 'store'])->name('motivos-licencia.store');
        Route::get('/{id}', [MotivoLicenciaController::class, 'show'])->name('motivos-licencia.show');
        Route::put('/{id}', [MotivoLicenciaController::class, 'update'])->name('motivos-licencia.update');
        Route::delete('/{id}', [MotivoLicenciaController::class, 'destroy'])->name('motivos-licencia.destroy');
        Route::get('/api/por-modulo/{moduloId?}', [MotivoLicenciaController::class, 'getPorModulo'])->name('motivos-licencia.por-modulo');
        Route::post('/api/clasificar-masiva', [MotivoLicenciaController::class, 'clasificarMasiva'])->name('motivos-licencia.clasificar-masiva');
    });

});

// Registro Influenza ETI
Route::middleware(['check.session'])->group(function () {
    $c = \App\Http\Controllers\RegistroTrabajoController::class;
    // Registro ETI (agrupadas)
    Route::prefix('registro-eti')->middleware('puede.ver')->group(function () use ($c) {
        Route::get('/', [$c, 'index'])->name('registro-eti.index');
        Route::get('/filtrar', [$c, 'filtrar'])->name('registro-eti.filtrar');
        Route::get('/buscar-dni', [$c, 'buscarDni'])->name('registro-eti.buscar-dni');
        Route::get('/informe', [$c, 'informe'])->name('registro-eti.informe');
        Route::get('/{id}', [$c, 'getById'])->name('registro-eti.get');
        Route::post('/', [$c, 'store'])->name('registro-eti.store');
        Route::put('/{id}', [$c, 'update'])->name('registro-eti.update');
        Route::delete('/{id}', [$c, 'destroy'])->name('registro-eti.destroy');
    });

    // Informe Reg. Trabajo (vista)
    Route::get('/informe-registro-trabajo', [$c, 'informeView'])->name('informe-registro-trabajo')->middleware('puede.ver');
});

Route::post('/logout', function () {
    \Illuminate\Support\Facades\Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout');

// Rutas de prueba (solo en desarrollo)
if (config('app.debug')) {
    require __DIR__.'/test.php';
}

// Rutas de debug para dengue
if (config('app.debug')) {
    require __DIR__.'/debug.php';
}

// Rutas de test sin middleware
if (config('app.debug')) {
    require __DIR__.'/test-middleware.php';
}

// Ruta test simple
if (config('app.debug')) {
    require __DIR__.'/test-simple.php';
}

// Debug efector
if (config('app.debug')) {
    require __DIR__.'/debug-efector.php';
}

// Test efector directo
if (config('app.debug')) {
    require __DIR__.'/test-efector-directo.php';
}

// Ruta de prueba para feriados
Route::get('/test-feriados', [FeriadoController::class, 'index'])->name('test-feriados');

// Ruta que coincide con la URL del módulo en base de datos
Route::get('/laravel-feriados', [FeriadoController::class, 'index'])->name('feriados-direct');

// Ruta de prueba sin middleware
Route::get('/test-feriados-sin-middleware', function() {
    return view('feriados.index', [
        'permisos' => [
            'crear' => true,
            'leer' => true,
            'editar' => true,
            'eliminar' => true
        ]
    ]);
});

// Ruta para establecer sesión de prueba y probar feriados
Route::get('/setup-test-session', function() {
    // Establecer una sesión de prueba (usuario super admin)
    session(['usuario_id' => 1]); // Asumiendo que el ID 1 existe

    return redirect('/feriados')->with('message', 'Sesión de prueba establecida');
});

// Ruta de prueba para API sin middleware
Route::get('/test-feriados-api', [FeriadoController::class, 'listar'])->name('test-feriados-api');

// Ruta de prueba para feriados fijos sin middleware
Route::get('/test-feriados-fijos', [FeriadoController::class, 'feriadosFijos']);

// Ruta de prueba simple
Route::get('/test-simple', function() {
    return view('test-feriados-simple');
})->name('test.simple');

// Ruta de feriados directa sin layout complejo
Route::get('/feriados-direct', function() {
    return view('feriados-direct');
})->name('feriados.direct');

// Test para modal de feriados fijos
Route::get('/test-modal-feriados-fijos', function() {
    return view('test-feriados-fijos');
});

// Test para show feriado sin middleware
Route::get('/test-show-feriado/{id}', [FeriadoController::class, 'show']);

// Test para organigrama sin middleware
Route::get('/test-organigrama', [OrganigramaController::class, 'index']);
Route::get('/test-organigrama/get', [OrganigramaController::class, 'getOrganigrama']);
Route::get('/test-organigrama-simple', function() {
    return response()->json(['test' => 'funcionando', 'gerencias' => \App\Models\Gerencia::count()]);
});

Route::get('/test-organigrama-debug', function() {
    try {
        $gerencias = \App\Models\Gerencia::all();
        $html = '<ul>';
        foreach($gerencias as $g) {
            $html .= '<li>' . $g->Gerencia . '</li>';
        }
        $html .= '</ul>';
        return response()->json(['success' => true, 'html' => $html]);
    } catch (Exception $e) {
        return response()->json(['success' => false, 'error' => $e->getMessage()]);
    }
});

Route::get('/organigrama-final', [OrganigramaController::class, 'getOrganigrama'])->name('organigrama.test');

// Rutas para Control de Horarios (agrupadas)
Route::prefix('control-horarios')->middleware(['check.session', 'puede.ver'])->group(function () {
    Route::get('/', [App\Http\Controllers\ControlHorariosController::class, 'index'])->name('control-horarios');
});



// RUTA TEMPORAL DE PRUEBA - ELIMINAR DESPUÉS
Route::get('/test-personal/{id}', function ($id) {
    return response()->json([
        'success' => true,
        'data' => [
            'idEmpleado' => $id,
            'apellido' => 'PÉREZ TEST',
            'nombre' => 'JUAN TEST',
            'legajo' => '12345',
            'dni' => '30123456',
            'sexo' => 1,
            'estado' => 1
        ]
    ]);
});
// Rutas para Programación del Personal (agrupadas)
Route::prefix('programacion-personal')->middleware(['check.session', 'puede.ver'])->group(function () {
    Route::get('/', [App\Http\Controllers\ProgramacionPersonalController::class, 'index'])->name('programacion-personal');
    Route::post('/exportar', [App\Http\Controllers\ProgramacionPersonalController::class, 'exportar'])->name('programacion-personal.exportar');
    Route::get('/exportar', [App\Http\Controllers\ProgramacionPersonalController::class, 'exportar'])->name('programacion-personal.exportar-get');
});

// Importación de horarios (relojes)
Route::middleware(['check.session'])->group(function () {
    Route::prefix('import-horarios')->middleware('puede.ver')->group(function () {
        Route::get('/', [ImportHorariosController::class, 'index'])->name('import-horarios');
    });
});
// Rutas API para programación personal (con middleware web para sesiones)
Route::prefix('api/programacion-personal')->middleware(['check.session'])->group(function () {
    // Compatibilidad con el JS original - ruta principal
    Route::get('/obtener', [App\Http\Controllers\ProgramacionPersonalController::class, 'obtenerProgramacion']);
    Route::post('/obtener', [App\Http\Controllers\ProgramacionPersonalController::class, 'obtenerProgramacion']);

    // Gestión de jefes
    Route::get('/jefes', [App\Http\Controllers\ProgramacionPersonalController::class, 'obtenerJefesApi']);

    // Gestión de francos
    Route::post('/franco', [App\Http\Controllers\ProgramacionPersonalController::class, 'guardarFranco']);
    Route::delete('/franco/{id}', [App\Http\Controllers\ProgramacionPersonalController::class, 'eliminarFranco']);

    // Validaciones
    Route::post('/validar-solapamiento', [App\Http\Controllers\ProgramacionPersonalController::class, 'validarSolapamiento']);
    Route::get('/puede-programar/{fecha}', [App\Http\Controllers\ProgramacionPersonalController::class, 'puedeProgramar']);

    // Cálculos
    Route::post('/calcular-horas', [App\Http\Controllers\ProgramacionPersonalController::class, 'calcularHoras']);

    // Obtener datos específicos
    Route::get('/empleado/{id}/fila', [App\Http\Controllers\ProgramacionPersonalController::class, 'actualizarFilaPersonal']);
    Route::get('/empleado/{id}/guardias', [App\Http\Controllers\ProgramacionPersonalController::class, 'obtenerGuardiasEmpleado']);
    Route::get('/empleado/{id}/programacion', [App\Http\Controllers\ProgramacionPersonalController::class, 'obtenerProgramacionEmpleadoApi']);

    // Guardar guardias
    Route::post('/guardias/guardar', [App\Http\Controllers\ProgramacionPersonalController::class, 'guardarGuardias']);

    // Guardar horario por día
    Route::post('/guardar-horario-dia', [App\Http\Controllers\ProgramacionPersonalController::class, 'guardarHorarioXDia']);

    // Guardar programación simple
    Route::post('/guardar-simple', [App\Http\Controllers\ProgramacionPersonalController::class, 'guardarProgramacionSimple']);

    // Eliminar programación
    Route::post('/programacion/eliminar', [App\Http\Controllers\ProgramacionPersonalController::class, 'eliminarHorario']);

    // Ruta para Service.php (compatibilidad con JS original)
    Route::post('/service', function(\Illuminate\Http\Request $request) {
        $action = $request->get('action');
        $controller = new App\Http\Controllers\ProgramacionPersonalController();

        switch($action) {
            case 'guardarHoarioXDia':
                return $controller->guardarHorarioXDia($request);
            case 'guardarProgramacionSimple':
                return $controller->guardarProgramacionSimple($request);
            case 'guardarGuardias':
                return $controller->guardarGuardias($request);
            case 'eliminarHorario':
                return $controller->eliminarHorario($request);
            case 'getProgramacionXPersonal':
                $idEmp = $request->query('idEmp');
                $desde = $request->query('desde');
                $hasta = $request->query('hasta', $desde);

                $programacion = $controller->obtenerProgramacionEmpleado($idEmp, $desde, $hasta);

                return response()->json([
                    'response' => [
                        'programacion' => $programacion
                    ]
                ]);
            default:
                return response()->json(['error' => 'Acción no válida'], 400);
        }
    });

    // Test endpoint
    Route::get('/test', function() {
        return response()->json([
            'status' => 'ok',
            'usuario_id' => session('usuario_id'),
            'timestamp' => now()->toDateTimeString()
        ]);
    });
});

// API Importación de horarios
Route::prefix('api/import-horarios')->middleware(['check.session'])->group(function () {
    Route::get('/', [ImportHorariosController::class, 'listar']);
    Route::post('/', [ImportHorariosController::class, 'store']);
    Route::put('/{id}', [ImportHorariosController::class, 'update']);
    Route::delete('/{id}', [ImportHorariosController::class, 'destroy']);
});

// API para jerarquía recursiva de jefes
Route::prefix('api/programacion-personal')->middleware(['check.session'])->group(function () {
    // Obtener jefes con jerarquía recursiva
    Route::get('/jefes/jerarquia', [App\Http\Controllers\ProgramacionPersonalController::class, 'obtenerJefesRecursivos']);
    
    // Eliminar programación por rango
    Route::delete('/programacion/rango', [App\Http\Controllers\ProgramacionPersonalController::class, 'eliminarRango']);
    
    // Eliminar guardias pagas por rango
    Route::delete('/guardias/pagas', [App\Http\Controllers\ProgramacionPersonalController::class, 'eliminarGuardiasPagas']);
    
    // Ajuste fino de programación
    Route::post('/ajuste-fino', [App\Http\Controllers\ProgramacionPersonalController::class, 'ajusteFino']);
    
    // Exportar Excel por turnos
    Route::get('/exportar-turnos', [App\Http\Controllers\ProgramacionPersonalController::class, 'exportarExcelTurnos']);
    
    // Control de horas por contrato
    Route::get('/control-horas-contrato', [App\Http\Controllers\ProgramacionPersonalController::class, 'controlHorasContrato']);
});

// Descarga de TXT importado
Route::get('/import-horarios/{id}/txt', [ImportHorariosController::class, 'descargar'])->middleware(['check.session']);
