@extends('layouts.app')

@section('title', 'Nueva Configuración de Ticket')
@section('page-title', 'Nueva Configuración')
@section('breadcrumbs')
    <li><span class="text-gray-400">/</span></li><li><a href="{{ route('ticket.index') }}" class="text-gray-500 hover:text-indigo-600">Tickets</a></li><li><span class="text-gray-400">/</span></li><li><span class="font-medium text-gray-700">Nuevo</span></li>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="p-6 bg-white border shadow-sm rounded-2xl">
        <form id="ticketForm" action="{{ route('ticket.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">Tipo de ticket *</label>
                    <select name="tipo" required class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                        <option value="">Seleccionar</option>
                        <option value="movimiento" {{ old('tipo') == 'movimiento' ? 'selected' : '' }}>Movimiento de caja</option>
                        <option value="transferencia" {{ old('tipo') == 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                        <option value="arqueo" {{ old('tipo') == 'arqueo' ? 'selected' : '' }}>Arqueo</option>
                        <option value="cierre" {{ old('tipo') == 'cierre' ? 'selected' : '' }}>Cierre de caja</option>
                    </select>
                    @error('tipo') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">Nombre de la empresa</label>
                    <input type="text" name="nombre_empresa" value="{{ old('nombre_empresa') }}" class="w-full px-4 py-2 border rounded-xl">
                </div>
                <div class="md:col-span-2">
                    <label class="block mb-1 text-sm font-medium text-gray-700">Logo (imagen)</label>
                    <input type="file" name="logo" accept="image/jpeg,image/png,image/gif,image/webp" class="w-full px-4 py-2 border rounded-xl">
                    <p class="mt-1 text-xs text-gray-400">Formatos: JPG, PNG, GIF, WEBP. Máx. 20MB.</p>
                    @error('logo') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block mb-1 text-sm font-medium text-gray-700">Dirección</label>
                    <input type="text" name="direccion" value="{{ old('direccion') }}" class="w-full px-4 py-2 border rounded-xl">
                </div>
                <div><label>Teléfono</label><input type="text" name="telefono" value="{{ old('telefono') }}" class="w-full px-4 py-2 border rounded-xl"></div>
                <div><label>Email</label><input type="email" name="email" value="{{ old('email') }}" class="w-full px-4 py-2 border rounded-xl"></div>
                <div><label>RFC</label><input type="text" name="rfc" value="{{ old('rfc') }}" class="w-full px-4 py-2 border rounded-xl"></div>
                <div><label>Cabecera (título del ticket)</label><input type="text" name="cabecera" value="{{ old('cabecera') }}" class="w-full px-4 py-2 border rounded-xl"></div>
                <div class="md:col-span-2"><label>Footer</label><input type="text" name="footer" value="{{ old('footer', '¡Gracias por su compra!') }}" class="w-full px-4 py-2 border rounded-xl"></div>
                <div><label>Ancho del papel</label><select name="ancho_papel" class="w-full px-4 py-2 border rounded-xl"><option value="80mm" {{ old('ancho_papel') == '80mm' ? 'selected' : '' }}>80mm</option><option value="58mm" {{ old('ancho_papel') == '58mm' ? 'selected' : '' }}>58mm</option></select></div>
                <div><label>Fuente</label><select name="fuente" class="w-full px-4 py-2 border rounded-xl"><option value="monospace" {{ old('fuente') == 'monospace' ? 'selected' : '' }}>Monospace</option><option value="sans-serif" {{ old('fuente') == 'sans-serif' ? 'selected' : '' }}>Sans-serif</option><option value="serif" {{ old('fuente') == 'serif' ? 'selected' : '' }}>Serif</option></select></div>
                <div><label>Tamaño de fuente (px)</label><input type="number" name="tamano_fuente" value="{{ old('tamano_fuente', 12) }}" min="8" max="20" class="w-32 px-4 py-2 border rounded-xl"></div>
                <div><label>Copias</label><input type="number" name="copias" value="{{ old('copias', 1) }}" min="1" max="5" class="w-24 px-4 py-2 border rounded-xl"></div>
            </div>
            <div class="grid grid-cols-2 gap-4 mt-6 md:grid-cols-4">
                <label><input type="checkbox" name="mostrar_logo" {{ old('mostrar_logo') ? 'checked' : '' }}> Mostrar logo</label>
                <label><input type="checkbox" name="mostrar_direccion" {{ old('mostrar_direccion', true) ? 'checked' : '' }}> Mostrar dirección</label>
                <label><input type="checkbox" name="mostrar_telefono" {{ old('mostrar_telefono', true) ? 'checked' : '' }}> Mostrar teléfono</label>
                <label><input type="checkbox" name="mostrar_email" {{ old('mostrar_email', true) ? 'checked' : '' }}> Mostrar email</label>
                <label><input type="checkbox" name="mostrar_rfc" {{ old('mostrar_rfc', true) ? 'checked' : '' }}> Mostrar RFC</label>
                <label><input type="checkbox" name="mostrar_regimen" {{ old('mostrar_regimen') ? 'checked' : '' }}> Mostrar régimen fiscal</label>
                <label><input type="checkbox" name="auto_imprimir" {{ old('auto_imprimir', true) ? 'checked' : '' }}> Auto imprimir</label>
                <label><input type="checkbox" name="facturar" {{ old('facturar', true) ? 'checked' : '' }}> Facturar</label>
                <label><input type="checkbox" name="activo" {{ old('activo', true) ? 'checked' : '' }}> Activo</label>
            </div>
            <div class="flex justify-end gap-3 pt-6 mt-6 border-t">
                <a href="{{ route('ticket.index') }}" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200">Cancelar</a>
                <button type="submit" class="px-6 py-2 text-white bg-indigo-600 rounded-xl hover:bg-indigo-700">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById('ticketForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    Swal.fire({ title: 'Guardando...', text: 'Por favor espere', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    fetch(this.action, { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(res => res.json())
    .then(data => { if(data.success) Swal.fire({ icon: data.icon, title: data.title, text: data.message, timer: 1500, showConfirmButton: false }).then(() => window.location.href = data.redirect); else Swal.fire({ icon: 'error', title: 'Error', text: data.message }); })
    .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Error al guardar' }));
});
</script>
@endsection