@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-fuchsia-500 text-start text-base font-semibold text-violet-950 bg-violet-50/90 focus:outline-none focus:bg-violet-100/90 focus:border-fuchsia-600 transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-violet-800/75 hover:text-violet-950 hover:bg-violet-50/50 hover:border-violet-200 focus:outline-none focus:text-violet-950 focus:bg-violet-50/50 focus:border-violet-200 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
