# Questions spécifiques — Module Terrain

---

## Modèle `models/Terrain.php`

**Q1. Quelles sont les propriétés du modèle `Terrain` ?**
`terrainId` (?int), `siteId` (int), `nom` (string), `estActif` (bool).

**Q2. Pourquoi `Terrain` a une propriété `siteId` et non un objet `Site` ?**
On stocke uniquement la clé étrangère (l'ID). Stocker un objet `Site` entier dans le modèle `Terrain` serait du chargement eager non contrôlé. Si on a besoin du site, on le charge séparément via `SiteRepository`.

**Q3. À quoi sert `estActif` dans `Terrain` ?**
C'est le soft delete : au lieu de supprimer physiquement un terrain, on le marque inactif (`Est_Actif = 0`). Les réservations passées liées à ce terrain restent intactes. On vérifie `estActif` dans `ReservationService` avant de créer une réservation.

**Q4. Pourquoi `terrainId` est `?int` ?**
Avant le `INSERT`, l'ID n'existe pas encore — il est généré par `AUTO_INCREMENT`. On passe `null` au constructeur. Après `insert()`, `lastInsertId()` retourne l'ID réel.

---

## Repository `repositories/TerrainRepository.php`

**Q5. Quelles méthodes contient `TerrainRepository` ?**
- `findAll()` → retourne tous les terrains
- `findById(int $id)` → retourne un terrain ou `null`
- `findBySite(int $siteId)` → retourne les terrains d'un site
- `insert(Terrain $terrain)` → insère, retourne l'ID
- `update(Terrain $terrain)` → met à jour
- `delete(int $id)` → soft delete (passe `Est_Actif = 0`)

**Q6. Comment fonctionne le soft delete dans `delete()` ?**
Au lieu d'un `DELETE FROM`, on fait un `UPDATE` :
```sql
UPDATE Terrains SET Est_Actif = 0 WHERE Terrain_ID = :id
```
Le terrain reste en base mais est marqué inactif. Les réservations existantes qui le référencent ne sont pas cassées.

**Q7. Que retourne `findBySite()` si le site n'a aucun terrain ?**
Un tableau vide `[]`. `findAll()` et `findBySite()` retournent toujours un tableau — jamais `null`. Le controller peut alors encoder `[]` en JSON sans vérification supplémentaire.

**Q8. Quelle est la différence entre `findAll()` et `findBySite()` ?**
- `findAll()` : `SELECT * FROM Terrains` — retourne tous les terrains de tous les sites.
- `findBySite($siteId)` : `SELECT * FROM Terrains WHERE Site_ID = :siteId` — retourne uniquement les terrains d'un site spécifique.

---

## Service `services/TerrainService.php`

**Q9. Pourquoi `TerrainService` utilise à la fois `TerrainRepository` et `SiteRepository` ?**
Pour vérifier que le site existe avant de créer un terrain. Si on crée un terrain avec un `siteId` inexistant, on violerait la contrainte de clé étrangère en base. Le service vérifie d'abord via `SiteRepository::findById()`.

**Q10. Quelles règles valide `createTerrain()` ?**
1. Le champ `nom` est obligatoire (`champs_manquants`)
2. Le `site_id` doit correspondre à un site existant (`site_introuvable`)

**Q11. Que retourne `deleteTerrain()` si le terrain n'existe pas ?**
La string `'terrain_introuvable'`. Le controller traduit ça en HTTP 404.

**Q12. Pourquoi `estActif` vaut `true` par défaut à la création ?**
Un terrain nouvellement créé est actif par définition. L'inactivité est un état exceptionnel (terrain hors service). La valeur par défaut évite d'avoir à passer `true` explicitement à chaque création.

---

## Controller `controllers/TerrainController.php`

**Q13. Quelles routes expose `TerrainController` ?**
| Méthode | Route | Description |
|---|---|---|
| GET | `/api/terrains` | Liste tous les terrains |
| GET | `/api/terrains/{id}` | Retourne un terrain |
| GET | `/api/sites/{id}/terrains` | Liste les terrains d'un site |
| POST | `/api/terrains` | Crée un terrain |
| PUT | `/api/terrains/{id}` | Met à jour un terrain |
| DELETE | `/api/terrains/{id}` | Désactive un terrain (soft delete) |

**Q14. Que se passe-t-il si on appelle `DELETE /api/terrains/1` alors qu'il a des réservations actives ?**
Dans le code actuel, le terrain est simplement marqué inactif (`Est_Actif = 0`). Les réservations existantes ne sont pas affectées. La règle qui empêche de créer une réservation sur un terrain inactif est dans `ReservationService`.

**Q15. Quel code HTTP retourne le controller si le site n'existe pas lors d'une création de terrain ?**
HTTP 404 avec `{"erreur": "Site X introuvable"}`.

---

## Tests `tests/TerrainRepositoryTest.php`

**Q16. Comment tester le soft delete dans `TerrainRepositoryTest` ?**
```php
$this->repository->delete(1);
$terrain = $this->repository->findById(1);
$this->assertFalse($terrain->isEstActif());
```
Après `delete()`, le terrain existe encore en base mais `isEstActif()` retourne `false`.

**Q17. Que vérifie `testFindBySiteRetourneLesTerrains()` ?**
Qu'après avoir inséré 2 terrains pour le site 1 et 1 terrain pour le site 2, `findBySite(1)` retourne exactement 2 terrains et `findBySite(2)` retourne 1 terrain.

**Q18. Pourquoi tester `findBySite()` avec un `siteId` inexistant ?**
Pour vérifier que la méthode retourne `[]` et non `null` ou une erreur. Un tableau vide est une réponse valide et attendue — le controller doit pouvoir l'encoder en JSON sans planter.
