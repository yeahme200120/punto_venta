<?php

use App\Http\Controllers\CajaController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ContrasenaMaestraController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\ImpresoraController;
use App\Http\Controllers\InsumoController;
use App\Http\Controllers\InventarioMovimientoController;
use App\Http\Controllers\LicenciaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ReporteCajaController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\UnidadMedidaController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\UsuarioPermisoController;
use Illuminate\Support\Facades\Route;

// ===== REDIRECCIÓN RAÍZ =====
Route::get('/', function () {
    return redirect('/login');
});

// ===== RUTAS PROTEGIDAS =====
Route::middleware(['auth', 'empresa.activa'])->group(function () {
    //Unidades de medida
    // Unidades de medida - El parámetro debe llamarse 'unidad_medida' (singular)
    Route::resource('unidades-medida', UnidadMedidaController::class)->parameters([
        'unidades-medida' => 'unidad_medida' // 👈 Cambiar a singular
    ]);

    Route::post('/unidades-medida/{unidad_medida}/toggle-activo', [UnidadMedidaController::class, 'toggleActivo'])
        ->name('unidades-medida.toggle-activo');

    // ----- DASHBOARD -----
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ----- USUARIOS -----
    Route::get('/usuarios/export', [UsuarioController::class, 'export'])->name('usuarios.export');
    Route::put('/usuarios/{usuario}/toggle-activo', [UsuarioController::class, 'toggleActivo'])->name('usuarios.toggle-activo');
    Route::resource('usuarios', UsuarioController::class);

    Route::get('/perfil', [UsuarioController::class, 'perfil'])->name('perfil.index');
    Route::put('/perfil', [UsuarioController::class, 'updatePerfil'])->name('perfil.update');

    // ----- PERMISOS DE USUARIO -----
    Route::get('/usuarios/{usuario}/permisos', [UsuarioPermisoController::class, 'edit'])->name('usuarios.permisos.edit');
    Route::put('/usuarios/{usuario}/permisos', [UsuarioPermisoController::class, 'update'])->name('usuarios.permisos.update');

    // ----- ROLES -----
    Route::get('/roles/export', [RoleController::class, 'export'])->name('roles.export');
    Route::resource('roles', RoleController::class);

    // ----- EMPRESAS (Solo Super Admin) -----
    Route::get('/empresas/export', [EmpresaController::class, 'export'])->name('empresas.export');
    Route::get('/empresa/{empresa}/cambiar', [EmpresaController::class, 'cambiar'])->name('empresa.cambiar');
    Route::resource('empresas', EmpresaController::class);

    // ----- LICENCIAS (Solo Super Admin) -----
    Route::get('/licencias/export', [LicenciaController::class, 'export'])->name('licencias.export');
    Route::resource('licencias', LicenciaController::class);

    // ----- PROVEEDORES -----
    Route::get('/proveedores/export', [ProveedorController::class, 'export'])->name('proveedores.export');
    Route::resource('proveedores', ProveedorController::class)->parameters([
        'proveedores' => 'proveedor'
    ]);
    // routes/web.php
    Route::post('/proveedores/{proveedor}/toggle-activo', [ProveedorController::class, 'toggleActivo'])->name('proveedores.toggle-activo');

    // ----- IMPRESORAS -----
    Route::get('/impresoras/export', [ImpresoraController::class, 'export'])->name('impresoras.export');
    Route::resource('impresoras', ImpresoraController::class);

    // ----- SUCURSALES -----
    Route::get('/sucursal/{sucursal}/cambiar', [SucursalController::class, 'cambiar'])->name('sucursal.cambiar');
    Route::get('/sucursales/create', [SucursalController::class, 'create'])->name('sucursales.create');
    Route::post('/sucursales', [SucursalController::class, 'store'])->name('sucursales.store');
    Route::get('/sucursales/{sucursal}/edit', [SucursalController::class, 'edit'])->name('sucursales.edit');
    Route::put('/sucursales/{sucursal}', [SucursalController::class, 'update'])->name('sucursales.update');

    Route::get('/clientes/export', [ClienteController::class, 'export'])->name('clientes.export');
    Route::resource('clientes', ClienteController::class);

    // Categorías
    Route::get('/categorias/export', [CategoriaController::class, 'export'])->name('categorias.export');
    Route::resource('categorias', CategoriaController::class);

    // Productos
    Route::get('/productos/export', [ProductoController::class, 'export'])->name('productos.export');
    Route::resource('productos', ProductoController::class);
    // Rutas para imágenes
    Route::post('/productos/{producto}/imagenes', [ProductoController::class, 'subirImagen'])->name('productos.imagenes.subir');
    Route::delete('/productos/{producto}/imagenes/{imagen}', [ProductoController::class, 'eliminarImagen'])->name('productos.imagenes.eliminar');
    Route::put('/productos/{producto}/imagenes/{imagen}/principal', [ProductoController::class, 'imagenPrincipal'])->name('productos.imagenes.principal');

    // Rutas para productos relacionados
    Route::post('/productos/{producto}/relacionados', [ProductoController::class, 'agregarRelacionado'])->name('productos.relacionados.agregar');
    Route::delete('/productos/{producto}/relacionados/{relacionado}', [ProductoController::class, 'eliminarRelacionado'])->name('productos.relacionados.eliminar');

    // Rutas para generar SKU único
    Route::post('/productos/generar-sku', [ProductoController::class, 'generarSkuUnico'])->name('productos.generar-sku');
    Route::post('/productos/generar-sku-por-nombre', [ProductoController::class, 'generarSkuPorNombre'])->name('productos.generar-sku-por-nombre');

    // Insumos
    Route::get('/insumos/export', [InsumoController::class, 'export'])->name('insumos.export');
    Route::resource('insumos', InsumoController::class);
    Route::post('/insumos/{insumo}/toggle-activo', [InsumoController::class, 'toggleActivo'])->name('insumos.toggle-activo');

    // Movimientos de Inventario
    Route::get('/inventario/movimientos', [InventarioMovimientoController::class, 'index'])->name('inventario.movimientos');
    Route::get('/inventario/movimientos/create', [InventarioMovimientoController::class, 'create'])->name('inventario.movimientos.create');
    Route::post('/inventario/movimientos', [InventarioMovimientoController::class, 'store'])->name('inventario.movimientos.store');

    // Exportación de movimientos
    Route::get('/inventario/movimientos/export', [InventarioMovimientoController::class, 'export'])->name('inventario.movimientos.export');

    // ===== CAJA =====
    
    Route::prefix('caja')->name('cajas.')->group(function () {
        // Gestión de cajas (CRUD)
        Route::get('/cajas', [CajaController::class, 'indexCajas'])->name('cajas.index');
        Route::get('/cajas/create', [CajaController::class, 'createCaja'])->name('cajas.create');
        Route::post('/cajas', [CajaController::class, 'storeCaja'])->name('cajas.store');
        Route::get('/cajas/{caja}/edit', [CajaController::class, 'editCaja'])->name('cajas.edit');
        Route::put('/cajas/{caja}', [CajaController::class, 'updateCaja'])->name('cajas.update');
        Route::delete('/cajas/{caja}', [CajaController::class, 'destroyCaja'])->name('cajas.destroy');
        // Apertura/Cierre
        Route::get('/apertura', [CajaController::class, 'aperturaIndex'])->name('apertura');
        Route::post('/abrir', [CajaController::class, 'abrirCaja'])->name('abrir');
        Route::post('/cerrar', [CajaController::class, 'cerrarCaja'])->name('cerrar');

        // Operaciones
        Route::get('/operaciones', [CajaController::class, 'operaciones'])->name('operaciones');
        Route::post('/movimiento', [CajaController::class, 'registrarMovimiento'])->name('movimiento.registrar');

        // Autorizaciones
        Route::get('/autorizaciones', [CajaController::class, 'autorizacionesPendientes'])->name('autorizaciones');
        Route::post('/movimiento/{movimientoId}/autorizar', [CajaController::class, 'autorizarMovimiento'])->name('movimiento.autorizar');

        // Transferencias
        Route::get('/transferencias', [CajaController::class, 'transferencias'])->name('transferencias');
        Route::post('/transferencia/solicitar', [CajaController::class, 'solicitarTransferencia'])->name('transferencia.solicitar');
        Route::post('/transferencia/{transferenciaId}/autorizar', [CajaController::class, 'autorizarTransferencia'])->name('transferencia.autorizar');

        // Reportes
        Route::get('/reporte/{aperturaId}', [CajaController::class, 'reporteDia'])->name('reporte.dia');

    });
        Route::prefix('perfil')->name('perfil.')->group(function () {
            Route::get('/contraseñas-maestras', [ContrasenaMaestraController::class, 'index'])->name('contraseñas');
            Route::post('/contraseñas-maestras', [ContrasenaMaestraController::class, 'store'])->name('contraseñas.store');
            Route::delete('/contraseñas-maestras/{contraseñaMaestra}', [ContrasenaMaestraController::class, 'destroy'])->name('contraseñas.destroy');
        });
    Route::prefix('reportes')->name('reportes.')->group(function () {
        Route::get('/caja-dashboard', [ReporteCajaController::class, 'dashboard'])->name('caja.dashboard');
        Route::get('/caja-exportar', [ReporteCajaController::class, 'exportar'])->name('caja.exportar');
    });
    // Arqueos
    Route::get('/arqueos', [CajaController::class, 'arqueos'])->name('arqueos');
    Route::post('/arqueo/registrar', [CajaController::class, 'registrarArqueo'])->name('arqueo.registrar');
    Route::get('/arqueo/{arqueo}', [CajaController::class, 'verArqueo'])->name('arqueo.ver');
    Route::get('/arqueo/{arqueo}/imprimir', [CajaController::class, 'imprimirArqueo'])->name('arqueo.imprimir');
});

// ===== RUTAS DE AUTENTICACIÓN (Breeze) =====
require __DIR__ . '/auth.php';