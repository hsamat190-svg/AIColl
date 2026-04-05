@php
    $fmtInt = static fn (int $n) => number_format($n, 0, '.', ' ');
    $lastStr = $lastActivity
        ? $lastActivity->diffForHumans()
        : __('Dashboard activity none');
    $simTimeStr =
        $simHours .
        __('Dashboard time hour short') .
        ' ' .
        sprintf('%02d', $simMinsRem) .
        __('Dashboard time min short');
    $deskI18n = [
        'chartLegendBefore' => __('Dashboard chart legend before'),
        'chartLegendAfter' => __('Dashboard chart legend after'),
        'chartX' => __('Dashboard chart x'),
        'chartY' => __('Dashboard chart y'),
        'momentumOk' => __('Dashboard momentum conserved'),
        'momentumApprox' => __('Dashboard momentum approx'),
    ];
@endphp

<x-app-layout>
    <div
        id="aicoll-desk"
        class="desk-bleed text-white"
        data-chart-labels="{{ e(json_encode($chartLabels)) }}"
        data-chart-before="{{ e(json_encode($chartBefore)) }}"
        data-chart-after="{{ e(json_encode($chartAfter)) }}"
    >
        <script type="application/json" id="desk-i18n-data">@json($deskI18n)</script>

        <header class="max-w-[88rem] mx-auto mb-6 sm:mb-8">
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-white [text-shadow:0_0_32px_rgba(168,85,247,0.35)]">
                {{ __('Dashboard desk title') }}
            </h1>
            <p class="mt-2 text-sm text-violet-200/75 max-w-2xl">
                {{ __('Dashboard desk tagline') }}
            </p>
        </header>

        <div class="desk-grid">
            {{-- Общая статистика --}}
            <section class="desk-card">
                <h2 class="desk-card-title">{{ __('Dashboard stat general') }}</h2>
                <ul class="space-y-4">
                    <li class="flex items-center gap-4">
                        <div class="desk-stat-icon-wrap desk-neon-blue">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75V18a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 18v-2.25M18.75 8.25V6a2.25 2.25 0 0 0-2.25-2.25H8.25A2.25 2.25 0 0 0 6 6v2.25" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 8.25h15v9.75a2.25 2.25 0 0 1-2.25 2.25h-10.5A2.25 2.25 0 0 1 4.5 18V8.25Z" />
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-medium uppercase tracking-wide text-violet-300/80">{{ __('Dashboard stat calculations') }}</div>
                            <div class="desk-stat-num mt-0.5">{{ $fmtInt(max(0, $calculationsCount)) }}</div>
                        </div>
                    </li>
                    <li class="flex items-center gap-4">
                        <div class="desk-stat-icon-wrap desk-neon-pink">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9A2.25 2.25 0 0 0 4.5 18.75Z" />
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-medium uppercase tracking-wide text-violet-300/80">{{ __('Dashboard stat videos') }}</div>
                            <div class="desk-stat-num mt-0.5 desk-neon-magenta">{{ $fmtInt(max(0, $videosCount)) }}</div>
                        </div>
                    </li>
                    <li class="flex items-center gap-4">
                        <div class="desk-stat-icon-wrap desk-neon-magenta">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-medium uppercase tracking-wide text-violet-300/80">{{ __('Dashboard stat last activity') }}</div>
                            <div class="mt-0.5 text-lg font-semibold text-white/95">{{ $lastStr }}</div>
                        </div>
                    </li>
                    <li class="flex items-center gap-4">
                        <div class="desk-stat-icon-wrap desk-neon-blue">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m6-4a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-medium uppercase tracking-wide text-violet-300/80">{{ __('Dashboard stat sim time') }}</div>
                            <div class="desk-stat-num mt-0.5">{{ $simTimeStr }}</div>
                        </div>
                    </li>
                </ul>
            </section>

            {{-- Быстрый анализ --}}
            <section class="desk-card">
                <h2 class="desk-card-title">{{ __('Dashboard quick title') }}</h2>
                <div class="grid gap-4 lg:grid-cols-2 lg:gap-5">
                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-2 sm:gap-3">
                            <div>
                                <label class="mb-1 block text-[0.7rem] font-medium uppercase tracking-wide text-violet-300/80" for="desk-m-a">{{ __('Dashboard mass a') }}</label>
                                <input class="desk-input" id="desk-m-a" type="number" step="any" value="15" min="0.01" />
                                <span class="mt-0.5 block text-[0.65rem] text-violet-400/70">{{ __('Dashboard unit kg') }}</span>
                            </div>
                            <div>
                                <label class="mb-1 block text-[0.7rem] font-medium uppercase tracking-wide text-violet-300/80" for="desk-v-a">{{ __('Dashboard speed a') }}</label>
                                <input class="desk-input" id="desk-v-a" type="number" step="any" value="10" />
                                <span class="mt-0.5 block text-[0.65rem] text-violet-400/70">{{ __('Dashboard unit ms') }}</span>
                            </div>
                            <div>
                                <label class="mb-1 block text-[0.7rem] font-medium uppercase tracking-wide text-violet-300/80" for="desk-m-b">{{ __('Dashboard mass b') }}</label>
                                <input class="desk-input" id="desk-m-b" type="number" step="any" value="20" min="0.01" />
                                <span class="mt-0.5 block text-[0.65rem] text-violet-400/70">{{ __('Dashboard unit kg') }}</span>
                            </div>
                            <div>
                                <label class="mb-1 block text-[0.7rem] font-medium uppercase tracking-wide text-violet-300/80" for="desk-v-b">{{ __('Dashboard speed b') }}</label>
                                <input class="desk-input" id="desk-v-b" type="number" step="any" value="5" />
                                <span class="mt-0.5 block text-[0.65rem] text-violet-400/70">{{ __('Dashboard unit ms') }}</span>
                            </div>
                        </div>
                        <p class="text-xs text-violet-400/65">{{ __('Dashboard formula block') }}</p>
                    </div>
                    <div class="rounded-xl border border-fuchsia-500/35 bg-black/30 p-4 shadow-[0_0_28px_rgba(244,114,182,0.12)] ring-1 ring-fuchsia-400/15">
                        <div class="desk-formula-glow mb-4 text-center">
                            <span class="f-p">P</span><span class="f-rest"> = m·v</span>
                        </div>
                        <ul class="space-y-2 text-sm">
                            <li class="flex flex-wrap items-baseline gap-2 text-violet-100/95">
                                <span class="text-violet-300/80">{{ __('Dashboard momentum before') }}:</span>
                                <span id="desk-p-before" class="text-xl font-bold tabular-nums text-sky-300 [text-shadow:0_0_14px_rgba(56,189,248,0.5)]">250</span>
                                <span class="text-violet-400/80">{{ __('Simulator momentum unit') }}</span>
                            </li>
                            <li class="flex flex-wrap items-baseline gap-2 text-violet-100/95">
                                <span class="text-violet-300/80">{{ __('Dashboard momentum after') }}:</span>
                                <span id="desk-p-after" class="text-xl font-bold tabular-nums text-fuchsia-300 [text-shadow:0_0_14px_rgba(244,114,182,0.45)]">250</span>
                                <span class="text-violet-400/80">{{ __('Simulator momentum unit') }}</span>
                                <span id="desk-momentum-law" class="block w-full text-sm font-medium text-emerald-400 sm:inline sm:w-auto">{{ __('Dashboard momentum conserved') }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </section>

            {{-- Видео статус --}}
            <section class="desk-card">
                <h2 class="desk-card-title">{{ __('Dashboard video title') }}</h2>
                <ul class="space-y-0">
                    <li class="desk-video-row">
                        <div class="desk-thumb" aria-hidden="true"></div>
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-sm font-medium text-white/95">{{ __('Dashboard video name 1') }}</div>
                            <div class="mt-0.5 text-xs text-sky-300/90">100% · {{ __('Dashboard video status done') }}</div>
                        </div>
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-sky-400/40 text-sky-300 text-lg [filter:drop-shadow(0_0_6px_rgba(56,189,248,0.6))]" aria-hidden="true">∞</span>
                    </li>
                    <li class="desk-video-row">
                        <div class="desk-thumb" aria-hidden="true"></div>
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-sm font-medium text-white/95">{{ __('Dashboard video name 2') }}</div>
                            <div class="mt-0.5 text-xs text-fuchsia-300/90">60% · {{ __('Dashboard video status processing') }}</div>
                        </div>
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-fuchsia-400/40 text-fuchsia-300 text-lg [filter:drop-shadow(0_0_6px_rgba(232,121,249,0.55))]" aria-hidden="true">∞</span>
                    </li>
                    <li class="desk-video-row">
                        <div class="desk-thumb" aria-hidden="true"></div>
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-sm font-medium text-white/95">{{ __('Dashboard video name 3') }}</div>
                            <div class="mt-0.5 text-xs text-violet-300/80">0% · {{ __('Dashboard video status queue') }}</div>
                        </div>
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-violet-400/35 text-violet-300 text-lg" aria-hidden="true">∞</span>
                    </li>
                    <li class="desk-video-row">
                        <div class="desk-thumb ring-1 ring-rose-500/40" aria-hidden="true"></div>
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-sm font-medium text-white/95">{{ __('Dashboard video name 4') }}</div>
                            <div class="mt-0.5 flex items-center gap-1 text-xs text-rose-300">
                                <span class="font-bold" aria-hidden="true">!</span>
                                {{ __('Dashboard video status error') }}
                            </div>
                        </div>
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-violet-400/35 text-violet-300 text-lg" aria-hidden="true">∞</span>
                    </li>
                </ul>
            </section>

            {{-- График энергии --}}
            <section class="desk-card">
                <h2 class="desk-card-title">{{ __('Dashboard chart title') }}</h2>
                <div class="desk-chart-wrap">
                    <canvas id="desk-energy-chart" aria-label="{{ __('Dashboard chart title') }}"></canvas>
                </div>
            </section>
        </div>
    </div>

    @push('scripts')
        @vite(['resources/js/dashboard.js'])
    @endpush
</x-app-layout>
