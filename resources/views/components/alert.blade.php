@props(['type' => 'success', 'message' => ''])

@php
    $colors = [
        'success' => 'bg-green-50 border-green-200 text-green-700',
        'error' => 'bg-red-50 border-red-200 text-red-700',
        'warning' => 'bg-yellow-50 border-yellow-200 text-yellow-700',
        'info' => 'bg-blue-50 border-blue-200 text-blue-700',
    ];
    
    $icons = [
        'success' => '✅',
        'error' => '⚠️',
        'warning' => '⚡',
        'info' => 'ℹ️',
    ];
@endphp

@if($message)
<div class="{{ $colors[$type] ?? $colors['info'] }} border px-4 py-3 rounded-xl mb-6 flex items-center gap-2">
    <span>{{ $icons[$type] ?? 'ℹ️' }}</span>
    <span>{{ $message }}</span>
</div>
@endif