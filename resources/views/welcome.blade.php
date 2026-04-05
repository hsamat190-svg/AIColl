<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'AIColl') }} — {{ __('Landing hero badge') }}</title>
        <link rel="icon" href="{{ asset('images/aicol-logo.png') }}" type="image/png">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            body { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
        </style>
    </head>
    <body class="landing-mesh min-h-screen text-white antialiased relative overflow-x-hidden">
        <div class="pointer-events-none fixed inset-0 z-0 landing-ray" aria-hidden="true"></div>
        <div class="pointer-events-none fixed inset-0 z-0 bg-[linear-gradient(105deg,transparent_38%,rgba(168,85,247,0.045)_52%,transparent_66%)]" aria-hidden="true"></div>

        @if (Route::has('login'))
            <header class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-4 flex flex-wrap items-center justify-between gap-4">
                <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                    <x-application-logo class="h-9 w-9 sm:h-10 sm:w-10 shrink-0 rounded-xl ring-1 ring-white/15 shadow-[0_0_28px_rgba(168,85,247,0.35)] bg-black/20" />
                    <span class="text-lg font-semibold tracking-tight text-white">{{ config('app.name', 'AIColl') }}</span>
                </a>

                <nav class="hidden md:flex items-center gap-10 text-sm text-zinc-400">
                    <a href="#ecosystem" class="hover:text-white transition-colors">{{ __('Landing nav ecosystem') }}</a>
                    <a href="#tech" class="hover:text-white transition-colors">{{ __('Landing nav tech') }}</a>
                    <a href="#lab-ring" class="hover:text-white transition-colors">{{ __('Landing nav lab') }}</a>
                </nav>

                <div class="flex items-center gap-3 sm:gap-4">
                    <x-language-switcher tone="dark" />
                    @auth
                        <a
                            href="{{ route('lab.index') }}"
                            class="inline-flex items-center justify-center rounded-full px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-purple-600 via-fuchsia-600 to-pink-500 shadow-[0_0_32px_rgba(168,85,247,0.35)] hover:brightness-110 transition-all"
                        >
                            {{ __('Dashboard') }}
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="inline-flex items-center justify-center rounded-full px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-purple-600 via-fuchsia-600 to-pink-500 shadow-[0_0_32px_rgba(168,85,247,0.35)] hover:brightness-110 transition-all"
                        >
                            {{ __('Landing cta lab') }}
                        </a>
                    @endauth
                </div>
            </header>
        @endif

        <main class="relative z-10 isolate">
            <section class="max-w-4xl mx-auto px-4 sm:px-6 text-center pt-10 sm:pt-16 pb-16 sm:pb-24">
                <p class="inline-flex items-center gap-2 rounded-full border border-fuchsia-500/25 bg-fuchsia-500/5 px-4 py-1.5 text-xs font-medium uppercase tracking-[0.2em] text-fuchsia-200/90 mb-8">
                    <span class="h-1.5 w-1.5 rounded-full bg-fuchsia-400 shadow-[0_0_10px_#e879f9]"></span>
                    {{ __('Landing hero badge') }}
                </p>
                <h1 class="text-4xl sm:text-5xl md:text-6xl font-bold tracking-tight text-white leading-[1.1]">
                    {{ __('Landing hero title') }}
                </h1>
                <p class="mt-6 text-base sm:text-lg text-zinc-400 max-w-2xl mx-auto leading-relaxed">
                    {{ __('Landing hero subtitle') }}
                </p>
                <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
                    @auth
                        <a
                            href="{{ route('lab.index') }}"
                            class="inline-flex items-center justify-center rounded-full px-8 py-3.5 text-sm font-semibold text-white bg-gradient-to-r from-purple-600 to-fuchsia-600 shadow-[0_0_40px_rgba(168,85,247,0.4)] hover:brightness-110 transition-all"
                        >
                            {{ __('Simulator') }}
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="inline-flex items-center justify-center rounded-full px-8 py-3.5 text-sm font-semibold text-white bg-gradient-to-r from-purple-600 to-fuchsia-600 shadow-[0_0_40px_rgba(168,85,247,0.4)] hover:brightness-110 transition-all"
                        >
                            {{ __('Landing hero primary') }}
                        </a>
                    @endauth
                    <a
                        href="#ecosystem"
                        class="inline-flex items-center justify-center rounded-full px-8 py-3.5 text-sm font-semibold text-zinc-200 border border-white/15 bg-white/[0.03] hover:bg-white/[0.07] hover:border-white/25 transition-all"
                    >
                        {{ __('Landing hero secondary') }}
                    </a>
                </div>
            </section>

            <section id="lab-ring" class="max-w-5xl mx-auto px-4 sm:px-6 pb-20 sm:pb-28">
                <div class="flex flex-wrap items-center justify-center gap-4 md:gap-6">
                    @php
                        $ringItems = [
                            ['key' => 'physics', 'icon' => 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z'],
                            ['key' => 'ai', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
                            ['key' => 'video', 'icon' => 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z'],
                            ['key' => 'compare', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                        ];
                    @endphp
                    @foreach (array_slice($ringItems, 0, 2) as $item)
                        <div class="glass-card flex flex-col items-center justify-center w-[4.5rem] h-[4.5rem] sm:w-20 sm:h-20 md:w-24 md:h-24 text-zinc-300 hover:text-fuchsia-300 hover:border-fuchsia-500/30 hover:shadow-[0_0_28px_rgba(168,85,247,0.15)] transition-all duration-300">
                            <svg class="w-7 h-7 sm:w-8 sm:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $item['icon'] }}" />
                            </svg>
                            <span class="mt-1.5 text-[10px] sm:text-xs text-zinc-500 font-medium">{{ __('Landing feature '.$item['key']) }}</span>
                        </div>
                    @endforeach

                    <div class="relative flex items-center justify-center w-28 h-28 sm:w-32 sm:h-32 md:w-36 md:h-36">
                        <div class="absolute inset-0 rounded-full bg-gradient-to-br from-purple-600 to-fuchsia-500 opacity-40 blur-2xl"></div>
                        <div class="relative flex items-center justify-center w-full h-full rounded-full bg-gradient-to-br from-purple-500 via-fuchsia-600 to-pink-500 shadow-[0_0_60px_rgba(168,85,247,0.55)] ring-2 ring-white/20">
                            <svg class="w-14 h-14 sm:w-16 sm:h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <circle cx="8" cy="12" r="3.5" stroke-width="1.75" />
                                <circle cx="16" cy="12" r="3.5" stroke-width="1.75" />
                                <path stroke-linecap="round" stroke-width="1.75" d="M11.5 12h1" />
                            </svg>
                        </div>
                    </div>

                    @foreach (array_slice($ringItems, 2, 2) as $item)
                        <div class="glass-card flex flex-col items-center justify-center w-[4.5rem] h-[4.5rem] sm:w-20 sm:h-20 md:w-24 md:h-24 text-zinc-300 hover:text-fuchsia-300 hover:border-fuchsia-500/30 hover:shadow-[0_0_28px_rgba(168,85,247,0.15)] transition-all duration-300">
                            <svg class="w-7 h-7 sm:w-8 sm:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $item['icon'] }}" />
                            </svg>
                            <span class="mt-1.5 text-[10px] sm:text-xs text-zinc-500 font-medium">{{ __('Landing feature '.$item['key']) }}</span>
                        </div>
                    @endforeach
                </div>
            </section>

            <section id="ecosystem" class="scroll-mt-24 px-4 sm:px-6 pb-24">
                <h2 class="text-center text-2xl sm:text-3xl font-bold text-white tracking-tight mb-4">
                    {{ __('Landing ecosystem title') }}
                </h2>
                <p id="tech" class="text-center text-sm text-zinc-500 max-w-xl mx-auto mb-12 scroll-mt-24">
                    {{ __('Landing footer') }}
                </p>
                <div class="max-w-6xl mx-auto grid md:grid-cols-3 gap-6">
                    <article class="glass-card p-6 sm:p-8 flex flex-col hover:border-fuchsia-500/20 transition-colors">
                        <span class="text-xs font-semibold uppercase tracking-wider text-fuchsia-400/90 mb-3">{{ __('Simulator') }}</span>
                        <h3 class="text-lg font-semibold text-white mb-3">{{ __('Landing card sim title') }}</h3>
                        <p class="text-sm text-zinc-400 leading-relaxed flex-1">{{ __('Landing card sim text') }}</p>
                    </article>
                    <article class="glass-card p-6 sm:p-8 flex flex-col hover:border-fuchsia-500/20 transition-colors md:scale-[1.02] md:shadow-[0_0_48px_rgba(168,85,247,0.12)]">
                        <span class="text-xs font-semibold uppercase tracking-wider text-fuchsia-400/90 mb-3">{{ __('Landing card ai badge') }}</span>
                        <h3 class="text-lg font-semibold text-white mb-3">{{ __('Landing card video title') }}</h3>
                        <p class="text-sm text-zinc-400 leading-relaxed flex-1">{{ __('Landing card video text') }}</p>
                    </article>
                    <article class="glass-card p-6 sm:p-8 flex flex-col hover:border-fuchsia-500/20 transition-colors">
                        <span class="text-xs font-semibold uppercase tracking-wider text-fuchsia-400/90 mb-3">{{ __('Landing card assistant badge') }}</span>
                        <h3 class="text-lg font-semibold text-white mb-3">{{ __('Landing card stack title') }}</h3>
                        <p class="text-sm text-zinc-400 leading-relaxed flex-1">{{ __('Landing card stack text') }}</p>
                    </article>
                </div>
            </section>

            <footer class="border-t border-white/[0.06] py-10 px-4 text-center text-sm text-zinc-600">
                <p>{{ __('Landing footer') }}</p>
                <p class="mt-2 text-zinc-700">{{ config('app.name', 'AIColl') }}</p>
            </footer>
        </main>
    </body>
</html>
