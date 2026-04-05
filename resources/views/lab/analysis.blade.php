<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-violet-950 leading-tight tracking-tight">
            {{ __('Physics vs AI') }} — #{{ $experiment->id }}
        </h2>
    </x-slot>

    <div class="py-8 sm:py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid md:grid-cols-2 gap-4 text-sm">
                <div class="admin-surface p-5">
                    <h3 class="font-semibold text-violet-950 mb-2">{{ __('Comparison') }}</h3>
                    <ul class="space-y-1 text-violet-800/85">
                        <li>{{ __('Velocity MAE') }}: <strong>{{ $experiment->comparison['velocity_mae'] ?? '—' }}</strong></li>
                        <li>{{ __('Energy error %') }}: <strong>{{ $experiment->comparison['energy_error_percent'] ?? '—' }}</strong></li>
                        <li>{{ __('AI source') }}: <strong>{{ $experiment->comparison['ai_source'] ?? '—' }}</strong></li>
                    </ul>
                </div>
                <div class="admin-surface p-5">
                    <h3 class="font-semibold text-violet-950 mb-2">{{ __('Final velocities') }}</h3>
                    <div class="grid grid-cols-2 gap-2 text-xs font-mono">
                        <div>
                            <p class="text-violet-600/80 mb-1">{{ __('Physics v1') }}</p>
                            <pre class="bg-violet-50/70 border border-violet-100 p-2 rounded-lg text-violet-900">{{ json_encode($experiment->physics_result['v1'] ?? [], JSON_PRETTY_PRINT) }}</pre>
                        </div>
                        <div>
                            <p class="text-violet-600/80 mb-1">{{ __('AI v1') }}</p>
                            <pre class="bg-violet-50/70 border border-violet-100 p-2 rounded-lg text-violet-900">{{ json_encode($experiment->ai_prediction['v1'] ?? [], JSON_PRETTY_PRINT) }}</pre>
                        </div>
                        <div>
                            <p class="text-violet-600/80 mb-1">{{ __('Physics v2') }}</p>
                            <pre class="bg-violet-50/70 border border-violet-100 p-2 rounded-lg text-violet-900">{{ json_encode($experiment->physics_result['v2'] ?? [], JSON_PRETTY_PRINT) }}</pre>
                        </div>
                        <div>
                            <p class="text-violet-600/80 mb-1">{{ __('AI v2') }}</p>
                            <pre class="bg-violet-50/70 border border-violet-100 p-2 rounded-lg text-violet-900">{{ json_encode($experiment->ai_prediction['v2'] ?? [], JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                </div>
            </div>
            <div class="admin-surface p-5">
                <canvas id="analysis-chart" height="120"></canvas>
            </div>
        </div>
    </div>

    <div id="aicoll-analysis" class="hidden"
         data-experiment="{{ e(json_encode($experiment->only(['physics_result', 'ai_prediction', 'comparison']))) }}">
    </div>

    @push('scripts')
        @vite(['resources/js/lab.js'])
    @endpush
</x-app-layout>
