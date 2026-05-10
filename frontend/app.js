// URL de base de l'API PHP
const API = 'http://localhost:8000';

// ── Navigation ──────────────────────────────────────────────
// Cache toutes les sections au démarrage, puis affiche uniquement Sites.
document.querySelectorAll('.section').forEach(s => s.style.display = 'none');
document.getElementById('section-sites').style.display = 'block';

// Gère les clics sur les boutons de navigation :
// masque toutes les sections et affiche uniquement celle correspondant au bouton cliqué.
document.querySelectorAll('.nav-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.section').forEach(s => s.style.display = 'none');
        document.getElementById('section-' + btn.dataset.section).style.display = 'block';
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
// Appelle GET /api/membres avec les filtres actifs (catégorie et/ou inactifs).
async function chargerMembres(categorie = '') {
    const inactifs = document.getElementById('filtre-inactifs').checked;
    try {
        let url;
        if (inactifs) {
            url = `${API}/api/membres?inactifs=1`;
        } else if (categorie) {
            url = `${API}/api/membres?categorie=${categorie}`;
        } else {
            url = `${API}/api/membres`;
        }
        const res = await fetch(url);
        const membres = await res.json();
        afficherMembres(membres, inactifs);
    } catch {
        afficherErreur('erreur-membres', 'Impossible de contacter le serveur.');
    }
}

// Injecte les membres dans le tableau HTML.
// Affiche "Désactiver" pour les actifs, "Réactiver" pour les inactifs.
function afficherMembres(membres, inactifs = false) {
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
                ${inactifs
                    ? `<button class="btn-reactiver" data-id="${m.id}">Réactiver</button>`
                    : `<button class="btn-supprimer" data-id="${m.id}">Désactiver</button>`
                }
            </td>
        </tr>
    `).join('');

    tbody.querySelectorAll('.btn-supprimer').forEach(btn => {
        btn.addEventListener('click', () => desactiverMembre(btn.dataset.id));
    });

    tbody.querySelectorAll('.btn-reactiver').forEach(btn => {
        btn.addEventListener('click', () => reactiverMembre(btn.dataset.id));
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

// Appelle PUT /api/membres/:id avec est_actif: true, puis recharge la liste.
async function reactiverMembre(id) {
    if (!confirm('Réactiver ce membre ?')) return;
    try {
        const res = await fetch(`${API}/api/membres/${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ est_actif: true }),
        });
        if (!res.ok) throw new Error();
        cacherErreur('erreur-membres');
        chargerMembres(document.getElementById('filtre-categorie').value);
    } catch {
        afficherErreur('erreur-membres', 'Erreur lors de la réactivation.');
    }
}

// Recharge la liste quand le filtre catégorie change.
document.getElementById('filtre-categorie').addEventListener('change', e => {
    chargerMembres(e.target.value);
});

// Recharge la liste quand la checkbox inactifs change.
document.getElementById('filtre-inactifs').addEventListener('change', () => {
    chargerMembres(document.getElementById('filtre-categorie').value);
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

// ── PÉNALITÉS ────────────────────────────────────────────────
// Appelle GET /api/penalites (avec filtres optionnels) et affiche le résultat.
async function chargerPenalites(params = {}) {
    try {
        const query = new URLSearchParams(params).toString();
        const url = query ? `${API}/api/penalites?${query}` : `${API}/api/penalites`;
        const res = await fetch(url);
        const penalites = await res.json();
        afficherPenalites(penalites);
    } catch {
        afficherErreur('erreur-penalites', 'Impossible de contacter le serveur.');
    }
}

// Injecte les pénalités dans le tableau HTML.
// Attache les listeners "Lever" et "Supprimer" sur chaque ligne générée.
function afficherPenalites(penalites) {
    const tbody = document.getElementById('tbody-penalites');

    if (!penalites.length) {
        tbody.innerHTML = '<tr><td colspan="7">Aucune pénalité.</td></tr>';
        return;
    }

    tbody.innerHTML = penalites.map(p => `
        <tr>
            <td>${p.id}</td>
            <td>${p.membre_id}</td>
            <td>${p.cause}</td>
            <td>${p.date_debut}</td>
            <td>${p.date_fin}</td>
            <td>${p.date_levee ? 'Oui' : 'Non'}</td>
            <td>
                ${!p.date_levee ? `<button class="btn-lever" data-id="${p.id}">Lever</button>` : ''}
                <button class="btn-supprimer" data-id="${p.id}">Supprimer</button>
            </td>
        </tr>
    `).join('');

    tbody.querySelectorAll('.btn-lever').forEach(btn => {
        btn.addEventListener('click', () => ouvrirFormLever(btn.dataset.id));
    });

    tbody.querySelectorAll('.btn-supprimer').forEach(btn => {
        btn.addEventListener('click', () => supprimerPenalite(btn.dataset.id));
    });
}

// Ouvre le formulaire de levée en pré-remplissant l'ID de la pénalité.
function ouvrirFormLever(penaliteId) {
    const form = document.getElementById('form-lever-penalite');
    form.penalite_id.value = penaliteId;
    document.getElementById('lever-penalite-id').textContent = `#${penaliteId}`;
    form.style.display = 'block';
}

// Appelle PATCH /api/penalites/:id/lever avec admin_id et raison.
document.getElementById('form-lever-penalite').addEventListener('submit', async e => {
    e.preventDefault();
    const form = e.target;
    const id = form.penalite_id.value;
    const body = {
        admin_id: parseInt(form.admin_id.value),
        raison:   form.raison.value,
    };

    try {
        const res = await fetch(`${API}/api/penalites/${id}/lever`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body),
        });
        if (!res.ok) throw new Error();
        form.style.display = 'none';
        form.reset();
        cacherErreur('erreur-penalites');
        chargerPenalites(filtresPenalitesActuels());
    } catch {
        afficherErreur('erreur-penalites', 'Erreur lors de la levée (vérifiez que l\'admin est GLOBAL).');
    }
});

// Cache et réinitialise le formulaire de levée.
document.getElementById('btn-annuler-lever').addEventListener('click', () => {
    document.getElementById('form-lever-penalite').style.display = 'none';
    document.getElementById('form-lever-penalite').reset();
});

// Appelle DELETE /api/penalites/:id après confirmation, puis recharge la liste.
async function supprimerPenalite(id) {
    if (!confirm('Supprimer cette pénalité ?')) return;
    try {
        const res = await fetch(`${API}/api/penalites/${id}`, { method: 'DELETE' });
        if (!res.ok) throw new Error();
        cacherErreur('erreur-penalites');
        chargerPenalites(filtresPenalitesActuels());
    } catch {
        afficherErreur('erreur-penalites', 'Erreur lors de la suppression.');
    }
}

// Retourne les paramètres de filtre actuellement actifs.
function filtresPenalitesActuels() {
    const params = {};
    const membreId = document.getElementById('filtre-penalite-membre-id').value;
    const actives  = document.getElementById('filtre-penalites-actives').checked;
    if (membreId) params.membre_id = membreId;
    if (actives)  params.actives   = 1;
    return params;
}

// Lance la recherche par membre quand on clique sur "Chercher".
document.getElementById('btn-filtrer-penalite-membre').addEventListener('click', () => {
    cacherErreur('erreur-penalites');
    chargerPenalites(filtresPenalitesActuels());
});

// Recharge avec filtre actives quand la checkbox change.
document.getElementById('filtre-penalites-actives').addEventListener('change', () => {
    chargerPenalites(filtresPenalitesActuels());
});

// Recharge toutes les pénalités et réinitialise les filtres.
document.getElementById('btn-toutes-penalites').addEventListener('click', () => {
    document.getElementById('filtre-penalite-membre-id').value = '';
    document.getElementById('filtre-penalites-actives').checked = false;
    cacherErreur('erreur-penalites');
    chargerPenalites();
});

// Remplit le select membre du formulaire de création de pénalité.
async function chargerMembresDansPenalite() {
    try {
        const res = await fetch(`${API}/api/membres`);
        const membres = await res.json();
        const select = document.querySelector('#form-penalite select[name="membre_id"]');
        select.innerHTML = '<option value="">-- Choisir un membre --</option>';
        membres.forEach(m => {
            const opt = document.createElement('option');
            opt.value = m.id;
            opt.textContent = `${m.nom} ${m.prenom} (${m.matricule})`;
            select.appendChild(opt);
        });
    } catch {
        afficherErreur('erreur-penalites', 'Impossible de charger les membres.');
    }
}

// Affiche le formulaire et charge le select membre.
document.getElementById('btn-nouvelle-penalite').addEventListener('click', () => {
    chargerMembresDansPenalite();
    document.getElementById('form-penalite').style.display = 'block';
});

// Cache et réinitialise le formulaire de création.
document.getElementById('btn-annuler-penalite').addEventListener('click', () => {
    document.getElementById('form-penalite').style.display = 'none';
    document.getElementById('form-penalite').reset();
});

// Appelle POST /api/penalites avec les données du formulaire, puis recharge la liste.
document.getElementById('form-penalite').addEventListener('submit', async e => {
    e.preventDefault();
    const form = e.target;
    const body = {
        membre_id:  parseInt(form.membre_id.value),
        date_debut: form.date_debut.value,
        date_fin:   form.date_fin.value,
        cause:      form.cause.value,
    };
    if (form.reservation_id.value) {
        body.reservation_id = parseInt(form.reservation_id.value);
    }

    try {
        const res = await fetch(`${API}/api/penalites`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body),
        });
        if (!res.ok) throw new Error();
        form.style.display = 'none';
        form.reset();
        cacherErreur('erreur-penalites');
        chargerPenalites();
    } catch {
        afficherErreur('erreur-penalites', 'Erreur lors de la création de la pénalité.');
    }
});

// ── INSCRIPTIONS ─────────────────────────────────────────────
// Appelle GET /api/reservations/:id/inscriptions et affiche les joueurs inscrits.
async function chargerInscriptions(reservationId) {
    try {
        const res = await fetch(`${API}/api/reservations/${reservationId}/inscriptions`);
        if (!res.ok) throw new Error();
        const inscriptions = await res.json();
        afficherInscriptions(inscriptions, reservationId);
    } catch {
        afficherErreur('erreur-inscriptions', 'Réservation introuvable.');
    }
}

// Injecte les joueurs inscrits dans le tableau HTML.
// Attache les listeners de retrait sur chaque bouton généré.
function afficherInscriptions(inscriptions, reservationId) {
    const tbody = document.getElementById('tbody-inscriptions');

    if (!inscriptions.length) {
        tbody.innerHTML = '<tr><td colspan="5">Aucun joueur inscrit.</td></tr>';
        document.getElementById('form-inscription').style.display = 'block';
        chargerMembresDansInscription();
        return;
    }

    tbody.innerHTML = inscriptions.map(i => `
        <tr>
            <td>${i.membre_id ?? i.id}</td>
            <td>${i.nom ?? '—'}</td>
            <td>${i.prenom ?? '—'}</td>
            <td>${i.matricule ?? '—'}</td>
            <td>
                <button class="btn-supprimer" data-membre="${i.membre_id ?? i.id}">Retirer</button>
            </td>
        </tr>
    `).join('');

    tbody.querySelectorAll('.btn-supprimer').forEach(btn => {
        btn.addEventListener('click', () => retirerJoueur(reservationId, btn.dataset.membre));
    });

    // Affiche le formulaire d'ajout après le tableau.
    document.getElementById('form-inscription').style.display = 'block';
    chargerMembresDansInscription();
}

// Appelle DELETE /api/reservations/:id/inscriptions/:membre_id après confirmation.
async function retirerJoueur(reservationId, membreId) {
    if (!confirm('Retirer ce joueur de la réservation ?')) return;
    try {
        const res = await fetch(`${API}/api/reservations/${reservationId}/inscriptions/${membreId}`, {
            method: 'DELETE',
        });
        if (!res.ok) throw new Error();
        cacherErreur('erreur-inscriptions');
        chargerInscriptions(reservationId);
    } catch {
        afficherErreur('erreur-inscriptions', 'Erreur lors du retrait du joueur.');
    }
}

// Remplit le select membre du formulaire d'inscription.
async function chargerMembresDansInscription() {
    try {
        const res = await fetch(`${API}/api/membres`);
        const membres = await res.json();
        const select = document.querySelector('#form-inscription select[name="membre_id"]');
        select.innerHTML = '<option value="">-- Choisir un membre --</option>';
        membres.forEach(m => {
            const opt = document.createElement('option');
            opt.value = m.id;
            opt.textContent = `${m.nom} ${m.prenom} (${m.matricule})`;
            select.appendChild(opt);
        });
    } catch {
        afficherErreur('erreur-inscriptions', 'Impossible de charger les membres.');
    }
}

// Lance la recherche quand on clique sur "Chercher".
document.getElementById('btn-chercher-inscription').addEventListener('click', () => {
    const reservationId = document.getElementById('filtre-reservation-id').value;
    if (!reservationId) return;
    cacherErreur('erreur-inscriptions');
    chargerInscriptions(reservationId);
});

// Cache et réinitialise le formulaire d'ajout.
document.getElementById('btn-annuler-inscription').addEventListener('click', () => {
    document.getElementById('form-inscription').style.display = 'none';
    document.getElementById('form-inscription').reset();
});

// Appelle POST /api/reservations/:id/inscriptions avec le membre_id, puis recharge.
document.getElementById('form-inscription').addEventListener('submit', async e => {
    e.preventDefault();
    const form = e.target;
    const reservationId = document.getElementById('filtre-reservation-id').value;
    const body = { membre_id: parseInt(form.membre_id.value) };

    try {
        const res = await fetch(`${API}/api/reservations/${reservationId}/inscriptions`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body),
        });
        if (!res.ok) throw new Error();
        form.reset();
        cacherErreur('erreur-inscriptions');
        chargerInscriptions(reservationId);
    } catch {
        afficherErreur('erreur-inscriptions', 'Erreur lors de l\'inscription (joueur déjà inscrit ?).');
    }
});

// ── HORAIRES ─────────────────────────────────────────────────
// Appelle GET /api/horaires (avec filtre site_id optionnel) et affiche le résultat.
async function chargerHoraires(siteId = '') {
    try {
        const url = siteId ? `${API}/api/horaires?site_id=${siteId}` : `${API}/api/horaires`;
        const res = await fetch(url);
        const horaires = await res.json();
        afficherHoraires(horaires);
    } catch {
        afficherErreur('erreur-horaires', 'Impossible de contacter le serveur.');
    }
}

// Injecte les horaires dans le tableau HTML.
// Attache les listeners de suppression sur chaque bouton généré.
function afficherHoraires(horaires) {
    const tbody = document.getElementById('tbody-horaires');

    if (!horaires.length) {
        tbody.innerHTML = '<tr><td colspan="6">Aucun horaire.</td></tr>';
        return;
    }

    tbody.innerHTML = horaires.map(h => `
        <tr>
            <td>${h.id}</td>
            <td>${h.site_id}</td>
            <td>${h.annee}</td>
            <td>${h.heure_debut}</td>
            <td>${h.heure_fin}</td>
            <td>
                <button class="btn-supprimer" data-id="${h.id}">Supprimer</button>
            </td>
        </tr>
    `).join('');

    tbody.querySelectorAll('.btn-supprimer').forEach(btn => {
        btn.addEventListener('click', () => supprimerHoraire(btn.dataset.id));
    });
}

// Appelle DELETE /api/horaires/:id après confirmation, puis recharge la liste.
async function supprimerHoraire(id) {
    if (!confirm('Supprimer cet horaire ?')) return;
    try {
        const res = await fetch(`${API}/api/horaires/${id}`, { method: 'DELETE' });
        if (!res.ok) throw new Error();
        cacherErreur('erreur-horaires');
        chargerHoraires(document.getElementById('filtre-horaire-site').value);
    } catch {
        afficherErreur('erreur-horaires', 'Erreur lors de la suppression.');
    }
}

// Remplit les selects de site pour horaires (filtre + formulaire).
async function chargerSitesDansHoraires() {
    try {
        const res = await fetch(`${API}/sites`);
        const sites = await res.json();

        const filtre = document.getElementById('filtre-horaire-site');
        filtre.innerHTML = '<option value="">Tous les sites</option>';

        const selectForm = document.querySelector('#form-horaire select[name="site_id"]');
        selectForm.innerHTML = '<option value="">-- Choisir un site --</option>';

        sites.forEach(s => {
            const label = `${s.nom} (${s.ville ?? s.id})`;
            [filtre, selectForm].forEach(sel => {
                const opt = document.createElement('option');
                opt.value = s.id;
                opt.textContent = label;
                sel.appendChild(opt);
            });
        });
    } catch {
        afficherErreur('erreur-horaires', 'Impossible de charger les sites.');
    }
}

// Recharge les horaires quand le filtre site change.
document.getElementById('filtre-horaire-site').addEventListener('change', e => {
    chargerHoraires(e.target.value);
});

// Affiche le formulaire de création.
document.getElementById('btn-nouvel-horaire').addEventListener('click', () => {
    document.getElementById('form-horaire').style.display = 'block';
});

// Cache et réinitialise le formulaire.
document.getElementById('btn-annuler-horaire').addEventListener('click', () => {
    document.getElementById('form-horaire').style.display = 'none';
    document.getElementById('form-horaire').reset();
});

// Appelle POST /api/horaires avec les données du formulaire, puis recharge la liste.
document.getElementById('form-horaire').addEventListener('submit', async e => {
    e.preventDefault();
    const form = e.target;
    const body = {
        site_id:     parseInt(form.site_id.value),
        annee:       parseInt(form.annee.value),
        heure_debut: form.heure_debut.value + ':00',
        heure_fin:   form.heure_fin.value + ':00',
    };

    try {
        const res = await fetch(`${API}/api/horaires`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body),
        });
        if (!res.ok) throw new Error();
        form.style.display = 'none';
        form.reset();
        cacherErreur('erreur-horaires');
        chargerHoraires(document.getElementById('filtre-horaire-site').value);
    } catch {
        afficherErreur('erreur-horaires', 'Erreur lors de la création (doublon site+année ?).');
    }
});

// ── FERMETURES ───────────────────────────────────────────────
// Appelle GET /api/fermetures (avec filtres optionnels) et affiche le résultat.
async function chargerFermetures(params = {}) {
    try {
        const query = new URLSearchParams(params).toString();
        const url = query ? `${API}/api/fermetures?${query}` : `${API}/api/fermetures`;
        const res = await fetch(url);
        const fermetures = await res.json();
        afficherFermetures(fermetures);
    } catch {
        afficherErreur('erreur-fermetures', 'Impossible de contacter le serveur.');
    }
}

// Injecte les fermetures dans le tableau HTML.
// Attache les listeners de suppression sur chaque bouton généré.
function afficherFermetures(fermetures) {
    const tbody = document.getElementById('tbody-fermetures');

    if (!fermetures.length) {
        tbody.innerHTML = '<tr><td colspan="6">Aucune fermeture.</td></tr>';
        return;
    }

    tbody.innerHTML = fermetures.map(f => `
        <tr>
            <td>${f.id}</td>
            <td>${f.site_id ? f.site_id : 'Globale'}</td>
            <td>${f.date_debut}</td>
            <td>${f.date_fin}</td>
            <td>${f.raison ?? '—'}</td>
            <td>
                <button class="btn-supprimer" data-id="${f.id}">Supprimer</button>
            </td>
        </tr>
    `).join('');

    tbody.querySelectorAll('.btn-supprimer').forEach(btn => {
        btn.addEventListener('click', () => supprimerFermeture(btn.dataset.id));
    });
}

// Appelle DELETE /api/fermetures/:id après confirmation, puis recharge la liste.
async function supprimerFermeture(id) {
    if (!confirm('Supprimer cette fermeture ?')) return;
    try {
        const res = await fetch(`${API}/api/fermetures/${id}`, { method: 'DELETE' });
        if (!res.ok) throw new Error();
        cacherErreur('erreur-fermetures');
        chargerFermetures(filtresFermeturesActuels());
    } catch {
        afficherErreur('erreur-fermetures', 'Erreur lors de la suppression.');
    }
}

// Retourne les paramètres de filtre fermetures actuellement actifs.
function filtresFermeturesActuels() {
    const params = {};
    const siteId   = document.getElementById('filtre-fermeture-site').value;
    const globales = document.getElementById('filtre-fermetures-globales').checked;
    if (siteId)   params.site_id  = siteId;
    if (globales) params.globales = 1;
    return params;
}

// Remplit les selects de site pour fermetures (filtre + formulaire).
async function chargerSitesDansFermetures() {
    try {
        const res = await fetch(`${API}/sites`);
        const sites = await res.json();

        const filtre = document.getElementById('filtre-fermeture-site');
        filtre.innerHTML = '<option value="">Tous les sites</option>';

        const selectForm = document.querySelector('#form-fermeture select[name="site_id"]');
        selectForm.innerHTML = '<option value="">-- Globale (tous les sites) --</option>';

        sites.forEach(s => {
            const label = `${s.nom} (${s.ville ?? s.id})`;
            [filtre, selectForm].forEach(sel => {
                const opt = document.createElement('option');
                opt.value = s.id;
                opt.textContent = label;
                sel.appendChild(opt);
            });
        });
    } catch {
        afficherErreur('erreur-fermetures', 'Impossible de charger les sites.');
    }
}

// Recharge les fermetures quand le filtre site change.
document.getElementById('filtre-fermeture-site').addEventListener('change', () => {
    chargerFermetures(filtresFermeturesActuels());
});

// Recharge les fermetures quand la checkbox globales change.
document.getElementById('filtre-fermetures-globales').addEventListener('change', () => {
    chargerFermetures(filtresFermeturesActuels());
});

// Affiche le formulaire de création.
document.getElementById('btn-nouvelle-fermeture').addEventListener('click', () => {
    document.getElementById('form-fermeture').style.display = 'block';
});

// Cache et réinitialise le formulaire.
document.getElementById('btn-annuler-fermeture').addEventListener('click', () => {
    document.getElementById('form-fermeture').style.display = 'none';
    document.getElementById('form-fermeture').reset();
});

// Appelle POST /api/fermetures avec les données du formulaire, puis recharge la liste.
document.getElementById('form-fermeture').addEventListener('submit', async e => {
    e.preventDefault();
    const form = e.target;
    const body = {
        date_debut: form.date_debut.value,
        date_fin:   form.date_fin.value,
        raison:     form.raison.value || undefined,
    };
    if (form.site_id.value) {
        body.site_id = parseInt(form.site_id.value);
    }

    try {
        const res = await fetch(`${API}/api/fermetures`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body),
        });
        if (!res.ok) throw new Error();
        form.style.display = 'none';
        form.reset();
        cacherErreur('erreur-fermetures');
        chargerFermetures();
    } catch {
        afficherErreur('erreur-fermetures', 'Erreur lors de la création.');
    }
});

// ── ADMINISTRATEURS ───────────────────────────────────────────
// Appelle GET /api/administrateurs et affiche le résultat.
async function chargerAdministrateurs() {
    try {
        const res = await fetch(`${API}/api/administrateurs`);
        const admins = await res.json();
        afficherAdministrateurs(admins);
    } catch {
        afficherErreur('erreur-administrateurs', 'Impossible de contacter le serveur.');
    }
}

// Injecte les administrateurs dans le tableau HTML.
// Attache les listeners de désactivation sur chaque bouton généré.
function afficherAdministrateurs(admins) {
    const tbody = document.getElementById('tbody-administrateurs');

    if (!admins.length) {
        tbody.innerHTML = '<tr><td colspan="7">Aucun administrateur.</td></tr>';
        return;
    }

    tbody.innerHTML = admins.map(a => `
        <tr>
            <td>${a.id}</td>
            <td>${a.login}</td>
            <td>${a.nom ?? '—'}</td>
            <td>${a.prenom ?? '—'}</td>
            <td>${a.type}</td>
            <td>${a.site_id ? a.site_id : 'Global'}</td>
            <td>
                <button class="btn-supprimer" data-id="${a.id}">Désactiver</button>
            </td>
        </tr>
    `).join('');

    tbody.querySelectorAll('.btn-supprimer').forEach(btn => {
        btn.addEventListener('click', () => desactiverAdmin(btn.dataset.id));
    });
}

// Appelle DELETE /api/administrateurs/:id après confirmation, puis recharge la liste.
async function desactiverAdmin(id) {
    if (!confirm('Désactiver cet administrateur ?')) return;
    try {
        const res = await fetch(`${API}/api/administrateurs/${id}`, { method: 'DELETE' });
        if (!res.ok) throw new Error();
        cacherErreur('erreur-administrateurs');
        chargerAdministrateurs();
    } catch {
        afficherErreur('erreur-administrateurs', 'Erreur lors de la désactivation.');
    }
}

// Affiche ou cache le select site selon le type choisi (obligatoire pour SITE).
document.querySelector('#form-admin select[name="type"]').addEventListener('change', async e => {
    const labelSite = document.getElementById('label-site-admin');
    if (e.target.value === 'SITE') {
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

// Affiche le formulaire de création.
document.getElementById('btn-nouvel-admin').addEventListener('click', () => {
    document.getElementById('form-admin').style.display = 'block';
});

// Cache et réinitialise le formulaire.
document.getElementById('btn-annuler-admin').addEventListener('click', () => {
    document.getElementById('form-admin').style.display = 'none';
    document.getElementById('form-admin').reset();
    document.getElementById('label-site-admin').style.display = 'none';
});

// Appelle POST /api/administrateurs avec les données du formulaire, puis recharge la liste.
document.getElementById('form-admin').addEventListener('submit', async e => {
    e.preventDefault();
    const form = e.target;
    const body = {
        login:       form.login.value,
        mot_de_passe: form.mot_de_passe.value,
        nom:         form.nom.value || undefined,
        prenom:      form.prenom.value || undefined,
        email:       form.email.value || undefined,
        type:        form.type.value,
    };
    if (form.type.value === 'SITE') {
        body.site_id = parseInt(form.site_id.value);
    }

    try {
        const res = await fetch(`${API}/api/administrateurs`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body),
        });
        if (!res.ok) throw new Error();
        form.style.display = 'none';
        form.reset();
        document.getElementById('label-site-admin').style.display = 'none';
        cacherErreur('erreur-administrateurs');
        chargerAdministrateurs();
    } catch {
        afficherErreur('erreur-administrateurs', 'Erreur lors de la création (login déjà utilisé ?).');
    }
});

// ── Init ─────────────────────────────────────────────────────
// Chargement initial des données au démarrage de la page.
// ── PAIEMENTS ─────────────────────────────────────────────────
// Appelle GET /api/inscriptions/:id/paiement et affiche le résultat.
async function chargerPaiement(inscriptionId) {
    const resultat = document.getElementById('resultat-paiement');
    const form     = document.getElementById('form-paiement');
    resultat.style.display = 'none';
    form.style.display     = 'none';

    try {
        const res = await fetch(`${API}/api/inscriptions/${inscriptionId}/paiement`);

        if (res.status === 404) {
            // Pas encore de paiement : affiche le formulaire de création.
            form.style.display = 'block';
            return;
        }

        if (!res.ok) throw new Error();
        const paiement = await res.json();
        afficherPaiement(paiement);
    } catch {
        afficherErreur('erreur-paiements', 'Inscription introuvable.');
    }
}

// Injecte le paiement dans le tableau et attache le bouton d'annulation.
function afficherPaiement(p) {
    const tbody = document.getElementById('tbody-paiement');
    tbody.innerHTML = `
        <tr>
            <td>${p.id}</td>
            <td>${p.inscription_id}</td>
            <td>${p.montant} €</td>
            <td>${p.methode ?? '—'}</td>
            <td>${p.date_paiement}</td>
            <td>${p.est_annule ? 'Oui' : 'Non'}</td>
            <td>
                ${!p.est_annule
                    ? `<button class="btn-supprimer" data-id="${p.id}">Annuler</button>`
                    : '—'}
            </td>
        </tr>
    `;

    tbody.querySelector('.btn-supprimer')?.addEventListener('click', () => annulerPaiement(p.id));
    document.getElementById('resultat-paiement').style.display = 'block';
}

// Appelle DELETE /api/paiements/:id après confirmation, puis recharge.
async function annulerPaiement(paiementId) {
    if (!confirm('Annuler ce paiement ?')) return;
    try {
        const res = await fetch(`${API}/api/paiements/${paiementId}`, { method: 'DELETE' });
        if (!res.ok) throw new Error();
        cacherErreur('erreur-paiements');
        const inscriptionId = document.getElementById('filtre-inscription-id').value;
        chargerPaiement(inscriptionId);
    } catch {
        afficherErreur('erreur-paiements', 'Erreur lors de l\'annulation.');
    }
}

// Lance la recherche quand on clique sur "Chercher".
document.getElementById('btn-chercher-paiement').addEventListener('click', () => {
    const inscriptionId = document.getElementById('filtre-inscription-id').value;
    if (!inscriptionId) return;
    cacherErreur('erreur-paiements');
    chargerPaiement(inscriptionId);
});

// Cache le formulaire quand on clique sur "Annuler".
document.getElementById('btn-annuler-paiement').addEventListener('click', () => {
    document.getElementById('form-paiement').style.display = 'none';
    document.getElementById('form-paiement').reset();
});

// Appelle POST /api/inscriptions/:id/paiement avec montant fixe 15.00, puis recharge.
document.getElementById('form-paiement').addEventListener('submit', async e => {
    e.preventDefault();
    const form = e.target;
    const inscriptionId = document.getElementById('filtre-inscription-id').value;
    const body = { montant: 15.00 };
    if (form.methode.value) body.methode = form.methode.value;

    try {
        const res = await fetch(`${API}/api/inscriptions/${inscriptionId}/paiement`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body),
        });
        if (!res.ok) throw new Error();
        form.style.display = 'none';
        form.reset();
        cacherErreur('erreur-paiements');
        chargerPaiement(inscriptionId);
    } catch {
        afficherErreur('erreur-paiements', 'Erreur lors du paiement (déjà payé ?).');
    }
});

// ── Init ─────────────────────────────────────────────────────
// Chargement initial des données au démarrage de la page.
chargerSites();
chargerTerrains();
chargerMembres();
chargerPenalites();
chargerSitesDansHoraires();
chargerHoraires();
chargerSitesDansFermetures();
chargerFermetures();
chargerAdministrateurs();
