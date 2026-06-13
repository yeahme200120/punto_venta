<?php
// app/Models/ContraseñaMaestra.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class ContrasenaMaestra extends Model
{
    protected $table = 'contrasena_maestras';

    protected $fillable = [
        'user_id',
        'password_hash',
        'password_texto',
        'tipo',
        'activo',
        'ultimo_uso'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'ultimo_uso' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function verificar($password)
    {
        return Hash::check($password, $this->password_hash);
    }
    /**
     * Verificar contraseña maestra para un usuario
     */
    public static function verificarPassword($userId, $password, $tipo = null)
    {
        $query = self::where('user_id', $userId)
            ->where('activo', true);

        if ($tipo) {
            $query->where('tipo', $tipo);
        }

        $contrasena = $query->first();

        if (!$contrasena) {
            return false;
        }

        if ($contrasena->verificar($password)) {
            $contrasena->update(['ultimo_uso' => now()]);
            return true;
        }

        return false;
    }
    public static function validarPasswordGlobal($password, $empresaId = null)
    {
        // Buscar todas las contraseñas maestras activas
        $contrasenas = self::where('activo', true)->get();

        foreach ($contrasenas as $contrasena) {
            // Verificar la contraseña
            if (Hash::check($password, $contrasena->password_hash)) {
                $usuario = $contrasena->user;
                if (!$usuario)
                    continue;

                // Verificar rol
                if ($usuario->hasRole('Super Admin')) {
                    // Super admin puede con cualquier empresa
                    $contrasena->update(['ultimo_uso' => now()]);
                    return $usuario;
                }

                if ($usuario->hasRole('Administrador')) {
                    // Administrador: debe pertenecer a la misma empresa de la operación
                    if ($empresaId && $usuario->empresa_id == $empresaId) {
                        $contrasena->update(['ultimo_uso' => now()]);
                        return $usuario;
                    }
                    // Si no hay empresa específica, asumimos que sí
                    if (!$empresaId) {
                        $contrasena->update(['ultimo_uso' => now()]);
                        return $usuario;
                    }
                }
            }
        }

        return null;
    }
}