# Questions spécifiques — Module Site

---

## Modèle `models/Site.php`

**Q1. Quelles sont les propriétés du modèle `Site` ?**
`siteId` (?int), `nom` (string), `adresse` (string), `ville` (string).

**Q2. Pourquoi `siteId` est `?int` et pas `int` ?**
Parce qu'une nouvelle instance de `Site` créée avant le `INSERT` n'a pas encore d'ID. La base de données génère l'ID automatiquement (`AUTO_INCREMENT`). On passe `null` au constructeur, et après `insert()` on récupère l'ID via `lastInsertId()`.

**Q3. Le modèle `Site` fait-il du SQL ?**
Non. Un modèle ne fait jamais de SQL. Il stocke uniquement les données et expose des getters/setters. Tout le SQL est dans `SiteRepository`.

---

## Repository `repositories/SiteRepository.php`

**Q4. Quelles méthodes contient `SiteRepository` ?**
- `findAll()` → retourne tous les sites
- `findById(int $id)` → retourne un site ou `null`
- `insert(Site $site)` → insère un site, retourne son ID
- `update(Site $site)` → met à jour un site existant
- `delete(int $id)` → supprime un site

**Q5. Que retourne `findById()` si le site n'existe pas ?**
`null`. Le type de retour est `?Site`. Le service vérifie ensuite `if ($site === null)` pour retourner une erreur 404.

**Q6. Comment fonctionne la méthode `hydrate()` dans `SiteRepository` ?**
Elle prend un tableau de lignes SQL brutes (tableaux associatifs) et retourne un tableau d'objets `Site`. Elle utilise `array_map` avec une arrow function :
```php
return array_map(fn($row) => $this->hydrateOne($row), $rows);
```

**Q7. Que fait `hydrateOne()` ?**
Elle transforme une seule ligne SQL en objet `Site` :
```php
return new Site(
    (int)    $row['Site_ID'],
    (string) $row['Nom'],
    (string) $row['Adresse'],
    (string) $row['Ville']
);
```
Les cast explicites (`(int)`, `(string)`) garantissent les bons types PHP même si PDO retourne des strings.

**Q8. Pourquoi utiliser `PDO::FETCH_ASSOC` dans les repositories ?**
Pour récupérer chaque ligne sous forme de tableau associatif (`['Site_ID' => 1, 'Nom' => 'Tennis Club', ...]`). On hydrate ensuite manuellement nos objets PHP. C'est plus explicite que `FETCH_OBJ` et on contrôle exactement ce qui est créé.

**Q9. Pourquoi `delete()` prend un `int $id` et pas un objet `Site` ?**
Pour supprimer, on n'a besoin que de l'ID — pas des autres données. Passer un objet entier juste pour son ID serait inutile. La méthode est plus simple et plus claire avec juste l'ID.

---

## Service `services/SiteService.php`

**Q10. Quelles méthodes contient `SiteService` ?**
- `getAllSites()` → retourne tous les sites
- `getSiteById(int $id)` → retourne un site ou `'site_introuvable'`
- `createSite(array $data)` → crée un site, retourne son ID ou une erreur
- `updateSite(int $id, array $data)` → met à jour, retourne `true` ou une erreur
- `deleteSite(int $id)` → supprime, retourne `true` ou `'site_introuvable'`

**Q11. Quel type de retour a `createSite()` ? Pourquoi ?**
`int|string` — retourne l'ID (int) si succès, ou une string d'erreur si la validation échoue. C'est le pattern utilisé dans tout le projet pour éviter les exceptions dans les cas d'erreur "normaux".

**Q12. Quelles règles valide `createSite()` ?**
Les champs `nom`, `adresse`, `ville` sont obligatoires. Si l'un est absent ou vide, le service retourne `'champs_manquants'`.

---

## Controller `controllers/SiteController.php`

**Q13. Quelles routes expose `SiteController` ?**
| Méthode | Route | Description |
|---|---|---|
| GET | `/api/sites` | Liste tous les sites |
| GET | `/api/sites/{id}` | Retourne un site |
| POST | `/api/sites` | Crée un site |
| PUT | `/api/sites/{id}` | Met à jour un site |
| DELETE | `/api/sites/{id}` | Supprime un site |

**Q14. Pourquoi le controller appelle `header('Content-Type: application/json')` avant chaque réponse ?**
Pour indiquer au client que la réponse est du JSON. Sans ce header, le navigateur ou l'application cliente peut interpréter la réponse comme du HTML.

**Q15. Que retourne le controller si `getSiteById()` reçoit un ID inexistant ?**
HTTP 404 avec :
```json
{ "erreur": "Site 99 introuvable" }
```

---

## Tests `tests/SiteRepositoryTest.php`

**Q16. Pourquoi utiliser SQLite en mémoire dans les tests de Repository ?**
SQLite est une base embarquée qui fonctionne sans installation. La base est créée et détruite automatiquement à chaque test via `setUp()`. Les tests sont rapides, isolés, et fonctionnent sur n'importe quelle machine sans MySQL.

**Q17. Que fait `setUp()` dans `SiteRepositoryTest` ?**
Crée une base SQLite en mémoire, crée la table `Sites`, insère 2 sites de test, instancie `SiteRepository` avec ce PDO. Exécuté avant chaque test pour garantir un état propre.

**Q18. Comment tester que `findById()` retourne `null` pour un ID inexistant ?**
```php
$this->assertNull($this->repository->findById(999));
```
