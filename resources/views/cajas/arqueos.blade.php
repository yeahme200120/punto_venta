@extends('layouts.app')

@section('title', 'Arqueo de Caja')
@section('page-title', 'Arqueo de Caja')
@section('breadcrumbs')
    <li>
        <span class="text-gray-400">/</span>
    </li>
    <li>
        <a href="{{ route('cajas.index') }}" class="text-gray-500 transition-colors hover:text-indigo-600">
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
            
            <form action="{{ route('cajas.arqueo.registrar') }}" method="POST" id="formArqueo">
                @csrf
                <input type="hidden" name="apertura_id" value="{{ $apertura->id }}">
                
                <div class="space-y-4">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">
                                💵 Efectivo contado *
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-gray-500">$</span>
                                <input type="number" name="efectivo_contado" id="efectivo_contado" step="0.01" required
                                    class="w-full py-2 pl-8 pr-4 border rounded-xl focus:ring-2 focus:ring-indigo-500"
                                    oninput="calcularTotales()">
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Sistema: ${{ number_format($totalesSistema['efectivo'], 2) }}</p>
                        </div>
                        
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">
                                💳 Tarjeta Débito contado *
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-gray-500">$</span>
                                <input type="number" name="tarjeta_debito_contado" id="tarjeta_debito_contado" step="0.01" required
                                    class="w-full py-2 pl-8 pr-4 border rounded-xl focus:ring-2 focus:ring-indigo-500"
                                    oninput="calcularTotales()">
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Sistema: ${{ number_format($totalesSistema['tarjeta_debito'], 2) }}</p>
                        </div>
                        
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">
                                💎 Tarjeta Crédito contado *
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-gray-500">$</span>
                                <input type="number" name="tarjeta_credito_contado" id="tarjeta_credito_contado" step="0.01" required
                                    class="w-full py-2 pl-8 pr-4 border rounded-xl focus:ring-2 focus:ring-indigo-500"
                                    oninput="calcularTotales()">
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Sistema: ${{ number_format($totalesSistema['tarjeta_credito'], 2) }}</p>
                        </div>
                        
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">
                                🎫 Vale contado *
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-gray-500">$</span>
                                <input type="number" name="vale_contado" id="vale_contado" step="0.01" required
                                    class="w-full py-2 pl-8 pr-4 border rounded-xl focus:ring-2 focus:ring-indigo-500"
                                    oninput="calcularTotales()">
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Sistema: ${{ number_format($totalesSistema['vale'], 2) }}</p>
                        </div>
                        
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">
                                🏦 Transferencia contado *
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-gray-500">$</span>
                                <input type="number" name="transferencia_contado" id="transferencia_contado" step="0.01" required
                                    class="w-full py-2 pl-8 pr-4 border rounded-xl focus:ring-2 focus:ring-indigo-500"
                                    oninput="calcularTotales()">
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Sistema: ${{ number_format($totalesSistema['transferencia'], 2) }}</p>
                        </div>
                        
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">
                                📄 Cheque contado *
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-gray-500">$</span>
                                <input type="number" name="cheque_contado" id="cheque_contado" step="0.01" required
                                    class="w-full py-2 pl-8 pr-4 border rounded-xl focus:ring-2 focus:ring-indigo-500"
                                    oninput="calcularTotales()">
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Sistema: ${{ number_format($totalesSistema['cheque'], 2) }}</p>
                        </div>
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
                    </div>
                    
                    <div class="flex gap-3">
                        <button type="submit" name="estado" value="borrador" class="flex-1 py-3 font-medium text-indigo-600 transition border border-indigo-600 rounded-xl hover:bg-indigo-50">
                            💾 Guardar Borrador
                        </button>
                        <button type="submit" name="estado" value="finalizado" class="flex-1 py-3 font-semibold text-white transition shadow-lg bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-xl hover:from-indigo-700 hover:to-cyan-600"
                                onclick="return confirm('¿Finalizar arqueo? Una vez finalizado no se podrá modificar.')">
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
    const efectivo = parseFloat(document.getElementById('efectivo_contado').value) || 0;
    const debito = parseFloat(document.getElementById('tarjeta_debito_contado').value) || 0;
    const credito = parseFloat(document.getElementById('tarjeta_credito_contado').value) || 0;
    const vale = parseFloat(document.getElementById('vale_contado').value) || 0;
    const transferencia = parseFloat(document.getElementById('transferencia_contado').value) || 0;
    const cheque = parseFloat(document.getElementById('cheque_contado').value) || 0;
    
    const totalContado = efectivo + debito + credito + vale + transferencia + cheque;
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
</script>
@endsection