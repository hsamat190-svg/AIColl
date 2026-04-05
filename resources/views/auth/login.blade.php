@php
    $loginPromoSlides = [
        [
            'title' => __('Login split headline'),
            'text' => __('Login split tagline'),
            'dotAria' => __('Login promo dot', ['num' => 1]),
        ],
        [
            'title' => __('Login promo sim title'),
            'text' => __('Login promo sim text'),
            'dotAria' => __('Login promo dot', ['num' => 2]),
        ],
        [
            'title' => __('Login promo ai title'),
            'text' => __('Login promo ai text'),
            'dotAria' => __('Login promo dot', ['num' => 3]),
        ],
        [
            'title' => __('Login promo solve title'),
            'text' => __('Login promo solve text'),
            'dotAria' => __('Login promo dot', ['num' => 4]),
        ],
    ];
@endphp
<x-auth-split-layout>
    <div class="w-full max-w-4xl mx-auto">
        <div
            class="flex flex-col md:flex-row rounded-3xl overflow-hidden shadow-[0_25px_80px_rgba(0,0,0,0.45)] ring-1 ring-white/10 bg-white/[0.04] backdrop-blur-sm"
        >
            {{-- Сол панель: брендинг + 4 слайд (автокарусель) --}}
            <aside
                class="relative hidden md:flex md:w-[46%] lg:w-1/2 flex-col justify-between min-h-[28rem] lg:min-h-[32rem] p-8 lg:p-10 bg-gradient-to-br from-purple-600/35 via-fuchsia-600/25 to-violet-950/50 border-e border-white/10"
                x-data="loginPromoCarousel(@js($loginPromoSlides))"
                role="region"
                aria-roledescription="{{ __('Login promo carousel roledescription') }}"
                aria-label="{{ __('Login promo carousel label') }}"
            >
                <div
                    class="pointer-events-none absolute inset-0 opacity-[0.12]"
                    style="background-image: linear-gradient(rgba(255,255,255,0.15) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.15) 1px, transparent 1px); background-size: 28px 28px;"
                ></div>
                <div class="relative z-10 flex flex-col flex-1 min-h-0">
                    <div class="flex items-center gap-3 shrink-0">
                        <x-application-logo class="h-11 w-11 shrink-0 rounded-xl ring-1 ring-white/20 shadow-[0_0_32px_rgba(168,85,247,0.35)]" />
                        <span class="text-xl font-bold tracking-tight text-white">{{ config('app.name', 'AIColl') }}</span>
                    </div>
                    <div
                        class="mt-6 flex-1 min-h-[12rem] lg:min-h-[14rem] overflow-hidden"
                        @mouseenter="pause()"
                        @mouseleave="start()"
                        aria-live="polite"
                    >
                        <div
                            class="flex h-full transition-transform duration-1000 ease-in-out motion-reduce:transition-none"
                            :style="trackStyle()"
                        >
                            <template x-for="(slide, si) in slides" :key="si">
                                <div
                                    class="shrink-0 flex flex-col justify-start space-y-4 pe-2"
                                    :style="{ flexBasis: slideBasisPct(), width: slideBasisPct() }"
                                >
                                    <h1
                                        class="text-2xl lg:text-3xl font-bold text-white leading-tight tracking-tight"
                                        x-text="slide.title"
                                    ></h1>
                                    <p
                                        class="text-sm lg:text-base text-zinc-300/90 leading-relaxed max-w-sm"
                                        x-text="slide.text"
                                    ></p>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
                <nav class="relative z-10 flex flex-wrap items-center gap-2 pt-8" aria-label="{{ __('Login promo dots label') }}">
                    <template x-for="(slide, si) in slides" :key="'dot-' + si">
                        <button
                            type="button"
                            class="h-1.5 w-1.5 rounded-full transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-white/80 focus-visible:ring-offset-2 focus-visible:ring-offset-violet-950/40"
                            :class="idx === si ? 'bg-white' : 'bg-white/30 hover:bg-white/50'"
                            :aria-label="slide.dotAria"
                            @click="goTo(si)"
                        ></button>
                    </template>
                </nav>
            </aside>

            {{-- Оң панель: форма (ақ карта, референстегі орналасу) --}}
            <div class="w-full md:w-[54%] lg:w-1/2 bg-white text-gray-900 px-6 py-8 sm:px-10 sm:py-10 lg:px-12 lg:py-12">
                {{-- Мобильда қысқа бренд жолы --}}
                <div class="flex md:hidden items-center gap-3 pb-6 mb-2 border-b border-gray-100">
                    <x-application-logo class="h-10 w-10 shrink-0 rounded-xl ring-1 ring-gray-200" />
                    <div>
                        <p class="font-semibold text-gray-900">{{ config('app.name', 'AIColl') }}</p>
                        <p class="text-lg font-semibold text-fuchsia-700">{{ __('Welcome login title') }}</p>
                    </div>
                </div>

                <div class="text-center mb-6 hidden md:block">
                    <a href="/" class="inline-flex flex-col items-center gap-2 group">
                        <x-application-logo class="h-12 w-12 rounded-xl ring-1 ring-gray-200 shadow-sm mx-auto" />
                        <span class="text-lg font-semibold text-gray-900 tracking-tight group-hover:text-fuchsia-700 transition-colors">
                            {{ config('app.name', 'AIColl') }}
                        </span>
                    </a>
                    <h2 class="mt-5 text-2xl font-bold text-gray-900 tracking-tight">
                        {{ __('Welcome login title') }}
                    </h2>
                </div>

                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <div>
                        <x-input-label for="email" :value="__('Email')" class="text-gray-700" />
                        <x-text-input
                            id="email"
                            class="login-split-input block mt-2 w-full"
                            type="email"
                            name="email"
                            :value="old('email')"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="email@example.com"
                        />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="password" :value="__('Password')" class="text-gray-700" />
                        <x-text-input
                            id="password"
                            class="login-split-input block mt-2 w-full"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                        />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 pt-1">
                        <label for="remember_me" class="inline-flex items-center">
                            <input
                                id="remember_me"
                                type="checkbox"
                                class="rounded-md border-gray-300 text-fuchsia-600 shadow-sm focus:ring-fuchsia-500"
                                name="remember"
                            >
                            <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                        </label>
                        @if (Route::has('password.request'))
                            <a
                                class="text-sm font-medium text-fuchsia-600 hover:text-fuchsia-700 focus:outline-none focus:underline"
                                href="{{ route('password.request') }}"
                            >
                                {{ __('Forgot your password?') }}
                            </a>
                        @endif
                    </div>

                    <button
                        type="submit"
                        class="w-full inline-flex justify-center items-center rounded-xl border border-transparent bg-gradient-to-r from-purple-600 to-fuchsia-600 px-4 py-3.5 text-sm font-semibold text-white shadow-[0_8px_30px_rgba(168,85,247,0.35)] hover:brightness-105 focus:outline-none focus:ring-2 focus:ring-fuchsia-500 focus:ring-offset-2 transition"
                    >
                        {{ __('Log in') }}
                    </button>
                </form>

                <p class="mt-6 text-center md:hidden">
                    <a href="/" class="text-sm text-gray-500 hover:text-gray-800">{{ __('Back to home') }}</a>
                </p>
            </div>
        </div>
    </div>
</x-auth-split-layout>
