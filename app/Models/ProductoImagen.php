<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductoImagen extends Model
{
    protected $table = 'producto_imagens';
    
    protected $fillable = [
        'producto_id',
        'imagen',
        'orden',
        'principal'
    ];
    
    protected $casts = [
        'principal' => 'boolean',
        'orden' => 'integer'
    ];
    
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
    
    public function getUrlAttribute()
    {
        return Storage::url($this->imagen);
    }
    
     protected static function boot()
    {
        parent::boot();
        
        static::deleting(function($imagen) {
            // Intentar eliminar el archivo por cualquier medio
            try {
                if (Storage::disk('public')->exists($imagen->imagen)) {
                    Storage::disk('public')->delete($imagen->imagen);
                }
                
                // Fallback con sistema de archivos
                $fullPath = public_path('storage/' . $imagen->imagen);
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
                
                $storagePath = storage_path('app/public/' . $imagen->imagen);
                if (file_exists($storagePath)) {
                    unlink($storagePath);
                }
            } catch (\Exception $e) {
                Log::error('Error al eliminar archivo de imagen: ' . $e->getMessage());
            }
        });
    }
}
