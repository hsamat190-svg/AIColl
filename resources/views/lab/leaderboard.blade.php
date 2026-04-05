<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-violet-950 leading-tight tracking-tight">
            {{ __('Leaderboard') }}
        </h2>
    </x-slot>

    <div class="py-8 sm:py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-5 flex flex-wrap gap-3 items-center text-sm text-violet-900/90">
                <label class="font-medium text-violet-800">{{ __('Tag filter') }}
                    <input type="text" id="lb-tag" placeholder="daily-..." class="ml-2 rounded-xl border-violet-200 px-2 py-1.5 text-violet-950 shadow-sm focus:border-fuchsia-400 focus:ring-1 focus:ring-fuchsia-400/40">
                </label>
                <button type="button" id="lb-refresh" class="rounded-xl bg-violet-100 px-4 py-2 font-medium text-violet-900 ring-1 ring-violet-200/80 hover:bg-fuchsia-50 hover:ring-fuchsia-200/60 transition-colors">{{ __('Refresh') }}</button>
            </div>
            <div class="admin-surface overflow-x-auto">
                <table class="min-w-full text-sm text-violet-900/90" id="lb-table">
                    <thead class="admin-table-head">
                        <tr>
                            <th class="p-3">#</th>
                            <th class="p-3">{{ __('User') }}</th>
                            <th class="p-3">{{ __('Score') }}</th>
                            <th class="p-3">{{ __('Tag') }}</th>
                            <th class="p-3">{{ __('When') }}</th>
                        </tr>
                    </thead>
                    <tbody id="lb-body"></tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        @vite(['resources/js/lab.js'])
    @endpush
</x-app-layout>
