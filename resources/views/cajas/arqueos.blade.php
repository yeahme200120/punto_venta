    @extends('layouts.app')

    @section('title', 'Arqueo de Caja')
    @section('page-title', 'Arqueo de Caja')
    @section('breadcrumbs')
        <li>
            <span class="text-gray-400">/</span>
        </li>
        <li>
            <a href="{{ route('cajas.cajas.index') }}" class="text-gray-500 transition-colors hover:text-indigo-600">
                Cajas
            </a>
        </li>
        <li>
            <span class="text-gray-400">/</span>
        </li>
        <li>
            <span class="font-medium text-gray-700">Arqueo</span>
        </li>
    @endsection

    @section('content')

    <x-alert type="success" :message="session('success')" />
    <x-alert type="error" :message="session('error')" />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Formulario de arqueo --}}
        <div class="lg:col-span-2">
            <div class="p-6 bg-white shadow-lg rounded-3xl">
                <div class="flex items-center gap-3 mb-6">
                    <div class="flex items-center justify-center w-12 h-12 text-xl bg-indigo-100 rounded-full">💰</div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-800">Registrar Arqueo</h2>
                        <p class="text-sm text-gray-500">Cuenta el dinero físico y registra los montos</p>
                    </div>
                </div>
                
                {{-- Resumen de movimientos --}}
                <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-3">
                    <div class="p-3 text-center bg-green-50 rounded-xl">
                        <p class="text-xs text-gray-500">Total Ingresos</p>
                        <p class="text-xl font-bold text-green-600">+ ${{ number_format($totalIngresos, 2) }}</p>
                    </div>
                    <div class="p-3 text-center bg-red-50 rounded-xl">
                        <p class="text-xs text-gray-500">Total Egresos</p>
                        <p class="text-xl font-bold text-red-600">- ${{ number_format($totalEgresos, 2) }}</p>
                    </div>
                    <div class="p-3 text-center bg-blue-50 rounded-xl">
                        <p class="text-xs text-gray-500">Saldo Actual</p>
                        <p class="text-xl font-bold text-blue-600">${{ number_format($totalSistema, 2) }}</p>
                    </div>
                </div>

                {{-- Detalle de ingresos por forma de pago --}}
                <div class="p-4 mb-4 bg-green-50 rounded-xl">
                    <h4 class="mb-2 font-semibold text-green-800">📈 Ingresos por forma de pago</h4>
                    <div class="grid grid-cols-2 gap-2 text-sm md:grid-cols-3">
                        @foreach($formasPago as $forma)
                            @if(($totalesSistema[$forma->clave] ?? 0) > 0)
                            <div class="flex justify-between">
                                <span>{!! $forma->icono !!} {{ $forma->nombre }}:</span>
                                <span class="font-semibold text-green-600">+${{ number_format($totalesSistema[$forma->clave] ?? 0, 2) }}</span>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- Detalle de egresos por forma de pago --}}
                <div class="p-4 mb-4 bg-red-50 rounded-xl">
                    <h4 class="mb-2 font-semibold text-red-800">📉 Egresos por forma de pago</h4>
                    <div class="grid grid-cols-2 gap-2 text-sm md:grid-cols-3">
                        @foreach($formasPago as $forma)
                            @if(($egresosSistema[$forma->clave] ?? 0) > 0)
                            <div class="flex justify-between">
                                <span>{!! $forma->icono !!} {{ $forma->nombre }}:</span>
                                <span class="font-semibold text-red-600">-${{ number_format($egresosSistema[$forma->clave] ?? 0, 2) }}</span>
                            </div>
                            @endif
                        @endforeach
                        <div class="flex justify-between pt-2 mt-2 border-t border-red-200 col-span-full">
                            <span class="font-bold">Total Egresos:</span>
                            <span class="font-bold text-red-800">-${{ number_format($totalEgresos, 2) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Lo que debería haber en caja --}}
                <div class="p-4 mb-6 bg-blue-50 rounded-xl">
                    <h4 class="mb-2 font-semibold text-blue-800">💰 Lo que debería haber en caja (físico)</h4>
                    <div class="grid grid-cols-2 gap-2 text-sm md:grid-cols-3">
                        @foreach($formasPago as $forma)
                            <div class="flex justify-between">
                                <span>{!! $forma->icono !!} {{ $forma->nombre }}:</span>
                                <span class="font-semibold">${{ number_format($totalesSistema[$forma->clave] ?? 0, 2) }}</span>
                            </div>
                        @endforeach
                        <div class="flex justify-between pt-2 mt-2 border-t border-blue-200 col-span-full">
                            <span class="font-bold">Total esperado:</span>
                            <span class="font-bold text-blue-800">${{ number_format($totalSistema, 2) }}</span>
                        </div>
                    </div>
                </div>
                
                <form action="{{ route('cajas.arqueo.registrar') }}" method="POST" id="formArqueo">
                    @csrf
                    <input type="hidden" name="apertura_id" value="{{ $apertura->id }}">
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            @foreach($formasPago as $forma)
                                <div>
                                    <label class="block mb-1 text-sm font-medium text-gray-700">
                                        {!! $forma->icono !!} {{ $forma->nombre }} contado
                                        @if($forma->clave == 'efectivo')
                                            <span class="text-red-500">*</span>
                                        @endif
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2.5 text-gray-500">$</span>
                                        <input type="number" 
                                            name="{{ $forma->clave }}_contado" 
                                            id="{{ $forma->clave }}_contado" 
                                            step="0.01" 
                                            value="{{ $totalesSistema[$forma->clave] ?? 0 }}"
                                            @if($forma->clave == 'efectivo') required @endif
                                            class="w-full py-2 pl-8 pr-4 border rounded-xl focus:ring-2 focus:ring-indigo-500"
                                            oninput="calcularTotales()">
                                    </div>
                                    <div class="flex justify-between mt-1">
                                        <p class="text-xs text-gray-500">
                                            Sistema: ${{ number_format($totalesSistema[$forma->clave] ?? 0, 2) }}
                                        </p>
                                        @if(($totalesSistema[$forma->clave] ?? 0) > 0)
                                        <button type="button" 
                                                onclick="document.getElementById('{{ $forma->clave }}_contado').value = '{{ $totalesSistema[$forma->clave] ?? 0 }}'; calcularTotales();"
                                                class="text-xs text-indigo-600 hover:text-indigo-800">
                                            📋 Copiar
                                        </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">Observaciones</label>
                            <textarea name="observaciones" rows="3" class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-indigo-500"></textarea>
                        </div>
                        
                        <div class="p-4 space-y-2 bg-gray-50 rounded-xl">
                            <div class="flex items-center justify-between">
                                <span class="font-medium">💰 Total contado:</span>
                                <span id="total_contado" class="text-xl font-bold text-green-600">$0.00</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="font-medium">📊 Total según sistema:</span>
                                <span class="text-xl font-bold text-blue-600">${{ number_format($totalSistema, 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between pt-2 border-t">
                                <span class="font-medium">⚖️ Diferencia:</span>
                                <span id="diferencia" class="text-xl font-bold">$0.00</span>
                            </div>
                            @if($totalSistema > 0)
                            <div class="pt-2 mt-2 text-sm text-yellow-600 border-t">
                                💡 Si la diferencia es positiva (sobrante), puedes retirar el excedente.
                            </div>
                            @endif
                        </div>
                        
                        <div class="flex gap-3">
                            <button type="submit" name="estado" value="borrador" class="flex-1 py-3 font-medium text-indigo-600 transition border border-indigo-600 rounded-xl hover:bg-indigo-50">
                                💾 Guardar Borrador
                            </button>
                            <button type="button" id="btnFinalizar" class="flex-1 py-3 font-semibold text-white transition shadow-lg bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-xl hover:from-indigo-700 hover:to-cyan-600">
                                ✅ Finalizar Arqueo
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        {{-- Historial de arqueos --}}
        <div class="lg:col-span-1">
            <div class="sticky p-6 bg-white shadow-lg rounded-3xl top-24">
                <h3 class="mb-4 text-lg font-bold text-slate-800">📋 Historial de arqueos</h3>
                
                <div class="space-y-3 overflow-y-auto max-h-96">
                    @forelse($arqueos as $item)
                    <div class="p-3 border rounded-xl hover:bg-gray-50">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium">{{ $item->created_at->format('d/m/Y H:i') }}</p>
                                <p class="text-xs text-gray-500">Por: {{ $item->usuario->name }}</p>
                            </div>
                            <span class="px-2 py-1 text-xs rounded-full {{ $item->estado == 'finalizado' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                {{ $item->estado == 'finalizado' ? 'Finalizado' : 'Borrador' }}
                            </span>
                        </div>
                        <div class="flex justify-between mt-2 text-sm">
                            <span>Total contado:</span>
                            <span class="font-semibold">${{ number_format($item->total_contado, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span>Diferencia:</span>
                            <span class="font-semibold {{ $item->diferencia >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $item->diferencia >= 0 ? '+' : '' }}${{ number_format($item->diferencia, 2) }}
                            </span>
                        </div>
                        <div class="flex gap-2 mt-2">
                            <a href="{{ route('cajas.arqueo.ver', $item) }}" class="text-xs text-indigo-600 hover:text-indigo-800">Ver detalle</a>
                            <a href="{{ route('cajas.arqueo.imprimir', $item) }}" class="text-xs text-gray-600 hover:text-gray-800" target="_blank">Imprimir</a>
                        </div>
                    </div>
                    @empty
                    <p class="py-6 text-center text-gray-400">No hay arqueos registrados</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <script>
    function calcularTotales() {
        let totalContado = 0;
        const formasPago = @json($formasPago);
        
        formasPago.forEach(forma => {
            const input = document.getElementById(`${forma.clave}_contado`);
            if (input) {
                const valor = parseFloat(input.value) || 0;
                totalContado += valor;
            }
        });
        
        const totalSistema = {{ $totalSistema }};
        const diferencia = totalContado - totalSistema;
        
        document.getElementById('total_contado').innerHTML = `$${totalContado.toFixed(2)}`;
        
        const diferenciaSpan = document.getElementById('diferencia');
        diferenciaSpan.innerHTML = `${diferencia >= 0 ? '+' : ''}$${Math.abs(diferencia).toFixed(2)}`;
        
        if (diferencia > 0) {
            diferenciaSpan.className = 'text-xl font-bold text-green-600';
        } else if (diferencia < 0) {
            diferenciaSpan.className = 'text-xl font-bold text-red-600';
        } else {
            diferenciaSpan.className = 'text-xl font-bold text-gray-600';
        }
    }

    function validarFormulario() {
        const efectivoInput = document.getElementById('efectivo_contado');
        const efectivo = efectivoInput ? parseFloat(efectivoInput.value) || 0 : 0;
        
        if (efectivo <= 0) {
            Swal.fire({
                title: 'Campo requerido',
                text: 'El campo Efectivo contado es obligatorio y debe ser mayor a 0.',
                icon: 'warning',
                confirmButtonColor: '#4f46e5',
                confirmButtonText: 'Entendido'
            });
            return false;
        }
        
        return true;
    }

    // SweetAlert para finalizar arqueo
    document.getElementById('btnFinalizar').addEventListener('click', function(e) {
        e.preventDefault();
        
        if (!validarFormulario()) {
            return;
        }
        
        Swal.fire({
            title: '¿Finalizar arqueo?',
            text: 'Una vez finalizado no se podrá modificar.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#ef4444',
            confirmButtonText: '✅ Sí, finalizar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('formArqueo');
                const inputEstado = document.createElement('input');
                inputEstado.type = 'hidden';
                inputEstado.name = 'estado';
                inputEstado.value = 'finalizado';
                form.appendChild(inputEstado);
                form.submit();
            }
        });
    });

    // Validar también para guardar borrador
    document.querySelector('button[name="estado"][value="borrador"]').addEventListener('click', function(e) {
        if (!validarFormulario()) {
            e.preventDefault();
            return false;
        }
    });

    // Inicializar los valores por defecto con los totales del sistema
    document.addEventListener('DOMContentLoaded', function() {
        const formasPago = @json($formasPago);
        const totalesSistema = @json($totalesSistema);
        
        formasPago.forEach(forma => {
            const input = document.getElementById(`${forma.clave}_contado`);
            if (input && totalesSistema[forma.clave] > 0) {
                input.value = totalesSistema[forma.clave];
            }
        });
        calcularTotales();
    });
    </script>
    @endsection