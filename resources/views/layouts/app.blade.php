<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="icon" href="{{ asset('images/aicol-logo.png') }}" type="image/png">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        @php
            $layoutRailLabels = [
                'close' => __('Nav sidebar close mobile'),
                'expand' => __('Nav sidebar expand'),
                'collapse' => __('Nav sidebar collapse'),
            ];
        @endphp
        <div
            x-data="layoutShell(@js($layoutRailLabels))"
            @keydown.escape.window="sidebarOpen = false; profileMenuOpen = false"
            class="flex min-h-screen text-gray-800"
        >
            @include('layouts.sidebar')

            <div class="admin-app-shell relative flex min-h-screen min-w-0 flex-1 flex-col">
                {{-- Мобиль: полоска с меню + язык справа --}}
                <div class="sticky top-0 z-30 flex h-14 shrink-0 items-center gap-3 border-b border-violet-200/60 bg-white/95 px-4 backdrop-blur-md shadow-sm shadow-violet-500/[0.04] sm:px-6 lg:hidden">
                    <button
                        type="button"
                        @click="sidebarOpen = true"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-violet-200/80 bg-violet-50/80 text-violet-800 hover:bg-violet-100 focus:outline-none focus:ring-2 focus:ring-fuchsia-400/40"
                        :aria-expanded="sidebarOpen ? 'true' : 'false'"
                        aria-controls="app-sidebar"
                    >
                        <span class="sr-only">{{ __('Nav open menu') }}</span>
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                    <div class="ms-auto flex items-center">
                        <x-language-switcher class="shrink-0" />
                    </div>
                </div>

                {{-- Десктоп: язык в правом верхнем углу окна (не в сайдбаре) --}}
                <div class="pointer-events-none fixed end-4 top-4 z-[100] hidden lg:block">
                    <div class="pointer-events-auto rounded-2xl border border-violet-200/80 bg-white/95 p-1 shadow-md shadow-violet-500/10 backdrop-blur-sm">
                        <x-language-switcher class="shrink-0" />
                    </div>
                </div>

                @isset($header)
                    <header class="admin-header-bar">
                        <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8 flex items-start gap-4">
                            <span class="mt-1 h-9 w-1 shrink-0 rounded-full bg-gradient-to-b from-violet-500 via-fuchsia-500 to-pink-400 shadow-sm shadow-fuchsia-500/30" aria-hidden="true"></span>
                            <div class="min-w-0 flex-1">{{ $header }}</div>
                        </div>
                    </header>
                @endisset

                <main class="flex-1 text-gray-800">
                    {{ $slot }}
                </main>
            </div>
        </div>
        @stack('scripts')
    </body>
</html>
