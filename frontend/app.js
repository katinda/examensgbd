// URL de base de l'API PHP
const API = 'http://localhost:8000';

// ── Navigation ──────────────────────────────────────────────
// Gère les clics sur les boutons de navigation :
// masque toutes les sections et affiche uniquement celle correspondant au bouton cliqué.
document.querySelectorAll('.nav-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('section-' + btn.dataset.section).classList.add('active');
    });
});

// ── Utilitaires ──────────────────────────────────────────────
// Affiche un message d'erreur dans l'élément dont l'id est fourni.
function afficherErreur(id, message) {
    const el = document.getElementById(id);
    el.textContent = message;
    el.style.display = 'block';
}

// Cache le message d'erreur de l'élément dont l'id est fourni.
function cacherErreur(id) {
    document.getElementById(id).style.display = 'none';
}

// ── SITES ────────────────────────────────────────────────────
// Appelle GET /sites et passe le résultat à afficherSites.
async function chargerSites() {
    try {
        const res = await fetch(`${API}/sites`);
        const sites = await res.json();
        afficherSites(sites);
    } catch {
        afficherErreur('erreur-sites', 'Impossible de contacter le serveur.');
    }
}

// Injecte les sites dans le tableau HTML.
// Attache aussi les listeners de suppression sur chaque bouton généré.
function afficherSites(sites) {
    const tbody = document.getElementById('tbody-sites');

    if (!sites.length) {
        tbody.innerHTML = '<tr><td colspan="5">Aucun site.</td></tr>';
        return;
    }

    tbody.innerHTML = sites.map(s => `
        <tr>
            <td>${s.id}</td>
            <td>${s.nom}</td>
            <td>${s.ville ?? '—'}</td>
            <td>${s.code_postal ?? '—'}</td>
            <td>
                <button class="btn-supprimer" data-id="${s.id}">Supprimer</button>
            </td>
        </tr>
    `).join('');

    tbody.querySelectorAll('.btn-supprimer').forEach(btn => {
        btn.addEventListener('click', () => supprimerSite(btn.dataset.id));
    });
}

// Appelle DELETE /sites/:id après confirmation, puis recharge la liste.
async function supprimerSite(id) {
    if (!confirm('Supprimer ce site ?')) return;
    try {
        const res = await fetch(`${API}/sites/${id}`, { method: 'DELETE' });
        if (!res.ok) throw new Error();
        cacherErreur('erreur-sites');
        chargerSites();
    } catch {
        afficherErreur('erreur-sites', 'Erreur lors de la suppression.');
    }
}

// Affiche le formulaire de création quand on clique sur "+ Nouveau site".
document.getElementById('btn-nouveau-site').addEventListener('click', () => {
    document.getElementById('form-site').style.display = 'block';
});

// Cache et réinitialise le formulaire quand on clique sur "Annuler".
document.getElementById('btn-annuler-site').addEventListener('click', () => {
    document.getElementById('form-site').style.display = 'none';
    document.getElementById('form-site').reset();
});

// Appelle POST /sites avec les données du formulaire, puis recharge la liste.
document.getElementById('form-site').addEventListener('submit', async e => {
    e.preventDefault();
    const form = e.target;
    const body = {
        nom:          form.nom.value,
        adresse:      form.adresse.value,
        ville:        form.ville.value,
        code_postal:  form.code_postal.value,
    };

    try {
        const res = await fetch(`${API}/sites`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body),
        });
        if (!res.ok) throw new Error();
        form.style.display = 'none';
        form.reset();
        cacherErreur('erreur-sites');
        chargerSites();
    } catch {
        afficherErreur('erreur-sites', 'Erreur lors de la création du site.');
    }
});

// ── TERRAINS ─────────────────────────────────────────────────
// Appelle GET /terrains et passe le résultat à afficherTerrains.
async function chargerTerrains() {
    try {
        const res = await fetch(`${API}/terrains`);
        const terrains = await res.json();
        afficherTerrains(terrains);
    } catch {
        afficherErreur('erreur-terrains', 'Impossible de contacter le serveur.');
    }
}

// Injecte les terrains dans le tableau HTML.
// Attache aussi les listeners de suppression sur chaque bouton généré.
function afficherTerrains(terrains) {
    const tbody = document.getElementById('tbody-terrains');

    if (!terrains.length) {
        tbody.innerHTML = '<tr><td colspan="6">Aucun terrain.</td></tr>';
        return;
    }

    tbody.innerHTML = terrains.map(t => `
        <tr>
            <td>${t.id}</td>
            <td>${t.site_id}</td>
            <td>${t.num_terrain}</td>
            <td>${t.libelle ?? '—'}</td>
            <td>${t.est_actif ? 'Oui' : 'Non'}</td>
            <td>
                <button class="btn-supprimer" data-id="${t.id}">Supprimer</button>
            </td>
        </tr>
    `).join('');

    tbody.querySelectorAll('.btn-supprimer').forEach(btn => {
        btn.addEventListener('click', () => supprimerTerrain(btn.dataset.id));
    });
}

// Appelle DELETE /terrains/:id après confirmation, puis recharge la liste.
async function supprimerTerrain(id) {
    if (!confirm('Supprimer ce terrain ?')) return;
    try {
        const res = await fetch(`${API}/terrains/${id}`, { method: 'DELETE' });
        if (!res.ok) throw new Error();
        cacherErreur('erreur-terrains');
        chargerTerrains();
    } catch {
        afficherErreur('erreur-terrains', 'Erreur lors de la suppression.');
    }
}

// Remplit le <select> des sites dans le formulaire terrain.
async function chargerSitesDansSelect() {
    try {
        const res = await fetch(`${API}/sites`);
        const sites = await res.json();
        const select = document.querySelector('#form-terrain select[name="site_id"]');
        select.innerHTML = '<option value="">-- Choisir un site --</option>';
        sites.forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.id;
            opt.textContent = `${s.nom} (${s.ville ?? s.id})`;
            select.appendChild(opt);
        });
    } catch {
        afficherErreur('erreur-terrains', 'Impossible de charger les sites.');
    }
}

// Affiche le formulaire et charge les sites dans le select.
document.getElementById('btn-nouveau-terrain').addEventListener('click', () => {
    chargerSitesDansSelect();
    document.getElementById('form-terrain').style.display = 'block';
});

// Cache et réinitialise le formulaire quand on clique sur "Annuler".
document.getElementById('btn-annuler-terrain').addEventListener('click', () => {
    document.getElementById('form-terrain').style.display = 'none';
    document.getElementById('form-terrain').reset();
});

// Appelle POST /terrains avec les données du formulaire, puis recharge la liste.
document.getElementById('form-terrain').addEventListener('submit', async e => {
    e.preventDefault();
    const form = e.target;
    const body = {
        site_id:     parseInt(form.site_id.value),
        num_terrain: parseInt(form.num_terrain.value),
        libelle:     form.libelle.value,
    };

    try {
        const res = await fetch(`${API}/terrains`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body),
        });
        if (!res.ok) throw new Error();
        form.style.display = 'none';
        form.reset();
        cacherErreur('erreur-terrains');
        chargerTerrains();
    } catch {
        afficherErreur('erreur-terrains', 'Erreur lors de la création du terrain.');
    }
});

// ── Init ─────────────────────────────────────────────────────
// Chargement initial des sites et terrains au démarrage de la page.
chargerSites();
chargerTerrains();
