# Documentation - Inscription

## C'est quoi une Inscription ?

Une inscription c'est le lien entre un membre et une réservation.
Un match de padel nécessite exactement **4 joueurs**. L'organisateur est automatiquement inscrit lors de la création de la réservation. Les 3 autres places sont ensuite remplies via l'API.

Deux cas d'usage :
- **Match PRIVE** : l'organisateur invite 3 joueurs spécifiques
- **Match PUBLIC** : n'importe quel membre peut s'inscrire jusqu'à compléter les 4 places

---

## Fichiers créés

### `models/Inscription.php`
La fiche d'identité d'une inscription. Contient 4 propriétés :
- `Inscription_ID` : numéro unique de l'inscription
- `Reservation_ID` : à quelle réservation appartient cette inscription (clé étrangère)
- `Membre_ID` : quel membre est inscrit (clé étrangère)
- `Est_Organisateur` : `true` = c'est lui qui a créé la réservation, `false` = joueur invité ou inscrit librement

Possède un constructeur qui oblige à fournir toutes les valeurs dès la création.
`estOrganisateur` vaut `false` par défaut — seul `ReservationService` le passe à `true`.
Ne fait jamais de SQL. Stocke juste des données.

---

### `repositories/InscriptionRepository.php`
Gère tout le SQL de la table Inscriptions. Contient 5 méthodes :
- `findByReservation($reservationId)` → retourne toutes les inscriptions d'une réservation (les 4 joueurs)
- `findByReservationAndMembre($reservationId, $membreId)` → retourne l'inscription d'un membre précis ou null
- `countByReservation($reservationId)` → compte le nombre de joueurs inscrits (utilisé pour vérifier la limite de 4)
- `insert($inscription)` → crée une nouvelle inscription, retourne son ID
- `delete($reservationId, $membreId)` → supprime l'inscription d'un membre sur une réservation

La connexion PDO est reçue en paramètre (injection de dépendance).

---

### `services/InscriptionService.php`
Contient la logique métier des inscriptions. Utilise **trois repositories** :
`InscriptionRepository` + `ReservationRepository` + `MembreRepository`.

Contient 3 méthodes :
- `getInscriptionsByReservation($reservationId)` → retourne la liste des joueurs inscrits
- `addJoueur($reservationId, $membreId)` → valide les règles métier, inscrit le joueur
- `removeJoueur($reservationId, $membreId)` → retire un joueur, retourne false si non inscrit

#### Règles de validation dans addJoueur()
| Erreur retournée | Condition | Code HTTP |
|---|---|---|
| `reservation_introuvable` | la réservation n'existe pas | 404 |
| `membre_introuvable` | le membre n'existe pas ou est inactif | 404 |
| `reservation_complete` | la réservation a déjà 4 joueurs | 409 |
| `deja_inscrit` | ce membre est déjà inscrit à cette réservation | 409 |

---

### `controllers/InscriptionController.php`
Reçoit les requêtes HTTP et renvoie du JSON. Contient 3 méthodes :

| Méthode | Route | Description |
|---|---|---|
| `getByReservation()` | GET /api/reservations/{id}/inscriptions | Retourne la liste des joueurs inscrits |
| `addJoueur()` | POST /api/reservations/{id}/inscriptions | Inscrit un joueur (400, 404 ou 409 possible) |
| `removeJoueur()` | DELETE /api/reservations/{id}/inscriptions/{membreId} | Retire un joueur ou 404 |

---

### Modification de `services/ReservationService.php`
Lors de la création d'une réservation, `ReservationService::createReservation()` crée automatiquement l'inscription de l'organisateur (`Est_Organisateur = 1`) dans une **transaction PDO**.
Si l'inscription échoue, la réservation est annulée (`rollBack`).

---

## Endpoints disponibles

```
GET    /api/reservations/{id}/inscriptions              → liste les joueurs inscrits
POST   /api/reservations/{id}/inscriptions              → inscrit un joueur
DELETE /api/reservations/{id}/inscriptions/{membreId}   → retire un joueur
```

### Exemple POST /api/reservations/{id}/inscriptions
```json
{
    "membre_id": 2
}
```

### Réponse 201 Created
```json
{
    "message": "Joueur inscrit avec succès",
    "id": 3
}
```

### Codes de réponse possibles
| Code | Signification |
|---|---|
| 201 | Joueur inscrit avec succès |
| 400 | Champ membre_id manquant |
| 404 | Réservation ou membre introuvable |
| 409 | Réservation complète ou joueur déjà inscrit |

---

## Tests unitaires

### `tests/InscriptionRepositoryTest.php` — 7 tests
- `testFindByReservationRetourneLesInscriptions`
- `testFindByReservationRetourneVideSiAucune`
- `testFindByReservationAndMembreRetourneLInscription`
- `testFindByReservationAndMembreRetourneNullSiNonInscrit`
- `testCountByReservationRetourneLeBonNombre`
- `testInsertAjouteUneInscription`
- `testDeleteSupprimeUneInscription`

### `tests/InscriptionServiceTest.php` — 8 tests
- `testGetInscriptionsByReservationRetourneLesInscriptions`
- `testAddJoueurRetourneReservationIntrouvable`
- `testAddJoueurRetourneMembreIntrouvable`
- `testAddJoueurRetourneReservationComplete`
- `testAddJoueurRetourneDejaInscrit`
- `testAddJoueurRetourneUnId`
- `testRemoveJoueurRetourneTrueSiInscrit`
- `testRemoveJoueurRetourneFalseSiNonInscrit`
