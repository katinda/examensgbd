# Questions spécifiques — Module Inscription

---

## Modèle `models/Inscription.php`

**Q1. Quelles sont les propriétés du modèle `Inscription` ?**
`inscriptionId` (?int), `reservationId` (int), `membreId` (int), `estOrganisateur` (bool).

**Q2. Pourquoi `inscriptionId` est `?int` ?**
Avant le `INSERT`, l'inscription n'a pas encore d'ID — c'est la base qui le génère via `AUTO_INCREMENT`. On passe `null` au constructeur. Exemple :
```php
$inscription = new Inscription(null, $reservationId, $membreId, false);
$id = $this->inscriptionRepository->insert($inscription); // id généré ici
```

**Q3. Pourquoi `estOrganisateur` vaut `false` par défaut dans le constructeur ?**
Dans la majorité des cas, on crée des inscriptions pour des joueurs normaux. Seul `ReservationService` passe `true` (pour l'organisateur). La valeur par défaut évite de devoir passer `false` explicitement à chaque appel normal.

**Q4. Le modèle `Inscription` fait-il du SQL ou de la validation ?**
Non. Il stocke uniquement les données et expose des getters. Tout le SQL est dans `InscriptionRepository`, toute la logique métier est dans `InscriptionService`.

---

## Repository `repositories/InscriptionRepository.php`

**Q5. Quelles sont les 5 méthodes de `InscriptionRepository` ?**
- `findByReservation(int $reservationId): array` → tous les joueurs d'une réservation
- `findByReservationAndMembre(int $reservationId, int $membreId): ?Inscription` → un joueur précis ou null
- `countByReservation(int $reservationId): int` → nombre de joueurs inscrits
- `insert(Inscription $inscription): int` → crée l'inscription, retourne l'ID
- `delete(int $reservationId, int $membreId): void` → supprime une inscription

**Q6. Pourquoi `delete()` prend deux paramètres au lieu de l'`Inscription_ID` ?**
Dans les routes HTTP, on identifie une inscription par la combinaison `(reservationId, membreId)` — c'est la contrainte UNIQUE de la table. On n'expose pas l'`Inscription_ID` dans les URLs du projet. La route est :
```
DELETE /api/reservations/{id}/inscriptions/{membreId}
```

**Q7. Comment fonctionne `findByReservationAndMembre()` ?**
```php
$stmt = $this->pdo->prepare("
    SELECT * FROM Inscriptions
    WHERE Reservation_ID = :reservationId
      AND Membre_ID       = :membreId
");
$stmt->execute([':reservationId' => $reservationId, ':membreId' => $membreId]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
return $row ? $this->hydrateOne($row) : null;
```
Si la ligne existe → retourne un objet `Inscription`. Sinon → retourne `null` (convertit le `false` PDO en `null`).

**Q8. Pourquoi avoir `countByReservation()` séparément au lieu de faire `count(findByReservation())` ?**
`SELECT COUNT(*)` est une requête SQL optimisée — la base ne charge pas toutes les lignes, elle compte directement. `count(findByReservation())` chargerait tous les objets `Inscription` en mémoire juste pour les compter. C'est inefficace surtout si on a beaucoup d'inscriptions.

**Q9. Que retourne `insert()` après avoir inséré une inscription ?**
L'ID auto-généré par la base :
```php
return (int) $this->pdo->lastInsertId();
```
Cet ID est ensuite retourné par `InscriptionService::addJoueur()` et renvoyé dans la réponse JSON avec le code 201.

---

## Service `services/InscriptionService.php`

**Q10. Quels sont les 3 repositories utilisés par `InscriptionService` ? Pourquoi chacun ?**
- `InscriptionRepository` : pour créer, supprimer, compter, et chercher des inscriptions
- `ReservationRepository` : pour vérifier que la réservation existe (règle 1)
- `MembreRepository` : pour vérifier que le membre existe et est actif (règle 2)

**Q11. Cite les 4 règles de validation de `addJoueur()` dans l'ordre.**
```
1. reservation_introuvable → 404  (la réservation n'existe pas)
2. membre_introuvable      → 404  (le membre n'existe pas ou est inactif)
3. reservation_complete    → 409  (déjà 4 joueurs inscrits)
4. deja_inscrit            → 409  (ce membre est déjà inscrit à cette réservation)
```

**Q12. Pourquoi vérifier `reservation_complete` AVANT `deja_inscrit` ?**
Si la réservation est complète (4 joueurs), peu importe si le membre est déjà inscrit ou non — la réponse est la même : inscription impossible. Vérifier `reservation_complete` en premier court-circuite la requête `findByReservationAndMembre()` inutile.

**Q13. Que retourne `addJoueur()` si tout va bien ?**
Un `int` : l'ID de la nouvelle inscription. Type de retour déclaré : `int|string`.

**Q14. Comment `removeJoueur()` vérifie-t-il que le joueur est inscrit ?**
```php
$inscription = $this->inscriptionRepository->findByReservationAndMembre($reservationId, $membreId);
if ($inscription === null) {
    return false;
}
$this->inscriptionRepository->delete($reservationId, $membreId);
return true;
```
Si `findByReservationAndMembre()` retourne `null` → joueur non inscrit → retourne `false`. Sinon → supprime et retourne `true`.

**Q15. Que retourne `getInscriptionsByReservation()` si personne n'est inscrit ?**
Un tableau vide `[]`. `findByReservation()` retourne toujours un tableau — jamais `null`. Le controller encode `[]` en JSON sans vérification.

---

## Controller `controllers/InscriptionController.php`

**Q16. Quelles routes expose `InscriptionController` ?**
| Méthode | Route | Description |
|---|---|---|
| GET | `/api/reservations/{id}/inscriptions` | Liste les joueurs inscrits |
| POST | `/api/reservations/{id}/inscriptions` | Inscrit un joueur |
| DELETE | `/api/reservations/{id}/inscriptions/{membreId}` | Retire un joueur |

**Q17. Que contient le body d'un `POST /api/reservations/1/inscriptions` ?**
```json
{ "membre_id": 3 }
```
Seul `membre_id` est requis. La réservation est identifiée via l'URL.

**Q18. Que retourne le controller en cas de succès d'une inscription ?**
HTTP 201 avec :
```json
{ "message": "Joueur inscrit avec succès", "id": 5 }
```
L'`id` est l'ID de la nouvelle inscription générée en base.

**Q19. Comment le controller gère-t-il les 4 cas d'erreur de `addJoueur()` ?**
Via `match()` sur la valeur retournée par le service :
```php
match ($result) {
    'reservation_introuvable' => http_response_code(404) + JSON,
    'membre_introuvable'      => http_response_code(404) + JSON,
    'reservation_complete'    => http_response_code(409) + JSON,
    'deja_inscrit'            => http_response_code(409) + JSON,
    default                   => http_response_code(201) + JSON,
};
```

**Q20. Que fait le controller si `membre_id` est absent du body ?**
HTTP 400 avec :
```json
{ "erreur": "Le champ \"membre_id\" est obligatoire" }
```
Cette validation se fait dans le controller — pas dans le service — car c'est une validation de l'entrée HTTP (champ manquant), pas une règle métier.

---

## Tests `tests/InscriptionRepositoryTest.php` — 7 tests

**Q21. Que contient la base SQLite de test avant chaque test ?**
```sql
INSERT INTO Inscriptions (Reservation_ID, Membre_ID, Est_Organisateur)
VALUES (1, 1, 1),  -- organisateur
       (1, 2, 0);  -- joueur invité
```
2 inscriptions pour la réservation 1.

**Q22. Que vérifie `testFindByReservationAndMembreRetourneLInscription()` ?**
```php
$inscription = $this->repository->findByReservationAndMembre(1, 1);
$this->assertNotNull($inscription);
$this->assertTrue($inscription->isEstOrganisateur());
```
Que le membre 1 est bien inscrit à la réservation 1, ET qu'il est bien l'organisateur.

**Q23. Que vérifie `testInsertAjouteUneInscription()` ?**
```php
$inscription = new Inscription(null, 1, 3, false);
$id = $this->repository->insert($inscription);
$this->assertGreaterThan(0, $id);
$this->assertEquals(3, $this->repository->countByReservation(1));
```
Après insertion, l'ID retourné est positif ET le compte passe bien de 2 à 3.

---

## Tests `tests/InscriptionServiceTest.php` — 8 tests

**Q24. Comment simuler que la réservation n'existe pas dans `testAddJoueurRetourneReservationIntrouvable()` ?**
```php
$reservationRepo = $this->createStub(ReservationRepository::class);
$reservationRepo->method('findById')->willReturn(null);
```
Le stub retourne `null` → le service retourne `'reservation_introuvable'`.

**Q25. Comment simuler une réservation déjà complète dans `testAddJoueurRetourneReservationComplete()` ?**
```php
$inscriptionRepo = $this->createStub(InscriptionRepository::class);
$inscriptionRepo->method('countByReservation')->willReturn(4);
```
Le stub retourne 4 → le service détecte `>= 4` → retourne `'reservation_complete'`.

**Q26. Pourquoi les tests de `InscriptionService` n'utilisent pas de base SQLite ?**
Parce qu'on teste uniquement la logique du service (les règles métier), pas le SQL. Les repositories sont remplacés par des stubs qui retournent des valeurs prédéfinies. Cela isole le service de toute infrastructure.
