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

// ── Init ─────────────────────────────────────────────────────
// Chargement initial des sites au démarrage de la page.
chargerSites();
