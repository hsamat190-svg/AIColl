@props(['tone' => 'light'])
@php
    $current = session('locale', 'ru');
    $isDark = $tone === 'dark';
    $btn = $isDark
        ? 'rounded-full p-2 text-zinc-300 bg-white/5 ring-1 ring-white/10 hover:bg-white/10 hover:text-white focus:outline-none focus:ring-2 focus:ring-fuchsia-500/50'
        : 'rounded-full p-2 text-violet-800 bg-violet-50/90 ring-1 ring-violet-200/70 hover:bg-violet-100 hover:text-violet-950 focus:outline-none focus:ring-2 focus:ring-fuchsia-400/45';
    $panel = $isDark
        ? 'rounded-xl border border-white/10 bg-zinc-950/95 backdrop-blur-xl py-1 shadow-xl shadow-black/40 min-w-[10rem]'
        : 'rounded-xl border border-violet-100 bg-white py-1 shadow-lg shadow-violet-500/10 min-w-[10rem]';
    $linkBase = 'flex items-center gap-2 px-3 py-2 text-sm transition-colors';
    $linkRu = $current === 'ru'
        ? ($isDark ? 'bg-white/10 text-white font-medium' : 'bg-violet-100 text-violet-950 font-medium')
        : ($isDark ? 'text-zinc-300 hover:bg-white/5 hover:text-white' : 'text-violet-800 hover:bg-violet-50/80');
    $linkKz = $current === 'kz'
        ? ($isDark ? 'bg-white/10 text-white font-medium' : 'bg-violet-100 text-violet-950 font-medium')
        : ($isDark ? 'text-zinc-300 hover:bg-white/5 hover:text-white' : 'text-violet-800 hover:bg-violet-50/80');
@endphp
<div {{ $attributes->merge(['class' => 'relative inline-block text-sm']) }} x-data="{ open: false }" @keydown.escape.window="open = false">
    <span class="sr-only">{{ __('Language') }}</span>
    <button
        type="button"
        class="{{ $btn }}"
        @click="open = !open"
        :aria-expanded="open"
        aria-haspopup="true"
        title="{{ __('Language') }}"
    >
        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" />
        </svg>
    </button>
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.outside="open = false"
        class="absolute end-0 z-50 mt-2 {{ $panel }}"
        style="display: none;"
        role="menu"
    >
        <a href="{{ route('locale.switch', ['locale' => 'ru']) }}" class="{{ $linkBase }} {{ $linkRu }}" role="menuitem" @click="open = false">
            <span class="w-8 font-semibold tabular-nums">RU</span>
            <span class="{{ $isDark ? 'text-zinc-400' : 'text-violet-600/80' }}">{{ __('Language name ru') }}</span>
        </a>
        <a href="{{ route('locale.switch', ['locale' => 'kz']) }}" class="{{ $linkBase }} {{ $linkKz }}" role="menuitem" @click="open = false">
            <span class="w-8 font-semibold tabular-nums">ҚАЗ</span>
            <span class="{{ $isDark ? 'text-zinc-400' : 'text-violet-600/80' }}">{{ __('Language name kz') }}</span>
        </a>
    </div>
</div>
