@extends('layouts.app')

@section('title', 'Renovación de Licencia')
@section('page-title', 'Renovación de Licencia')

@section('content')
<div class="max-w-4xl mx-auto" x-data="renovacionLicencia()" x-init="init">
    <div class="overflow-hidden bg-white shadow-lg rounded-3xl">

        {{-- Header --}}
        <div class="px-6 py-4 bg-gradient-to-r from-indigo-600 to-cyan-500">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-white">Renovación de Licencia</h2>
                    <p class="text-sm text-indigo-100">Actualiza la licencia de la empresa sin modificar su fecha de
                        inicio original</p>
                </div>
                <div class="px-3 py-1 text-sm font-semibold text-indigo-700 bg-white rounded-full">
                    {{ $empresa->nombre }}
                </div>
            </div>
        </div>

        <div class="p-6">
            {{-- Información actual de la empresa --}}
            <div class="p-4 mb-6 bg-blue-50 rounded-xl">
                <h3 class="mb-2 font-semibold text-blue-800">📋 Información actual</h3>
                <div class="grid grid-cols-2 gap-4 text-sm md:grid-cols-4">
                    <div>
                        <span class="text-gray-500">Licencia actual:</span>
                        <p class="font-medium">
                            @if($licenciaActiva && isset($licenciaActiva->licencia))
                            {{ $licenciaActiva->licencia->nombre }}
                            @elseif($empresa->licencia)
                            {{ $empresa->licencia->nombre }}
                            @else
                            Sin licencia
                            @endif
                        </p>
                    </div>
                    <div>
                        <span class="text-gray-500">Vigencia:</span>
                        <p class="font-medium">
                            @php
                            $fechaInicio = null;
                            $fechaFin = null;

                            if ($licenciaActiva && isset($licenciaActiva->fecha_inicio_periodo)) {
                            $fechaInicio = $licenciaActiva->fecha_inicio_periodo;
                            $fechaFin = $licenciaActiva->fecha_fin_periodo;
                            } elseif ($empresa->fecha_inicio) {
                            $fechaInicio = $empresa->fecha_inicio;
                            $fechaFin = $empresa->fecha_fin;
                            }
                            @endphp
                            @if($fechaInicio && $fechaFin)
                            {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} -
                            {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
                            @else
                            N/A - N/A
                            @endif
                        </p>
                    </div>
                    <div>
                        <span class="text-gray-500">Días restantes:</span>
                        <p class="font-medium {{ $diasRestantes < 30 ? 'text-red-600' : 'text-green-600' }}">
                            {{ $diasRestantes }} días
                        </p>
                        <p class="text-xs text-gray-400">(Vigente hasta las 23:59:59)</p>
                    </div>
                    <div>
                        <span class="text-gray-500">Fecha inicio original:</span>
                        <p class="font-medium">
                            {{ $empresa->fecha_inicio instanceof \DateTime ? $empresa->fecha_inicio->format('d/m/Y') :
                            date('d/m/Y', strtotime($empresa->fecha_inicio)) }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Formulario de renovación --}}
            <form action="{{ route('empresas.licencias.renovar.procesar', $empresa) }}" method="POST">
                @csrf

                <div class="space-y-5">
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">Nueva Licencia *</label>
                        <select name="licencia_id" x-model="licenciaId" @change="calcularFechas" required
                            class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                            <option value="">Seleccionar licencia...</option>
                            @foreach($licencias as $licencia)
                            <option value="{{ $licencia->id }}" data-dias="{{ $licencia->dias }}"
                                data-precio="{{ $licencia->precio }}">
                                {{ $licencia->nombre }} - ${{ number_format($licencia->precio, 2) }}
                                ({{ $licencia->dias }} días, {{ $licencia->max_usuarios }} usuarios,
                                {{ $licencia->max_sucursales }} sucursales)
                            </option>
                            @endforeach
                        </select>
                        @error('licencia_id') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">Inicio de periodo *</label>
                            <input type="date" name="fecha_inicio_periodo" x-model="fechaInicioPeriodo"
                                @change="calcularFechas" required
                                class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                            <p class="mt-1 text-xs text-gray-500">Fecha desde la que comenzará la nueva licencia</p>
                            @error('fecha_inicio_periodo') <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">Fin de periodo *</label>
                            <input type="date" name="fecha_fin_periodo" x-model="fechaFinPeriodo" readonly
                                class="w-full px-4 py-3 border rounded-xl bg-gray-50 focus:ring-2 focus:ring-indigo-500">
                            <p class="mt-1 text-xs text-gray-500" x-show="fechaFinPeriodo" x-text="mensajeCalculo"></p>
                            @error('fecha_fin_periodo') <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">Monto pagado *</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-gray-500">$</span>
                                <input type="number" name="monto_pagado" x-model="montoPagado" step="0.01" min="0"
                                    required
                                    class="w-full py-3 pl-8 pr-4 border rounded-xl focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <p class="mt-1 text-xs text-gray-500" x-show="montoPagado && montoPagado < precioLicencia"
                                x-text="'⚠️ Monto menor al precio de la licencia: $' + precioLicencia"
                                class="text-yellow-600"></p>
                            @error('monto_pagado') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">Referencia de pago</label>
                            <input type="text" name="referencia_pago" value="{{ old('referencia_pago') }}"
                                class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500"
                                placeholder="Folio, comprobante, etc.">
                            @error('referencia_pago') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">Observaciones</label>
                        <textarea name="observaciones" rows="3"
                            class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500"
                            placeholder="Notas sobre la renovación..."></textarea>
                        @error('observaciones') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Resumen --}}
                    <div x-show="licenciaId" class="p-4 bg-gray-50 rounded-xl">
                        <h4 class="mb-2 font-semibold text-gray-700">📊 Resumen de renovación</h4>
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div>Licencia seleccionada:</div>
                            <div class="font-medium" x-text="nombreLicencia"></div>
                            <div>Periodo:</div>
                            <div class="font-medium" x-text="fechaInicioPeriodo + ' al ' + fechaFinPeriodo"></div>
                            <div>Días de vigencia:</div>
                            <div class="font-medium" x-text="diasLicencia + ' días'"></div>
                            <div>Monto:</div>
                            <div class="font-medium text-green-600" x-text="'$' + montoPagado"></div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-6 mt-6 border-t">
                    <a href="{{ route('empresas.show', $empresa) }}"
                        class="px-6 py-3 border-2 border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="px-8 py-3 text-white shadow-md bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-xl hover:from-indigo-700 hover:to-cyan-600">
                        💾 Registrar Renovación
                    </button>
                </div>
            </form>

            {{-- Historial de renovaciones --}}
            @if($historial->count() > 0)
            <div class="pt-6 mt-8 border-t">
                <h3 class="mb-3 font-semibold text-gray-800">📜 Historial de renovaciones</h3>
                <div class="space-y-2 overflow-y-auto max-h-64">
                    @foreach($historial as $item)
                    <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50">
                        <div>
                            <p class="font-medium">{{ $item->licencia->nombre }}</p>
                            <p class="text-xs text-gray-500">{{
                                \Carbon\Carbon::parse($item->fecha_inicio_periodo)->format('d/m/Y') }} -
                                {{ \Carbon\Carbon::parse($item->fecha_fin_periodo)->format('d/m/Y') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-green-600">${{ number_format($item->monto_pagado, 2) }}</p>
                            <p class="text-xs text-gray-400">Registrado: {{
                                \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
function renovacionLicencia() {
    let licenciasArray = [];
    @foreach($licencias as $licencia)
        licenciasArray.push({
            id: {{ $licencia->id }},
            nombre: '{{ addslashes($licencia->nombre) }}',
            dias: {{ $licencia->dias }},
            precio: {{ $licencia->precio }}
        });
    @endforeach

    return {
        licenciasData: licenciasArray,
        licenciaId: '',
        fechaInicioPeriodo: '{{ date("Y-m-d") }}',
        fechaFinPeriodo: '',
        montoPagado: '',
        nombreLicencia: '',
        diasLicencia: 0,
        precioLicencia: 0,
        mensajeCalculo: '',

        init() {
            @if(old('licencia_id'))
                this.licenciaId = '{{ old("licencia_id") }}';
                this.fechaInicioPeriodo = '{{ old("fecha_inicio_periodo", date("Y-m-d")) }}';
                this.calcularFechas();
                this.montoPagado = '{{ old("monto_pagado") }}';
            @endif
        },

        calcularFechas() {
            if (!this.licenciaId || !this.fechaInicioPeriodo) {
                return;
            }

            const licencia = this.licenciasData.find(l => l.id == parseInt(this.licenciaId));
            if (!licencia) return;

            this.nombreLicencia = licencia.nombre;
            this.diasLicencia = licencia.dias;
            this.precioLicencia = licencia.precio;

            // 🔥 MÉTODO MANUAL CON UTC
            const [year, month, day] = this.fechaInicioPeriodo.split('-').map(Number);
            
            const fechaInicioUTC = new Date(Date.UTC(year, month - 1, day));
            const fechaFinUTC = new Date(fechaInicioUTC);
            fechaFinUTC.setUTCDate(fechaFinUTC.getUTCDate() + licencia.dias);
            
            const yearFin = fechaFinUTC.getUTCFullYear();
            const monthFin = String(fechaFinUTC.getUTCMonth() + 1).padStart(2, '0');
            const dayFin = String(fechaFinUTC.getUTCDate()).padStart(2, '0');

            this.fechaFinPeriodo = `${yearFin}-${monthFin}-${dayFin}`;
            
            const diasTexto = licencia.dias === 1 ? 'día' : 'días';
            this.mensajeCalculo = `📅 Vigencia: ${licencia.dias} ${diasTexto} (del ${this.fechaInicioPeriodo} al ${this.fechaFinPeriodo})`;

            if (!this.montoPagado) {
                this.montoPagado = licencia.precio;
            }
            
            console.log('fechaInicioPeriodo:', this.fechaInicioPeriodo);
            console.log('fechaFinPeriodo calculada:', this.fechaFinPeriodo);
        }
    }
}
</script>
@endsection