<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-violet-950 leading-tight tracking-tight">
            {{ __('Simulator') }}
        </h2>
    </x-slot>

    <div class="py-8 sm:py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid lg:grid-cols-2 gap-6">
                <div class="admin-surface p-6 space-y-4">
                    <form id="lab-form" class="space-y-3 text-sm">
                        <input type="hidden" name="material" value="steel">
                        <fieldset class="border border-violet-100 rounded-xl p-3 bg-violet-50/30">
                            <legend class="text-xs font-medium text-violet-700/80">{{ __('Collision type') }}</legend>
                            <div class="mt-2 space-y-2">
                                <label class="flex items-center gap-2 cursor-pointer text-violet-900/90">
                                    <input type="radio" name="collision_type" value="elastic" checked class="rounded-full border-violet-300 text-fuchsia-600 shadow-sm focus:ring-fuchsia-400/50">
                                    <span>{{ __('Elastic collision') }}</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer text-violet-900/90">
                                    <input type="radio" name="collision_type" value="inelastic" class="rounded-full border-violet-300 text-fuchsia-600 shadow-sm focus:ring-fuchsia-400/50">
                                    <span>{{ __('Inelastic collision') }}</span>
                                </label>
                            </div>
                        </fieldset>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="block text-sm text-violet-900/90">{{ __('Simulator mass m1') }}
                                <input type="number" step="0.01" name="m1" value="1" class="mt-1 w-full rounded-xl border-violet-200 shadow-sm focus:border-fuchsia-400 focus:ring-fuchsia-400/35" required>
                            </label>
                            <label class="block text-sm text-violet-900/90">{{ __('Simulator mass m2') }}
                                <input type="number" step="0.01" name="m2" value="2" class="mt-1 w-full rounded-xl border-violet-200 shadow-sm focus:border-fuchsia-400 focus:ring-fuchsia-400/35" required>
                            </label>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <fieldset class="border border-violet-100 rounded-xl p-3 bg-violet-50/30">
                                <legend class="text-xs font-medium text-violet-700/80">{{ __('Simulator velocity v1 legend') }}</legend>
                                <label class="block text-sm text-violet-900/90">x <input type="number" step="0.01" name="v1_x" value="2" class="mt-1 w-full rounded-xl border-violet-200 shadow-sm focus:border-fuchsia-400 focus:ring-fuchsia-400/35"></label>
                                <label class="block text-sm text-violet-900/90">y <input type="number" step="0.01" name="v1_y" value="0" class="mt-1 w-full rounded-xl border-violet-200 shadow-sm focus:border-fuchsia-400 focus:ring-fuchsia-400/35"></label>
                            </fieldset>
                            <fieldset class="border border-violet-100 rounded-xl p-3 bg-violet-50/30">
                                <legend class="text-xs font-medium text-violet-700/80">{{ __('Simulator velocity v2 legend') }}</legend>
                                <label class="block text-sm text-violet-900/90">x <input type="number" step="0.01" name="v2_x" value="-1" class="mt-1 w-full rounded-xl border-violet-200 shadow-sm focus:border-fuchsia-400 focus:ring-fuchsia-400/35"></label>
                                <label class="block text-sm text-violet-900/90">y <input type="number" step="0.01" name="v2_y" value="0" class="mt-1 w-full rounded-xl border-violet-200 shadow-sm focus:border-fuchsia-400 focus:ring-fuchsia-400/35"></label>
                            </fieldset>
                        </div>
                        <button type="submit" class="admin-btn-primary-solid w-full py-3">
                            {{ __('Run simulation') }}
                        </button>
                    </form>
                    <p id="lab-status" class="text-sm text-violet-700/75"></p>
                    <pre id="lab-json" class="text-xs bg-violet-50/60 border border-violet-100 p-2 rounded-xl max-h-48 overflow-auto hidden text-violet-900"></pre>
                </div>
                <div class="admin-surface p-6">
                    <canvas
                        id="lab-canvas"
                        width="480"
                        height="320"
                        class="w-full max-w-lg mx-auto border border-violet-100 rounded-xl bg-white shadow-inner"
                        data-inelastic-e="{{ config('collision.materials.steel.restitution') }}"
                        data-collision-label="{{ __('Collision moment') }}"
                        data-unit-kg="{{ __('Simulator unit kg') }}"
                        data-unit-ms="{{ __('Simulator unit m s') }}"
                        data-axis-x-label="{{ __('Simulator axis x meters') }}"
                    ></canvas>
                    <div class="mt-3 flex flex-col items-center gap-2">
                        <button
                            type="button"
                            id="lab-continue"
                            class="hidden w-full max-w-xs rounded-xl bg-gradient-to-r from-violet-700 to-fuchsia-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-violet-500/25 hover:brightness-105 focus:outline-none focus:ring-2 focus:ring-fuchsia-400/50 focus:ring-offset-2 focus:ring-offset-white"
                        >
                            {{ __('Simulator continue') }}
                        </button>
                        <button
                            type="button"
                            id="lab-reset"
                            class="w-full max-w-xs rounded-xl border border-violet-200 bg-white px-4 py-2.5 text-sm font-medium text-violet-900 shadow-sm hover:bg-violet-50/80 focus:outline-none focus:ring-2 focus:ring-fuchsia-400/40 focus:ring-offset-2 focus:ring-offset-white"
                        >
                            {{ __('Simulator reset') }}
                        </button>
                        <p class="text-xs text-violet-600/75 text-center">{{ __('Simulator canvas hint') }}</p>
                    </div>
                </div>
            </div>

            @php
                $simLabI18n = [
                    'title' => __('Simulator solution title'),
                    'given' => __('Simulator section given'),
                    'body1' => __('Simulator body 1'),
                    'body2' => __('Simulator body 2'),
                    'mass' => __('Mass'),
                    'velocity' => __('Simulator velocity label'),
                    'vector' => __('Simulator vector'),
                    'magnitude' => __('Simulator magnitude'),
                    'momentumSection' => __('Simulator momentum section'),
                    'energySection' => __('Simulator energy section'),
                    'before' => __('Simulator before collision'),
                    'after' => __('Simulator after collision'),
                    'delta' => __('Simulator delta'),
                    'systemSum' => __('Simulator system sum'),
                    'collisionHappened' => __('Simulator collision happened'),
                    'collisionNo' => __('Simulator collision no'),
                    'downloadPdf' => __('Simulator download pdf'),
                    'serviceUnavailable' => __('Simulator service unavailable'),
                    'savedExperiment' => __('Simulator saved experiment'),
                    'energyUnit' => __('Simulator energy unit'),
                    'momentumUnit' => __('Simulator momentum unit'),
                    'collisionType' => __('Collision type'),
                    'elastic' => __('Elastic collision'),
                    'inelastic' => __('Inelastic collision'),
                    'unitKg' => __('Simulator unit kg'),
                    'running' => __('Simulator running'),
                    'historyDefaultNamePattern' => __('History auto name 2d'),
                    'experimentAndHistorySaved' => __('Simulator experiment and history saved'),
                    'summaryTableTitle' => __('Simulator summary table title'),
                    'summaryColMetric' => __('Simulator summary col metric'),
                    'summaryColBefore' => __('Simulator summary col before'),
                    'summaryColAfter' => __('Simulator summary col after'),
                    'summaryColDelta' => __('Simulator summary col delta'),
                    'summaryRowMomentum' => __('Simulator summary row momentum'),
                    'summaryRowEnergy' => __('Simulator summary row energy'),
                ];
            @endphp
            {{-- JSON в data-* часто ломается после e() / кавычек; читаем из script --}}
            <script type="application/json" id="lab-sim-i18n-data">@json($simLabI18n)</script>
            <div
                id="lab-results-wrap"
                class="hidden mt-8 max-w-7xl mx-auto sm:px-6 lg:px-8"
            >
                <div class="admin-surface p-6 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <h3 id="lab-results-heading" class="text-lg font-semibold text-violet-950"></h3>
                        <button
                            type="button"
                            id="lab-download-pdf"
                            class="inline-flex justify-center rounded-xl border border-violet-200 bg-violet-50/90 px-4 py-2.5 text-sm font-semibold text-violet-900 hover:bg-fuchsia-50 hover:border-fuchsia-200/80 focus:outline-none focus:ring-2 focus:ring-fuchsia-400/45"
                        ></button>
                    </div>
                    <div id="lab-results-body" class="text-sm text-violet-950/95 space-y-6"></div>
                </div>
            </div>

            <div id="lab-system-summary-section" class="mt-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
                <div class="admin-surface p-6 space-y-4">
                    <h3 class="text-lg font-semibold text-violet-950">
                        {{ __('Simulator charts title') }}
                    </h3>
                    <div id="lab-summary-above-charts" class="hidden" aria-live="polite"></div>
                    <p id="lab-summary-placeholder" class="text-sm text-violet-600/85">
                        {{ __('Simulator charts empty') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        @vite(['resources/js/lab.js'])
    @endpush
</x-app-layout>
