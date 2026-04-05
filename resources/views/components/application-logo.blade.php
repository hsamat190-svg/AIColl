@props([])

<img
    src="{{ asset('images/aicol-logo.png') }}"
    alt="{{ config('app.name', 'AIColl') }}"
    {{ $attributes->merge(['class' => 'object-contain']) }}
    decoding="async"
/>
