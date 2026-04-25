// js/perfil.js
document.addEventListener('DOMContentLoaded', () => {
    // ── PANEL AÑADIR ──────────────────────────────────────
    const panelAñadir = document.getElementById('panelAñadir');
    const btnAbrir    = document.getElementById('btnAbrirPanel');
    const btnVacio    = document.getElementById('btnVacioPanel');
    const btnCerrar   = document.getElementById('cerrarPanel');

    function abrirPanel() { panelAñadir?.classList.add('open'); }
    function cerrarPanel() { panelAñadir?.classList.remove('open'); }

    btnAbrir?.addEventListener('click', abrirPanel);
    btnVacio?.addEventListener('click', abrirPanel);
    btnCerrar?.addEventListener('click', cerrarPanel);
    panelAñadir?.addEventListener('click', e => { if (e.target === panelAñadir) cerrarPanel(); });

    // ── BUSCADOR DENTRO DEL PANEL ──────────────────────
    const busqInput = document.getElementById('busqInput');
    const busqDrop  = document.getElementById('busqDrop');
    const juegoSel  = document.getElementById('juegoSel');
    let timer = null;

    // Abrir automáticamente si viene con rawg_id en URL
    const params = new URLSearchParams(window.location.search);
    if (params.get('rawg_id')) {
        document.getElementById('f_rawg_id').value    = params.get('rawg_id');
        document.getElementById('f_rawg_titulo').value = params.get('rawg_titulo') || '';
        juegoSel.innerHTML = `<span class="juego-selected-name">${params.get('rawg_titulo') || ''}</span>
            <button type="button" class="btn btn-ghost btn-sm" id="btnCambiar">Cambiar</button>`;
        juegoSel.style.display = 'flex';
        busqInput.style.display = 'none';
        abrirPanel();
        document.getElementById('btnCambiar')?.addEventListener('click', resetBusq);
    }

    function resetBusq() {
        juegoSel.style.display  = 'none';
        busqInput.style.display = 'block';
        busqInput.value = '';
        document.getElementById('f_rawg_id').value = '';
        busqDrop.classList.remove('open');
    }

    busqInput?.addEventListener('input', () => {
        clearTimeout(timer);
        const q = busqInput.value.trim();
        if (q.length < 2) { busqDrop.classList.remove('open'); return; }

        timer = setTimeout(() => {
            fetch(`../api/buscar.php?q=${encodeURIComponent(q)}`)
                .then(r => r.json())
                .then(items => {
                    if (!items.length) {
                        busqDrop.innerHTML = '<div style="padding:.8rem;font-size:.82rem;color:var(--grey)">Sin resultados</div>';
                        busqDrop.classList.add('open');
                        return;
                    }
                    busqDrop.innerHTML = items.map(j => `
                        <div class="drop-item"
                             data-id="${j.id}" data-titulo="${j.titulo.replace(/"/g,'&quot;')}"
                             data-img="${j.imagen||''}" data-genero="${j.generos||''}"
                             data-anio="${j.anio||''}" data-dev="">
                            ${j.imagen ? `<img src="${j.imagen}" loading="lazy">` : '<div style="width:32px;height:44px;background:var(--surface2);border-radius:2px;flex-shrink:0"></div>'}
                            <div class="drop-item-info">
                                <div class="drop-item-title">${j.titulo}</div>
                                <div class="drop-item-meta">${j.generos||'—'} · ${j.anio||'—'}</div>
                            </div>
                        </div>`).join('');
                    busqDrop.classList.add('open');

                    busqDrop.querySelectorAll('.drop-item').forEach(el => {
                        el.addEventListener('click', () => {
                            document.getElementById('f_rawg_id').value     = el.dataset.id;
                            document.getElementById('f_rawg_titulo').value  = el.dataset.titulo;
                            document.getElementById('f_rawg_imagen').value  = el.dataset.img;
                            document.getElementById('f_rawg_genero').value  = el.dataset.genero;
                            document.getElementById('f_rawg_anio').value    = el.dataset.anio;

                            juegoSel.innerHTML = `
                                ${el.dataset.img ? `<img src="${el.dataset.img}">` : ''}
                                <span class="juego-selected-name">${el.dataset.titulo}</span>
                                <button type="button" class="btn btn-ghost btn-sm" id="btnCambiar">Cambiar</button>`;
                            juegoSel.style.display  = 'flex';
                            busqInput.style.display = 'none';
                            busqDrop.classList.remove('open');
                            document.getElementById('btnCambiar')?.addEventListener('click', resetBusq);
                        });
                    });
                });
        }, 380);
    });

    document.addEventListener('click', e => {
        if (!busqInput?.contains(e.target) && !busqDrop?.contains(e.target))
            busqDrop?.classList.remove('open');
    });
});
