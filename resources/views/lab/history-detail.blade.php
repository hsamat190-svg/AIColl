<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-violet-950 leading-tight tracking-tight">
                {{ __('History detail title') }}
            </h2>
            <div class="flex flex-wrap items-center gap-3">
                <button
                    type="button"
                    id="history-detail-pdf-btn"
                    data-filename="{{ __('History pdf filename') }}"
                    class="admin-btn-primary-solid !py-2.5 text-sm shrink-0"
                >
                    {{ __('History download pdf') }}
                </button>
                <a href="{{ route('lab.history') }}" class="admin-link text-sm font-medium">
                    {{ __('History detail back') }}
                </a>
            </div>
        </div>
    </x-slot>

    @push('scripts')
        @vite(['resources/js/history-detail.js'])
    @endpush

    <div class="py-8 sm:py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status') === 'history-updated')
                <p class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                    {{ __('History record updated') }}
                </p>
            @endif

            <div class="admin-surface p-6 space-y-4">
                <h3 class="text-sm font-semibold text-violet-950">{{ __('History detail rename heading') }}</h3>
                <form
                    action="{{ route('lab.history.update', $record) }}"
                    method="post"
                    class="flex flex-col sm:flex-row gap-3 sm:items-end"
                >
                    @csrf
                    @method('PATCH')
                    <div class="min-w-0 flex-1">
                        <label for="history-detail-name" class="sr-only">{{ __('History col name') }}</label>
                        <input
                            id="history-detail-name"
                            type="text"
                            name="name"
                            value="{{ $record->name }}"
                            required
                            maxlength="255"
                            class="w-full rounded-xl border-violet-200 shadow-sm text-sm focus:border-fuchsia-400 focus:ring-fuchsia-400/35"
                        />
                    </div>
                    <button type="submit" class="admin-btn-primary-solid !py-2.5 text-sm shrink-0">
                        {{ __('Save') }}
                    </button>
                </form>
            </div>

            <div id="history-detail-pdf-root" class="admin-surface p-6 sm:p-8 space-y-8 text-violet-950">
                <header class="space-y-1 border-b border-violet-100 pb-5">
                    <h3 class="text-lg font-bold text-violet-950">{{ $record->name }}</h3>
                    <p class="text-sm text-violet-700/90 tabular-nums">
                        {{ $record->created_at->timezone(config('app.timezone'))->format('d.m.Y H:i') }}
                        —
                        @if ($record->source === \App\Models\LabRecord::SOURCE_SIMULATOR_2D)
                            {{ __('History source simulator_2d') }}
                        @else
                            {{ __('History source simulator_3d') }}
                        @endif
                    </p>
                </header>

                @if ($record->source === \App\Models\LabRecord::SOURCE_SIMULATOR_2D && ! $experiment)
                    <p class="text-sm text-amber-900 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3">
                        {{ __('History detail experiment gone') }}
                    </p>
                @endif

                @foreach ($historySections as $section)
                    @if (($section['type'] ?? '') === 'protocol')
                        <section class="space-y-4">
                            @if (! empty($section['protocol']['title']))
                                <h4 class="text-base font-semibold text-violet-950">{{ $section['protocol']['title'] }}</h4>
                            @endif
                            @foreach (($section['protocol']['blocks'] ?? []) as $block)
                                <div class="space-y-3 border-t border-violet-100/90 pt-5 first:border-0 first:pt-0">
                                    @if (! empty($block['heading']))
                                        <h5 class="text-sm font-semibold text-violet-900">{{ $block['heading'] }}</h5>
                                    @endif
                                    @if (($block['type'] ?? '') === 'physics')
                                        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                                            @foreach ($block['steps'] ?? [] as $step)
                                                <div class="rounded-xl border border-violet-100 bg-violet-50/70 p-4 space-y-2">
                                                    @if (! empty($step['title']))
                                                        <p class="text-xs font-semibold text-violet-800">{{ $step['title'] }}</p>
                                                    @endif
                                                    @if (! empty($step['formula']))
                                                        <p class="font-mono text-xs text-violet-900 break-words">{{ $step['formula'] }}</p>
                                                    @endif
                                                    @if (! empty($step['detail']))
                                                        <p class="text-sm text-violet-800/95 leading-relaxed">{{ $step['detail'] }}</p>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        @foreach ($block['paragraphs'] ?? [] as $para)
                                            <p class="text-sm text-violet-800/95 leading-relaxed max-w-5xl">{{ $para }}</p>
                                        @endforeach
                                    @endif
                                </div>
                            @endforeach
                        </section>
                    @else
                        <section class="space-y-3">
                            @if (! empty($section['title']))
                                <h4 class="text-sm font-semibold text-violet-950">{{ $section['title'] }}</h4>
                            @endif
                            @foreach ($section['paragraphs'] ?? [] as $para)
                                <p class="text-sm text-violet-800/95 leading-relaxed whitespace-pre-line">{{ $para }}</p>
                            @endforeach
                            @if (! empty($section['rows']))
                                <dl class="grid gap-2 text-sm">
                                    @foreach ($section['rows'] as $row)
                                        <div class="flex flex-col gap-0.5 sm:flex-row sm:gap-4 border-b border-violet-100/80 pb-3 last:border-0 sm:items-baseline">
                                            <dt class="shrink-0 font-medium text-violet-900 sm:w-56">{{ $row['label'] }}</dt>
                                            <dd class="text-violet-800 min-w-0 break-words">{{ $row['value'] }}</dd>
                                        </div>
                                    @endforeach
                                </dl>
                            @endif
                        </section>
                    @endif
                @endforeach

                @if ($record->source === \App\Models\LabRecord::SOURCE_SIMULATOR_2D && $experiment)
                    <div class="pt-2 border-t border-violet-100">
                        <a href="{{ route('lab.analysis', $experiment) }}" class="admin-link text-sm font-medium">
                            {{ __('History open analysis') }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
