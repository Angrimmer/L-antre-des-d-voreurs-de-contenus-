/* =============================================
   BIBLIOTHÈQUE — logique principale
   ============================================= */

const modal         = document.getElementById('modalSearch');
const btnAdd        = document.getElementById('btnAdd');
const modalClose    = document.getElementById('modalClose');
const searchInput   = document.getElementById('searchInput');
const searchBtn     = document.getElementById('searchBtn');
const searchResults = document.getElementById('searchResults');
const libCover      = document.getElementById('libCover');
const libDetails    = document.getElementById('libDetails');
const lightbox      = document.getElementById('lightbox');
const lightboxImg   = document.getElementById('lightboxImg');

// --- Filtre liste ---
const libSearch = document.getElementById('libSearch');
if (libSearch) {
    libSearch.addEventListener('input', () => {
        const q = libSearch.value.trim().toLowerCase();
        const allItems   = document.querySelectorAll('.lib-item');
        const allLetters = document.querySelectorAll('.list-letter');

        allItems.forEach(el => {
            const title = el.querySelector('.item-title').textContent.toLowerCase();
            el.style.display = (!q || title.includes(q)) ? '' : 'none';
        });

        // Cache les séparateurs de lettre si aucun item visible sous eux
        allLetters.forEach(letter => {
            let sibling = letter.nextElementSibling;
            let hasVisible = false;
            while (sibling && !sibling.classList.contains('list-letter')) {
                if (sibling.classList.contains('lib-item') && sibling.style.display !== 'none') {
                    hasVisible = true;
                    break;
                }
                sibling = sibling.nextElementSibling;
            }
            letter.style.display = hasVisible ? '' : 'none';
        });
    });
}

// --- Lightbox ---
lightbox.addEventListener('click', () => {
    lightbox.classList.remove('open');
    lightboxImg.src = '';
});

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') lightbox.classList.remove('open');
});

// Copie locale des items pour mise à jour sans rechargement
let items = [...ITEMS];

// --- Modal ---
btnAdd.addEventListener('click', () => modal.classList.add('open'));
modalClose.addEventListener('click', closeModal);
modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });

function closeModal() {
    modal.classList.remove('open');
    searchInput.value = '';
    searchResults.innerHTML = '';
}

// --- Recherche ---
searchBtn.addEventListener('click', doSearch);
searchInput.addEventListener('keydown', e => { if (e.key === 'Enter') doSearch(); });

async function doSearch() {
    const q = searchInput.value.trim();
    if (q.length < 2) return;

    searchResults.innerHTML = '<p class="search-loading">Recherche en cours...</p>';

    const subtypeEl = document.getElementById('bookSubtype');
    const subtype   = subtypeEl ? `&subtype=${subtypeEl.value}` : '';

    try {
        const res  = await fetch(`api/search.php?q=${encodeURIComponent(q)}&cat=${CURRENT_CAT}${subtype}`);
        const data = await res.json();

        if (!data.results || data.results.length === 0) {
            searchResults.innerHTML = '<p class="search-empty">Aucun résultat.</p>';
            return;
        }

        searchResults.innerHTML = data.results.map(item => `
            <div class="result-card">
                <img class="result-cover"
                     src="${item.cover_url ?? ''}"
                     alt="${escHtml(item.title)}"
                     onerror="this.style.visibility='hidden'">
                <div class="result-info">
                    <div class="result-title">${escHtml(item.title)}</div>
                    <div class="result-year">${item.year ?? ''}</div>
                    <div class="result-synopsis">${escHtml(item.synopsis ?? '')}</div>
                </div>
                <button class="btn-result-add"
                        data-id="${item.external_id}"
                        data-title="${escAttr(item.title)}"
                        data-cover="${escAttr(item.cover_url ?? '')}"
                        data-year="${item.year ?? ''}">
                    + Ajouter
                </button>
            </div>
        `).join('');

        const existingIds = items.map(i => i.external_id);
        searchResults.querySelectorAll('.btn-result-add').forEach(btn => {
            if (existingIds.includes(btn.dataset.id)) {
                btn.textContent = '✓ Déjà ajouté';
                btn.classList.add('added');
                btn.disabled = true;
            } else {
                btn.addEventListener('click', () => addItem(btn));
            }
        });

    } catch {
        searchResults.innerHTML = '<p class="search-empty">Erreur de connexion.</p>';
    }
}

// --- Ajout manuel ---
document.getElementById('manualSubmit').addEventListener('click', async () => {
    const title = document.getElementById('manualTitle').value.trim();
    if (!title) {
        document.getElementById('manualTitle').focus();
        return;
    }

    const btn = document.getElementById('manualSubmit');
    btn.disabled    = true;
    btn.textContent = '...';

    const subtypeEl  = document.getElementById('bookSubtype');
    const payload = {
        external_id: 'manual_' + Date.now(),
        title,
        cover_url: document.getElementById('manualCover').value.trim() || null,
        year:      document.getElementById('manualYear').value  || null,
        category:  CURRENT_CAT,
        status:    'planifie',
        book_type: subtypeEl ? subtypeEl.value : null,
    };

    try {
        const res  = await fetch('api/library.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify(payload),
        });
        const data = await res.json();

        if (data.success) {
            btn.textContent = '✓ Ajouté';
            setTimeout(() => location.reload(), 800);
        } else {
            btn.disabled    = false;
            btn.textContent = '+ Ajouter';
        }
    } catch {
        btn.disabled    = false;
        btn.textContent = '+ Ajouter';
    }
});

// --- Ajout à la bibliothèque ---
async function addItem(btn) {
    const subtypeEl  = document.getElementById('bookSubtype');
    const payload = {
        external_id: btn.dataset.id,
        title:       btn.dataset.title,
        cover_url:   btn.dataset.cover || null,
        year:        btn.dataset.year  || null,
        category:    CURRENT_CAT,
        status:      'planifie',
        book_type:   subtypeEl ? subtypeEl.value : null,
    };

    btn.disabled    = true;
    btn.textContent = '...';

    try {
        const res  = await fetch('api/library.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify(payload),
        });
        const data = await res.json();

        if (data.success) {
            btn.textContent = '✓ Ajouté';
            btn.classList.add('added');
            setTimeout(() => location.reload(), 800);
        } else {
            btn.disabled    = false;
            btn.textContent = '+ Ajouter';
        }
    } catch {
        btn.disabled    = false;
        btn.textContent = '+ Ajouter';
    }
}

// --- Sélection d'un item ---
document.querySelectorAll('.lib-item').forEach(el => {
    el.addEventListener('click', () => {
        document.querySelectorAll('.lib-item').forEach(i => i.classList.remove('selected'));
        el.classList.add('selected');
        showDetails(el);
    });
});

const firstItem = document.querySelector('.lib-item');
if (firstItem) showDetails(firstItem);

// --- Affichage des détails ---
function showDetails(el) {
    const dbId = el.dataset.dbid;
    const item = items.find(i => i.id == dbId);
    if (!item) return;

    // Cover
    if (item.cover_url) {
        const url = escHtml(item.cover_url);
        libCover.innerHTML = `
            <div class="cover-bg" style="background-image:url('${url}')"></div>
            <img class="cover-poster" src="${url}" alt="${escHtml(item.title)}" title="Cliquer pour agrandir">
        `;
        libCover.querySelector('.cover-poster').addEventListener('click', () => {
            lightboxImg.src = url;
            lightbox.classList.add('open');
        });
    } else {
        libCover.innerHTML = `<div class="cover-placeholder"><p>${escHtml(item.title)}</p></div>`;
    }

    renderDetails(item, el);
}

function renderDetails(item, listEl) {
    const isLivre = item.category === 'livre';

    libDetails.innerHTML = `
        <div class="details-title">${escHtml(item.title)}</div>

        ${item.year ? `<div class="details-meta">📅 ${item.year}</div>` : ''}

        ${isLivre ? `
        <div class="details-status-wrap">
            <label class="details-label">Type</label>
            <select class="select-book-type" data-id="${item.id}">
                <option value=""      ${!item.book_type                   ? 'selected' : ''}>— Non précisé —</option>
                <option value="livre" ${item.book_type === 'livre' ? 'selected' : ''}>📖 Livre</option>
                <option value="manga" ${item.book_type === 'manga' ? 'selected' : ''}>🇯🇵 Manga</option>
                <option value="bd"    ${item.book_type === 'bd'    ? 'selected' : ''}>🎨 BD</option>
            </select>
        </div>
        <div class="field-row">
            <div class="field-group">
                <label class="details-label">${item.book_type === 'livre' ? 'Livres possédés' : 'Tomes possédés'}</label>
                <input type="number" class="details-input details-input--short" id="field_volumes_owned"
                       min="0" placeholder="ex: 8" value="${item.volumes_owned ?? ''}">
            </div>
            <div class="field-group">
                <label class="details-label">${item.book_type === 'livre' ? 'Livres sortis' : 'Tomes sortis'}</label>
                <input type="number" class="details-input details-input--short" id="field_volumes_out"
                       min="0" placeholder="ex: 12" value="${item.volumes_out ?? ''}">
            </div>
        </div>
        ` : ''}

        <div class="details-status-wrap">
            <label class="details-label">Statut</label>
            <select class="select-status" data-id="${item.id}">
                <option value="planifie"  ${item.status === 'planifie'  ? 'selected' : ''}>📋 Planifié</option>
                <option value="en_cours"  ${item.status === 'en_cours'  ? 'selected' : ''}>▶️ En cours</option>
                <option value="termine"   ${item.status === 'termine'   ? 'selected' : ''}>✅ Terminé</option>
                <option value="abandonne" ${item.status === 'abandonne' ? 'selected' : ''}>🚫 Abandonné</option>
            </select>
        </div>

        <div class="details-status-fields" id="statusFields">
            ${renderStatusFields(item)}
        </div>

        <div class="details-actions">
            <button class="btn-remove" data-id="${item.id}">Supprimer</button>
        </div>
    `;

    // Changement de type (livre/manga/bd)
    const selectBookType = libDetails.querySelector('.select-book-type');
    if (selectBookType) {
        selectBookType.addEventListener('change', async function () {
            item.book_type = this.value || null;
            await save(item.id, { book_type: this.value || null });
        });
    }

    // Autosave volumes
    ['field_volumes_out', 'field_volumes_owned'].forEach(fieldId => {
        const col = fieldId.replace('field_', '');
        const el = libDetails.querySelector(`#${fieldId}`);
        if (!el) return;
        let t = null;
        el.addEventListener('input', () => {
            item[col] = el.value;
            clearTimeout(t);
            t = setTimeout(() => save(item.id, { [col]: el.value || null }), 800);
        });
        el.addEventListener('blur', () => {
            clearTimeout(t);
            save(item.id, { [col]: el.value || null });
        });
    });

    // Changement de statut → on redessine les champs + on sauvegarde
    libDetails.querySelector('.select-status').addEventListener('change', async function () {
        const newStatus = this.value;

        // Si on passe à "terminé", on pré-remplit l'avis définitif avec l'avis temporaire
        if (newStatus === 'termine' && item.temp_review && !item.final_review) {
            item.final_review = item.temp_review;
        }

        item.status = newStatus;
        await save(item.id, { status: newStatus });

        document.getElementById('statusFields').innerHTML = renderStatusFields(item);
        bindStatusFields(item);

        const dot = listEl.querySelector('.item-status');
        if (dot) dot.className = `item-status status-${newStatus}`;
    });

    bindStatusFields(item);

    libDetails.querySelector('.btn-remove').addEventListener('click', async function () {
        if (!confirm(`Supprimer "${item.title}" de ta bibliothèque ?`)) return;
        await fetch(`api/library.php?id=${this.dataset.id}`, { method: 'DELETE' });
        location.reload();
    });
}

// Génère le HTML des champs selon le statut et la catégorie
function renderStatusFields(item) {
    const isFilm  = item.category === 'film';
    const isJeu   = item.category === 'jeu';
    const isLivre = item.category === 'livre';

    switch (item.status) {

        case 'planifie':
            return `
                <div class="field-group">
                    <label class="details-label">Date prévue</label>
                    <input type="date" class="details-input" id="field_planned_date"
                           value="${item.planned_date ?? ''}">
                </div>
                <div class="field-group">
                    <label class="details-label">Notes</label>
                    <textarea class="details-textarea" id="field_personal_notes"
                              placeholder="Pourquoi tu veux voir ça, où tu en as entendu parler...">${escHtml(item.personal_notes ?? '')}</textarea>
                </div>`;

        case 'en_cours':
            if (isLivre) return `
                <div class="field-group">
                    <label class="details-label">Avis temporaire</label>
                    <textarea class="details-textarea" id="field_temp_review"
                              placeholder="Tes impressions pour l'instant...">${escHtml(item.temp_review ?? '')}</textarea>
                </div>`;

            if (isFilm) return `
                <div class="field-group">
                    <label class="details-label">Opus actuel</label>
                    <input type="number" class="details-input details-input--short" id="field_current_episode"
                           min="1" placeholder="ex: 3" value="${item.current_episode ?? ''}">
                </div>
                <div class="field-group">
                    <label class="details-label">Avis temporaire</label>
                    <textarea class="details-textarea" id="field_temp_review"
                              placeholder="Tes impressions pour l'instant...">${escHtml(item.temp_review ?? '')}</textarea>
                </div>`;

            if (isJeu) return `
                <div class="field-group">
                    <label class="details-label">Heures de jeu</label>
                    <input type="number" class="details-input details-input--short" id="field_current_episode"
                           min="0" placeholder="ex: 42" value="${item.current_episode ?? ''}">
                </div>
                <div class="field-group">
                    <label class="details-label">Avis temporaire</label>
                    <textarea class="details-textarea" id="field_temp_review"
                              placeholder="Tes impressions pour l'instant...">${escHtml(item.temp_review ?? '')}</textarea>
                </div>`;

            // anime / serie
            return `
                <div class="field-group">
                    <label class="details-label">Épisode actuel</label>
                    <input type="number" class="details-input details-input--short" id="field_current_episode"
                           min="1" placeholder="ex: 12" value="${item.current_episode ?? ''}">
                </div>
                <div class="field-row">
                    <div class="field-group">
                        <label class="details-label">Saison en cours</label>
                        <input type="number" class="details-input details-input--short" id="field_airing_season"
                               min="1" placeholder="ex: 2" value="${item.airing_season ?? ''}">
                    </div>
                    <div class="field-group">
                        <label class="details-label">Saison actuelle</label>
                        <input type="number" class="details-input details-input--short" id="field_current_season"
                               min="1" placeholder="ex: 2" value="${item.current_season ?? ''}">
                    </div>
                </div>
                <div class="field-group">
                    <label class="details-label">Avis temporaire</label>
                    <textarea class="details-textarea" id="field_temp_review"
                              placeholder="Tes impressions pour l'instant...">${escHtml(item.temp_review ?? '')}</textarea>
                </div>`;

        case 'termine':
            if (isFilm || isJeu || isLivre) return `
                <div class="field-group">
                    <label class="details-label">Avis définitif</label>
                    <textarea class="details-textarea" id="field_final_review"
                              placeholder="Ton avis final...">${escHtml(item.final_review ?? '')}</textarea>
                </div>`;

            // anime / serie
            return `
                <div class="field-group">
                    <label class="details-label">Saison finale</label>
                    <input type="number" class="details-input details-input--short" id="field_current_season"
                           min="1" placeholder="ex: 3" value="${item.current_season ?? ''}">
                </div>
                <div class="field-group">
                    <label class="details-label">Avis définitif</label>
                    <textarea class="details-textarea" id="field_final_review"
                              placeholder="Ton avis final...">${escHtml(item.final_review ?? '')}</textarea>
                </div>`;

        case 'abandonne':
            return `
                <div class="field-group">
                    <label class="details-label">Raison (optionnel)</label>
                    <textarea class="details-textarea" id="field_temp_review"
                              placeholder="Pourquoi tu as arrêté...">${escHtml(item.temp_review ?? '')}</textarea>
                </div>`;

        default:
            return '';
    }
}

// Branche les écouteurs d'autosave sur les champs générés
function bindStatusFields(item) {
    const fieldMap = {
        field_planned_date:    'planned_date',
        field_current_episode: 'current_episode',
        field_airing_season:   'airing_season',
        field_current_season:  'current_season',
        field_temp_review:     'temp_review',
        field_final_review:    'final_review',
        field_personal_notes:  'personal_notes',
    };

    let saveTimer = null;

    Object.entries(fieldMap).forEach(([elId, col]) => {
        const el = document.getElementById(elId);
        if (!el) return;

        // Sauvegarde différée pendant la frappe
        el.addEventListener('input', () => {
            item[col] = el.value;
            clearTimeout(saveTimer);
            saveTimer = setTimeout(() => save(item.id, { [col]: el.value }), 800);
        });

        // Sauvegarde immédiate quand le champ perd le focus
        el.addEventListener('blur', () => {
            clearTimeout(saveTimer);
            save(item.id, { [col]: el.value });
        });
    });
}

// --- Sauvegarde ---
const saveToast   = document.getElementById('saveToast');
let   toastTimer  = null;

async function save(id, data) {
    try {
        const res    = await fetch('api/library.php', {
            method:  'PUT',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ id, ...data }),
        });
        const result = await res.json();

        if (result.error) {
            console.error('Erreur sauvegarde :', result.error);
            showToast('⚠ Erreur : migration SQL non appliquée ?', true);
        } else {
            showToast('✓ Sauvegardé');
        }
    } catch {
        showToast('⚠ Sauvegarde impossible', true);
    }
}

function showToast(msg, isError = false) {
    saveToast.textContent = msg;
    saveToast.style.borderColor = isError ? '#7f1d1d' : '#166534';
    saveToast.style.color       = isError ? '#ef4444' : '#22c55e';
    saveToast.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => saveToast.classList.remove('show'), 2000);
}

// --- Utilitaires ---
function escHtml(str) {
    return String(str ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function escAttr(str) {
    return String(str ?? '').replace(/"/g, '&quot;');
}
