@php
    $name = config('app.name', 'AIColl');
@endphp

<a
    {{ $attributes->merge([
        'href' => route('lab.index'),
        'class' =>
            'inline-flex shrink-0 items-center justify-center rounded-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-fuchsia-400/45 focus-visible:ring-offset-2 focus-visible:ring-offset-[#f3f1f9]',
    ]) }}
>
    <img
        src="{{ asset('images/aicol-logo.png') }}"
        alt="{{ $name }}"
        {{-- Фронтендтегідей: дөңгелектелген рамка + күлгін жарық --}}
        class="h-9 w-9 sm:h-10 sm:w-10 object-contain rounded-xl ring-1 ring-violet-300/85 bg-white shadow-[0_0_28px_rgba(168,85,247,0.28)]"
        width="40"
        height="40"
        decoding="async"
    />
</a>
