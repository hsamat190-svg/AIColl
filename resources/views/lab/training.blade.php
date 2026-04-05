<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-violet-950 leading-tight tracking-tight">
            {{ __('Training mode') }}
        </h2>
    </x-slot>

    <div class="py-8 sm:py-10">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="admin-surface p-6 text-sm text-violet-800/85 space-y-3">
                <p class="leading-relaxed">
                    {{ __('Training intro') }}
                </p>
                <p class="flex flex-wrap gap-3 items-center">
                    <a href="{{ route('lab.game') }}" class="admin-link">{{ __('Game mode') }}</a>
                    <a href="{{ route('lab.video') }}" class="admin-link">{{ __('Video stub') }}</a>
                    <span class="text-violet-300">|</span>
                    <span class="text-violet-700/80">{{ __('POST /api/lab/dataset/batch (count, seed)') }}</span>
                </p>
                <form id="training-form" class="space-y-3">
                    <label class="block font-medium text-violet-900">{{ __('Epochs') }}
                        <input type="number" name="epochs" value="30" min="1" class="mt-1 w-full rounded-xl border-violet-200 shadow-sm focus:border-fuchsia-400 focus:ring-fuchsia-400/35">
                    </label>
                    <label class="block font-medium text-violet-900">{{ __('Hidden size') }}
                        <input type="number" name="hidden_size" value="64" min="8" class="mt-1 w-full rounded-xl border-violet-200 shadow-sm focus:border-fuchsia-400 focus:ring-fuchsia-400/35">
                    </label>
                    <label class="block font-medium text-violet-900">{{ __('Learning rate') }}
                        <input type="text" name="lr" value="0.001" class="mt-1 w-full rounded-xl border-violet-200 shadow-sm focus:border-fuchsia-400 focus:ring-fuchsia-400/35">
                    </label>
                    <button type="submit" class="admin-btn-primary-solid">{{ __('Submit (demo)') }}</button>
                </form>
                <pre id="training-out" class="text-xs bg-violet-50/70 border border-violet-100 p-3 rounded-xl hidden text-violet-900"></pre>
            </div>
        </div>
    </div>

    @push('scripts')
        @vite(['resources/js/lab.js'])
    @endpush
</x-app-layout>
