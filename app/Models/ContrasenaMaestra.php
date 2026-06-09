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
}