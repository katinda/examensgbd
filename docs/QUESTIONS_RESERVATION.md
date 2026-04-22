# Questions spécifiques — Module Reservation

---

## Modèle `models/Reservation.php`

**Q1. Quelles sont les propriétés du modèle `Reservation` ?**
`reservationId` (?int), `terrainId` (int), `organisateurId` (int), `date` (string), `heureDebut` (string), `heureFin` (string), `estPublique` (bool).

**Q2. `heureFin` est-elle calculée ou stockée en base ?**
Les deux : elle est calculée dans `ReservationService::createReservation()` (heureDebut + 1h30), puis la valeur calculée est stockée en base. Le modèle reçoit la valeur finale via son constructeur — il ne calcule rien.

**Q3. Pourquoi `organisateurId` et non un objet `Membre` dans le modèle ?**
On stocke uniquement la clé étrangère. Charger l'objet `Membre` complet dans le modèle `Reservation` serait du chargement eager non demandé. Si on a besoin des infos du membre, on les charge séparément via `MembreRepository`.

**Q4. À quoi sert `estPublique` ?**
Distingue les matchs privés (l'organisateur invite des joueurs spécifiques) des matchs publics (n'importe quel membre peut s'inscrire). Dans le code actuel, cette valeur est stockée mais la logique de restriction n'est pas encore implémentée.

---

## Repository `repositories/ReservationRepository.php`

**Q5. Quelles méthodes contient `ReservationRepository` ?**
- `findAll()` → toutes les réservations
- `findById(int $id)` → une réservation ou `null`
- `findByTerrain(int $terrainId)` → réservations d'un terrain
- `findByMembre(int $membreId)` → réservations d'un organisateur
- `insert(Reservation $reservation)` → insère, retourne l'ID
- `update(Reservation $reservation)` → met à jour
- `delete(int $id)` → supprime physiquement

**Q6. Pourquoi `delete()` dans `ReservationRepository` est une vraie suppression (pas un soft delete) ?**
Une réservation annulée n'a pas besoin d'historique dans ce projet. Contrairement aux membres et terrains qui ont des relations avec d'autres tables, supprimer une réservation et ses inscriptions associées (via `CASCADE`) est acceptable ici.

**Q7. Comment `hydrateOne()` reconstruit un objet `Reservation` depuis une ligne SQL ?**
```php
return new Reservation(
    (int)    $row['Reservation_ID'],
    (int)    $row['Terrain_ID'],
    (int)    $row['Organisateur_ID'],
    (string) $row['Date'],
    (string) $row['Heure_Debut'],
    (string) $row['Heure_Fin'],
    (bool)   $row['Est_Publique']
);
```

---

## Service `services/ReservationService.php`

**Q8. Quels paramètres reçoit le constructeur de `ReservationService` ?**
```php
public function __construct(
    private ReservationRepository $reservationRepository,
    private TerrainRepository     $terrainRepository,
    private MembreRepository      $membreRepository,
    private InscriptionRepository $inscriptionRepository,
    private PDO                   $pdo
) {}
```
Il reçoit 5 dépendances : 4 repositories + le PDO pour la transaction.

**Q9. Pourquoi `ReservationService` a besoin du PDO directement ?**
Pour gérer la transaction qui englobe le `INSERT` de la réservation ET le `INSERT` de l'inscription de l'organisateur. La transaction est gérée au niveau du service, pas des repositories. Le même `$pdo` est partagé entre les deux repositories pour que `beginTransaction()` couvre les deux opérations.

**Q10. Comment `createReservation()` calcule-t-il `heureFin` ?**
```php
$debut = new DateTime($data['heure_debut']);
$debut->modify('+90 minutes');
$heureFin = $debut->format('H:i:s');
```
On crée un objet `DateTime`, on ajoute 90 minutes, on formate en `H:i:s`.

**Q11. Explique le bloc transaction dans `createReservation()` :**
```php
$this->pdo->beginTransaction();
try {
    $reservationId = $this->reservationRepository->insert($reservation);
    $inscription = new Inscription(null, $reservationId, (int) $data['organisateur_id'], true);
    $this->inscriptionRepository->insert($inscription);
    $this->pdo->commit();
    return $reservationId;
} catch (Exception $e) {
    $this->pdo->rollBack();
    throw $e;
}
```
- `beginTransaction()` : met les deux INSERT en attente
- Premier INSERT : crée la réservation, récupère son ID
- Deuxième INSERT : inscrit l'organisateur avec `Est_Organisateur = true`
- `commit()` : valide les deux en même temps
- `rollBack()` : si n'importe quelle étape échoue, annule tout — pas de réservation sans organisateur

**Q12. Quelles règles valide `createReservation()` avant d'insérer ?**
1. Les champs obligatoires sont présents (`champs_manquants`)
2. Le terrain existe et est actif (`terrain_introuvable`)
3. L'organisateur existe et est actif (`membre_introuvable`)

**Q13. Pourquoi vérifier que le terrain est actif (`isEstActif()`) avant de créer une réservation ?**
Un terrain désactivé (hors service) ne doit pas accepter de nouvelles réservations. Si on ne vérifiait pas, on créerait des réservations sur un terrain inutilisable. La règle est dans le service, pas dans le repository.

---

## Controller `controllers/ReservationController.php`

**Q14. Quelles routes expose `ReservationController` ?**
| Méthode | Route | Description |
|---|---|---|
| GET | `/api/reservations` | Liste toutes les réservations |
| GET | `/api/reservations/{id}` | Retourne une réservation |
| POST | `/api/reservations` | Crée une réservation |
| PUT | `/api/reservations/{id}` | Met à jour une réservation |
| DELETE | `/api/reservations/{id}` | Supprime une réservation |

**Q15. Que contient le body d'un `POST /api/reservations` ?**
```json
{
    "terrain_id": 1,
    "organisateur_id": 2,
    "date": "2025-06-15",
    "heure_debut": "10:00:00",
    "est_publique": true
}
```
`heure_fin` n'est pas envoyée — elle est calculée automatiquement par le service.

**Q16. Que retourne le controller si le terrain n'existe pas ?**
HTTP 404 avec `{"erreur": "Terrain X introuvable"}`.

---

## Tests

**Q17. Pourquoi `ReservationServiceTest` a une méthode helper `creerPdo()` ?**
```php
private function creerPdo(): PDO {
    return new PDO('sqlite::memory:');
}
```
Le constructeur de `ReservationService` requiert un objet PDO (pour les transactions). Dans les tests de service, on passe un PDO SQLite en mémoire — on ne teste pas la transaction elle-même mais la logique de validation.

**Q18. Après la modification du constructeur de `ReservationService` (ajout de `InscriptionRepository` et `PDO`), comment les tests existants ont-ils été corrigés ?**
On a ajouté les deux arguments manquants à chaque instanciation de `ReservationService` dans les tests :
```php
$service = new ReservationService(
    $this->createStub(ReservationRepository::class),
    $this->createStub(TerrainRepository::class),
    $this->createStub(MembreRepository::class),
    $this->createStub(InscriptionRepository::class), // ajouté
    $this->creerPdo()                                // ajouté
);
```

**Q19. Comment tester que `heureFin` est bien calculée à `heureDebut + 1h30` ?**
On appelle `createReservation()` avec `heure_debut = '10:00:00'` et on vérifie que la réservation insérée en base a `heure_fin = '11:30:00'`. Dans un test de repository, on peut vérifier via `findById()` après le `INSERT`.

**Q20. Combien de tests contient `ReservationServiceTest` ? Que testent-ils ?**
9 tests :
- `testCreateReservationRetourneChamsManquants`
- `testCreateReservationRetourneTerrainIntrouvable`
- `testCreateReservationRetourneTerrainInactif`
- `testCreateReservationRetourneMembreIntrouvable`
- `testCreateReservationRetourneMembreInactif`
- `testCreateReservationRetourneUnId`
- `testGetReservationByIdRetourneLaReservation`
- `testGetReservationByIdRetourneIntrouvable`
- `testDeleteReservationRetourneIntrouvable`
