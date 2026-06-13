@extends('layouts.app')

@section('title', 'Editar Usuario')
@section('page-title', 'Editar Usuario')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="overflow-hidden bg-white shadow-lg rounded-3xl">
        <div class="p-6 border-b bg-gradient-to-r from-indigo-600 to-cyan-500">
            <h2 class="text-xl font-bold text-white">Editar usuario</h2>
            <p class="text-sm text-indigo-100">{{ $usuario->name }}</p>
        </div>

        <form id="formEditarUsuario" onsubmit="return actualizarUsuario(event)" class="p-6 space-y-5">
            @csrf
            @method('PUT')
            
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">Nombre completo *</label>
                <input type="text" name="name" id="name" value="{{ old('name', $usuario->name) }}" required 
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">Correo electrónico *</label>
                <input type="email" name="email" id="email" value="{{ old('email', $usuario->email) }}" required 
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">Nueva contraseña</label>
                <input type="password" name="password" id="password" minlength="6"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                <p class="mt-1 text-xs text-gray-400">Dejar en blanco para mantener la actual</p>
            </div>

            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">Confirmar nueva contraseña</label>
                <input type="password" name="password_confirmation" id="password_confirmation"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
            </div>

            @if(auth()->user()->hasRole('Super Admin'))
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">Empresa</label>
                <select name="empresa_id" id="empresa_id" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                    <option value="">Seleccionar empresa...</option>
                    @foreach($empresas as $empresa)
                        <option value="{{ $empresa->id }}" {{ $empresa->id == $usuario->empresa_id ? 'selected' : '' }}>
                            {{ $empresa->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">Sucursal</label>
                <select name="sucursal_id" id="sucursal_id" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                    <option value="">Seleccionar sucursal...</option>
                    @foreach($sucursales as $sucursal)
                        <option value="{{ $sucursal->id }}" {{ $sucursal->id == $usuario->sucursal_id ? 'selected' : '' }}>
                            {{ $sucursal->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">Roles</label>
                <div class="space-y-2">
                    @foreach($roles as $role)
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="roles[]" value="{{ $role->name }}" 
                            {{ $usuario->hasRole($role->name) ? 'checked' : '' }}
                            class="text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <span class="text-sm text-gray-700">{{ $role->name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="activo" id="activo" value="1" {{ $usuario->activo ? 'checked' : '' }}
                    class="text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                <label for="activo" class="text-sm font-medium text-gray-700">Usuario activo</label>
            </div>

            <div class="flex justify-end gap-4 pt-4">
                <a href="{{ route('usuarios.index') }}" 
                    class="px-6 py-3 font-medium transition border-2 border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50">
                    Cancelar
                </a>
                <button type="submit" id="btnGuardarEdit" 
                    class="px-8 py-3 font-semibold text-white transition shadow-lg bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-xl hover:from-indigo-700 hover:to-cyan-600">
                    💾 Guardar cambios
                </button>
            </div>
        </form>
    </div>
</div>

<script>
async function actualizarUsuario(event) {
    event.preventDefault();
    const btn = document.getElementById('btnGuardarEdit');
    const form = document.getElementById('formEditarUsuario');
    const formData = new FormData(form);
    const url = '{{ route("usuarios.update", $usuario) }}';
    
    btn.disabled = true;
    Swal.fire({ title: 'Guardando cambios...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    
    try {
        const res = await axios.post(url, formData, { 
            headers: { 'X-HTTP-Method-Override': 'PUT' } 
        });
        if (res.data?.success !== false) {
            await Swal.fire({ icon: 'success', title: 'Usuario actualizado', text: 'Los cambios se han guardado correctamente.', timer: 2000 });
            window.location.href = '{{ route("usuarios.index") }}';
        }
    } catch(e) {
        let msg = 'Error al actualizar usuario';
        if (e.response?.status === 422) {
            const errors = e.response.data.errors;
            msg = Object.values(errors).flat().join('\n');
        } else if (e.response?.data?.message) {
            msg = e.response.data.message;
        }
        Swal.fire({ icon: 'error', title: 'Error', text: msg, confirmButtonColor: '#ef4444' });
    } finally { 
        btn.disabled = false; 
    }
    return false;
}
</script>
@endsection