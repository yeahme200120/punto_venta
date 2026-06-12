@extends('layouts.app')

@section('title', 'Editar Rol')
@section('page-title', 'Editar: ' . $role->name)

@section('content')
<div class="max-w-4xl mx-auto">
    <x-alert type="error" :message="session('error')" />
    
    @if($errors->any())
    <div class="p-4 mb-6 border border-red-200 bg-red-50 rounded-xl">
        <ul class="list-disc list-inside text-sm text-red-600">@foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
    </div>
    @endif

    <div class="p-8 bg-white shadow-lg rounded-3xl">
        <div class="mb-8 text-center">
            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-purple-600 to-pink-500 text-white flex items-center justify-center text-2xl shadow-lg mx-auto mb-4">{{ strtoupper(substr($role->name, 0, 1)) }}</div>
            <h2 class="text-2xl font-bold">Editar: {{ $role->name }}</h2>
        </div>

        <form action="{{ route('roles.update', $role) }}" method="POST" id="formEditarRol" onsubmit="return actualizarRol(event)">
            @csrf @method('PUT')
            
            <div class="mb-6">
                <label class="block text-sm font-medium mb-2">Nombre del rol *</label>
                <input type="text" name="name" value="{{ old('name', $role->name) }}" required 
                    class="w-full border rounded-xl px-4 py-3" {{ $role->name === 'Super Admin' ? 'readonly' : '' }}>
            </div>

            <div class="mb-6">
                <h3 class="font-bold text-lg mb-4">Permisos</h3>
                <div class="flex gap-2 mb-4">
                    <button type="button" onclick="marcarTodos()" class="px-4 py-2 bg-green-500 text-white rounded-xl text-sm">✓ Todos</button>
                    <button type="button" onclick="desmarcarTodos()" class="px-4 py-2 bg-red-500 text-white rounded-xl text-sm">✕ Ninguno</button>
                </div>

                <div class="space-y-4 max-h-[400px] overflow-y-auto">
                    @foreach($permisos as $modulo => $items)
                    <div class="border rounded-xl p-4">
                        <h4 class="text-sm font-bold uppercase text-indigo-600 mb-2">{{ $modulo }}</h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-1">
                            @foreach($items as $permiso)
                            <label class="flex items-center gap-2 p-2 rounded-lg hover:bg-indigo-50 cursor-pointer text-sm">
                                <input type="checkbox" name="permisos[]" value="{{ $permiso->name }}" class="permiso-check w-4 h-4 text-indigo-600 rounded"
                                    {{ $role->hasPermissionTo($permiso->name) ? 'checked' : '' }}>
                                <span class="text-xs">{{ str_replace('_', ' ', $permiso->name) }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end gap-4 pt-6 border-t">
                <a href="{{ route('roles.index') }}" class="px-6 py-3 border-2 rounded-xl">Cancelar</a>
                @can('editar_roles')
                <button type="submit" id="btnGuardarEdit" class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-cyan-500 text-white rounded-xl">💾 Guardar cambios</button>
                @endcan
            </div>
        </form>
    </div>
</div>

<script>
function marcarTodos() { document.querySelectorAll('.permiso-check').forEach(cb => cb.checked = true); }
function desmarcarTodos() { document.querySelectorAll('.permiso-check').forEach(cb => cb.checked = false); }

async function actualizarRol(event) {
    event.preventDefault();
    const btn = document.getElementById('btnGuardarEdit');
    const form = document.getElementById('formEditarRol');
    const formData = new FormData(form);
    
    btn.disabled = true;
    Swal.fire({ title: 'Guardando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    
    try {
        const res = await axios.post(form.action, formData, { headers: { 'X-HTTP-Method-Override': 'PUT' } });
        if (res.data?.success !== false) {
            await Swal.fire({ icon: 'success', title: 'Rol actualizado', timer: 2000 });
            window.location.href = '{{ route("roles.index") }}';
        }
    } catch(e) {
        let msg = 'Error al actualizar';
        if (e.response?.status === 422) msg = Object.values(e.response.data.errors).flat().join('\n');
        Swal.fire({ icon: 'error', title: 'Error', text: msg, confirmButtonColor: '#ef4444' });
    } finally { btn.disabled = false; }
    return false;
}
</script>
@endsection