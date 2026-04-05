@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-fuchsia-500 text-sm font-semibold leading-5 text-violet-950 focus:outline-none focus:border-fuchsia-600 transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-violet-800/65 hover:text-violet-950 hover:border-violet-200 focus:outline-none focus:text-violet-950 focus:border-violet-200 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
