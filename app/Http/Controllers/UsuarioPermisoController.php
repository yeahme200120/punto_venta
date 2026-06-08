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
        // Agrupar permisos por módulo
        $permisos = Permission::all()->groupBy(function ($permiso) {
            $partes = explode('_', $permiso->name);

            // Si es un permiso especial que comienza con 'ver_', quitar 'ver_'
            $modulo = $partes[0];

            // Saltar la acción (ver, crear, editar, eliminar)
            if (in_array($modulo, ['ver', 'crear', 'editar', 'eliminar'])) {
                array_shift($partes);
                $modulo = implode('_', $partes);
            } else {
                // Para permisos como 'abrir_caja', 'cerrar_caja', etc.
                $modulo = $partes[0];
            }

            // Casos especiales para permisos de cobranza
            if (
                str_contains($permiso->name, 'cobranza') ||
                str_contains($permiso->name, 'creditos') ||
                str_contains($permiso->name, 'pagare') ||
                in_array($permiso->name, ['registrar_abono', 'pagar_pagare', 'condonar_adeudo', 'ver_condonaciones', 'ver_historial_cobranza', 'cancelar_cobro'])
            ) {
                $modulo = 'cobranza';
            }

            // Casos especiales para caja
            if (
                str_contains($permiso->name, 'caja') ||
                in_array($permiso->name, ['abrir_caja', 'cerrar_caja', 'realizar_arqueo', 'autorizar_movimiento', 'autorizar_transferencia'])
            ) {
                $modulo = 'caja';
            }

            // Casos especiales para ventas
            if (
                str_contains($permiso->name, 'ventas') ||
                in_array($permiso->name, ['cancelar_ventas', 'ver_historial_ventas', 'imprimir_ticket_venta', 'convertir_cotizacion', 'imprimir_cotizacion'])
            ) {
                $modulo = 'ventas';
            }

            return $modulo ?: 'otros';
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
