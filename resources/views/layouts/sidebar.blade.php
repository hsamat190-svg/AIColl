@php
    $name = config('app.name', 'AIColl');
    $user = Auth::user();
    $initial = mb_strtoupper(mb_substr((string) $user->name, 0, 1)) ?: '?';
    $isSim = request()->routeIs('lab.index');
    $isAi = request()->routeIs('lab.video');
    $isProb = request()->routeIs('lab.problems');
    $isHist = request()->routeIs('lab.history')
        || request()->routeIs('lab.history.show')
        || request()->routeIs('lab.analysis');
    $navActive =
        'app-sidebar-link app-sidebar-link--active flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium leading-snug transition-colors';
    $navIdle =
        'app-sidebar-link flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium leading-snug transition-colors';
@endphp

{{-- Mobile overlay --}}
<div
    x-show="sidebarOpen"
    x-transition:enter="transition-opacity ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @click="sidebarOpen = false"
    class="fixed inset-0 z-40 bg-[#0a0612]/70 backdrop-blur-[2px] lg:hidden"
    style="display: none;"
    aria-hidden="true"
></div>

<aside
    id="app-sidebar"
    :class="[
        sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
        sidebarCollapsed ? 'lg:w-[5.25rem] app-sidebar--collapsed' : 'lg:w-[17.5rem]',
    ]"
    class="app-sidebar-shell fixed inset-y-0 left-0 z-50 flex min-w-0 w-[17.5rem] shrink-0 flex-col transition-[width,transform] duration-200 ease-out lg:static lg:z-0 lg:min-h-screen"
    style="background-color: #120d1d"
    aria-label="{{ __('Nav sidebar label') }}"
>
    <div class="app-sidebar-inner flex min-h-0 min-w-0 flex-1 flex-col overflow-x-hidden overflow-y-auto overscroll-contain px-3 pt-4 pb-4">
        {{-- Бренд + иконка сворачивания (моб.: закрыть панель) --}}
        <div class="sidebar-brand-inner mb-5 flex shrink-0 items-center gap-2">
            <a
                href="{{ route('lab.index') }}"
                @click="sidebarOpen = false"
                class="sidebar-brand-link flex min-w-0 flex-1 items-center gap-2.5 rounded-xl px-1.5 py-1 transition hover:bg-white/[0.04]"
            >
                <div class="sidebar-brand-logo flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-fuchsia-500 via-violet-600 to-purple-800 p-[2px] shadow-[0_0_22px_rgba(192,38,211,0.35)] ring-1 ring-white/15">
                    <img
                        src="{{ asset('images/aicol-logo.png') }}"
                        alt="{{ $name }}"
                        class="h-full w-full rounded-[10px] object-contain bg-[#120d1d]"
                        width="36"
                        height="36"
                        decoding="async"
                    />
                </div>
                <span class="sidebar-brand-text truncate text-lg font-bold tracking-tight text-white">{{ $name }}</span>
            </a>
            <button
                type="button"
                @click="toggleRail()"
                class="sidebar-rail-toggle flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-white/12 bg-white/[0.06] text-white/85 transition hover:border-fuchsia-400/35 hover:bg-white/[0.1] hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-fuchsia-400/45"
                :aria-label="railAriaLabel()"
                aria-controls="app-sidebar"
            >
                <svg class="h-5 w-5 lg:hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
                <svg class="hidden h-5 w-5 lg:block" x-show="!sidebarCollapsed" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.75 19.5l-7.5-7.5 7.5-7.5m-6 15L5.25 12l7.5-7.5" />
                </svg>
                <svg class="hidden h-5 w-5 lg:block" x-show="sidebarCollapsed" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 4.5l7.5 7.5-7.5 7.5m-6-15l7.5 7.5-7.5 7.5" />
                </svg>
            </button>
        </div>

        <nav class="space-y-1 shrink-0 pb-2" role="navigation">
            {{-- Симулятор — Beaker --}}
            <a href="{{ route('lab.index') }}" @click="sidebarOpen = false" class="{{ $isSim ? $navActive : $navIdle }}">
                <span class="app-sidebar-icon-wrap flex h-9 w-9 shrink-0 items-center justify-center rounded-lg">
                    <svg class="app-sidebar-icon h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m4.5 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23-.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.611L5.114 15.3M19.8 15.3V9.75a2.25 2.25 0 0 0-2.25-2.25h-6.75a2.25 2.25 0 0 0-2.25 2.25v5.55" />
                    </svg>
                </span>
                <span class="sidebar-nav-label min-w-0 flex-1 break-words">{{ __('Simulator') }}</span>
            </a>

            {{-- AI Анализ — Sparkles --}}
            <a href="{{ route('lab.video') }}" @click="sidebarOpen = false" class="{{ $isAi ? $navActive : $navIdle }}">
                <span class="app-sidebar-icon-wrap flex h-9 w-9 shrink-0 items-center justify-center rounded-lg">
                    <svg class="app-sidebar-icon h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Zm8.446-7.189L18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Zm-1.365 11.852L16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
                    </svg>
                </span>
                <span class="sidebar-nav-label min-w-0 flex-1 break-words">{{ __('Nav ai analysis') }}</span>
            </a>

            {{-- Есеп шығару — Academic cap --}}
            <a href="{{ route('lab.problems') }}" @click="sidebarOpen = false" class="{{ $isProb ? $navActive : $navIdle }}">
                <span class="app-sidebar-icon-wrap flex h-9 w-9 shrink-0 items-center justify-center rounded-lg">
                    <svg class="app-sidebar-icon h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                    </svg>
                </span>
                <span class="sidebar-nav-label min-w-0 flex-1 break-words">{{ __('Nav problem solver') }}</span>
            </a>

            {{-- История — Clock --}}
            <a href="{{ route('lab.history') }}" @click="sidebarOpen = false" class="{{ $isHist ? $navActive : $navIdle }}">
                <span class="app-sidebar-icon-wrap flex h-9 w-9 shrink-0 items-center justify-center rounded-lg">
                    <svg class="app-sidebar-icon h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </span>
                <span class="sidebar-nav-label min-w-0 flex-1 break-words">{{ __('History') }}</span>
            </a>
        </nav>

        <div class="flex-1 min-h-4" aria-hidden="true"></div>

        {{-- Профиль: ∞ солда, аты/email оңда (Breeze стилі) --}}
        <div
            class="relative mt-2 shrink-0 border-t border-white/10 pt-4"
            @click.outside="profileMenuOpen = false"
        >
            <div
                x-show="profileMenuOpen"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-1"
                class="absolute bottom-full left-0 right-0 z-[70] mb-2 rounded-xl border border-fuchsia-500/25 bg-[#1a1428] py-1.5 shadow-[0_-8px_32px_rgba(0,0,0,0.55)] ring-1 ring-white/10"
                style="display: none;"
                role="menu"
                aria-label="{{ __('Nav profile open menu') }}"
            >
                <a
                    href="{{ route('profile.edit') }}#profile-information"
                    role="menuitem"
                    @click="profileMenuOpen = false; sidebarOpen = false"
                    class="block px-4 py-2.5 text-sm text-white/90 hover:bg-white/[0.08]"
                >{{ __('Profile') }}</a>
                <a
                    href="{{ route('profile.edit') }}#profile-password"
                    role="menuitem"
                    @click="profileMenuOpen = false; sidebarOpen = false"
                    class="block px-4 py-2.5 text-sm text-white/90 hover:bg-white/[0.08]"
                >{{ __('Nav settings') }}</a>
                <div class="my-1 border-t border-white/10" role="presentation"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        type="submit"
                        role="menuitem"
                        class="w-full px-4 py-2.5 text-left text-sm text-rose-300/95 hover:bg-white/[0.06]"
                        @click="profileMenuOpen = false; sidebarOpen = false"
                    >{{ __('Log Out') }}</button>
                </form>
            </div>

            <button
                type="button"
                @click="toggleProfileMenu()"
                :aria-expanded="profileMenuOpen ? 'true' : 'false'"
                aria-label="{{ __('Nav profile open menu') }}"
                class="sidebar-profile-trigger flex w-full items-center gap-3 rounded-xl px-2 py-2.5 text-left outline-none transition hover:bg-white/[0.06] focus-visible:ring-2 focus-visible:ring-fuchsia-400/50"
            >
                <div
                    class="sidebar-profile-avatar relative flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-fuchsia-500 via-violet-500 to-purple-800 p-[2px] shadow-[0_0_20px_rgba(168,85,247,0.45)] ring-1 ring-fuchsia-400/30"
                    aria-hidden="true"
                >
                    <div class="flex h-full w-full items-center justify-center rounded-full bg-[#120d1d] shadow-[inset_0_0_18px_rgba(168,85,247,0.15)]">
                        <span
                            class="select-none text-xl font-light leading-none text-white [text-shadow:0.5px_0_0_rgba(248,113,113,0.4),-0.5px_0_0_rgba(96,165,250,0.4)]"
                        >∞</span>
                    </div>
                </div>
                <div class="sidebar-profile-meta min-w-0 flex-1">
                    <div class="truncate text-sm font-semibold text-white leading-tight">{{ $user->name }}</div>
                    <div class="app-sidebar-profile-email mt-0.5 truncate text-xs leading-tight">{{ $user->email }}</div>
                </div>
                <svg
                    class="sidebar-profile-chevron h-4 w-4 shrink-0 text-fuchsia-400/65 transition-transform duration-200"
                    :class="profileMenuOpen ? 'rotate-180' : ''"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="2"
                    stroke="currentColor"
                    aria-hidden="true"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" />
                </svg>
            </button>
        </div>
    </div>
</aside>
