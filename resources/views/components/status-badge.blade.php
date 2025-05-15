@props([
    'status',
    'size' => 'base' // base or sm
])

@php
    $baseClasses = 'inline-flex items-center rounded-full font-medium';
    $sizeClasses = $size === 'sm' ? 'px-2.5 py-0.5 text-xs' : 'px-3 py-1 text-sm';
    
    switch($status) {
        case 'approved':
        case 'completed':
            $colorClasses = 'bg-green-100 text-green-800';
            break;
        case 'rejected':
            $colorClasses = 'bg-red-100 text-red-800';
            break;
        case 'pending':
            $colorClasses = 'bg-yellow-100 text-yellow-800';
            break;
        default:
            $colorClasses = 'bg-gray-100 text-gray-800';
    }
@endphp

<span {{ $attributes->merge(['class' => $baseClasses . ' ' . $sizeClasses . ' ' . $colorClasses]) }}>
    {{ ucfirst($status) }}
</span>
