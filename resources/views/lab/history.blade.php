<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-violet-950 leading-tight tracking-tight">
            {{ __('History page title') }}
        </h2>
    </x-slot>

    <div class="py-8 sm:py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status') === 'history-updated')
                <p class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                    {{ __('History record updated') }}
                </p>
            @endif
            @if (session('status') === 'history-deleted')
                <p class="rounded-xl border border-violet-200 bg-violet-50 px-4 py-3 text-sm text-violet-900">
                    {{ __('History record deleted') }}
                </p>
            @endif

            <div class="admin-surface overflow-hidden overflow-x-auto">
                <table class="min-w-full text-sm text-violet-900/90">
                    <thead class="admin-table-head">
                        <tr>
                            <th class="p-3">{{ __('History col name') }}</th>
                            <th class="p-3">{{ __('History col created') }}</th>
                            <th class="p-3">{{ __('History col simulator') }}</th>
                            <th class="p-3 text-end">{{ __('History col actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($records as $record)
                            <tr class="border-t border-violet-100/80 hover:bg-violet-50/40 transition-colors">
                                <td class="p-3 font-medium text-violet-950 max-w-[14rem] sm:max-w-md truncate" title="{{ $record->name }}">
                                    {{ $record->name }}
                                </td>
                                <td class="p-3 tabular-nums text-violet-800/90 whitespace-nowrap">
                                    {{ $record->created_at->timezone(config('app.timezone'))->format('d.m.Y H:i') }}
                                </td>
                                <td class="p-3">
                                    @if ($record->source === \App\Models\LabRecord::SOURCE_SIMULATOR_2D)
                                        <span class="inline-flex rounded-lg bg-violet-100 px-2.5 py-1 text-xs font-semibold text-violet-900 ring-1 ring-violet-200/80">
                                            {{ __('History source simulator_2d') }}
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-lg bg-fuchsia-100 px-2.5 py-1 text-xs font-semibold text-fuchsia-900 ring-1 ring-fuchsia-200/80">
                                            {{ __('History source simulator_3d') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="p-3">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        <a
                                            href="{{ route('lab.history.show', $record) }}"
                                            class="admin-link text-xs sm:text-sm"
                                        >{{ __('History open details') }}</a>
                                        @if (! empty($record->payload['experiment_id']))
                                            <a
                                                href="{{ route('lab.analysis', ['experiment' => $record->payload['experiment_id']]) }}"
                                                class="admin-link text-xs sm:text-sm"
                                            >{{ __('History open analysis') }}</a>
                                        @endif
                                        <form
                                            action="{{ route('lab.history.destroy', $record) }}"
                                            method="post"
                                            class="inline"
                                            onsubmit="return confirm(@json(__('History confirm delete')))"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-red-200 bg-white text-red-600 shadow-sm hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-400/35"
                                                title="{{ __('History delete') }}"
                                            >
                                                <span class="sr-only">{{ __('History delete') }}</span>
                                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-8 text-center text-violet-700/75">
                                    {{ __('History empty') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($records->hasPages())
                <div class="mt-2">{{ $records->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
