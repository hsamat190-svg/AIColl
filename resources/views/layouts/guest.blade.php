<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="icon" href="{{ asset('images/aicol-logo.png') }}" type="image/png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            body.guest-surface { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
        </style>
    </head>
    <body class="guest-surface landing-mesh min-h-screen text-zinc-100 antialiased">
        <div class="pointer-events-none fixed inset-0 z-0 landing-ray" aria-hidden="true"></div>
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 px-4 relative z-10">
            <div class="absolute top-4 right-4 sm:top-6 sm:right-6 z-20">
                <x-language-switcher tone="dark" />
            </div>
            <div class="mb-6">
                <a href="/" class="inline-flex items-center gap-2 rounded-2xl p-2 ring-1 ring-white/10 hover:ring-fuchsia-500/30 transition-all">
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-purple-500 to-fuchsia-600 shadow-[0_0_24px_rgba(168,85,247,0.35)]">
                        <x-application-logo class="h-7 w-7 fill-current text-white opacity-95" />
                    </span>
                </a>
            </div>

            <div class="w-full sm:max-w-md glass-card overflow-hidden shadow-[0_0_60px_rgba(0,0,0,0.35)]">
                <div class="guest-auth-form px-6 py-8 sm:px-8 sm:py-10">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
