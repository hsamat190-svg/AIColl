<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-violet-950 leading-tight tracking-tight">
            {{ __('3D simulator title') }}
        </h2>
    </x-slot>

    @php
        $sim3dI18n = [
            'intro' => __('3D simulator intro'),
            'dropTitle' => __('3D simulator drop title'),
            'dropHint' => __('3D simulator drop hint'),
            'formats' => __('3D simulator formats'),
            'analyze' => __('3D simulator analyze'),
            'analyzing' => __('3D simulator analyzing'),
            'clear' => __('3D simulator clear'),
            'emptyRight' => __('3D simulator empty right'),
            'metricSpeed' => __('3D simulator metric speed'),
            'metricMass' => __('3D simulator metric mass'),
            'metricModel' => __('3D simulator metric model'),
            'metricCost' => __('3D simulator metric cost'),
            'interval' => __('3D simulator interval'),
            'confidence' => __('3D simulator confidence'),
            'errorNoFile' => __('3D simulator error no file'),
            'errorGeneric' => __('3D simulator error generic'),
            'scenarioTitle' => __('3D simulator scenario title'),
            'scenarioConfLabel' => __('3D simulator scenario conf label'),
            'unitKmh' => __('3D simulator unit kmh'),
            'unitKg' => __('3D simulator unit kg'),
        ];
    @endphp
    <script type="application/json" id="sim3d-i18n-data">@json($sim3dI18n)</script>

    <div class="py-8 sm:py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div
                class="sim3d-shell admin-surface min-h-[calc(100vh-14rem)] overflow-hidden grid lg:grid-cols-2 shadow-md shadow-violet-500/10"
            >
                {{-- Левая панель: загрузка --}}
                <div class="relative flex flex-col border-b lg:border-b-0 lg:border-r border-violet-100 p-6 sm:p-8 lg:p-10 bg-white/80 min-h-0">
                    <div
                        class="pointer-events-none absolute inset-0 opacity-[0.35]"
                        style="background-image: linear-gradient(rgba(139,92,246,0.07) 1px, transparent 1px), linear-gradient(90deg, rgba(139,92,246,0.07) 1px, transparent 1px); background-size: 24px 24px;"
                    ></div>
                    <div class="relative z-10 flex flex-col flex-1 min-h-0 overflow-y-auto overflow-x-hidden">
                        <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-widest text-fuchsia-600">
                            <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-br from-violet-500 to-fuchsia-500 shadow-sm shadow-fuchsia-400/50"></span>
                            {{ __('3D simulator brand') }}
                        </div>
                        <h3 class="mt-3 text-xl sm:text-2xl font-semibold text-violet-950 tracking-tight" id="sim3d-drop-title"></h3>
                        <p class="mt-2 text-sm text-violet-800/75 leading-relaxed max-w-md" id="sim3d-intro"></p>

                        <div
                            id="sim3d-dropzone"
                            class="mt-8 flex-1 min-h-[220px] flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-violet-200 bg-violet-50/60 hover:border-fuchsia-300/90 hover:bg-fuchsia-50/40 transition-colors cursor-pointer px-6 py-10"
                        >
                            <input type="file" id="sim3d-file" class="hidden" accept="video/mp4,video/webm,video/quicktime,video/x-msvideo,.mp4,.webm,.mov,.avi">
                            <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-violet-100 to-fuchsia-100 border border-violet-200/80 flex items-center justify-center mb-4 shadow-sm">
                                <svg class="w-7 h-7 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                            </div>
                            <p class="text-sm text-violet-900/90 text-center font-medium" id="sim3d-drop-hint"></p>
                            <p class="mt-3 text-xs text-violet-600/80 text-center" id="sim3d-formats"></p>
                            <p id="sim3d-filename" class="mt-4 text-sm text-fuchsia-700 font-medium hidden truncate max-w-full"></p>
                        </div>

                        <div class="mt-6 flex flex-wrap gap-3 shrink-0">
                            <button
                                type="button"
                                id="sim3d-analyze"
                                disabled
                                class="admin-btn-primary-solid px-8 py-3 text-sm font-semibold disabled:opacity-40 disabled:pointer-events-none disabled:hover:brightness-100"
                            ></button>
                            <button
                                type="button"
                                id="sim3d-clear"
                                class="inline-flex items-center justify-center rounded-xl border border-violet-200 bg-white px-5 py-3 text-sm font-medium text-violet-900 shadow-sm hover:bg-violet-50/80 hover:border-violet-300 transition-colors"
                            ></button>
                        </div>
                    </div>
                </div>

                {{-- Правая панель: вывод ИИ --}}
                <div class="relative flex flex-col p-6 sm:p-8 lg:p-10 bg-gradient-to-br from-violet-50/90 via-white to-fuchsia-50/50">
                    <div class="pointer-events-none absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[min(100%,28rem)] h-[min(100%,28rem)] rounded-full bg-fuchsia-200/20 blur-3xl" aria-hidden="true"></div>
                    <div class="relative z-10 flex flex-col flex-1 min-h-0">
                        <div class="flex items-center justify-between gap-4">
                            <h3 class="text-lg font-semibold text-violet-950">{{ __('3D simulator results title') }}</h3>
                            <span id="sim3d-badge" class="hidden text-[10px] font-semibold uppercase tracking-wider px-2.5 py-1 rounded-lg bg-emerald-100 text-emerald-800 border border-emerald-200/80">demo</span>
                        </div>

                        <div id="sim3d-empty" class="mt-10 flex-1 flex items-center justify-center text-center px-4">
                            <p class="text-violet-700/70 text-sm max-w-xs leading-relaxed" id="sim3d-empty-text"></p>
                        </div>

                        <div id="sim3d-results" class="hidden mt-6 space-y-6 flex-1 overflow-y-auto pr-1">
                            <div class="rounded-xl border border-violet-100 bg-white/90 p-4 shadow-sm space-y-1">
                                <p class="text-xs font-semibold uppercase tracking-wide text-violet-600/80" id="sim3d-scenario-title"></p>
                                <p id="sim3d-scenario-label" class="text-base font-semibold text-violet-950"></p>
                                <p id="sim3d-scenario-conf" class="text-xs text-violet-600/80"></p>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-4">
                                <article class="rounded-xl border border-violet-100 bg-white/90 p-4 shadow-sm">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-violet-600/80" data-metric-label="speed"></p>
                                    <p class="mt-2 text-2xl font-semibold text-violet-950 tabular-nums" id="sim3d-val-speed">—</p>
                                    <p class="mt-1 text-xs text-violet-600/75" id="sim3d-range-speed"></p>
                                </article>
                                <article class="rounded-xl border border-violet-100 bg-white/90 p-4 shadow-sm">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-violet-600/80" data-metric-label="mass"></p>
                                    <p class="mt-2 text-2xl font-semibold text-violet-950 tabular-nums" id="sim3d-val-mass">—</p>
                                    <p class="mt-1 text-xs text-violet-600/75" id="sim3d-range-mass"></p>
                                </article>
                                <article class="sm:col-span-2 rounded-xl border border-violet-100 bg-white/90 p-4 shadow-sm">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-violet-600/80" data-metric-label="model"></p>
                                    <p class="mt-2 text-base font-medium text-violet-900 leading-snug" id="sim3d-val-model">—</p>
                                </article>
                                <article class="sm:col-span-2 rounded-xl border border-violet-100 bg-white/90 p-4 shadow-sm">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-violet-600/80" data-metric-label="cost"></p>
                                    <p class="mt-2 text-2xl font-semibold text-violet-950 tabular-nums" id="sim3d-val-cost">—</p>
                                    <p class="mt-1 text-xs text-violet-600/75" id="sim3d-range-cost"></p>
                                </article>
                            </div>

                            <div class="rounded-xl border border-violet-100 bg-white/90 p-4 shadow-sm">
                                <p class="text-xs font-semibold uppercase tracking-wide text-violet-800" id="sim3d-confidence-label"></p>
                                <div class="mt-2 h-2 rounded-full bg-violet-100 overflow-hidden">
                                    <div id="sim3d-confidence-bar" class="h-full rounded-full bg-gradient-to-r from-violet-500 to-fuchsia-500 w-0 transition-all duration-700"></div>
                                </div>
                                <p class="mt-1 text-sm text-violet-700/80 tabular-nums" id="sim3d-confidence-val"></p>
                            </div>
                        </div>

                        <p id="sim3d-error" class="hidden mt-4 text-sm text-red-800 bg-red-50 border border-red-200 rounded-xl px-3 py-2"></p>
                    </div>
                </div>

                {{-- Хаттама: толық ен (екі бағанның астында) --}}
                <div
                    id="sim3d-protocol-wrap"
                    class="hidden col-span-1 lg:col-span-2 border-t border-violet-200/90 bg-gradient-to-b from-violet-50/40 via-white/90 to-violet-50/30 px-4 py-8 sm:px-6 lg:px-10"
                >
                    <div
                        class="max-w-7xl mx-auto w-full rounded-2xl border border-violet-200/90 bg-white/95 p-6 sm:p-8 lg:p-10 shadow-md shadow-violet-500/[0.07] max-h-[min(75vh,48rem)] overflow-y-auto"
                    >
                        <div id="sim3d-protocol-root" class="space-y-6 lg:space-y-8"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        @vite(['resources/js/lab.js'])
    @endpush
</x-app-layout>
