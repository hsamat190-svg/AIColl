import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

/**
 * Корневой layout (app): сайдбар + rail. Подписи с PHP через x-data="layoutShell(@js(...))".
 */
Alpine.data('layoutShell', (railLabels) => ({
    sidebarOpen: false,
    sidebarCollapsed: false,
    profileMenuOpen: false,
    sidebarRail: railLabels ?? { close: '', expand: '', collapse: '' },
    railAriaLabel() {
        if (!window.matchMedia('(min-width: 1024px)').matches) {
            return this.sidebarRail.close;
        }
        return this.sidebarCollapsed ? this.sidebarRail.expand : this.sidebarRail.collapse;
    },
    toggleRail() {
        if (window.matchMedia('(min-width: 1024px)').matches) {
            this.sidebarCollapsed = !this.sidebarCollapsed;
        } else {
            this.sidebarOpen = false;
        }
    },
    /** Свёрнутый rail: первый клик по аккаунту разворачивает сайдбар; иначе — меню профиля */
    toggleProfileMenu() {
        const desktop = window.matchMedia('(min-width: 1024px)').matches;
        if (desktop && this.sidebarCollapsed) {
            this.sidebarCollapsed = false;
            this.profileMenuOpen = false;
            return;
        }
        this.profileMenuOpen = !this.profileMenuOpen;
    },
}));

/** Логин бетінің сол панелі: 4 слайд, автоматты ауысым (оң → сол). */
Alpine.data('loginPromoCarousel', (slides) => ({
    slides: Array.isArray(slides) ? slides : [],
    idx: 0,
    timer: null,
    durationMs: 4500,
    init() {
        this.start();
    },
    start() {
        clearInterval(this.timer);
        if (this.slides.length < 2) {
            return;
        }
        this.timer = setInterval(() => {
            this.idx = (this.idx + 1) % this.slides.length;
        }, this.durationMs);
    },
    pause() {
        clearInterval(this.timer);
        this.timer = null;
    },
    goTo(i) {
        const n = this.slides.length;
        if (n < 1) {
            return;
        }
        this.idx = ((i % n) + n) % n;
        this.pause();
        this.start();
    },
    trackStyle() {
        const n = this.slides.length || 1;
        const pct = 100 / n;

        return {
            width: `${n * 100}%`,
            transform: `translateX(-${this.idx * pct}%)`,
        };
    },
    slideBasisPct() {
        const n = this.slides.length || 1;

        return `${100 / n}%`;
    },
}));

Alpine.start();
