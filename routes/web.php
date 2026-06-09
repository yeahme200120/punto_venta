<?php

use App\Http\Controllers\CajaController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CobranzaController;
use App\Http\Controllers\ContrasenaMaestraController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\FormaPagoController;
use App\Http\Controllers\ImpresoraController;
use App\Http\Controllers\InsumoController;
use App\Http\Controllers\InventarioMovimientoController;
use App\Http\Controllers\LicenciaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ReporteCajaController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\RespaldoController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\TicketConfiguracionController;
use App\Http\Controllers\UnidadMedidaController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\UsuarioPermisoController;
use App\Http\Controllers\VentaController;
use Illuminate\Support\Facades\Route;

// ===== REDIRECCIÓN RAÍZ =====
Route::get('/', function () {
    return redirect('/login');
});

// ===== RUTAS PROTEGIDAS =====
Route::middleware(['auth', 'empresa.activa'])->group(function () {

    // ===== DASHBOARD =====
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware(['permiso:ver_dashboard'])
        ->name('dashboard');
    Route::get('/dashboard/exportar', [DashboardController::class, 'exportar'])
        ->name('dashboard.exportar');

    // ===== DASHBOARD DE CAJA =====
    Route::get('/dashboard-caja', [DashboardController::class, 'index'])
        ->middleware(['permiso:ver_dashboard_caja'])
        ->name('dashboard.caja');

    // ===== EMPRESAS =====
    Route::prefix('empresas')->name('empresas.')->middleware(['auth', 'empresa.activa'])->group(function () {
        Route::get('/', [EmpresaController::class, 'index'])->name('index')->middleware('permiso:ver_empresas');
        Route::get('/create', [EmpresaController::class, 'create'])->name('create')->middleware('permiso:crear_empresas');
        Route::post('/', [EmpresaController::class, 'store'])->name('store')->middleware('permiso:crear_empresas');
        Route::get('/export', [EmpresaController::class, 'export'])->name('export')->middleware('permiso:ver_empresas');
        Route::get('/{empresa}', [EmpresaController::class, 'show'])->name('show')->middleware('permiso:ver_empresas');
        Route::get('/{empresa}/edit', [EmpresaController::class, 'edit'])->name('edit')->middleware('permiso:editar_empresas');
        Route::put('/{empresa}', [EmpresaController::class, 'update'])->name('update')->middleware('permiso:editar_empresas');
        Route::delete('/{empresa}', [EmpresaController::class, 'destroy'])->name('destroy')->middleware('permiso:eliminar_empresas');
    });
    Route::get('/empresa/{empresa}/cambiar', [EmpresaController::class, 'cambiar'])->name('empresa.cambiar');

    // ===== LICENCIAS =====
    Route::prefix('licencias')->name('licencias.')->middleware(['auth', 'empresa.activa'])->group(function () {
        Route::get('/', [LicenciaController::class, 'index'])->name('index')->middleware('permiso:ver_licencias');
        Route::get('/create', [LicenciaController::class, 'create'])->name('create')->middleware('permiso:crear_licencias');
        Route::post('/', [LicenciaController::class, 'store'])->name('store')->middleware('permiso:crear_licencias');
        Route::get('/export', [LicenciaController::class, 'export'])->name('export')->middleware('permiso:ver_licencias');
        Route::get('/{licencia}', [LicenciaController::class, 'show'])->name('show')->middleware('permiso:ver_licencias');
        Route::get('/{licencia}/edit', [LicenciaController::class, 'edit'])->name('edit')->middleware('permiso:editar_licencias');
        Route::put('/{licencia}', [LicenciaController::class, 'update'])->name('update')->middleware('permiso:editar_licencias');
        Route::delete('/{licencia}', [LicenciaController::class, 'destroy'])->name('destroy')->middleware('permiso:eliminar_licencias');
    });

    // ===== PROVEEDORES =====
    Route::prefix('proveedores')->name('proveedores.')->middleware(['auth', 'empresa.activa'])->group(function () {
        Route::get('/', [ProveedorController::class, 'index'])->name('index')->middleware('permiso:ver_proveedores');
        Route::get('/create', [ProveedorController::class, 'create'])->name('create')->middleware('permiso:crear_proveedores');
        Route::post('/', [ProveedorController::class, 'store'])->name('store')->middleware('permiso:crear_proveedores');
        Route::get('/export', [ProveedorController::class, 'export'])->name('export')->middleware('permiso:ver_proveedores');
        Route::get('/{proveedor}', [ProveedorController::class, 'show'])->name('show')->middleware('permiso:ver_proveedores');
        Route::get('/{proveedor}/edit', [ProveedorController::class, 'edit'])->name('edit')->middleware('permiso:editar_proveedores');
        Route::put('/{proveedor}', [ProveedorController::class, 'update'])->name('update')->middleware('permiso:editar_proveedores');
        Route::delete('/{proveedor}', [ProveedorController::class, 'destroy'])->name('destroy')->middleware('permiso:eliminar_proveedores');
        Route::post('/{proveedor}/toggle-activo', [ProveedorController::class, 'toggleActivo'])->name('toggle-activo')->middleware('permiso:editar_proveedores');
    });

    // ===== CLIENTES =====
    Route::prefix('clientes')->name('clientes.')->middleware(['auth', 'empresa.activa'])->group(function () {
        Route::get('/', [ClienteController::class, 'index'])->name('index')->middleware('permiso:ver_clientes');
        Route::get('/create', [ClienteController::class, 'create'])->name('create')->middleware('permiso:crear_clientes');
        Route::post('/', [ClienteController::class, 'store'])->name('store')->middleware('permiso:crear_clientes');
        Route::get('/export', [ClienteController::class, 'export'])->name('export')->middleware('permiso:ver_clientes');
        Route::get('/{cliente}', [ClienteController::class, 'show'])->name('show')->middleware('permiso:ver_clientes');
        Route::get('/{cliente}/edit', [ClienteController::class, 'edit'])->name('edit')->middleware('permiso:editar_clientes');
        Route::put('/{cliente}', [ClienteController::class, 'update'])->name('update')->middleware('permiso:editar_clientes');
        Route::delete('/{cliente}', [ClienteController::class, 'destroy'])->name('destroy')->middleware('permiso:eliminar_clientes');
    });

    // ===== CATEGORÍAS =====
    Route::prefix('categorias')->name('categorias.')->middleware(['auth', 'empresa.activa'])->group(function () {
        Route::get('/', [CategoriaController::class, 'index'])->name('index')->middleware('permiso:ver_categorias');
        Route::get('/create', [CategoriaController::class, 'create'])->name('create')->middleware('permiso:crear_categorias');
        Route::post('/', [CategoriaController::class, 'store'])->name('store')->middleware('permiso:crear_categorias');
        Route::get('/export', [CategoriaController::class, 'export'])->name('export')->middleware('permiso:ver_categorias');
        Route::get('/{categoria}', [CategoriaController::class, 'show'])->name('show')->middleware('permiso:ver_categorias');
        Route::get('/{categoria}/edit', [CategoriaController::class, 'edit'])->name('edit')->middleware('permiso:editar_categorias');
        Route::put('/{categoria}', [CategoriaController::class, 'update'])->name('update')->middleware('permiso:editar_categorias');
        Route::delete('/{categoria}', [CategoriaController::class, 'destroy'])->name('destroy')->middleware('permiso:eliminar_categorias');
    });

    // ===== UNIDADES DE MEDIDA =====
    Route::prefix('unidades-medida')->name('unidades-medida.')->middleware(['auth', 'empresa.activa'])->group(function () {
        Route::get('/', [UnidadMedidaController::class, 'index'])->name('index')->middleware('permiso:ver_unidades_medida');
        Route::get('/create', [UnidadMedidaController::class, 'create'])->name('create')->middleware('permiso:crear_unidades_medida');
        Route::post('/', [UnidadMedidaController::class, 'store'])->name('store')->middleware('permiso:crear_unidades_medida');
        Route::get('/export', [UnidadMedidaController::class, 'export'])->name('export')->middleware('permiso:ver_unidades_medida');
        Route::get('/{unidad_medida}', [UnidadMedidaController::class, 'show'])->name('show')->middleware('permiso:ver_unidades_medida');
        Route::get('/{unidad_medida}/edit', [UnidadMedidaController::class, 'edit'])->name('edit')->middleware('permiso:editar_unidades_medida');
        Route::put('/{unidad_medida}', [UnidadMedidaController::class, 'update'])->name('update')->middleware('permiso:editar_unidades_medida');
        Route::delete('/{unidad_medida}', [UnidadMedidaController::class, 'destroy'])->name('destroy')->middleware('permiso:eliminar_unidades_medida');
        Route::post('/{unidad_medida}/toggle-activo', [UnidadMedidaController::class, 'toggleActivo'])->name('toggle-activo')->middleware('permiso:editar_unidades_medida');
    });

    // ===== PRODUCTOS =====
    Route::prefix('productos')->name('productos.')->middleware(['auth', 'empresa.activa'])->group(function () {
        Route::get('/', [ProductoController::class, 'index'])->name('index')->middleware('permiso:ver_productos');
        Route::get('/create', [ProductoController::class, 'create'])->name('create')->middleware('permiso:crear_productos');
        Route::post('/', [ProductoController::class, 'store'])->name('store')->middleware('permiso:crear_productos');
        Route::get('/export', [ProductoController::class, 'export'])->name('export')->middleware('permiso:ver_productos');
        Route::post('/generar-sku', [ProductoController::class, 'generarSkuUnico'])->name('generar-sku')->middleware('permiso:crear_productos');
        Route::post('/generar-sku-por-nombre', [ProductoController::class, 'generarSkuPorNombre'])->name('generar-sku-por-nombre')->middleware('permiso:crear_productos');
        Route::get('/{producto}', [ProductoController::class, 'show'])->name('show')->middleware('permiso:ver_productos');
        Route::get('/{producto}/edit', [ProductoController::class, 'edit'])->name('edit')->middleware('permiso:editar_productos');
        Route::put('/{producto}', [ProductoController::class, 'update'])->name('update')->middleware('permiso:editar_productos');
        Route::delete('/{producto}', [ProductoController::class, 'destroy'])->name('destroy')->middleware('permiso:eliminar_productos');

        // Imágenes de productos
        Route::post('/{producto}/imagenes', [ProductoController::class, 'subirImagen'])->name('imagenes.subir')->middleware('permiso:editar_productos');
        Route::delete('/{producto}/imagenes/{imagen}', [ProductoController::class, 'eliminarImagen'])->name('imagenes.eliminar')->middleware('permiso:editar_productos');
        Route::put('/{producto}/imagenes/{imagen}/principal', [ProductoController::class, 'imagenPrincipal'])->name('imagenes.principal')->middleware('permiso:editar_productos');

        // Productos relacionados
        Route::post('/{producto}/relacionados', [ProductoController::class, 'agregarRelacionado'])->name('relacionados.agregar')->middleware('permiso:editar_productos');
        Route::delete('/{producto}/relacionados/{relacionado}', [ProductoController::class, 'eliminarRelacionado'])->name('relacionados.eliminar')->middleware('permiso:editar_productos');
    });

    // ===== INSUMOS =====
    Route::prefix('insumos')->name('insumos.')->middleware(['auth', 'empresa.activa'])->group(function () {
        Route::get('/', [InsumoController::class, 'index'])->name('index')->middleware('permiso:ver_insumos');
        Route::get('/create', [InsumoController::class, 'create'])->name('create')->middleware('permiso:crear_insumos');
        Route::post('/', [InsumoController::class, 'store'])->name('store')->middleware('permiso:crear_insumos');
        Route::get('/export', [InsumoController::class, 'export'])->name('export')->middleware('permiso:ver_insumos');
        Route::get('/{insumo}', [InsumoController::class, 'show'])->name('show')->middleware('permiso:ver_insumos');
        Route::get('/{insumo}/edit', [InsumoController::class, 'edit'])->name('edit')->middleware('permiso:editar_insumos');
        Route::put('/{insumo}', [InsumoController::class, 'update'])->name('update')->middleware('permiso:editar_insumos');
        Route::delete('/{insumo}', [InsumoController::class, 'destroy'])->name('destroy')->middleware('permiso:eliminar_insumos');
        Route::post('/{insumo}/toggle-activo', [InsumoController::class, 'toggleActivo'])->name('toggle-activo')->middleware('permiso:editar_insumos');
    });

    // ===== SUCURSALES =====
    Route::prefix('sucursales')->name('sucursales.')->middleware(['auth', 'empresa.activa'])->group(function () {
        Route::get('/', [SucursalController::class, 'index'])->name('index')->middleware('permiso:ver_sucursales');
        Route::get('/create', [SucursalController::class, 'create'])->name('create')->middleware('permiso:crear_sucursales');
        Route::post('/', [SucursalController::class, 'store'])->name('store')->middleware('permiso:crear_sucursales');
        Route::get('/{sucursal}', [SucursalController::class, 'show'])->name('show')->middleware('permiso:ver_sucursales');
        Route::get('/{sucursal}/edit', [SucursalController::class, 'edit'])->name('edit')->middleware('permiso:editar_sucursales');
        Route::put('/{sucursal}', [SucursalController::class, 'update'])->name('update')->middleware('permiso:editar_sucursales');
        Route::delete('/{sucursal}', [SucursalController::class, 'destroy'])->name('destroy')->middleware('permiso:eliminar_sucursales');
    });
    Route::get('/sucursal/{sucursal}/cambiar', [SucursalController::class, 'cambiar'])->name('sucursal.cambiar');

    // ===== IMPRESORAS =====
    Route::prefix('impresoras')->name('impresoras.')->middleware(['auth', 'empresa.activa'])->group(function () {
        Route::get('/', [ImpresoraController::class, 'index'])->name('index')->middleware('permiso:ver_impresoras');
        Route::get('/create', [ImpresoraController::class, 'create'])->name('create')->middleware('permiso:crear_impresoras');
        Route::post('/', [ImpresoraController::class, 'store'])->name('store')->middleware('permiso:crear_impresoras');
        Route::get('/export', [ImpresoraController::class, 'export'])->name('export')->middleware('permiso:ver_impresoras');
        Route::get('/{impresora}', [ImpresoraController::class, 'show'])->name('show')->middleware('permiso:ver_impresoras');
        Route::get('/{impresora}/edit', [ImpresoraController::class, 'edit'])->name('edit')->middleware('permiso:editar_impresoras');
        Route::put('/{impresora}', [ImpresoraController::class, 'update'])->name('update')->middleware('permiso:editar_impresoras');
        Route::delete('/{impresora}', [ImpresoraController::class, 'destroy'])->name('destroy')->middleware('permiso:eliminar_impresoras');
    });

    // ===== FORMAS DE PAGO =====
    Route::prefix('formas-pago')->name('formas_pago.')->middleware(['auth', 'empresa.activa'])->group(function () {
        Route::get('/', [FormaPagoController::class, 'index'])->name('index')->middleware('permiso:ver_formaspago');
        Route::get('/create', [FormaPagoController::class, 'create'])->name('create')->middleware('permiso:crear_formaspago');
        Route::post('/', [FormaPagoController::class, 'store'])->name('store')->middleware('permiso:crear_formaspago');
        Route::get('/{formaPago}/edit', [FormaPagoController::class, 'edit'])->name('edit')->middleware('permiso:editar_formaspago');
        Route::put('/{formaPago}', [FormaPagoController::class, 'update'])->name('update')->middleware('permiso:editar_formaspago');
        Route::delete('/{formaPago}', [FormaPagoController::class, 'destroy'])->name('destroy')->middleware('permiso:eliminar_formaspago');
        Route::post('/{formaPago}/toggle-activo', [FormaPagoController::class, 'toggleActivo'])->name('toggle-activo')->middleware('permiso:editar_formaspago');
    });

    // ===== TICKET =====
    Route::prefix('ticket')->name('ticket.')->middleware(['auth', 'empresa.activa'])->group(function () {
        Route::get('/', [TicketConfiguracionController::class, 'index'])->name('index')->middleware('permiso:ver_ticket');
        Route::get('/create', [TicketConfiguracionController::class, 'create'])->name('create')->middleware('permiso:crear_ticket');
        Route::post('/', [TicketConfiguracionController::class, 'store'])->name('store')->middleware('permiso:crear_ticket');
        Route::get('/diseno', [TicketConfiguracionController::class, 'diseno'])->name('diseno')->middleware('permiso:ver_ticket');
        Route::get('/{ticketConfiguracion}', [TicketConfiguracionController::class, 'show'])->name('show')->middleware('permiso:ver_ticket');
        Route::get('/{ticketConfiguracion}/edit', [TicketConfiguracionController::class, 'edit'])->name('edit')->middleware('permiso:editar_ticket');
        Route::put('/{ticketConfiguracion}', [TicketConfiguracionController::class, 'update'])->name('update')->middleware('permiso:editar_ticket');
        Route::delete('/{ticketConfiguracion}', [TicketConfiguracionController::class, 'destroy'])->name('destroy')->middleware('permiso:eliminar_ticket');
        Route::post('/{ticketConfiguracion}/toggle-activo', [TicketConfiguracionController::class, 'toggleActivo'])->name('toggle-activo')->middleware('permiso:editar_ticket');
    });

    // ===== USUARIOS =====
    Route::prefix('usuarios')->name('usuarios.')->middleware(['auth', 'empresa.activa'])->group(function () {
        Route::get('/', [UsuarioController::class, 'index'])->name('index')->middleware('permiso:ver_usuarios');
        Route::get('/create', [UsuarioController::class, 'create'])->name('create')->middleware('permiso:crear_usuarios');
        Route::post('/', [UsuarioController::class, 'store'])->name('store')->middleware('permiso:crear_usuarios');
        Route::get('/export', [UsuarioController::class, 'export'])->name('export')->middleware('permiso:ver_usuarios');
        Route::get('/{usuario}', [UsuarioController::class, 'show'])->name('show')->middleware('permiso:ver_usuarios');
        Route::get('/{usuario}/edit', [UsuarioController::class, 'edit'])->name('edit')->middleware('permiso:editar_usuarios');
        Route::put('/{usuario}', [UsuarioController::class, 'update'])->name('update')->middleware('permiso:editar_usuarios');
        Route::delete('/{usuario}', [UsuarioController::class, 'destroy'])->name('destroy')->middleware('permiso:eliminar_usuarios');
        Route::put('/{usuario}/toggle-activo', [UsuarioController::class, 'toggleActivo'])->name('toggle-activo')->middleware('permiso:editar_usuarios');
        Route::get('/{usuario}/permisos', [UsuarioPermisoController::class, 'edit'])->name('permisos.edit')->middleware('permiso:editar_usuarios');
        Route::put('/{usuario}/permisos', [UsuarioPermisoController::class, 'update'])->name('permisos.update')->middleware('permiso:editar_usuarios');
    });

    // Perfil (acceso propio)
    Route::get('/perfil', [UsuarioController::class, 'perfil'])->name('perfil.index');
    Route::put('/perfil', [UsuarioController::class, 'updatePerfil'])->name('perfil.update');

    // ===== CONTRASEÑAS MAESTRAS =====
    Route::prefix('perfil')->name('perfil.')->group(function () {
        Route::get('/contraseñas-maestras', [ContrasenaMaestraController::class, 'index'])->name('contraseñas');
        Route::post('/contraseñas-maestras', [ContrasenaMaestraController::class, 'store'])->name('contraseñas.store');
        Route::delete('/contraseñas-maestras/{contraseñaMaestra}', [ContrasenaMaestraController::class, 'destroy'])->name('contraseñas.destroy');
    });

    // ===== ROLES =====
    Route::prefix('roles')->name('roles.')->middleware(['auth', 'empresa.activa'])->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('index')->middleware('permiso:ver_roles');
        Route::get('/create', [RoleController::class, 'create'])->name('create')->middleware('permiso:crear_roles');
        Route::post('/', [RoleController::class, 'store'])->name('store')->middleware('permiso:crear_roles');
        Route::get('/export', [RoleController::class, 'export'])->name('export')->middleware('permiso:ver_roles');
        Route::get('/{role}', [RoleController::class, 'show'])->name('show')->middleware('permiso:ver_roles');
        Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit')->middleware('permiso:editar_roles');
        Route::put('/{role}', [RoleController::class, 'update'])->name('update')->middleware('permiso:editar_roles');
        Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy')->middleware('permiso:eliminar_roles');
    });

    // ===== INVENTARIO MOVIMIENTOS =====
    Route::prefix('inventario')->name('inventario.')->middleware(['auth', 'empresa.activa'])->group(function () {
        Route::get('/movimientos', [InventarioMovimientoController::class, 'index'])->name('movimientos')->middleware('permiso:ver_inventario');
        Route::get('/movimientos/create', [InventarioMovimientoController::class, 'create'])->name('movimientos.create')->middleware('permiso:crear_inventario');
        Route::post('/movimientos', [InventarioMovimientoController::class, 'store'])->name('movimientos.store')->middleware('permiso:crear_inventario');
        Route::get('/movimientos/export', [InventarioMovimientoController::class, 'export'])->name('movimientos.export')->middleware('permiso:ver_inventario');
    });

    // ===== CAJA =====
    Route::prefix('caja')->name('cajas.')->middleware(['auth', 'empresa.activa'])->group(function () {
        // Gestión de cajas
        Route::get('/cajas', [CajaController::class, 'indexCajas'])->name('cajas.index')->middleware('permiso:ver_cajas');
        Route::get('/cajas/create', [CajaController::class, 'createCaja'])->name('cajas.create')->middleware('permiso:crear_caja');
        Route::post('/cajas', [CajaController::class, 'storeCaja'])->name('cajas.store')->middleware('permiso:crear_caja');
        Route::get('/cajas/{caja}/edit', [CajaController::class, 'editCaja'])->name('cajas.edit')->middleware('permiso:editar_caja');
        Route::put('/cajas/{caja}', [CajaController::class, 'updateCaja'])->name('cajas.update')->middleware('permiso:editar_caja');
        Route::delete('/cajas/{caja}', [CajaController::class, 'destroyCaja'])->name('cajas.destroy')->middleware('permiso:eliminar_caja');

        // Apertura/Cierre
        Route::get('/apertura', [CajaController::class, 'aperturaIndex'])->name('apertura')->middleware('permiso:abrir_caja');
        Route::post('/abrir', [CajaController::class, 'abrirCaja'])->name('abrir')->middleware('permiso:abrir_caja');
        Route::post('/cerrar', [CajaController::class, 'cerrarCaja'])->name('cerrar')->middleware('permiso:cerrar_caja');

        // Operaciones
        Route::get('/operaciones', [CajaController::class, 'operaciones'])->name('operaciones')->middleware('permiso:ver_movimientos_caja');
        Route::post('/movimiento', [CajaController::class, 'registrarMovimiento'])->name('movimiento.registrar')->middleware('permiso:registrar_movimiento_caja');

        // Arqueos
        Route::get('/arqueos', [CajaController::class, 'arqueos'])->name('arqueos')->middleware('permiso:realizar_arqueo');
        Route::post('/arqueo/registrar', [CajaController::class, 'registrarArqueo'])->name('arqueo.registrar')->middleware('permiso:realizar_arqueo');
        Route::get('/arqueo/{arqueo}', [CajaController::class, 'verArqueo'])->name('arqueo.ver')->middleware('permiso:ver_arqueos');
        Route::get('/arqueo/{arqueo}/imprimir', [CajaController::class, 'imprimirArqueo'])->name('arqueo.imprimir')->middleware('permiso:imprimir_arqueo_caja');

        // Autorizaciones
        Route::get('/autorizaciones', [CajaController::class, 'autorizacionesPendientes'])->name('autorizaciones')->middleware('permiso:ver_autorizaciones_caja');
        Route::post('/movimiento/{movimientoId}/autorizar', [CajaController::class, 'autorizarMovimiento'])->name('movimiento.autorizar')->middleware('permiso:autorizar_movimiento');

        // Transferencias
        Route::get('/transferencias', [CajaController::class, 'transferencias'])->name('transferencias')->middleware('permiso:ver_transferencias_caja');
        Route::post('/transferencia/solicitar', [CajaController::class, 'solicitarTransferencia'])->name('transferencia.solicitar')->middleware('permiso:solicitar_transferencia_caja');
        Route::post('/transferencia/{transferenciaId}/autorizar', [CajaController::class, 'autorizarTransferencia'])->name('transferencia.autorizar')->middleware('permiso:autorizar_transferencia');

        // Reportes
        Route::get('/reporte/{aperturaId}', [CajaController::class, 'reporteDia'])->name('reporte.dia')->middleware('permiso:ver_reporte_caja_diario');

        // Tickets
        Route::get('/movimiento/{movimiento}/ticket', [CajaController::class, 'imprimirTicketMovimiento'])->name('movimiento.ticket');
        Route::get('/transferencia/{transferencia}/ticket', [CajaController::class, 'imprimirTicketTransferencia'])->name('transferencia.ticket');
        Route::get('/arqueo/{arqueo}/ticket', [CajaController::class, 'imprimirTicketArqueo'])->name('arqueo.ticket');
        Route::get('/cierre/{apertura}/ticket', [CajaController::class, 'imprimirTicketCierre'])->name('cierre.ticket');

        // Retiros
        Route::post('/retiro/registrar', [CajaController::class, 'registrarRetiro'])->name('retiro.registrar')->middleware('permiso:registrar_movimiento_caja');

        // Ver apertura
        Route::get('/apertura/{apertura}/ver', [CajaController::class, 'verApertura'])->name('verApertura')->middleware('permiso:ver_apertura_cierre');
    });

    // ===== REPORTES DE CAJA =====
    Route::prefix('reportes')->name('reportes.')->middleware(['auth', 'empresa.activa'])->group(function () {
        Route::get('/caja-dashboard', [ReporteCajaController::class, 'dashboard'])->name('caja.dashboard')->middleware('permiso:ver_dashboard_caja');
        Route::get('/caja-exportar', [ReporteCajaController::class, 'exportar'])->name('caja.exportar')->middleware('permiso:exportar_reportes_caja');
    });

    // ===== VENTAS =====
    Route::prefix('ventas')->name('ventas.')->middleware(['auth', 'empresa.activa'])->group(function () {
        Route::get('/', [VentaController::class, 'index'])->name('index');
        Route::post('/contado/store', [VentaController::class, 'storeContado'])->name('contado.store');
        Route::post('/credito/store', [VentaController::class, 'storeCredito'])->name('credito.store');
        Route::get('/historial', [VentaController::class, 'historial'])->name('historial');
        Route::get('/{id}/ticket', [VentaController::class, 'ticket'])->name('ticket');
        Route::get('/{id}', [VentaController::class, 'show'])->name('show');
        Route::get('/credito/{creditoId}/pagares', [VentaController::class, 'imprimirPagares'])->name('pagares');
    });

    // ===== COTIZACIONES =====
    Route::prefix('cotizaciones')->name('cotizaciones.')->middleware(['auth', 'empresa.activa'])->group(function () {
        Route::get('/', [CotizacionController::class, 'index'])->name('index');
        Route::post('/store', [CotizacionController::class, 'store'])->name('store');
        Route::get('/{id}/pdf', [CotizacionController::class, 'pdf'])->name('pdf');
        Route::get('/{id}/download', [CotizacionController::class, 'download'])->name('download');
        Route::get('/{id}', [CotizacionController::class, 'show'])->name('show');
        Route::delete('/{id}', [CotizacionController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/cargar-carrito', [CotizacionController::class, 'cargarCarrito'])->name('cargar-carrito');
    });

    // ===== CARRITO =====
    Route::prefix('carrito')->name('carrito.')->middleware(['auth', 'empresa.activa'])->group(function () {
        Route::get('/obtener', [CarritoController::class, 'obtener'])->name('obtener');
        Route::post('/agregar', [CarritoController::class, 'agregar'])->name('agregar');
        Route::delete('/item/{index}', [CarritoController::class, 'eliminarItem'])->name('eliminar-item');
        Route::put('/item/{index}/cantidad', [CarritoController::class, 'actualizarCantidad'])->name('actualizar-cantidad');
        Route::delete('/limpiar', [CarritoController::class, 'limpiar'])->name('limpiar');
        Route::post('/cargar-cotizacion/{cotizacionId}', [CarritoController::class, 'cargarCotizacion'])->name('cargar-cotizacion');
    });

    // ===== COBRANZA =====
    Route::prefix('cobranza')->name('cobranza.')->middleware(['auth', 'empresa.activa'])->group(function () {
        Route::get('/creditos', [CobranzaController::class, 'index'])->name('index')->middleware('permiso:ver_cobranza');
        Route::post('/abono', [CobranzaController::class, 'registrarAbono'])->name('abono.store')->middleware('permiso:registrar_abono');
        Route::post('/pagare/{id}/pagar', [CobranzaController::class, 'pagarPagare'])->name('pagare.pagar')->middleware('permiso:pagar_pagare');
        Route::get('/historial', [CobranzaController::class, 'historialGeneral'])->name('historial')->middleware('permiso:ver_historial_cobranza');
        Route::get('/condonaciones', [CobranzaController::class, 'condonaciones'])->name('condonaciones')->middleware('permiso:ver_condonaciones');
        Route::post('/condonar', [CobranzaController::class, 'condonarAdeudo'])->name('condonar')->middleware('permiso:condonar_adeudo');
        Route::get('/historial/{id}', [CobranzaController::class, 'historialPagos'])->name('historial.pagos')->middleware('permiso:ver_historial_cobros');
        Route::get('/{id}', [CobranzaController::class, 'show'])->name('show')->middleware('permiso:ver_cobranza');
    });

    // ===== REPORTES GENERALES =====
    Route::prefix('reportes')->name('reportes.')->middleware(['auth', 'empresa.activa'])->group(function () {
        Route::get('/ventas', [ReporteController::class, 'ventas'])->name('ventas')->middleware('permiso:ver_reportes');
        Route::get('/inventario', [ReporteController::class, 'inventario'])->name('inventario')->middleware('permiso:ver_reportes');
        Route::get('/cobranza', [ReporteController::class, 'cobranza'])->name('cobranza')->middleware('permiso:ver_reportes');
        Route::get('/ventas/exportar', [ReporteController::class, 'exportarVentas'])->name('ventas.exportar')->middleware('permiso:ver_reportes');
        Route::get('/cobranza/exportar', [ReporteController::class, 'exportarCobranza'])->name('cobranza.exportar')->middleware('permiso:ver_reportes');
    });

    // ===== RESPALDOS =====
    Route::prefix('respaldos')->name('respaldos.')->middleware(['auth', 'empresa.activa'])->group(function () {
        Route::get('/', [RespaldoController::class, 'index'])->name('index')->middleware('permiso:ver_respaldos');
        Route::get('/exportar/excel', [RespaldoController::class, 'exportarExcel'])->name('exportar.excel')->middleware('permiso:generar_respaldo');
        Route::get('/exportar/sql', [RespaldoController::class, 'exportarSQL'])->name('exportar.sql')->middleware('permiso:generar_respaldo');
        Route::post('/importar', [RespaldoController::class, 'importar'])->name('importar')->middleware('permiso:importar_datos');
    });
});

// ===== RUTAS DE AUTENTICACIÓN (Breeze) =====
require __DIR__ . '/auth.php';