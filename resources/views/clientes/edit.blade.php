@extends('layouts.app')

@section('title', 'Editar Cliente')
@section('page-title', 'Editar: ' . $cliente->nombre)

@section('content')

<div class="max-w-2xl mx-auto">
    <x-alert type="error" :message="session('error')" />
    <x-alert type="success" :message="session('success')" />
    
    @if($errors->any())
    <div class="p-4 mb-6 border border-red-200 bg-red-50 rounded-xl">
        <ul class="list-disc list-inside text-sm text-red-600">@foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
    </div>
    @endif

    <div class="p-8 bg-white shadow-lg rounded-3xl">
        <div class="mb-8 text-center">
            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-500 to-cyan-500 text-white flex items-center justify-center text-2xl font-bold shadow-lg mx-auto mb-4">👤</div>
            <h2 class="text-2xl font-bold">Editar: {{ $cliente->nombre }}</h2>
        </div>

        <form id="formEditarCliente" onsubmit="return actualizarCliente(event)">
            @csrf @method('PUT')
            
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium mb-2">Nombre *</label>
                    <input type="text" name="nombre" id="edit_nombre" value="{{ old('nombre', $cliente->nombre) }}" required 
                        class="w-full border rounded-xl px-4 py-3 {{ $errors->has('nombre') ? 'border-red-500' : 'border-gray-300' }}">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">RFC</label>
                    <input type="text" name="rfc" id="edit_rfc" value="{{ old('rfc', $cliente->rfc) }}" 
                        class="w-full border rounded-xl px-4 py-3 uppercase">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Teléfono</label>
                        <input type="text" name="telefono" id="edit_telefono" value="{{ old('telefono', $cliente->telefono) }}" 
                            class="w-full border rounded-xl px-4 py-3">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Correo</label>
                        <input type="email" name="correo" id="edit_correo" value="{{ old('correo', $cliente->correo) }}" 
                            class="w-full border rounded-xl px-4 py-3">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Dirección</label>
                    <textarea name="direccion" id="edit_direccion" rows="2" class="w-full border rounded-xl px-4 py-3">{{ old('direccion', $cliente->direccion) }}</textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-2">Sucursal</label>
                    <select name="sucursal_id" id="edit_sucursal_id" class="w-full border rounded-xl px-4 py-3">
                        <option value="">Sin sucursal</option>
                        @foreach($sucursales as $suc) 
                            <option value="{{ $suc->id }}" {{ $cliente->sucursal_id == $suc->id ? 'selected' : '' }}>{{ $suc->nombre }}</option> 
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-2">Tipo *</label>
                    <select name="tipo" id="edit_tipoCliente" required class="w-full border rounded-xl px-4 py-3">
                        <option value="contado" {{ $cliente->tipo == 'contado' ? 'selected' : '' }}>💵 Contado</option>
                        <option value="credito" {{ $cliente->tipo == 'credito' ? 'selected' : '' }}>📋 Crédito</option>
                    </select>
                </div>
                
                <div id="edit_creditoFields" class="space-y-5 {{ $cliente->tipo == 'credito' ? '' : 'hidden' }}">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Límite de crédito $</label>
                            <input type="number" name="limite_credito" id="edit_limite_credito" value="{{ old('limite_credito', $cliente->limite_credito) }}" min="0" step="0.01" class="w-full border rounded-xl px-4 py-3">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Días de crédito</label>
                            <input type="number" name="dias_credito" id="edit_dias_credito" value="{{ old('dias_credito', $cliente->dias_credito) }}" min="0" class="w-full border rounded-xl px-4 py-3">
                        </div>
                    </div>
                </div>
                
                {{-- Checkbox activo --}}
                <div>
                    <input type="hidden" name="activo" value="0">
                    <label class="flex items-center gap-3 p-4 cursor-pointer bg-slate-50 rounded-xl">
                        <input type="checkbox" name="activo" id="edit_activo" value="1" class="w-5 h-5 text-indigo-600 rounded" {{ old('activo', $cliente->activo) ? 'checked' : '' }}>
                        <span class="text-sm font-medium">Cliente activo</span>
                    </label>
                </div>
            </div>
            
            <div class="flex justify-end gap-4 pt-6 mt-8 border-t">
                <a href="{{ route('clientes.index') }}" class="px-6 py-3 border-2 rounded-xl">Cancelar</a>
                @can('editar_clientes')
                <button type="submit" id="btnGuardarEdit" class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-cyan-500 text-white rounded-xl">💾 Guardar cambios</button>
                @endcan
            </div>
        </form>
    </div>
</div>

<script>
// Toggle crédito
document.getElementById('edit_tipoCliente')?.addEventListener('change', function() {
    document.getElementById('edit_creditoFields').classList.toggle('hidden', this.value !== 'credito');
});

// ✅ Actualizar con Axios
async function actualizarCliente(event) {
    event.preventDefault();
    
    const btn = document.getElementById('btnGuardarEdit');
    const form = document.getElementById('formEditarCliente');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    
    // El checkbox: si no está marcado, el hidden envía 0
    data.activo = form.querySelector('#edit_activo').checked ? '1' : '0';
    
    if (!data.nombre || data.nombre.trim() === '') {
        Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'El nombre es obligatorio.', confirmButtonColor: '#4f46e5' });
        return false;
    }
    
    btn.disabled = true;
    btn.innerHTML = '⏳ Guardando...';
    
    Swal.fire({ title: 'Guardando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    
    try {
        const response = await axios.post('{{ route("clientes.update", $cliente) }}', {
            ...data,
            _method: 'PUT'
        });
        
        if (response.data.success) {
            await Swal.fire({
                icon: 'success',
                title: '¡Actualizado!',
                html: response.data.message,
                confirmButtonColor: '#10b981',
                timer: 2000
            });
            window.location.href = '{{ route("clientes.index") }}';
        } else {
            throw new Error(response.data.message || 'Error');
        }
    } catch (error) {
        console.error('Error:', error);
        let msg = 'Error al actualizar el cliente';
        if (error.response?.status === 422) {
            msg = Object.values(error.response.data.errors).flat().join('\n');
        } else if (error.response?.data?.message) {
            msg = error.response.data.message;
        }
        await Swal.fire({ icon: 'error', title: 'Error', text: msg, confirmButtonColor: '#ef4444' });
    } finally {
        btn.disabled = false;
        btn.innerHTML = '💾 Guardar cambios';
    }
    
    return false;
}
</script>
@endsection