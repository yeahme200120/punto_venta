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
        $roles = Role::all();
        
        // Agrupar permisos por módulo (la segunda palabra del nombre)
        $permisos = Permission::all()->groupBy(function($permiso) {
            $partes = explode('_', $permiso->name);
            // La primera palabra es la acción (ver, crear, editar, eliminar)
            // El resto es el módulo
            array_shift($partes);
            return implode('_', $partes) ?: 'otros';
        })->sortKeys();

        return view('usuarios.permisos', compact('usuario', 'roles', 'permisos'));
    }

    public function update(Request $request, User $usuario)
    {
        $usuario->syncRoles($request->roles ?? []);
        $usuario->syncPermissions($request->permisos ?? []);

        return redirect()->route('usuarios.index')
            ->with('success', 'Permisos actualizados correctamente.');
    }
}
