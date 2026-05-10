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

// ── MEMBRES ──────────────────────────────────────────────────
// Appelle GET /api/membres (avec filtre catégorie optionnel) et affiche le résultat.
async function chargerMembres(categorie = '') {
    try {
        const url = categorie ? `${API}/api/membres?categorie=${categorie}` : `${API}/api/membres`;
        const res = await fetch(url);
        const membres = await res.json();
        afficherMembres(membres);
    } catch {
        afficherErreur('erreur-membres', 'Impossible de contacter le serveur.');
    }
}

// Injecte les membres dans le tableau HTML.
// Attache aussi les listeners de suppression sur chaque bouton généré.
function afficherMembres(membres) {
    const tbody = document.getElementById('tbody-membres');

    if (!membres.length) {
        tbody.innerHTML = '<tr><td colspan="7">Aucun membre.</td></tr>';
        return;
    }

    tbody.innerHTML = membres.map(m => `
        <tr>
            <td>${m.id}</td>
            <td>${m.matricule}</td>
            <td>${m.nom}</td>
            <td>${m.prenom}</td>
            <td>${m.categorie}</td>
            <td>${m.email ?? '—'}</td>
            <td>
                <button class="btn-supprimer" data-id="${m.id}">Désactiver</button>
            </td>
        </tr>
    `).join('');

    tbody.querySelectorAll('.btn-supprimer').forEach(btn => {
        btn.addEventListener('click', () => desactiverMembre(btn.dataset.id));
    });
}

// Appelle DELETE /api/membres/:id après confirmation, puis recharge la liste.
async function desactiverMembre(id) {
    if (!confirm('Désactiver ce membre ?')) return;
    try {
        const res = await fetch(`${API}/api/membres/${id}`, { method: 'DELETE' });
        if (!res.ok) throw new Error();
        cacherErreur('erreur-membres');
        chargerMembres(document.getElementById('filtre-categorie').value);
    } catch {
        afficherErreur('erreur-membres', 'Erreur lors de la désactivation.');
    }
}

// Recharge la liste quand le filtre catégorie change.
document.getElementById('filtre-categorie').addEventListener('change', e => {
    chargerMembres(e.target.value);
});

// Affiche le formulaire et charge les sites dans le select si catégorie S sélectionnée.
document.getElementById('btn-nouveau-membre').addEventListener('click', () => {
    document.getElementById('form-membre').style.display = 'block';
});

// Cache et réinitialise le formulaire quand on clique sur "Annuler".
document.getElementById('btn-annuler-membre').addEventListener('click', () => {
    document.getElementById('form-membre').style.display = 'none';
    document.getElementById('form-membre').reset();
    document.getElementById('label-site-membre').style.display = 'none';
});

// Affiche ou cache le select site selon la catégorie choisie (obligatoire pour S).
document.querySelector('#form-membre select[name="categorie"]').addEventListener('change', async e => {
    const labelSite = document.getElementById('label-site-membre');
    if (e.target.value === 'S') {
        labelSite.style.display = 'block';
        const res = await fetch(`${API}/sites`);
        const sites = await res.json();
        const select = labelSite.querySelector('select');
        select.innerHTML = '<option value="">-- Choisir un site --</option>';
        sites.forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.id;
            opt.textContent = `${s.nom} (${s.ville ?? s.id})`;
            select.appendChild(opt);
        });
    } else {
        labelSite.style.display = 'none';
    }
});

// Appelle POST /api/membres avec les données du formulaire, puis recharge la liste.
document.getElementById('form-membre').addEventListener('submit', async e => {
    e.preventDefault();
    const form = e.target;
    const body = {
        matricule:  form.matricule.value,
        nom:        form.nom.value,
        prenom:     form.prenom.value,
        email:      form.email.value || undefined,
        telephone:  form.telephone.value || undefined,
        categorie:  form.categorie.value,
    };
    if (form.categorie.value === 'S') {
        body.site_id = parseInt(form.site_id.value);
    }

    try {
        const res = await fetch(`${API}/api/membres`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body),
        });
        if (!res.ok) throw new Error();
        form.style.display = 'none';
        form.reset();
        document.getElementById('label-site-membre').style.display = 'none';
        cacherErreur('erreur-membres');
        chargerMembres();
    } catch {
        afficherErreur('erreur-membres', 'Erreur lors de la création du membre.');
    }
});

// ── RÉSERVATIONS ─────────────────────────────────────────────
// Appelle GET /api/membres/:id/reservations et affiche les réservations du membre.
async function chargerReservationsMembre(membreId) {
    try {
        const res = await fetch(`${API}/api/membres/${membreId}/reservations`);
        if (!res.ok) throw new Error();
        const reservations = await res.json();
        afficherReservations(reservations);
    } catch {
        afficherErreur('erreur-reservations', 'Membre introuvable ou aucune réservation.');
    }
}

// Injecte les réservations dans le tableau HTML.
// Attache aussi les listeners de suppression sur chaque bouton généré.
function afficherReservations(reservations) {
    const tbody = document.getElementById('tbody-reservations');

    if (!reservations.length) {
        tbody.innerHTML = '<tr><td colspan="7">Aucune réservation.</td></tr>';
        return;
    }

    tbody.innerHTML = reservations.map(r => `
        <tr>
            <td>${r.id}</td>
            <td>${r.terrain_id}</td>
            <td>${r.organisateur_id}</td>
            <td>${r.date_match}</td>
            <td>${r.heure_debut}</td>
            <td>${r.type}</td>
            <td>
                <button class="btn-supprimer" data-id="${r.id}">Supprimer</button>
            </td>
        </tr>
    `).join('');

    tbody.querySelectorAll('.btn-supprimer').forEach(btn => {
        btn.addEventListener('click', () => supprimerReservation(btn.dataset.id));
    });
}

// Appelle DELETE /api/reservations/:id après confirmation, puis recharge la liste.
async function supprimerReservation(id) {
    if (!confirm('Supprimer cette réservation ?')) return;
    try {
        const res = await fetch(`${API}/api/reservations/${id}`, { method: 'DELETE' });
        if (!res.ok) throw new Error();
        cacherErreur('erreur-reservations');
        const membreId = document.getElementById('filtre-membre-id').value;
        if (membreId) chargerReservationsMembre(membreId);
    } catch {
        afficherErreur('erreur-reservations', 'Erreur lors de la suppression.');
    }
}

// Lance la recherche des réservations quand on clique sur "Chercher".
document.getElementById('btn-filtrer-membre').addEventListener('click', () => {
    const membreId = document.getElementById('filtre-membre-id').value;
    if (!membreId) return;
    cacherErreur('erreur-reservations');
    chargerReservationsMembre(membreId);
});

// Remplit les selects terrain et membre du formulaire de réservation.
async function chargerSelectsReservation() {
    try {
        const [resTerrains, resMembres] = await Promise.all([
            fetch(`${API}/terrains`),
            fetch(`${API}/api/membres`),
        ]);
        const terrains = await resTerrains.json();
        const membres  = await resMembres.json();

        const selectTerrain = document.querySelector('#form-reservation select[name="terrain_id"]');
        selectTerrain.innerHTML = '<option value="">-- Choisir un terrain --</option>';
        terrains.forEach(t => {
            const opt = document.createElement('option');
            opt.value = t.id;
            opt.textContent = `#${t.id} — ${t.libelle ?? 'Terrain ' + t.num_terrain}`;
            selectTerrain.appendChild(opt);
        });

        const selectMembre = document.querySelector('#form-reservation select[name="organisateur_id"]');
        selectMembre.innerHTML = '<option value="">-- Choisir un membre --</option>';
        membres.forEach(m => {
            const opt = document.createElement('option');
            opt.value = m.id;
            opt.textContent = `${m.nom} ${m.prenom} (${m.matricule})`;
            selectMembre.appendChild(opt);
        });
    } catch {
        afficherErreur('erreur-reservations', 'Impossible de charger les données.');
    }
}

// Affiche le formulaire et charge les selects terrain et membre.
document.getElementById('btn-nouvelle-reservation').addEventListener('click', () => {
    chargerSelectsReservation();
    document.getElementById('form-reservation').style.display = 'block';
});

// Cache et réinitialise le formulaire quand on clique sur "Annuler".
document.getElementById('btn-annuler-reservation').addEventListener('click', () => {
    document.getElementById('form-reservation').style.display = 'none';
    document.getElementById('form-reservation').reset();
});

// Appelle POST /api/reservations avec les données du formulaire, puis recharge la liste.
document.getElementById('form-reservation').addEventListener('submit', async e => {
    e.preventDefault();
    const form = e.target;
    const body = {
        terrain_id:      parseInt(form.terrain_id.value),
        organisateur_id: parseInt(form.organisateur_id.value),
        date_match:      form.date_match.value,
        heure_debut:     form.heure_debut.value + ':00',
        type:            form.type.value,
    };

    try {
        const res = await fetch(`${API}/api/reservations`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body),
        });
        if (!res.ok) throw new Error();
        form.style.display = 'none';
        form.reset();
        cacherErreur('erreur-reservations');
        const membreId = document.getElementById('filtre-membre-id').value;
        if (membreId) chargerReservationsMembre(membreId);
    } catch {
        afficherErreur('erreur-reservations', 'Erreur lors de la création de la réservation.');
    }
});

// ── Init ─────────────────────────────────────────────────────
// Chargement initial des sites, terrains et membres au démarrage de la page.
chargerSites();
chargerTerrains();
chargerMembres();
