@props([
    'header' => null,
    'footer' => null,
])

<div {{ $attributes->merge(['class' => 'bg-white overflow-hidden shadow-sm sm:rounded-lg']) }}>
    @if($header)
        <div class="border-b border-gray-200 bg-gray-50 px-4 py-5 sm:px-6">
            {{ $header }}
        </div>
    @endif

    <div class="p-6 text-gray-900">
        {{ $slot }}
    </div>

    @if($footer)
        <div class="bg-gray-50 px-4 py-4 sm:px-6">
            {{ $footer }}
        </div>
    @endif
</div>
