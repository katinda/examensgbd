# Documentation - Paiement

## C'est quoi un Paiement ?

Un paiement représente la part financière d'un joueur pour un match de padel.
Chaque match coûte **60€**, divisé en **4 parts de 15€** (une par joueur).
Un paiement est lié à **une seule inscription** (relation 1-1 via contrainte `UNIQUE` sur `Inscription_ID`).

Un paiement absent = le joueur est inscrit mais n'a pas encore payé.
Une annulation ne supprime pas la ligne : on marque `Est_Annule = 1` pour conserver l'historique du chiffre d'affaires.

---

## Fichiers créés

### `models/Paiement.php`
Contient 8 propriétés :
- `paiementId` : numéro unique du paiement (`?int`, null avant INSERT)
- `inscriptionId` : à quelle inscription appartient ce paiement (clé étrangère)
- `montant` : montant payé — toujours `15.00` (forcé côté serveur)
- `datePaiement` : horodatage de création (`?string`, null avant INSERT — renseigné par `DEFAULT CURRENT_TIMESTAMP`)
- `methode` : mode de paiement optionnel (`CARTE`, `VIREMENT`, `ESPECES`, `MOBILE`)
- `estAnnule` : `false` par défaut
- `montantRembourse` : nullable — renseigné uniquement lors de l'annulation
- `dateAnnulation` : nullable — renseignée uniquement lors de l'annulation

Possède une méthode `annuler(float $montantRembourse, string $dateAnnulation)` qui met à jour les 3 champs d'annulation en une seule opération.
Ne fait jamais de SQL. Stocke juste des données.

---

### `repositories/PaiementRepository.php`
Gère tout le SQL de la table Paiements. Contient 4 méthodes :
- `findByInscription($inscriptionId)` → retourne le paiement d'une inscription ou `null`
- `findById($paiementId)` → retourne un paiement par son ID ou `null`
- `insert($paiement)` → crée un nouveau paiement, retourne son ID
- `update($paiement)` → met à jour les champs d'annulation (`Est_Annule`, `Montant_Rembourse`, `Date_Annulation`)

`insert()` ne passe pas `Date_Paiement` — la base la renseigne via `DEFAULT CURRENT_TIMESTAMP`.
`update()` ne modifie jamais le montant ni la méthode après création.

---

### `repositories/InscriptionRepository.php` — modification
Ajout de la méthode `findById($inscriptionId)` → retourne une inscription par son `Inscription_ID` ou `null`.
Nécessaire pour que `PaiementService` vérifie l'existence d'une inscription avant d'enregistrer un paiement.

---

### `services/PaiementService.php`
Contient la logique métier des paiements. Utilise **deux repositories** :
`PaiementRepository` + `InscriptionRepository`.

Contient 3 méthodes :
- `getPaiementByInscription($inscriptionId)` → retourne le paiement ou une erreur
- `createPaiement($inscriptionId, $data)` → valide les règles métier, enregistre le paiement
- `annulerPaiement($paiementId)` → annule le paiement (remboursement total)

Constantes internes :
- `MONTANT_PART = 15.00` — le montant est forcé côté serveur
- `METHODES_VALIDES = ['CARTE', 'VIREMENT', 'ESPECES', 'MOBILE']`

#### Règles de validation dans `createPaiement()`
| Erreur retournée | Condition | Code HTTP |
|---|---|---|
| `inscription_introuvable` | l'inscription n'existe pas | 404 |
| `paiement_deja_existant` | cette inscription a déjà un paiement | 409 |
| `montant_invalide` | le montant n'est pas exactement 15.00 | 400 |
| `methode_invalide` | la méthode n'est pas dans la liste autorisée | 400 |

#### Règles de validation dans `annulerPaiement()`
| Erreur retournée | Condition | Code HTTP |
|---|---|---|
| `paiement_introuvable` | le paiement n'existe pas | 404 |
| `paiement_deja_annule` | le paiement est déjà annulé | 409 |

---

### `controllers/PaiementController.php`
Reçoit les requêtes HTTP et renvoie du JSON. Contient 3 méthodes :

| Méthode | Route | Description |
|---|---|---|
| `getByInscription()` | GET /api/inscriptions/{id}/paiement | Consulte le paiement d'une inscription |
| `create()` | POST /api/inscriptions/{id}/paiement | Enregistre le paiement (400, 404 ou 409 possible) |
| `annuler()` | DELETE /api/paiements/{id} | Annule un paiement (soft, ne supprime pas la ligne) |

---

## Endpoints disponibles

```
GET    /api/inscriptions/{id}/paiement   → consulte le paiement d'une inscription
POST   /api/inscriptions/{id}/paiement   → enregistre le paiement
DELETE /api/paiements/{id}               → annule un paiement
```

### Exemple POST /api/inscriptions/{id}/paiement
```json
{
    "montant": 15.00,
    "methode": "CARTE"
}
```

### Réponse 201 Created
```json
{
    "message": "Paiement enregistré avec succès",
    "id": 5
}
```

### Codes de réponse possibles
| Code | Signification |
|---|---|
| 200 | Paiement annulé avec succès |
| 201 | Paiement enregistré avec succès |
| 400 | Montant invalide ou méthode invalide |
| 404 | Inscription ou paiement introuvable |
| 409 | Paiement déjà existant ou déjà annulé |

---

## Tests unitaires

### `tests/PaiementRepositoryTest.php` — 6 tests
- `testFindByInscriptionRetourneLePaiement`
- `testFindByInscriptionRetourneNullSiAucunPaiement`
- `testFindByIdRetourneLePaiement`
- `testFindByIdRetourneNullSiInexistant`
- `testInsertAjouteUnPaiement`
- `testUpdateEnregistreLAnnulation`

### `tests/PaiementServiceTest.php` — 11 tests
- `testGetPaiementByInscriptionRetourneInscriptionIntrouvable`
- `testGetPaiementByInscriptionRetournePaiementIntrouvable`
- `testGetPaiementByInscriptionRetourneLePaiement`
- `testCreatePaiementRetourneInscriptionIntrouvable`
- `testCreatePaiementRetournePaiementDejaExistant`
- `testCreatePaiementRetourneMontantInvalide`
- `testCreatePaiementRetourneMethodeInvalide`
- `testCreatePaiementRetourneUnId`
- `testAnnulerPaiementRetournePaiementIntrouvable`
- `testAnnulerPaiementRetournePaiementDejaAnnule`
- `testAnnulerPaiementRetourneTrue`

### `tests/InscriptionRepositoryTest.php` — 2 tests ajoutés
- `testFindByIdRetourneLInscription`
- `testFindByIdRetourneNullSiInexistant`
