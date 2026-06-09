<div class="section">
    <div class="row">
        <span>Ticket de ejemplo</span>
        <span>#{{ $preview->numero }}</span>
    </div>
    <div class="row">
        <span>Fecha</span>
        <span>{{ $preview->fecha }} {{ $preview->hora }}</span>
    </div>
    <div class="divider"></div>
    @foreach($preview->items as $item)
    <div class="row">
        <span>{{ $item->cantidad }}x {{ $item->descripcion }}</span>
        <span>${{ number_format($item->importe, 2) }}</span>
    </div>
    @endforeach
    <div class="divider"></div>
    <div class="row">
        <span>Subtotal</span>
        <span>${{ number_format($preview->subtotal, 2) }}</span>
    </div>
    <div class="row">
        <span>IVA (16%)</span>
        <span>${{ number_format($preview->iva, 2) }}</span>
    </div>
    <div class="monto neutro">
        TOTAL: ${{ number_format($preview->total, 2) }}
    </div>
</div>