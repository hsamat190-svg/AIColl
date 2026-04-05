@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-violet-900/85']) }}>
    {{ $value ?? $slot }}
</label>
