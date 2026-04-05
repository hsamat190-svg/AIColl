<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} — {{ __('Log in') }}</title>
        <link rel="icon" href="{{ asset('images/aicol-logo.png') }}" type="image/png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            body.auth-split-surface { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
        </style>
    </head>
    <body class="auth-split-surface landing-mesh min-h-screen text-zinc-100 antialiased">
        <div class="pointer-events-none fixed inset-0 z-0 landing-ray" aria-hidden="true"></div>
        <div class="relative z-10 min-h-screen flex flex-col">
            <div class="absolute top-4 end-4 sm:top-6 sm:end-6 z-20">
                <x-language-switcher tone="dark" />
            </div>
            <div class="flex-1 flex items-center justify-center px-4 py-10 sm:py-12">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
