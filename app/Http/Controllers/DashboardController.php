<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $data = [
            'totalUsuarios' => User::where('empresa_id', $user->empresa_id)->count(),
            'empresa' => $user->empresa,
            'licencia' => $user->empresa->licencia,
            'diasRestantes' => $user->empresa->fecha_fin ? now()->diffInDays($user->empresa->fecha_fin, false) : 0,
        ];

        return view('dashboard.index', $data);
    }
}