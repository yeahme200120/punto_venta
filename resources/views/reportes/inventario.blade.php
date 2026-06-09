@extends('layouts.app')

@section('title', 'Reporte de Inventario')
@section('page-title', 'Reporte de Inventario')
@section('breadcrumbs')
    <li><span class="text-gray-400">/</span></li>
    <li><span class="font-medium text-gray-700">Reporte de Inventario</span></li>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Filtros --}}
    <div class="p-4 bg-white border shadow-sm rounded-2xl">
        <form method="GET" action="{{ route('reportes.inventario') }}" class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Categoría</label>
                <select name="categoria_id" class="w-full px-3 py-2 border rounded-xl">
                    <option value="">Todas</option>
                    @foreach($categorias as $cat)
                        <option value="{{ $cat->id }}" {{ request('categoria_id') == $cat->id ? 'selected' : '' }}>{{ $cat->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Stock bajo</label>
                <select name="stock_bajo" class="w-full px-3 py-2 border rounded-xl">
                    <option value="">Todos</option>
                    <option value="1" {{ request('stock_bajo') == 1 ? 'selected' : '' }}>Sí</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Buscar</label>
                <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Nombre, SKU, código..." class="w-full px-3 py-2 border rounded-xl">
            </div>
            <div class="flex justify-end gap-2">
                <button type="submit" class="px-4 py-2 text-white bg-indigo-600 rounded-xl hover:bg-indigo-700">Filtrar</button>
                <a href="{{ route('reportes.inventario') }}" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200">Limpiar</a>
            </div>
        </form>
    </div>

    {{-- Productos más vendidos --}}
    <div class="p-4 bg-white border shadow-sm rounded-2xl">
        <h3 class="mb-3 text-lg font-semibold">Top 10 productos más vendidos (último año)</h3>
        <div class="grid grid-cols-1 gap-2 md:grid-cols-2">
            @foreach($topProductos as $item)
                <div class="flex justify-between p-2 border-b">
                    <span>{{ $item->producto->nombre }}</span>
                    <span class="font-bold">{{ number_format($item->total_cantidad) }} uds - ${{ number_format($item->total_venta, 2) }}</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Tabla de productos --}}
    <div class="overflow-hidden bg-white border shadow-sm rounded-2xl">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="border-b bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left">Producto</th>
                        <th class="px-4 py-3 text-left">Categoría</th>
                        <th class="px-4 py-3 text-right">Stock</th>
                        <th class="px-4 py-3 text-right">Stock mínimo</th>
                        <th class="px-4 py-3 text-right">Precio venta</th>
                        <th class="px-4 py-3 text-center">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($productos as $p)
                    <tr class="border-b">
                        <td class="px-4 py-2">{{ $p->nombre }}</td>
                        <td class="px-4 py-2">{{ $p->categoria->nombre ?? '-' }}</td>
                        <td class="px-4 py-2 text-right {{ $p->stock <= $p->stock_minimo ? 'text-red-600 font-bold' : '' }}">{{ number_format($p->stock, 2) }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format($p->stock_minimo, 2) }}</td>
                        <td class="px-4 py-2 text-right">${{ number_format($p->precio_venta, 2) }}</td>
                        <td class="px-4 py-2 text-center">{{ $p->activo ? 'Activo' : 'Inactivo' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t">{{ $productos->links() }}</div>
    </div>
</div>
@endsection