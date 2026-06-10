<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\PermissionRegistrar;

class LimpiarPermisos extends Command
{
    protected $signature = 'permisos:limpiar';
    protected $description = 'Limpia la caché de permisos y expulsa usuarios';

    public function handle()
    {
        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();
        Cache::forget('spatie.permission.cache');
        
        // Limpiar sesiones de usuarios
        if (config('session.driver') === 'database') {
            \DB::table('sessions')->delete();
        }
        
        $this->info('✅ Caché de permisos limpiada correctamente');
        $this->info('✅ Usuarios expulsados del sistema');
        
        return Command::SUCCESS;
    }
}