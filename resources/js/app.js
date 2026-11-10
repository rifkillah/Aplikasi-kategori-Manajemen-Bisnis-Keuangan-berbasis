import './bootstrap';

import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.Chart = Chart;

/*
 * Appearance: system / light / dark, with instant switching and persistence.
 */
const systemDark = window.matchMedia('(prefers-color-scheme: dark)');

window.applyAppTheme = function (mode) {
    const dark = mode === 'dark' || (mode === 'system' && systemDark.matches);

    document.documentElement.dataset.theme = dark ? 'dark' : 'light';
    document.documentElement.dataset.themeMode = mode;

    const themeColor = document.querySelector('meta[name="theme-color"]');
    if (themeColor) {
        themeColor.setAttribute('content', dark ? '#070b1a' : '#eef0fb');
    }

    window.dispatchEvent(new CustomEvent('app-theme-changed', { detail: { mode, dark } }));
};

if (typeof systemDark.addEventListener === 'function') {
    systemDark.addEventListener('change', () => {
        if ((document.documentElement.dataset.themeMode || 'system') === 'system') {
            window.applyAppTheme('system');
        }
    });
}

window.themeToggle = function (initial) {
    return {
        mode: initial || 'system',

        init() {
            window.addEventListener('app-theme-changed', (event) => {
                this.mode = event.detail.mode;
            });
        },

        get label() {
            return { system: 'Ikuti sistem', light: 'Terang', dark: 'Gelap' }[this.mode] || 'Ikuti sistem';
        },

        cycle() {
            const order = ['system', 'light', 'dark'];
            this.mode = order[(order.indexOf(this.mode) + 1) % order.length];

            window.applyAppTheme(this.mode);

            const url = this.$el?.dataset?.themeUrl;
            if (!url) {
                return;
            }

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                },
                body: JSON.stringify({ theme_mode: this.mode }),
            }).catch(() => {});
        },
    };
};

Alpine.start();

/*
 * Scroll reveal.
 *
 * Elements marked with [data-reveal] fade + rise into place once they enter
 * the viewport. Siblings are staggered so lists and card grids feel alive.
 * The hidden state is gated behind the `reveal-init` class, which is added
 * here — if JavaScript never runs, all content stays visible.
 */
const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

// Applied before first paint (deferred module) so elements never flash visible
// and then jump back into their hidden reveal state.
if (!prefersReducedMotion) {
    document.documentElement.classList.add('reveal-init');
}

function initReveal() {
    const items = Array.from(document.querySelectorAll('[data-reveal]'));

    if (items.length === 0) {
        return;
    }

    // Drops the reveal bookkeeping once the entrance finishes, so hover
    // transforms and shadows are not delayed by the stagger.
    const settle = (item) => {
        item.removeAttribute('data-reveal');
        item.classList.remove('is-visible');
        item.style.transitionDelay = '';
        item.style.willChange = '';
    };

    if (prefersReducedMotion || !('IntersectionObserver' in window)) {
        items.forEach((item) => settle(item));
        return;
    }

    const counters = new Map();

    items.forEach((item) => {
        const parent = item.parentElement ?? document.body;
        const index = counters.get(parent) ?? 0;
        counters.set(parent, index + 1);

        // Cap the stagger so long tables do not wait too long.
        item.style.transitionDelay = `${Math.min(index * 60, 300)}ms`;
    });

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);

                const delay = parseFloat(entry.target.style.transitionDelay) || 0;
                window.setTimeout(() => settle(entry.target), delay + 700);
            });
        },
        { threshold: 0.12, rootMargin: '0px 0px -6% 0px' },
    );

    items.forEach((item) => observer.observe(item));
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initReveal, { once: true });
} else {
    initReveal();
}
