<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UsuarioPermisoController extends Controller
{
    public function edit(User $usuario)
    {
        $currentUser = auth()->user();
        
        // Verificar que NO se pueda editar permisos de un Super Admin
        if ($usuario->hasRole('Super Admin') && !$currentUser->hasRole('Super Admin')) {
            abort(403, 'No tienes permiso para editar los permisos de un Super Administrador.');
        }
        
        // Si el usuario actual no es Super Admin, excluir permisos de módulos restringidos
        $todosPermisos = Permission::all();
        
        if (!$currentUser->hasRole('Super Admin')) {
            // Excluir permisos de módulos que solo Super Admin puede ver
            $excluirModulos = ['empresas', 'licencias', 'notificaciones', 'impresoras'];
            $todosPermisos = $todosPermisos->filter(function($permiso) use ($excluirModulos) {
                foreach ($excluirModulos as $modulo) {
                    if (str_contains($permiso->name, $modulo)) {
                        return false;
                    }
                }
                return true;
            });
        }
        
        $roles = Role::all();
        
        // Si no es Super Admin, excluir el rol Super Admin de la lista
        if (!$currentUser->hasRole('Super Admin')) {
            $roles = $roles->filter(function($role) {
                return $role->name !== 'Super Admin';
            });
        }
        
        // Obtener permisos del usuario (directos + del rol)
        $permisosUsuario = $usuario->getAllPermissions()->pluck('name')->toArray();
        
        // Agrupar permisos por módulo
        $permisosAgrupados = $todosPermisos->groupBy(function ($permiso) {
            $nombre = $permiso->name;
            
            $mapaModulos = [
                'dashboard' => 'Dashboard',
                'empresas' => 'Empresas',
                'licencias' => 'Licencias',
                'inventario' => 'Inventario',
                'compras' => 'Compras',
                'proveedores' => 'Proveedores',
                'ventas' => 'Ventas',
                'facturacion' => 'Facturacion',
                'clientes' => 'Clientes',
                'cobranza' => 'Cobranza',
                'caja' => 'Caja',
                'formaspago' => 'FormasPago',
                'notificaciones' => 'Notificaciones',
                'impresoras' => 'Impresoras',
                'ticket' => 'Ticket',
                'usuarios' => 'Usuarios',
                'roles' => 'Roles',
                'reportes' => 'Reportes',
                'respaldos' => 'Respaldos',
                'insumos' => 'Insumos',
                'unidades_medida' => 'UnidadesMedida',
            ];
            
            foreach ($mapaModulos as $clave => $modulo) {
                if (str_contains($nombre, $clave)) {
                    return $modulo;
                }
            }
            return 'Otros';
        })->sortKeys();
        
        // Pasar las variables correctas a la vista
        return view('usuarios.permisos', compact('usuario', 'roles', 'permisosAgrupados', 'permisosUsuario'));
    }

    public function update(Request $request, User $usuario)
    {
        $currentUser = auth()->user();
        
        // No permitir modificar permisos de Super Admin
        if ($usuario->hasRole('Super Admin') && !$currentUser->hasRole('Super Admin')) {
            return redirect()->route('usuarios.index')
                ->with('error', 'No tienes permiso para modificar los permisos de un Super Administrador.');
        }
        
        $rolSeleccionado = $request->roles ? (is_array($request->roles) ? $request->roles[0] : $request->roles) : null;
        
        // Validar que no se asigne rol Super Admin si el usuario actual no es Super Admin
        if (!$currentUser->hasRole('Super Admin') && $rolSeleccionado === 'Super Admin') {
            return redirect()->route('usuarios.index')
                ->with('error', 'No tienes permiso para asignar el rol Super Administrador.');
        }
        
        // Asignar rol
        if ($rolSeleccionado) {
            $usuario->syncRoles([$rolSeleccionado]);
        } else {
            $usuario->syncRoles([]);
        }
        
        // Filtrar permisos que el usuario actual puede asignar
        $permisosAsignar = $request->permisos ?? [];
        
        if (!$currentUser->hasRole('Super Admin')) {
            // Excluir permisos de módulos restringidos
            $excluirModulos = ['empresas', 'licencias', 'notificaciones', 'impresoras'];
            $permisosAsignar = array_filter($permisosAsignar, function($permiso) use ($excluirModulos) {
                foreach ($excluirModulos as $modulo) {
                    if (str_contains($permiso, $modulo)) {
                        return false;
                    }
                }
                return true;
            });
        }
        
        $usuario->syncPermissions($permisosAsignar);
        
        // Limpiar caché
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        
        if (auth()->id() === $usuario->id) {
            auth()->setUser($usuario->fresh());
        }
        
        $mensaje = 'Permisos actualizados correctamente.';
        if ($rolSeleccionado) {
            $mensaje .= " Rol asignado: {$rolSeleccionado}.";
        }
        
        return redirect()->route('usuarios.index')
            ->with('success', $mensaje);
    }
}