<?php
// app/Models/ContraseñaMaestra.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        return \Hash::check($password, $this->password_hash);
    }
}