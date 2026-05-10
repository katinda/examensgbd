// URL de base de l'API PHP
const API = 'http://localhost:8000';

// Membre identifié (stocké en mémoire pendant la session)
let membreConnecte = null;

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

// ── Navigation ───────────────────────────────────────────────
// Masque toutes les sections utilisateur et affiche uniquement celle cliquée.
document.querySelectorAll('.nav-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.user-section').forEach(s => s.style.display = 'none');
        document.getElementById('section-' + btn.dataset.section).style.display = 'block';
    });
});

// ── IDENTIFICATION ───────────────────────────────────────────
// Appelle GET /api/membres/matricule/:matricule pour identifier le joueur.
document.getElementById('form-identification').addEventListener('submit', async e => {
    e.preventDefault();
    const matricule = document.getElementById('input-matricule').value.trim();
    cacherErreur('erreur-identification');

    try {
        const res = await fetch(`${API}/api/membres/matricule/${matricule}`);
        if (!res.ok) throw new Error();
        const membre = await res.json();
        connecterMembre(membre);
    } catch {
        afficherErreur('erreur-identification', 'Matricule introuvable ou membre inactif.');
    }
});

// Affiche le dashboard avec les infos du membre connecté.
function connecterMembre(membre) {
    membreConnecte = membre;
    document.getElementById('section-identification').style.display = 'none';
    document.getElementById('section-dashboard').style.display = 'block';
    document.getElementById('membre-prenom').textContent   = membre.prenom;
    document.getElementById('membre-nom').textContent      = membre.nom;
    document.getElementById('membre-matricule').textContent = membre.matricule;
    document.getElementById('membre-categorie').textContent = membre.categorie;
    chargerMesReservations();
    chargerMatchesPublics();
    chargerMesPenalites();
    chargerTerrainsDansForm();
}

// La déconnexion redirige vers index.html via le lien <a> dans user.html.
// La session est réinitialisée automatiquement au rechargement de la page.

// ── MES RÉSERVATIONS ─────────────────────────────────────────
// Appelle GET /api/membres/:id/reservations et affiche les réservations du membre.
async function chargerMesReservations() {
    try {
        const res = await fetch(`${API}/api/membres/${membreConnecte.id}/reservations`);
        const reservations = await res.json();
        afficherMesReservations(reservations);
    } catch {
        afficherErreur('erreur-mes-reservations', 'Impossible de charger vos réservations.');
    }
}

// Injecte les réservations du membre dans le tableau.
function afficherMesReservations(reservations) {
    const tbody = document.getElementById('tbody-mes-reservations');

    if (!reservations.length) {
        tbody.innerHTML = '<tr><td colspan="6">Aucune réservation.</td></tr>';
        return;
    }

    tbody.innerHTML = reservations.map(r => `
        <tr>
            <td>${r.id}</td>
            <td>${r.terrain_id}</td>
            <td>${r.date_match}</td>
            <td>${r.heure_debut}</td>
            <td>${r.type}</td>
            <td>
                <button class="btn-payer" data-reservation-id="${r.id}">Voir / Payer</button>
            </td>
        </tr>
    `).join('');

    tbody.querySelectorAll('.btn-payer').forEach(btn => {
        btn.addEventListener('click', () => voirPaiement(btn.dataset.reservationId));
    });
}

// Redirige vers la section paiement pour une inscription donnée.
// Récupère d'abord l'inscription du membre dans la réservation.
async function voirPaiement(reservationId) {
    try {
        const res = await fetch(`${API}/api/reservations/${reservationId}/inscriptions`);
        const inscriptions = await res.json();
        const inscription = inscriptions.find(i => i.membre_id === membreConnecte.id);
        if (!inscription) {
            alert('Vous n\'êtes pas inscrit à cette réservation.');
            return;
        }
        const resPaiement = await fetch(`${API}/api/inscriptions/${inscription.id}/paiement`);
        if (resPaiement.status === 404) {
            if (confirm('Vous n\'avez pas encore payé. Payer maintenant (15€) ?')) {
                payerInscription(inscription.id);
            }
        } else {
            const paiement = await resPaiement.json();
            alert(`Paiement ${paiement.est_annule ? 'annulé' : 'effectué'} le ${paiement.date_paiement} — ${paiement.montant}€`);
        }
    } catch {
        alert('Erreur lors de la vérification du paiement.');
    }
}

// Appelle POST /api/inscriptions/:id/paiement pour payer la part du joueur.
async function payerInscription(inscriptionId) {
    try {
        const res = await fetch(`${API}/api/inscriptions/${inscriptionId}/paiement`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ montant: 15.00 }),
        });
        if (!res.ok) throw new Error();
        alert('Paiement effectué avec succès !');
        chargerMesReservations();
    } catch {
        alert('Erreur lors du paiement.');
    }
}

// ── MATCHES PUBLICS ──────────────────────────────────────────
// Appelle GET /api/reservations/publiques.
// Membre S → filtre par son site. Membre G ou L → tous les sites.
async function chargerMatchesPublics() {
    const tbody = document.getElementById('tbody-matches-publics');
    try {
        const siteId = membreConnecte.categorie === 'S' ? membreConnecte.site_id : null;
        const url = siteId
            ? `${API}/api/reservations/publiques?site_id=${siteId}`
            : `${API}/api/reservations/publiques`;
        const res = await fetch(url);
        if (!res.ok) throw new Error();
        const matches = await res.json();
        afficherMatchesPublics(matches);
    } catch {
        tbody.innerHTML = '<tr><td colspan="6">Impossible de charger les matches publics.</td></tr>';
    }
}

// Injecte les matches publics dans le tableau avec bouton d'inscription.
function afficherMatchesPublics(matches) {
    const tbody = document.getElementById('tbody-matches-publics');

    if (!matches.length) {
        tbody.innerHTML = '<tr><td colspan="6">Aucun match public disponible.</td></tr>';
        return;
    }

    tbody.innerHTML = matches.map(m => `
        <tr>
            <td>${m.id}</td>
            <td>${m.terrain_id}</td>
            <td>${m.date_match}</td>
            <td>${m.heure_debut}</td>
            <td>${m.places_restantes}</td>
            <td>
                ${m.places_restantes > 0
                    ? `<button class="btn-inscrire" data-id="${m.id}">S'inscrire</button>`
                    : 'Complet'}
            </td>
        </tr>
    `).join('');

    tbody.querySelectorAll('.btn-inscrire').forEach(btn => {
        btn.addEventListener('click', () => sInscrireMatch(btn.dataset.id));
    });
}

// Appelle POST /api/reservations/:id/inscriptions pour s'inscrire à un match public.
async function sInscrireMatch(reservationId) {
    if (!confirm('Vous inscrire à ce match public ?')) return;
    try {
        const res = await fetch(`${API}/api/reservations/${reservationId}/inscriptions`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ membre_id: membreConnecte.id }),
        });
        if (!res.ok) throw new Error();
        alert('Inscription réussie ! Pensez à payer votre part (15€).');
        chargerMatchesPublics();
        chargerMesReservations();
    } catch {
        afficherErreur('erreur-matches-publics', 'Inscription impossible (déjà inscrit ou match complet).');
    }
}

// ── MES PÉNALITÉS ────────────────────────────────────────────
// Appelle GET /api/penalites?membre_id=:id et affiche les pénalités du membre.
async function chargerMesPenalites() {
    try {
        const res = await fetch(`${API}/api/penalites?membre_id=${membreConnecte.id}`);
        const penalites = await res.json();
        afficherMesPenalites(penalites);
    } catch {
        afficherErreur('erreur-mes-penalites', 'Impossible de charger vos pénalités.');
    }
}

// Injecte les pénalités dans le tableau.
function afficherMesPenalites(penalites) {
    const tbody = document.getElementById('tbody-mes-penalites');

    if (!penalites.length) {
        tbody.innerHTML = '<tr><td colspan="5">Aucune pénalité.</td></tr>';
        return;
    }

    tbody.innerHTML = penalites.map(p => `
        <tr>
            <td>${p.id}</td>
            <td>${p.cause}</td>
            <td>${p.date_debut}</td>
            <td>${p.date_fin}</td>
            <td>${p.date_levee ? 'Oui' : 'Non'}</td>
        </tr>
    `).join('');
}

// ── CRÉER UNE RÉSERVATION ────────────────────────────────────
// Remplit le select terrain du formulaire de réservation.
// Membre S → uniquement les terrains de son site. Membre G ou L → tous les terrains.
async function chargerTerrainsDansForm() {
    try {
        const siteId = membreConnecte.categorie === 'S' ? membreConnecte.site_id : null;
        const url = siteId
            ? `${API}/sites/${siteId}/terrains`
            : `${API}/terrains`;
        const res = await fetch(url);
        const terrains = await res.json();
        const select = document.querySelector('#form-nouvelle-reservation select[name="terrain_id"]');
        select.innerHTML = '<option value="">-- Choisir un terrain --</option>';
        terrains.forEach(t => {
            const opt = document.createElement('option');
            opt.value = t.id;
            opt.textContent = `#${t.id} — ${t.libelle ?? 'Terrain ' + t.num_terrain}`;
            select.appendChild(opt);
        });
    } catch {
        afficherErreur('erreur-nouvelle-reservation', 'Impossible de charger les terrains.');
    }
}

// Appelle POST /api/reservations avec le membre connecté comme organisateur.
document.getElementById('form-nouvelle-reservation').addEventListener('submit', async e => {
    e.preventDefault();
    const form = e.target;
    const body = {
        terrain_id:      parseInt(form.terrain_id.value),
        organisateur_id: membreConnecte.id,
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
        form.reset();
        cacherErreur('erreur-nouvelle-reservation');
        alert('Réservation créée avec succès !');
        chargerMesReservations();
    } catch {
        afficherErreur('erreur-nouvelle-reservation', 'Erreur lors de la réservation (créneau déjà pris, pénalité active ?).');
    }
});
