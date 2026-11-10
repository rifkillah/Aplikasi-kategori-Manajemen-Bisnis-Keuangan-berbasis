import './bootstrap';

import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.Chart = Chart;

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
