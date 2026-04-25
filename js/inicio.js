// js/inicio.js
document.addEventListener('DOMContentLoaded', () => {
    const slides = document.querySelectorAll('.hero-slide');
    const dots   = document.querySelectorAll('.hero-dot');
    if (!slides.length) return;

    let actual = 0, timer = null;

    function ir(idx) {
        slides[actual].classList.remove('activo');
        dots[actual].classList.remove('activo');
        actual = (idx + slides.length) % slides.length;
        slides[actual].classList.add('activo');
        dots[actual].classList.add('activo');
    }

    function auto() { clearInterval(timer); timer = setInterval(() => ir(actual + 1), 5500); }

    document.getElementById('heroNext')?.addEventListener('click', () => { ir(actual + 1); auto(); });
    document.getElementById('heroPrev')?.addEventListener('click', () => { ir(actual - 1); auto(); });
    dots.forEach(d => d.addEventListener('click', () => { ir(+d.dataset.i); auto(); }));

    // Swipe táctil
    let startX = 0;
    const hero = document.getElementById('hero');
    hero?.addEventListener('touchstart', e => startX = e.touches[0].clientX, { passive: true });
    hero?.addEventListener('touchend',   e => {
        const dx = e.changedTouches[0].clientX - startX;
        if (Math.abs(dx) > 50) { ir(actual + (dx < 0 ? 1 : -1)); auto(); }
    });

    if (slides.length > 1) auto();

    // Click en cards del ranking que no son links → abrir login si no hay sesión
    document.querySelectorAll('.card-overlay-btn').forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault(); e.stopPropagation();
            if (window.abrirLogin) window.abrirLogin();
        });
    });
});
