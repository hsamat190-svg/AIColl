@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-violet-200/90 bg-white text-violet-950 placeholder:text-violet-400/70 focus:border-fuchsia-400 focus:ring-fuchsia-400/35 rounded-xl shadow-sm']) }}>
