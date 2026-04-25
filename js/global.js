// js/global.js
// Buscador del navbar y modal de login

document.addEventListener('DOMContentLoaded', () => {

    // ── BUSCADOR NAVBAR ────────────────────────────────────
    const input    = document.getElementById('navInput');
    const dropdown = document.getElementById('navDropdown');
    let   timer    = null;

    const BASE = (() => {
        const m = document.querySelector('meta[name="base"]');
        return m ? m.content : '.';
    })();

    if (input && dropdown) {
        input.addEventListener('input', () => {
            clearTimeout(timer);
            const q = input.value.trim();
            if (q.length < 2) { dropdown.classList.remove('visible'); return; }
            timer = setTimeout(() => {
                fetch(`${BASE}/api/buscar.php?q=${encodeURIComponent(q)}`)
                    .then(r => r.json())
                    .then(items => {
                        if (!items.length) {
                            dropdown.innerHTML = `<div class="search-empty">Sin resultados para "${q}"</div>`;
                            dropdown.classList.add('visible');
                            return;
                        }
                        dropdown.innerHTML = items.map(j => `
                            <div class="search-item" data-id="${j.id}" data-titulo="${j.titulo.replace(/"/g,'&quot;')}">
                                <div class="search-item-img">
                                    ${j.imagen ? `<img src="${j.imagen}" loading="lazy">` : '🎮'}
                                </div>
                                <div class="search-item-info">
                                    <div class="search-item-title">${j.titulo}</div>
                                    <div class="search-item-meta">${j.generos || '—'} · ${j.anio || '—'}</div>
                                </div>
                                ${j.nota ? `<span class="search-item-score">★${j.nota}</span>` : ''}
                            </div>`).join('');
                        dropdown.classList.add('visible');

                        dropdown.querySelectorAll('.search-item').forEach(el => {
                            el.addEventListener('click', () => {
                                window.location.href = `${BASE}/vistas/juego.php?id=${el.dataset.id}`;
                            });
                        });
                    })
                    .catch(() => { dropdown.classList.remove('visible'); });
            }, 380);
        });

        document.addEventListener('click', e => {
            if (!input.contains(e.target) && !dropdown.contains(e.target))
                dropdown.classList.remove('visible');
        });
    }

    // ── MODAL LOGIN ────────────────────────────────────────
    const modalBg   = document.getElementById('modalLogin');
    const btnLogin  = document.getElementById('btnLogin');
    const btnCerrar = document.getElementById('cerrarLogin');

    function abrirLogin() { if (modalBg) modalBg.classList.add('open'); }
    function cerrarLogin() { if (modalBg) modalBg.classList.remove('open'); }

    if (btnLogin)  btnLogin.addEventListener('click', abrirLogin);
    if (btnCerrar) btnCerrar.addEventListener('click', cerrarLogin);
    if (modalBg)   modalBg.addEventListener('click', e => { if (e.target === modalBg) cerrarLogin(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrarLogin(); });

    // Exponer para uso en otras páginas
    window.abrirLogin = abrirLogin;
});
