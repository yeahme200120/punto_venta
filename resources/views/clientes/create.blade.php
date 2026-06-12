@extends('layouts.app')

@section('title', 'Nuevo Cliente')
@section('page-title', 'Nuevo Cliente')

@section('content')

<div class="max-w-2xl mx-auto">
    <x-alert type="error" :message="session('error')" />
    
    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
        <ul class="list-disc list-inside text-sm text-red-600">@foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
    </div>
    @endif

    <div class="bg-white rounded-3xl shadow-lg p-8">
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-500 to-cyan-500 text-white flex items-center justify-center text-2xl font-bold shadow-lg mx-auto mb-4">👤</div>
            <h2 class="text-2xl font-bold">Registrar cliente</h2>
        </div>

        <form id="formCrearCliente" onsubmit="return guardarCliente(event)">
            @csrf
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium mb-2">Nombre *</label>
                    <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" required 
                        class="w-full border rounded-xl px-4 py-3 {{ $errors->has('nombre') ? 'border-red-500' : 'border-gray-300' }}">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">RFC</label>
                    <input type="text" name="rfc" id="rfc" value="{{ old('rfc') }}" 
                        class="w-full border rounded-xl px-4 py-3 uppercase">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Teléfono</label>
                        <input type="text" name="telefono" id="telefono" value="{{ old('telefono') }}" 
                            class="w-full border rounded-xl px-4 py-3">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Correo</label>
                        <input type="email" name="correo" id="correo" value="{{ old('correo') }}" 
                            class="w-full border rounded-xl px-4 py-3">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Dirección</label>
                    <textarea name="direccion" id="direccion" rows="2" class="w-full border rounded-xl px-4 py-3">{{ old('direccion') }}</textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-2">Sucursal</label>
                    <select name="sucursal_id" id="sucursal_id" class="w-full border rounded-xl px-4 py-3">
                        <option value="">Sin sucursal</option>
                        @foreach($sucursales as $suc) 
                            <option value="{{ $suc->id }}" {{ old('sucursal_id') == $suc->id ? 'selected' : '' }}>{{ $suc->nombre }}</option> 
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-2">Tipo *</label>
                    <select name="tipo" id="tipoCliente" required class="w-full border rounded-xl px-4 py-3">
                        <option value="contado" {{ old('tipo') == 'contado' ? 'selected' : '' }}>💵 Contado</option>
                        <option value="credito" {{ old('tipo') == 'credito' ? 'selected' : '' }}>📋 Crédito</option>
                    </select>
                </div>
                
                <div id="creditoFields" class="space-y-5 {{ old('tipo') == 'credito' ? '' : 'hidden' }}">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Límite de crédito $</label>
                            <input type="number" name="limite_credito" id="limite_credito" value="{{ old('limite_credito', 0) }}" min="0" step="0.01" class="w-full border rounded-xl px-4 py-3">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Días de crédito</label>
                            <input type="number" name="dias_credito" id="dias_credito" value="{{ old('dias_credito', 0) }}" min="0" class="w-full border rounded-xl px-4 py-3">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end gap-4 mt-8 pt-6 border-t">
                <a href="{{ route('clientes.index') }}" class="px-6 py-3 border-2 rounded-xl">Cancelar</a>
                @can('crear_clientes')
                <button type="submit" id="btnGuardar" class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-cyan-500 text-white rounded-xl">💾 Crear cliente</button>
                @endcan
            </div>
        </form>
    </div>
</div>

<script>
// Toggle crédito
document.getElementById('tipoCliente')?.addEventListener('change', function() {
    document.getElementById('creditoFields').classList.toggle('hidden', this.value !== 'credito');
});

// ✅ Guardar con Axios
async function guardarCliente(event) {
    event.preventDefault();
    
    const btn = document.getElementById('btnGuardar');
    const form = document.getElementById('formCrearCliente');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    
    // Validar nombre
    if (!data.nombre || data.nombre.trim() === '') {
        Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'El nombre del cliente es obligatorio.', confirmButtonColor: '#4f46e5' });
        return false;
    }
    
    btn.disabled = true;
    btn.innerHTML = '⏳ Guardando...';
    
    Swal.fire({ title: 'Guardando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    
    try {
        const response = await axios.post('{{ route("clientes.store") }}', data);
        
        if (response.data.success) {
            await Swal.fire({
                icon: 'success',
                title: '¡Creado!',
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
        let msg = 'Error al crear el cliente';
        if (error.response?.status === 422) {
            const errors = error.response.data.errors;
            msg = Object.values(errors).flat().join('\n');
        } else if (error.response?.data?.message) {
            msg = error.response.data.message;
        }
        await Swal.fire({ icon: 'error', title: 'Error', text: msg, confirmButtonColor: '#ef4444' });
    } finally {
        btn.disabled = false;
        btn.innerHTML = '💾 Crear cliente';
    }
    
    return false;
}
</script>
@endsection