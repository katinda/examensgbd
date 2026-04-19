# Documentation - Site

## C'est quoi un Site ?

Un site c'est un endroit physique où on joue au padel.
Exemple : "Club Paris", "Club Lyon"...
C'est la base de tout le projet : les terrains appartiennent à un site,
les membres sont rattachés à un site, les réservations se font sur un terrain d'un site.

---

## Fichiers créés

### `models/Site.php`
La fiche d'identité d'un site. Contient 7 propriétés :
- `Site_ID` : numéro unique du site
- `Nom` : nom du site (obligatoire)
- `Adresse` : adresse (optionnel)
- `Ville` : ville (optionnel)
- `Code_Postal` : code postal (optionnel)
- `Est_Actif` : true = site ouvert, false = site fermé
- `Date_Creation` : date de création automatique

Ne fait jamais de SQL. Stocke juste des données.

---

### `repositories/SiteRepository.php`
Gère tout le SQL de la table Sites. Contient 5 méthodes :
- `findAll()` → retourne tous les sites
- `findById($id)` → retourne un site ou null
- `insert($site)` → crée un nouveau site, retourne son ID
- `update($site)` → met à jour un site existant
- `delete($id)` → supprime un site

La connexion PDO est reçue en paramètre (injection de dépendance).

---

### `services/SiteService.php`
Contient la logique métier des sites. Contient 5 méthodes :
- `getAllSites()` → retourne uniquement les sites **actifs**
- `getSiteById($id)` → retourne un site actif ou null
- `createSite($data)` → crée un site, le marque actif par défaut
- `updateSite($id, $data)` → met à jour un site, retourne false si inexistant
- `deleteSite($id)` → supprime un site, retourne false si inexistant

Ne fait jamais de SQL directement.

---

### `controllers/SiteController.php`
Reçoit les requêtes HTTP et renvoie du JSON. Contient 5 méthodes :

| Méthode | Route | Description |
|---|---|---|
| `getAll()` | GET /sites | Retourne tous les sites actifs |
| `getById()` | GET /sites/{id} | Retourne un site ou 404 |
| `create()` | POST /sites | Crée un site, retourne 201 |
| `update()` | PUT /sites/{id} | Met à jour un site ou 404 |
| `delete()` | DELETE /sites/{id} | Supprime un site ou 404 |

---

## Endpoints disponibles

```
GET    /sites          → liste tous les sites actifs
GET    /sites/{id}     → retourne un site précis
POST   /sites          → crée un nouveau site
PUT    /sites/{id}     → met à jour un site
DELETE /sites/{id}     → supprime un site
```

### Exemple de réponse GET /sites
```json
[
  {
    "id": 1,
    "nom": "Club Paris",
    "adresse": "10 rue de la Paix",
    "ville": "Paris",
    "code_postal": "75001",
    "est_actif": true,
    "date_creation": "2026-04-19 10:00:00"
  }
]
```

### Exemple POST /sites
```json
{
    "nom": "Club Bordeaux",
    "adresse": "1 avenue du Vin",
    "ville": "Bordeaux",
    "code_postal": "33000"
}
```

---

## Tests unitaires

### `tests/SiteRepositoryTest.php` — 6 tests
- `testFindAllRetourneTousLesSites`
- `testFindByIdRetourneLeBosSite`
- `testFindByIdRetourneNullSiInexistant`
- `testInsertAjouteUnSite`
- `testUpdateModifieUnSite`
- `testDeleteSupprimeUnSite`

### `tests/SiteServiceTest.php` — 9 tests
- `testGetAllSitesRetourneSeulementLesSitesActifs`
- `testGetSiteByIdRetourneNullSiSiteInactif`
- `testGetSiteByIdRetourneLeBosSiActif`
- `testGetSiteByIdRetourneNullSiInexistant`
- `testCreateSiteRetourneUnId`
- `testUpdateSiteRetourneTrueSiSiteExiste`
- `testUpdateSiteRetourneFalseSiSiteInexistant`
- `testDeleteSiteRetourneTrueSiSiteExiste`
- `testDeleteSiteRetourneFalseSiSiteInexistant`
