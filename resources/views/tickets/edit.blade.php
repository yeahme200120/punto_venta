@extends('layouts.app')

@section('title', 'Editar Configuración de Ticket')
@section('page-title', 'Editar Configuración')
@section('breadcrumbs')
    <li><span class="text-gray-400">/</span></li>
    <li><a href="{{ route('ticket.index') }}" class="text-gray-500 hover:text-indigo-600">Tickets</a></li>
    <li><span class="text-gray-400">/</span></li>
    <li><span class="font-medium text-gray-700">Editar</span></li>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="p-6 bg-white border shadow-sm rounded-2xl">
        <form id="ticketForm" action="{{ route('ticket.update', $ticketConfiguracion) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid gap-6 md:grid-cols-2">
                {{-- Tipo (solo lectura) --}}
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">Tipo de ticket</label>
                    <input type="text" value="{{ ucfirst($ticketConfiguracion->tipo) }}" disabled
                           class="w-full px-4 py-2 bg-gray-100 border rounded-xl">
                    <input type="hidden" name="tipo" value="{{ $ticketConfiguracion->tipo }}">
                </div>

                {{-- Nombre empresa --}}
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">Nombre de la empresa</label>
                    <input type="text" name="nombre_empresa" value="{{ old('nombre_empresa', $ticketConfiguracion->nombre_empresa) }}"
                           class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                    @error('nombre_empresa') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Logo actual + carga --}}
                <div class="md:col-span-2">
                    <label class="block mb-1 text-sm font-medium text-gray-700">Logo</label>
                    @if($ticketConfiguracion->logo_url && $ticketConfiguracion->mostrar_logo)
                        <div class="mb-2">
                            <img src="{{ $ticketConfiguracion->logo_url }}" class="h-12" alt="Logo actual">
                        </div>
                    @else
                        <p class="mb-2 text-gray-400">Sin logo actual</p>
                    @endif
                    <input type="file" name="logo" accept="image/jpeg,image/png,image/gif,image/webp"
                           class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                    <p class="mt-1 text-xs text-gray-400">Dejar vacío para mantener el actual.</p>
                    @error('logo') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Dirección --}}
                <div class="md:col-span-2">
                    <label class="block mb-1 text-sm font-medium text-gray-700">Dirección</label>
                    <input type="text" name="direccion" value="{{ old('direccion', $ticketConfiguracion->direccion) }}"
                           class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                    @error('direccion') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Teléfono --}}
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">Teléfono</label>
                    <input type="text" name="telefono" value="{{ old('telefono', $ticketConfiguracion->telefono) }}"
                           class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                    @error('telefono') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" value="{{ old('email', $ticketConfiguracion->email) }}"
                           class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                    @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- RFC --}}
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">RFC</label>
                    <input type="text" name="rfc" value="{{ old('rfc', $ticketConfiguracion->rfc) }}"
                           class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                    @error('rfc') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Cabecera --}}
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">Cabecera</label>
                    <input type="text" name="cabecera" value="{{ old('cabecera', $ticketConfiguracion->cabecera) }}"
                           class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                    @error('cabecera') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Footer (md:col-span-2) --}}
                <div class="md:col-span-2">
                    <label class="block mb-1 text-sm font-medium text-gray-700">Footer</label>
                    <input type="text" name="footer" value="{{ old('footer', $ticketConfiguracion->footer) }}"
                           class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                    @error('footer') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Ancho papel --}}
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">Ancho del papel</label>
                    <select name="ancho_papel" class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                        <option value="80mm" {{ old('ancho_papel', $ticketConfiguracion->ancho_papel) == '80mm' ? 'selected' : '' }}>80mm</option>
                        <option value="58mm" {{ old('ancho_papel', $ticketConfiguracion->ancho_papel) == '58mm' ? 'selected' : '' }}>58mm</option>
                    </select>
                    @error('ancho_papel') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Fuente --}}
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">Fuente</label>
                    <select name="fuente" class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                        <option value="monospace" {{ old('fuente', $ticketConfiguracion->fuente) == 'monospace' ? 'selected' : '' }}>Monospace</option>
                        <option value="sans-serif" {{ old('fuente', $ticketConfiguracion->fuente) == 'sans-serif' ? 'selected' : '' }}>Sans-serif</option>
                        <option value="serif" {{ old('fuente', $ticketConfiguracion->fuente) == 'serif' ? 'selected' : '' }}>Serif</option>
                    </select>
                    @error('fuente') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Tamaño fuente --}}
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">Tamaño fuente (px)</label>
                    <input type="number" name="tamano_fuente" value="{{ old('tamano_fuente', $ticketConfiguracion->tamano_fuente) }}"
                           min="8" max="20" class="w-32 px-4 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                    @error('tamano_fuente') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Copias --}}
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">Copias</label>
                    <input type="number" name="copias" value="{{ old('copias', $ticketConfiguracion->copias) }}"
                           min="1" max="5" class="w-24 px-4 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                    @error('copias') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Checkboxes --}}
            <div class="grid grid-cols-2 gap-4 mt-6 md:grid-cols-4">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="mostrar_logo" {{ old('mostrar_logo', $ticketConfiguracion->mostrar_logo) ? 'checked' : '' }}>
                    <span>Mostrar logo</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="mostrar_direccion" {{ old('mostrar_direccion', $ticketConfiguracion->mostrar_direccion) ? 'checked' : '' }}>
                    <span>Mostrar dirección</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="mostrar_telefono" {{ old('mostrar_telefono', $ticketConfiguracion->mostrar_telefono) ? 'checked' : '' }}>
                    <span>Mostrar teléfono</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="mostrar_email" {{ old('mostrar_email', $ticketConfiguracion->mostrar_email) ? 'checked' : '' }}>
                    <span>Mostrar email</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="mostrar_rfc" {{ old('mostrar_rfc', $ticketConfiguracion->mostrar_rfc) ? 'checked' : '' }}>
                    <span>Mostrar RFC</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="mostrar_regimen" {{ old('mostrar_regimen', $ticketConfiguracion->mostrar_regimen) ? 'checked' : '' }}>
                    <span>Mostrar régimen fiscal</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="auto_imprimir" {{ old('auto_imprimir', $ticketConfiguracion->auto_imprimir) ? 'checked' : '' }}>
                    <span>Auto imprimir</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="facturar" {{ old('facturar', $ticketConfiguracion->facturar) ? 'checked' : '' }}>
                    <span>Facturar</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="activo" {{ old('activo', $ticketConfiguracion->activo) ? 'checked' : '' }}>
                    <span>Activo</span>
                </label>
            </div>

            <div class="flex justify-end gap-3 pt-6 mt-6 border-t">
                <a href="{{ route('ticket.index') }}" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200">Cancelar</a>
                <button type="submit" class="px-6 py-2 text-white bg-indigo-600 rounded-xl hover:bg-indigo-700">Actualizar</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById('ticketForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    Swal.fire({
        title: 'Actualizando...',
        text: 'Por favor espere',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(async res => {
        const data = await res.json();
        if (!res.ok) {
            // Si es 422 (errores de validación)
            if (res.status === 422) {
                Swal.fire({
                    icon: 'error',
                    title: 'Errores de validación',
                    html: data.message || 'Hay errores en el formulario. Revise los campos.',
                    confirmButtonText: 'Entendido'
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: data.title || 'Error',
                    text: data.message || 'Ocurrió un error inesperado',
                    confirmButtonText: 'Ok'
                });
            }
            return;
        }
        // Éxito
        if (data.success) {
            Swal.fire({
                icon: data.icon,
                title: data.title,
                text: data.message,
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                window.location.href = data.redirect;
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: data.title || 'Error',
                text: data.message,
                confirmButtonText: 'Ok'
            });
        }
    })
    .catch(() => {
        Swal.fire({
            icon: 'error',
            title: 'Error de conexión',
            text: 'No se pudo completar la solicitud',
            confirmButtonText: 'Ok'
        });
    });
});
</script>
@endsection