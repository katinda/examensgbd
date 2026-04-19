# Documentation - Membre

## C'est quoi un Membre ?

Un membre c'est un joueur inscrit sur la plateforme PadelManager.
Il s'identifie uniquement par son matricule (pas de mot de passe côté joueur).
Trois catégories existent, chacune avec des droits de réservation différents :
- **G (Global)** : accès à tous les sites, 3 semaines d'anticipation
- **S (Site)** : rattaché à un seul site, 2 semaines d'anticipation
- **L (Libre)** : accès à tous les sites, 5 jours d'anticipation

---

## Fichiers créés

### `models/Membre.php`
La fiche d'identité d'un membre. Contient 10 propriétés :
- `Membre_ID` : numéro unique du membre
- `Matricule` : identifiant unique (format : lettre de catégorie + chiffres ex: G0001, S00001, L00001)
- `Nom`, `Prenom` : obligatoires
- `Email`, `Telephone` : optionnels
- `Categorie` : `G`, `S` ou `L`
- `Site_ID` : clé étrangère nullable (obligatoire si `S`, interdit si `G` ou `L`)
- `Est_Actif` : true = membre actif, false = membre désactivé (soft-delete)
- `Date_Creation` : date d'inscription automatique

Possède un constructeur qui oblige à fournir toutes les valeurs dès la création.
Ne fait jamais de SQL. Stocke juste des données.

---

### `repositories/MembreRepository.php`
Gère tout le SQL de la table Membres. Contient 7 méthodes :
- `findAll()` → retourne tous les membres
- `findById($id)` → retourne un membre ou null
- `findByMatricule($matricule)` → retourne un membre par son matricule ou null
- `findByCategorie($categorie)` → retourne tous les membres d'une catégorie (G, S ou L)
- `insert($membre)` → crée un nouveau membre, retourne son ID
- `update($membre)` → met à jour un membre existant
- `delete($id)` → supprime définitivement un membre

La connexion PDO est reçue en paramètre (injection de dépendance).

---

### `services/MembreService.php`
Contient la logique métier des membres. Utilise **deux repositories** :
`MembreRepository` + `SiteRepository` (pour vérifier que le site existe pour catégorie S).

Contient 7 méthodes :
- `getAllMembres()` → retourne uniquement les membres **actifs**
- `getMembresByCategorie($categorie)` → retourne les membres actifs d'une catégorie
- `getMembreById($id)` → retourne un membre actif ou null
- `getMembreByMatricule($matricule)` → retourne un membre actif par matricule ou null
- `createMembre($data)` → valide les règles métier, crée le membre
- `updateMembre($id, $data)` → met à jour, retourne false si inexistant
- `deleteMembre($id)` → **soft-delete** via `Est_Actif = 0`, retourne false si inexistant

#### Règles de validation dans createMembre()
| Erreur retournée | Condition | Code HTTP |
|---|---|---|
| `matricule_invalide` | format ne correspond pas à la catégorie (ex: G0001 pour S) | 400 |
| `site_requis` | catégorie S sans Site_ID | 400 |
| `site_interdit` | catégorie G ou L avec un Site_ID | 400 |
| `site_introuvable` | Site_ID fourni mais le site n'existe pas | 404 |
| `doublon_matricule` | ce matricule existe déjà | 409 |

---

### `controllers/MembreController.php`
Reçoit les requêtes HTTP et renvoie du JSON. Contient 6 méthodes :

| Méthode | Route | Description |
|---|---|---|
| `getAll()` | GET /api/membres | Retourne tous les membres actifs (filtre ?categorie= optionnel) |
| `getById()` | GET /api/membres/{id} | Retourne un membre ou 404 |
| `getByMatricule()` | GET /api/membres/matricule/{matricule} | Retourne un membre par matricule ou 404 |
| `create()` | POST /api/membres | Crée un membre (400, 404 ou 409 possible) |
| `update()` | PUT /api/membres/{id} | Met à jour un membre ou 404 |
| `delete()` | DELETE /api/membres/{id} | Soft-delete un membre ou 404 |

---

## Endpoints disponibles

```
GET    /api/membres                          → liste tous les membres actifs
GET    /api/membres?categorie=G              → filtre par catégorie (G, S ou L)
GET    /api/membres/{id}                     → retourne un membre précis
GET    /api/membres/matricule/{matricule}    → retourne un membre par matricule
POST   /api/membres                          → crée un nouveau membre
PUT    /api/membres/{id}                     → met à jour un membre
DELETE /api/membres/{id}                     → désactive un membre (soft-delete)
```

### Exemple POST /api/membres — catégorie G
```json
{
    "matricule": "G0001",
    "nom": "Dupont",
    "prenom": "Jean",
    "email": "jean.dupont@email.com",
    "telephone": "0601020304",
    "categorie": "G"
}
```

### Exemple POST /api/membres — catégorie S (site_id obligatoire)
```json
{
    "matricule": "S00001",
    "nom": "Martin",
    "prenom": "Alice",
    "categorie": "S",
    "site_id": 1
}
```

### Codes de réponse possibles
| Code | Signification |
|---|---|
| 201 | Membre créé avec succès |
| 400 | Champs manquants, matricule invalide, site requis ou site interdit |
| 404 | Site ou membre introuvable |
| 409 | Ce matricule existe déjà |

---

## Tests unitaires

### `tests/MembreRepositoryTest.php` — 9 tests
- `testFindAllRetourneTousLesMembres`
- `testFindByIdRetourneLeMembreCorrect`
- `testFindByIdRetourneNullSiInexistant`
- `testFindByMatriculeRetourneLeMembreCorrect`
- `testFindByMatriculeRetourneNullSiInexistant`
- `testFindByCategorieRetourneLesBonsMembres`
- `testInsertAjouteUnMembre`
- `testUpdateModifieUnMembre`
- `testDeleteSupprimeUnMembre`

### `tests/MembreServiceTest.php` — 15 tests
- `testGetAllMembresRetourneSeulementLesActifs`
- `testGetMembresByCategorieRetourneLesBonsActifs`
- `testGetMembreByIdRetourneNullSiInactif`
- `testGetMembreByIdRetourneLeMembreSiActif`
- `testGetMembreByMatriculeRetourneNullSiInactif`
- `testGetMembreByMatriculeRetourneLeMembreSiActif`
- `testCreateMembreRetourneMatriculeInvalide`
- `testCreateMembreRetourneSiteRequis`
- `testCreateMembreRetourneSiteInterdit`
- `testCreateMembreRetourneSiteIntrouvable`
- `testCreateMembreRetourneUnId`
- `testUpdateMembreRetourneTrueSiExiste`
- `testUpdateMembreRetourneFalseSiInexistant`
- `testDeleteMembreRetourneTrueSiExiste`
- `testDeleteMembreRetourneFalseSiInexistant`
