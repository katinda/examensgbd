# Documentation - Réservation

## C'est quoi une Réservation ?

Une réservation c'est un créneau de jeu réservé sur un terrain par un membre organisateur.
Chaque créneau dure **1h30** fixe — l'heure de fin est calculée automatiquement par le service.
Deux types existent :
- **PRIVE** : l'organisateur invite des joueurs spécifiques
- **PUBLIC** : le créneau est ouvert à tous les membres

---

## Fichiers créés

### `models/Reservation.php`
La fiche d'identité d'une réservation. Contient 11 propriétés :
- `Reservation_ID` : numéro unique de la réservation
- `Terrain_ID` : quel terrain est réservé (clé étrangère)
- `Organisateur_ID` : quel membre a créé la réservation (clé étrangère)
- `Date_Match` : date du match, format `YYYY-MM-DD`
- `Heure_Debut` : heure de début, format `HH:MM:SS`
- `Heure_Fin` : heure de fin = Heure_Debut + 1h30, **calculée par le service**
- `Type` : `PRIVE` ou `PUBLIC`
- `Etat` : cycle de vie — `EN_COURS`, `COMPLETEE`, `BASCULE_PUBLIC`, `ANNULEE`, `FORFAIT`, `TERMINEE`
- `Prix_Total` : prix du créneau, défaut `60.00 €` (divisé en 4 parts de 15 €)
- `Date_Creation` : date de création automatique
- `LastUpdate` : date de dernière modification automatique

Possède un constructeur qui oblige à fournir toutes les valeurs dès la création.
Ne fait jamais de SQL. Stocke juste des données.

---

### `repositories/ReservationRepository.php`
Gère tout le SQL de la table Reservations. Contient 6 méthodes :
- `findById($id)` → retourne une réservation ou null
- `findByOrganisateur($membreId)` → retourne toutes les réservations d'un membre (triées par date décroissante)
- `findByTerrainAndDate($terrainId, $date)` → retourne toutes les réservations d'un terrain à une date précise
- `findByTerrainDateHeure($terrainId, $date, $heureDebut)` → vérifie si un créneau est déjà pris, retourne la réservation ou null
- `insert($reservation)` → crée une nouvelle réservation, retourne son ID
- `update($reservation)` → met à jour l'état et le prix d'une réservation existante

La connexion PDO est reçue en paramètre (injection de dépendance).

---

### `services/ReservationService.php`
Contient la logique métier des réservations. Utilise **trois repositories** :
`ReservationRepository` + `TerrainRepository` + `MembreRepository`.

Version minimale — les règles avancées (horaires, fermetures, délais, pénalités) seront ajoutées après.

Contient 4 méthodes publiques + 1 méthode privée :
- `getReservationById($id)` → retourne une réservation ou null
- `getReservationsByMembre($membreId)` → retourne toutes les réservations d'un membre
- `getReservationsByTerrainAndDate($terrainId, $date)` → retourne les réservations d'un terrain à une date
- `createReservation($data)` → valide les règles métier, calcule Heure_Fin, crée la réservation
- `calculerHeureFin($heureDebut)` *(privée)* → ajoute 1h30 à l'heure de début

#### Règles de validation dans createReservation()
| Erreur retournée | Condition | Code HTTP |
|---|---|---|
| `terrain_introuvable` | le terrain n'existe pas | 404 |
| `terrain_inactif` | le terrain est fermé (Est_Actif = 0) | 400 |
| `organisateur_introuvable` | le membre n'existe pas ou est inactif | 404 |
| `creneau_pris` | ce créneau est déjà réservé sur ce terrain | 409 |

---

### `controllers/ReservationController.php`
Reçoit les requêtes HTTP et renvoie du JSON. Contient 4 méthodes :

| Méthode | Route | Description |
|---|---|---|
| `getById()` | GET /api/reservations/{id} | Retourne une réservation ou 404 |
| `getByMembre()` | GET /api/membres/{id}/reservations | Retourne les réservations d'un membre |
| `getByTerrainAndDate()` | GET /api/terrains/{id}/reservations?date=YYYY-MM-DD | Retourne les réservations d'un terrain à une date |
| `create()` | POST /api/reservations | Crée une réservation (400, 404 ou 409 possible) |

---

## Endpoints disponibles

```
GET  /api/reservations/{id}                          → retourne une réservation précise
GET  /api/membres/{id}/reservations                  → réservations d'un membre
GET  /api/terrains/{id}/reservations?date=YYYY-MM-DD → réservations d'un terrain à une date
POST /api/reservations                               → crée une nouvelle réservation
```

### Exemple POST /api/reservations
```json
{
    "terrain_id": 1,
    "organisateur_id": 1,
    "date_match": "2026-05-10",
    "heure_debut": "10:00:00",
    "type": "PRIVE"
}
```

### Réponse 201 Created
```json
{
    "id": 1
}
```

### Codes de réponse possibles
| Code | Signification |
|---|---|
| 201 | Réservation créée avec succès |
| 400 | Terrain inactif |
| 404 | Terrain ou organisateur introuvable |
| 409 | Ce créneau est déjà réservé |

---

## Tests unitaires

### `tests/ReservationRepositoryTest.php` — 10 tests
- `testFindByIdRetourneLaBonneReservation`
- `testFindByIdRetourneNullSiInexistant`
- `testFindByOrganisateurRetourneLesReservations`
- `testFindByOrganisateurRetourneVideSiAucune`
- `testFindByTerrainAndDateRetourneLesReservations`
- `testFindByTerrainAndDateRetourneVideSiAucune`
- `testFindByTerrainDateHeureRetourneLaReservation`
- `testFindByTerrainDateHeureRetourneNullSiLibre`
- `testInsertAjouteUneReservation`
- `testUpdateModifieUneReservation`

### `tests/ReservationServiceTest.php` — 9 tests
- `testGetReservationByIdRetourneLaReservation`
- `testGetReservationsByMembreRetourneLesReservations`
- `testGetReservationsByTerrainAndDateRetourneLesReservations`
- `testCreateReservationRetourneTerrainIntrouvable`
- `testCreateReservationRetourneTerrainInactif`
- `testCreateReservationRetourneOrganisateurIntrouvable`
- `testCreateReservationRetourneCreneauPris`
- `testCreateReservationRetourneUnId`
- `testCreateReservationCalculeHeureFin`
