<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-violet-950 leading-tight tracking-tight">
            {{ __('Game mode') }}
        </h2>
    </x-slot>

    <div class="py-8 sm:py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <p class="text-sm text-violet-800/80 leading-relaxed">
                {{ __('Game intro') }}
            </p>
            <div class="admin-surface p-6">
                <canvas id="game-canvas" width="480" height="280" class="w-full border border-violet-900/20 rounded-xl bg-[#0f0a18] shadow-inner"></canvas>
                <div class="mt-4 flex flex-wrap gap-2">
                    <button type="button" id="game-spawn" class="rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-emerald-600/20 hover:brightness-105">{{ __('Spawn') }}</button>
                    <button type="button" id="game-freeze" class="rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-amber-500/25 hover:brightness-105">{{ __('Freeze & run API') }}</button>
                </div>
                <p id="game-status" class="text-sm mt-3 text-violet-700/80"></p>
            </div>
        </div>
    </div>

    @push('scripts')
        @vite(['resources/js/lab.js'])
    @endpush
</x-app-layout>
