<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-violet-950 leading-tight tracking-tight">
            {{ __('Problem solver title') }}
        </h2>
    </x-slot>

    @php
        $solverI18n = [
            'placeholder' => __('Problem solver placeholder'),
            'submit' => __('Problem solver submit'),
            'loading' => __('Problem solver loading'),
            'resultHeading' => __('Problem solver result heading'),
            'errorGeneric' => __('Problem solver error generic'),
            'errorNetwork' => __('Problem solver error network'),
            'sourceLocal' => __('Problem solver source local'),
            'sourceOpenai' => __('Problem solver source openai'),
        ];
    @endphp
    <script type="application/json" id="problem-solver-i18n">@json($solverI18n)</script>

    <div class="py-8 sm:py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <p class="text-sm text-violet-800/90 leading-relaxed">
                {{ __('Problem solver intro') }}
            </p>

            <div class="admin-surface p-6 sm:p-8 space-y-4">
                <label for="solver-problem" class="block text-sm font-medium text-violet-950">
                    {{ __('Problem solver label task') }}
                </label>
                <textarea
                    id="solver-problem"
                    rows="8"
                    class="w-full rounded-xl border-violet-200 shadow-sm text-sm text-violet-950 placeholder:text-violet-400 focus:border-fuchsia-400 focus:ring-fuchsia-400/35"
                    placeholder="{{ __('Problem solver placeholder') }}"
                ></textarea>
                <div class="flex flex-wrap items-center gap-3">
                    <button
                        type="button"
                        id="solver-submit"
                        class="admin-btn-primary-solid !py-2.5 text-sm shrink-0"
                    >
                        {{ __('Problem solver submit') }}
                    </button>
                    <span id="solver-status" class="text-sm text-violet-600 min-h-[1.25rem]" aria-live="polite"></span>
                </div>
            </div>

            <div id="solver-result-wrap" class="hidden admin-surface p-6 sm:p-8 space-y-4">
                <div class="flex flex-wrap items-baseline justify-between gap-2 border-b border-violet-100 pb-3">
                    <h3 class="text-base font-semibold text-violet-950">{{ __('Problem solver result heading') }}</h3>
                    <span id="solver-source" class="text-xs font-medium uppercase tracking-wide text-violet-500"></span>
                </div>
                <div id="solver-result-body" class="space-y-6 text-sm text-violet-900 leading-relaxed"></div>
            </div>
        </div>
    </div>

    @push('scripts')
        @vite(['resources/js/problem-solver.js'])
    @endpush
</x-app-layout>
