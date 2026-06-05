<?php
// app/Http/Controllers/ContrasenaMaestraController.php
namespace App\Http\Controllers;

use App\Models\ContrasenaMaestra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ContrasenaMaestraController extends Controller
{
    public function index()
    {
        try {
            $contraseñas = ContrasenaMaestra::with('user')
                ->where('user_id', auth()->id())
                ->get();
            
            return view('perfil.contraseñas-maestras', compact('contraseñas'));
        } catch (\Exception $e) {
            Log::error('Error al cargar contraseñas: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar las contraseñas maestras.');
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'password' => 'required|string|min:6|confirmed',
            'tipo' => 'required|in:super_admin,admin',
        ]);

        DB::beginTransaction();
        try {
            $tipo = $validated['tipo'];
            
            // Verificar si ya existe una contraseña activa de este tipo
            $existente = ContrasenaMaestra::where('user_id', auth()->id())
                ->where('tipo', $tipo)
                ->where('activo', true)
                ->first();
            
            if ($existente) {
                // Desactivar la anterior
                $existente->update(['activo' => false]);
            }
            
            // Crear nueva contraseña
            ContrasenaMaestra::create([
                'user_id' => auth()->id(),
                'password_hash' => Hash::make($validated['password']),
                'password_texto' => $validated['password'], // Texto plano (solo para admin/superadmin)
                'tipo' => $tipo,
                'activo' => true,
            ]);
            
            DB::commit();
            return back()->with('success', 'Contraseña maestra creada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear contraseña maestra: ' . $e->getMessage());
            return back()->with('error', 'Error al crear la contraseña maestra.');
        }
    }

    public function destroy(ContrasenaMaestra $contrasenaMaestra)
    {
        try {
            $contrasenaMaestra->delete();
            return back()->with('success', 'Contraseña maestra eliminada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar contraseña: ' . $e->getMessage());
            return back()->with('error', 'Error al eliminar la contraseña maestra.');
        }
    }
}