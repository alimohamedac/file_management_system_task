@props([
    'type' => 'button',
    'variant' => 'primary',
])

<button 
    type="{{ $type }}"
    {{ $attributes->merge([
        'class' => 'btn btn-' . $variant
    ]) }}
>
    {{ $slot }}
</button>
