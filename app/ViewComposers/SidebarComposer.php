<?php

namespace App\ViewComposers;

use App\Models\Modulo;
use App\Models\Empresa;
use App\Models\Sucursal;
use Illuminate\View\View;
use Illuminate\Support\Facades\Request;

class SidebarComposer
{
    public function compose(View $view)
    {
        $user = auth()->user();
        
        $empresaActivaId = session('empresa_activa_id', $user->empresa_id);
        $empresaActiva = Empresa::with('licencia')->find($empresaActivaId);
        
        $sucursalActivaId = session('sucursal_activa_id', $user->sucursal_id);
        $sucursalActiva = $sucursalActivaId ? Sucursal::find($sucursalActivaId) : null;

        $modulos = Modulo::with([
            'menus' => function ($query) {
                $query->where('activo', true)
                    ->whereNull('menu_padre_id')
                    ->with(['hijos' => function ($q) {
                        $q->where('activo', true)->orderBy('orden');
                    }])
                    ->orderBy('orden');
            }
        ])->where('activo', true)->orderBy('nombre', 'asc')->get();

        $activeMenuIds = [];
        $activeParentIds = [];
        
        foreach ($modulos as $modulo) {
            foreach ($modulo->menus as $menu) {
                $menuRuta = $menu->ruta ? trim($menu->ruta, '/') : null;
                if ($menuRuta && Request::is($menuRuta . '*')) {
                    $activeMenuIds[] = $menu->id;
                }
                foreach ($menu->hijos as $hijo) {
                    $hijoRuta = $hijo->ruta ? trim($hijo->ruta, '/') : null;
                    if ($hijoRuta && Request::is($hijoRuta . '*')) {
                        $activeMenuIds[] = $hijo->id;
                        $activeMenuIds[] = $menu->id;
                        $activeParentIds[] = $menu->id;
                    }
                }
            }
        }
        
        $activeMenuIds = array_unique($activeMenuIds);
        $activeParentIds = array_unique($activeParentIds);

        $view->with([
            'user' => $user,
            'empresaActiva' => $empresaActiva,
            'sucursalActiva' => $sucursalActiva,
            'modulos' => $modulos,
            'activeMenuIds' => $activeMenuIds,
            'activeParentIds' => $activeParentIds,
        ]);
    }

    /**
     * Verificar si un usuario puede ver un menú
     */
    public static function puedeVerMenu($menu, $user)
    {
        if ($user->hasRole('Super Admin')) return true;
        if (!empty($menu->permiso)) {
            if (!$user->can($menu->permiso)) return false;
        }
        if ($menu->hijos->count() > 0) {
            foreach ($menu->hijos as $hijo) {
                if (empty($hijo->permiso) || $user->can($hijo->permiso)) {
                    return true;
                }
            }
            return false;
        }
        return true;
    }
}