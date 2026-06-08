<?php

use App\Http\Controllers\CajaController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CobranzaController;
use App\Http\Controllers\ContrasenaMaestraController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\DashboardCajaController;
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
use App\Http\Controllers\VentaController;
use Illuminate\Support\Facades\Route;

// ===== REDIRECCIÓN RAÍZ =====
Route::get('/', function () {
    return redirect('/login');
});

// ===== RUTAS PROTEGIDAS =====
Route::middleware(['auth', 'empresa.activa'])->group(function () {

    // ===== UNIDADES DE MEDIDA =====
    Route::middleware(['permiso:ver_unidades_medida'])->group(function () {
        Route::get('/unidades-medida', [UnidadMedidaController::class, 'index'])->name('unidades-medida.index');
        Route::get('/unidades-medida/{unidad_medida}', [UnidadMedidaController::class, 'show'])->name('unidades-medida.show');
    });

    Route::middleware(['permiso:crear_unidades_medida'])->group(function () {
        Route::get('/unidades-medida/create', [UnidadMedidaController::class, 'create'])->name('unidades-medida.create');
        Route::post('/unidades-medida', [UnidadMedidaController::class, 'store'])->name('unidades-medida.store');
    });

    Route::middleware(['permiso:editar_unidades_medida'])->group(function () {
        Route::get('/unidades-medida/{unidad_medida}/edit', [UnidadMedidaController::class, 'edit'])->name('unidades-medida.edit');
        Route::put('/unidades-medida/{unidad_medida}', [UnidadMedidaController::class, 'update'])->name('unidades-medida.update');
    });

    Route::middleware(['permiso:eliminar_unidades_medida'])->group(function () {
        Route::delete('/unidades-medida/{unidad_medida}', [UnidadMedidaController::class, 'destroy'])->name('unidades-medida.destroy');
    });

    Route::post('/unidades-medida/{unidad_medida}/toggle-activo', [UnidadMedidaController::class, 'toggleActivo'])
        ->middleware(['permiso:editar_unidades_medida'])
        ->name('unidades-medida.toggle-activo');

    // ===== DASHBOARD =====
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware(['permiso:ver_dashboard'])
        ->name('dashboard');
    // Exportar dashboard
    Route::get('/dashboard/exportar', [DashboardController::class, 'exportar'])->name('dashboard.exportar');
    // ===== USUARIOS =====
    Route::middleware(['permiso:ver_usuarios'])->group(function () {
        Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
        Route::get('/usuarios/{usuario}', [UsuarioController::class, 'show'])->name('usuarios.show');
        Route::get('/usuarios/export', [UsuarioController::class, 'export'])->name('usuarios.export');
    });

    Route::middleware(['permiso:crear_usuarios'])->group(function () {
        Route::get('/usuarios/create', [UsuarioController::class, 'create'])->name('usuarios.create');
        Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
    });

    Route::middleware(['permiso:editar_usuarios'])->group(function () {
        Route::get('/usuarios/{usuario}/edit', [UsuarioController::class, 'edit'])->name('usuarios.edit');
        Route::put('/usuarios/{usuario}', [UsuarioController::class, 'update'])->name('usuarios.update');
        Route::put('/usuarios/{usuario}/toggle-activo', [UsuarioController::class, 'toggleActivo'])->name('usuarios.toggle-activo');
    });

    Route::middleware(['permiso:eliminar_usuarios'])->group(function () {
        Route::delete('/usuarios/{usuario}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');
    });

    // Perfil (acceso propio)
    Route::get('/perfil', [UsuarioController::class, 'perfil'])->name('perfil.index');
    Route::put('/perfil', [UsuarioController::class, 'updatePerfil'])->name('perfil.update');

    // ===== PERMISOS DE USUARIO =====
    Route::middleware(['permiso:editar_usuarios'])->group(function () {
        Route::get('/usuarios/{usuario}/permisos', [UsuarioPermisoController::class, 'edit'])->name('usuarios.permisos.edit');
        Route::put('/usuarios/{usuario}/permisos', [UsuarioPermisoController::class, 'update'])->name('usuarios.permisos.update');
    });

    // ===== ROLES =====
    Route::middleware(['permiso:ver_roles'])->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('/roles/{role}', [RoleController::class, 'show'])->name('roles.show');
        Route::get('/roles/export', [RoleController::class, 'export'])->name('roles.export');
    });

    Route::middleware(['permiso:crear_roles'])->group(function () {
        Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    });

    Route::middleware(['permiso:editar_roles'])->group(function () {
        Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    });

    Route::middleware(['permiso:eliminar_roles'])->group(function () {
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });

    // ===== EMPRESAS (Solo Super Admin) =====
    Route::middleware(['permiso:ver_empresas'])->group(function () {
        Route::get('/empresas', [EmpresaController::class, 'index'])->name('empresas.index');
        Route::get('/empresas/{empresa}', [EmpresaController::class, 'show'])->name('empresas.show');
        Route::get('/empresas/export', [EmpresaController::class, 'export'])->name('empresas.export');
    });

    Route::middleware(['permiso:crear_empresas'])->group(function () {
        Route::get('/empresas/create', [EmpresaController::class, 'create'])->name('empresas.create');
        Route::post('/empresas', [EmpresaController::class, 'store'])->name('empresas.store');
    });

    Route::middleware(['permiso:editar_empresas'])->group(function () {
        Route::get('/empresas/{empresa}/edit', [EmpresaController::class, 'edit'])->name('empresas.edit');
        Route::put('/empresas/{empresa}', [EmpresaController::class, 'update'])->name('empresas.update');
        Route::get('/empresa/{empresa}/cambiar', [EmpresaController::class, 'cambiar'])->name('empresa.cambiar');
    });

    Route::middleware(['permiso:eliminar_empresas'])->group(function () {
        Route::delete('/empresas/{empresa}', [EmpresaController::class, 'destroy'])->name('empresas.destroy');
    });

    // ===== LICENCIAS =====
    Route::middleware(['permiso:ver_licencias'])->group(function () {
        Route::get('/licencias', [LicenciaController::class, 'index'])->name('licencias.index');
        Route::get('/licencias/{licencia}', [LicenciaController::class, 'show'])->name('licencias.show');
        Route::get('/licencias/export', [LicenciaController::class, 'export'])->name('licencias.export');
    });

    Route::middleware(['permiso:crear_licencias'])->group(function () {
        Route::get('/licencias/create', [LicenciaController::class, 'create'])->name('licencias.create');
        Route::post('/licencias', [LicenciaController::class, 'store'])->name('licencias.store');
    });

    Route::middleware(['permiso:editar_licencias'])->group(function () {
        Route::get('/licencias/{licencia}/edit', [LicenciaController::class, 'edit'])->name('licencias.edit');
        Route::put('/licencias/{licencia}', [LicenciaController::class, 'update'])->name('licencias.update');
    });

    Route::middleware(['permiso:eliminar_licencias'])->group(function () {
        Route::delete('/licencias/{licencia}', [LicenciaController::class, 'destroy'])->name('licencias.destroy');
    });

    // ===== PROVEEDORES =====
    Route::middleware(['permiso:ver_proveedores'])->group(function () {
        Route::get('/proveedores', [ProveedorController::class, 'index'])->name('proveedores.index');
        Route::get('/proveedores/{proveedor}', [ProveedorController::class, 'show'])->name('proveedores.show');
        Route::get('/proveedores/export', [ProveedorController::class, 'export'])->name('proveedores.export');
    });

    Route::middleware(['permiso:crear_proveedores'])->group(function () {
        Route::get('/proveedores/create', [ProveedorController::class, 'create'])->name('proveedores.create');
        Route::post('/proveedores', [ProveedorController::class, 'store'])->name('proveedores.store');
    });

    Route::middleware(['permiso:editar_proveedores'])->group(function () {
        Route::get('/proveedores/{proveedor}/edit', [ProveedorController::class, 'edit'])->name('proveedores.edit');
        Route::put('/proveedores/{proveedor}', [ProveedorController::class, 'update'])->name('proveedores.update');
        Route::post('/proveedores/{proveedor}/toggle-activo', [ProveedorController::class, 'toggleActivo'])->name('proveedores.toggle-activo');
    });

    Route::middleware(['permiso:eliminar_proveedores'])->group(function () {
        Route::delete('/proveedores/{proveedor}', [ProveedorController::class, 'destroy'])->name('proveedores.destroy');
    });

    // ===== IMPRESORAS =====
    Route::middleware(['permiso:ver_impresoras'])->group(function () {
        Route::get('/impresoras', [ImpresoraController::class, 'index'])->name('impresoras.index');
        Route::get('/impresoras/{impresora}', [ImpresoraController::class, 'show'])->name('impresoras.show');
        Route::get('/impresoras/export', [ImpresoraController::class, 'export'])->name('impresoras.export');
    });

    Route::middleware(['permiso:crear_impresoras'])->group(function () {
        Route::get('/impresoras/create', [ImpresoraController::class, 'create'])->name('impresoras.create');
        Route::post('/impresoras', [ImpresoraController::class, 'store'])->name('impresoras.store');
    });

    Route::middleware(['permiso:editar_impresoras'])->group(function () {
        Route::get('/impresoras/{impresora}/edit', [ImpresoraController::class, 'edit'])->name('impresoras.edit');
        Route::put('/impresoras/{impresora}', [ImpresoraController::class, 'update'])->name('impresoras.update');
    });

    Route::middleware(['permiso:eliminar_impresoras'])->group(function () {
        Route::delete('/impresoras/{impresora}', [ImpresoraController::class, 'destroy'])->name('impresoras.destroy');
    });

    // ===== SUCURSALES =====
    Route::middleware(['permiso:ver_sucursales'])->group(function () {
        Route::get('/sucursales', [SucursalController::class, 'index'])->name('sucursales.index');
        Route::get('/sucursales/{sucursal}', [SucursalController::class, 'show'])->name('sucursales.show');
    });

    Route::middleware(['permiso:crear_sucursales'])->group(function () {
        Route::get('/sucursales/create', [SucursalController::class, 'create'])->name('sucursales.create');
        Route::post('/sucursales', [SucursalController::class, 'store'])->name('sucursales.store');
    });

    Route::middleware(['permiso:editar_sucursales'])->group(function () {
        Route::get('/sucursales/{sucursal}/edit', [SucursalController::class, 'edit'])->name('sucursales.edit');
        Route::put('/sucursales/{sucursal}', [SucursalController::class, 'update'])->name('sucursales.update');
        Route::get('/sucursal/{sucursal}/cambiar', [SucursalController::class, 'cambiar'])->name('sucursal.cambiar');
    });

    Route::middleware(['permiso:eliminar_sucursales'])->group(function () {
        Route::delete('/sucursales/{sucursal}', [SucursalController::class, 'destroy'])->name('sucursales.destroy');
    });

    // ===== CLIENTES =====
    Route::middleware(['permiso:ver_clientes'])->group(function () {
        Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes.index');
        Route::get('/clientes/{cliente}', [ClienteController::class, 'show'])->name('clientes.show');
        Route::get('/clientes/export', [ClienteController::class, 'export'])->name('clientes.export');
    });

    Route::middleware(['permiso:crear_clientes'])->group(function () {
        Route::get('/clientes/create', [ClienteController::class, 'create'])->name('clientes.create');
        Route::post('/clientes', [ClienteController::class, 'store'])->name('clientes.store');
    });

    Route::middleware(['permiso:editar_clientes'])->group(function () {
        Route::get('/clientes/{cliente}/edit', [ClienteController::class, 'edit'])->name('clientes.edit');
        Route::put('/clientes/{cliente}', [ClienteController::class, 'update'])->name('clientes.update');
    });

    Route::middleware(['permiso:eliminar_clientes'])->group(function () {
        Route::delete('/clientes/{cliente}', [ClienteController::class, 'destroy'])->name('clientes.destroy');
    });

    // ===== CATEGORÍAS =====
    Route::middleware(['permiso:ver_categorias'])->group(function () {
        Route::get('/categorias', [CategoriaController::class, 'index'])->name('categorias.index');
        Route::get('/categorias/{categoria}', [CategoriaController::class, 'show'])->name('categorias.show');
        Route::get('/categorias/export', [CategoriaController::class, 'export'])->name('categorias.export');
    });

    Route::middleware(['permiso:crear_categorias'])->group(function () {
        Route::get('/categorias/create', [CategoriaController::class, 'create'])->name('categorias.create');
        Route::post('/categorias', [CategoriaController::class, 'store'])->name('categorias.store');
    });

    Route::middleware(['permiso:editar_categorias'])->group(function () {
        Route::get('/categorias/{categoria}/edit', [CategoriaController::class, 'edit'])->name('categorias.edit');
        Route::put('/categorias/{categoria}', [CategoriaController::class, 'update'])->name('categorias.update');
    });

    Route::middleware(['permiso:eliminar_categorias'])->group(function () {
        Route::delete('/categorias/{categoria}', [CategoriaController::class, 'destroy'])->name('categorias.destroy');
    });

    // ===== PRODUCTOS =====
    Route::middleware(['permiso:ver_productos'])->group(function () {
        Route::get('/productos', [ProductoController::class, 'index'])->name('productos.index');
        Route::get('/productos/{producto}', [ProductoController::class, 'show'])->name('productos.show');
        Route::get('/productos/export', [ProductoController::class, 'export'])->name('productos.export');
    });

    Route::middleware(['permiso:crear_productos'])->group(function () {
        Route::get('/productos/create', [ProductoController::class, 'create'])->name('productos.create');
        Route::post('/productos', [ProductoController::class, 'store'])->name('productos.store');
        Route::post('/productos/generar-sku', [ProductoController::class, 'generarSkuUnico'])->name('productos.generar-sku');
        Route::post('/productos/generar-sku-por-nombre', [ProductoController::class, 'generarSkuPorNombre'])->name('productos.generar-sku-por-nombre');
    });

    Route::middleware(['permiso:editar_productos'])->group(function () {
        Route::get('/productos/{producto}/edit', [ProductoController::class, 'edit'])->name('productos.edit');
        Route::put('/productos/{producto}', [ProductoController::class, 'update'])->name('productos.update');
    });

    Route::middleware(['permiso:eliminar_productos'])->group(function () {
        Route::delete('/productos/{producto}', [ProductoController::class, 'destroy'])->name('productos.destroy');
    });

    // Imágenes de productos
    Route::middleware(['permiso:editar_productos'])->group(function () {
        Route::post('/productos/{producto}/imagenes', [ProductoController::class, 'subirImagen'])->name('productos.imagenes.subir');
        Route::delete('/productos/{producto}/imagenes/{imagen}', [ProductoController::class, 'eliminarImagen'])->name('productos.imagenes.eliminar');
        Route::put('/productos/{producto}/imagenes/{imagen}/principal', [ProductoController::class, 'imagenPrincipal'])->name('productos.imagenes.principal');
    });

    // Productos relacionados
    Route::middleware(['permiso:editar_productos'])->group(function () {
        Route::post('/productos/{producto}/relacionados', [ProductoController::class, 'agregarRelacionado'])->name('productos.relacionados.agregar');
        Route::delete('/productos/{producto}/relacionados/{relacionado}', [ProductoController::class, 'eliminarRelacionado'])->name('productos.relacionados.eliminar');
    });

    // ===== INSUMOS =====
    Route::middleware(['permiso:ver_insumos'])->group(function () {
        Route::get('/insumos', [InsumoController::class, 'index'])->name('insumos.index');
        Route::get('/insumos/{insumo}', [InsumoController::class, 'show'])->name('insumos.show');
        Route::get('/insumos/export', [InsumoController::class, 'export'])->name('insumos.export');
    });

    Route::middleware(['permiso:crear_insumos'])->group(function () {
        Route::get('/insumos/create', [InsumoController::class, 'create'])->name('insumos.create');
        Route::post('/insumos', [InsumoController::class, 'store'])->name('insumos.store');
    });

    Route::middleware(['permiso:editar_insumos'])->group(function () {
        Route::get('/insumos/{insumo}/edit', [InsumoController::class, 'edit'])->name('insumos.edit');
        Route::put('/insumos/{insumo}', [InsumoController::class, 'update'])->name('insumos.update');
        Route::post('/insumos/{insumo}/toggle-activo', [InsumoController::class, 'toggleActivo'])->name('insumos.toggle-activo');
    });

    Route::middleware(['permiso:eliminar_insumos'])->group(function () {
        Route::delete('/insumos/{insumo}', [InsumoController::class, 'destroy'])->name('insumos.destroy');
    });

    // ===== MOVIMIENTOS DE INVENTARIO =====
    Route::middleware(['permiso:ver_inventario'])->group(function () {
        Route::get('/inventario/movimientos', [InventarioMovimientoController::class, 'index'])->name('inventario.movimientos');
        Route::get('/inventario/movimientos/export', [InventarioMovimientoController::class, 'export'])->name('inventario.movimientos.export');
    });

    Route::middleware(['permiso:crear_inventario'])->group(function () {
        Route::get('/inventario/movimientos/create', [InventarioMovimientoController::class, 'create'])->name('inventario.movimientos.create');
        Route::post('/inventario/movimientos', [InventarioMovimientoController::class, 'store'])->name('inventario.movimientos.store');
    });

    // ===== CAJA =====
    Route::prefix('caja')->name('cajas.')->group(function () {

        // Gestión de cajas (CRUD)
        Route::middleware(['permiso:ver_cajas'])->group(function () {
            Route::get('/cajas', [CajaController::class, 'indexCajas'])->name('cajas.index');
        });

        Route::middleware(['permiso:crear_caja'])->group(function () {
            Route::get('/cajas/create', [CajaController::class, 'createCaja'])->name('cajas.create');
            Route::post('/cajas', [CajaController::class, 'storeCaja'])->name('cajas.store');
        });

        Route::middleware(['permiso:editar_caja'])->group(function () {
            Route::get('/cajas/{caja}/edit', [CajaController::class, 'editCaja'])->name('cajas.edit');
            Route::put('/cajas/{caja}', [CajaController::class, 'updateCaja'])->name('cajas.update');

        });

        Route::middleware(['permiso:eliminar_caja'])->group(function () {
            Route::delete('/cajas/{caja}', [CajaController::class, 'destroyCaja'])->name('cajas.destroy');
        });

        // Apertura/Cierre
        Route::middleware(['permiso:abrir_caja'])->group(function () {
            Route::get('/apertura', [CajaController::class, 'aperturaIndex'])->name('apertura');
            Route::post('/abrir', [CajaController::class, 'abrirCaja'])->name('abrir');
            Route::post('/cerrar', [CajaController::class, 'cerrarCaja'])->name('cerrar');
        });

        // Operaciones
        Route::middleware(['permiso:ver_movimientos_caja'])->group(function () {
            Route::get('/operaciones', [CajaController::class, 'operaciones'])->name('operaciones');
        });

        Route::middleware(['permiso:registrar_movimiento_caja'])->group(function () {
            Route::post('/movimiento', [CajaController::class, 'registrarMovimiento'])->name('movimiento.registrar');
        });

        // Arqueos
        Route::middleware(['permiso:realizar_arqueo'])->group(function () {
            Route::get('/arqueos', [CajaController::class, 'arqueos'])->name('arqueos');
            Route::post('/arqueo/registrar', [CajaController::class, 'registrarArqueo'])->name('arqueo.registrar');
            Route::get('/arqueo/{arqueo}', [CajaController::class, 'verArqueo'])->name('arqueo.ver');
            Route::get('/arqueo/{arqueo}/imprimir', [CajaController::class, 'imprimirArqueo'])->name('arqueo.imprimir');
        });

        // Autorizaciones
        Route::middleware(['permiso:ver_autorizaciones_caja'])->group(function () {
            Route::get('/autorizaciones', [CajaController::class, 'autorizacionesPendientes'])->name('autorizaciones');
        });

        Route::middleware(['permiso:autorizar_movimiento'])->group(function () {
            Route::post('/movimiento/{movimientoId}/autorizar', [CajaController::class, 'autorizarMovimiento'])->name('movimiento.autorizar');
        });

        // Transferencias
        Route::middleware(['permiso:ver_transferencias_caja'])->group(function () {
            Route::get('/transferencias', [CajaController::class, 'transferencias'])->name('transferencias');
        });

        Route::middleware(['permiso:solicitar_transferencia_caja'])->group(function () {
            Route::post('/transferencia/solicitar', [CajaController::class, 'solicitarTransferencia'])->name('transferencia.solicitar');
        });

        Route::middleware(['permiso:autorizar_transferencia'])->group(function () {
            Route::post('/transferencia/{transferenciaId}/autorizar', [CajaController::class, 'autorizarTransferencia'])->name('transferencia.autorizar');
        });

        // Reportes
        Route::middleware(['permiso:ver_reporte_caja_diario'])->group(function () {
            Route::get('/reporte/{aperturaId}', [CajaController::class, 'reporteDia'])->name('reporte.dia');
        });

        // Dentro del grupo de rutas de caja, agregar:

        // Tickets
        Route::get('/movimiento/{movimiento}/ticket', [CajaController::class, 'imprimirTicketMovimiento'])->name('movimiento.ticket');
        Route::get('/transferencia/{transferencia}/ticket', [CajaController::class, 'imprimirTicketTransferencia'])->name('transferencia.ticket');
        Route::get('/arqueo/{arqueo}/ticket', [CajaController::class, 'imprimirTicketArqueo'])->name('arqueo.ticket');
        Route::get('/cierre/{apertura}/ticket', [CajaController::class, 'imprimirTicketCierre'])->name('cierre.ticket');
        // Dentro del grupo de caja
        Route::post('/retiro/registrar', [CajaController::class, 'registrarRetiro'])->name('retiro.registrar');
    });

    // ===== CONTRASEÑAS MAESTRAS (Perfil) =====
    Route::prefix('perfil')->name('perfil.')->group(function () {
        Route::get('/contraseñas-maestras', [ContrasenaMaestraController::class, 'index'])->name('contraseñas');
        Route::post('/contraseñas-maestras', [ContrasenaMaestraController::class, 'store'])->name('contraseñas.store');
        Route::delete('/contraseñas-maestras/{contraseñaMaestra}', [ContrasenaMaestraController::class, 'destroy'])->name('contraseñas.destroy');
    });

    // ===== REPORTES DE CAJA =====
    Route::prefix('reportes')->name('reportes.')->group(function () {
        Route::get('/caja-dashboard', [ReporteCajaController::class, 'dashboard'])
            ->middleware(['permiso:ver_dashboard_caja'])
            ->name('caja.dashboard');

        Route::get('/caja-exportar', [ReporteCajaController::class, 'exportar'])
            ->middleware(['permiso:exportar_reportes_caja'])
            ->name('caja.exportar');
    });
    // ===== DASHBOARD DE CAJA =====
    Route::get('/dashboard-caja', [DashboardCajaController::class, 'index'])
        ->middleware(['permiso:ver_dashboard_caja'])
        ->name('dashboard.caja');
    // ===== VENTAS =====
    Route::prefix('ventas')->name('ventas.')->middleware(['auth', 'empresa.activa'])->group(function () {
        Route::get('/', [VentaController::class, 'index'])->name('index');
        Route::post('/contado/store', [VentaController::class, 'storeContado'])->name('contado.store');
        Route::post('/credito/store', [VentaController::class, 'storeCredito'])->name('credito.store');
        Route::get('/historial', [VentaController::class, 'historial'])->name('historial');
        Route::get('/{id}/ticket', [VentaController::class, 'ticket'])->name('ticket');
        Route::get('/{id}', [VentaController::class, 'show'])->name('show');
    });
    // ===== COTIZACIONES =====
    Route::prefix('cotizaciones')->name('cotizaciones.')->middleware(['auth', 'empresa.activa'])->group(function () {
        Route::get('/', [CotizacionController::class, 'index'])->name('index');
        Route::post('/store', [CotizacionController::class, 'store'])->name('store');
        Route::get('/{id}/pdf', [CotizacionController::class, 'pdf'])->name('pdf');
        Route::get('/{id}/download', [CotizacionController::class, 'download'])->name('download');  // ← Agregar esta línea

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
    Route::prefix('cobranza')->name('cobranza.')->middleware(['auth', 'empresa.activa'])->group(function () {
        // Rutas fijas (sin parámetros o con parámetros explícitos) PRIMERO
        Route::get('/creditos', [CobranzaController::class, 'index'])->name('index');
        Route::post('/abono', [CobranzaController::class, 'registrarAbono'])->name('abono.store');
        Route::post('/pagare/{id}/pagar', [CobranzaController::class, 'pagarPagare'])->name('pagare.pagar');
        Route::get('/historial', [CobranzaController::class, 'historialGeneral'])->name('historial');
        Route::get('/condonaciones', [CobranzaController::class, 'condonaciones'])->name('condonaciones');
        Route::post('/condonar', [CobranzaController::class, 'condonarAdeudo'])->name('condonar');
        Route::get('/historial/{id}', [CobranzaController::class, 'historialPagos'])->name('historial.pagos');

        // Ruta dinámica AL FINAL
        Route::get('/{id}', [CobranzaController::class, 'show'])->name('show');
    });
});

// ===== RUTAS DE AUTENTICACIÓN (Breeze) =====
require __DIR__ . '/auth.php';