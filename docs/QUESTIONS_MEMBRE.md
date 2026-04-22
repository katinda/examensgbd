# Questions spécifiques — Module Membre

---

## Modèle `models/Membre.php`

**Q1. Quelles sont les propriétés du modèle `Membre` ?**
`membreId` (?int), `nom` (string), `prenom` (string), `email` (string), `motDePasse` (string), `estActif` (bool).

**Q2. Pourquoi `estActif` est un `bool` et pas un `int` ?**
En PHP, `bool` est le type sémantiquement correct pour une valeur vrai/faux. PDO retourne `0` ou `1` depuis MySQL — on le caste en `bool` dans `hydrateOne()` avec `(bool) $row['Est_Actif']`. Le modèle n'expose que du PHP propre, pas des artefacts SQL.

**Q3. Pourquoi ne pas supprimer physiquement un membre de la base ?**
D'autres tables référencent le membre (Inscriptions, Réservations via l'organisateur). Supprimer le membre casserait les contraintes de clé étrangère et ferait perdre l'historique des réservations passées. On désactive (`Est_Actif = 0`) — c'est le pattern soft delete.

**Q4. Pourquoi `motDePasse` est une propriété du modèle ?**
Le modèle reflète la structure de la table. En production, `motDePasse` contiendrait un hash (bcrypt) — jamais le mot de passe en clair. Dans ce projet d'école, c'est simplifié mais la structure est là.

---

## Repository `repositories/MembreRepository.php`

**Q5. Quelles méthodes contient `MembreRepository` ?**
- `findAll()` → retourne tous les membres
- `findById(int $id)` → retourne un membre ou `null`
- `findByEmail(string $email)` → retourne un membre ou `null` (utile pour vérifier les doublons)
- `insert(Membre $membre)` → insère, retourne l'ID
- `update(Membre $membre)` → met à jour
- `delete(int $id)` → soft delete (`Est_Actif = 0`)

**Q6. Pourquoi avoir `findByEmail()` en plus de `findById()` ?**
Pour détecter les doublons lors de la création d'un compte : un email doit être unique. Avant d'insérer un nouveau membre, le service appelle `findByEmail()` — si ça retourne un objet, l'email est déjà pris.

**Q7. Comment fonctionne le soft delete dans `delete()` de `MembreRepository` ?**
```sql
UPDATE Membres SET Est_Actif = 0 WHERE Membre_ID = :id
```
Le membre reste en base avec toutes ses données. Ses inscriptions et réservations passées sont préservées. Il ne peut plus participer à de nouvelles réservations car `InscriptionService` vérifie `isEstActif()`.

**Q8. Que retourne `findById()` pour un membre inactif ?**
L'objet `Membre` complet avec `estActif = false`. Le repository ne filtre pas sur `Est_Actif` — il retourne tout. C'est le service qui décide quoi faire selon l'état `estActif`.

---

## Service `services/MembreService.php`

**Q9. Quelles règles valide `createMembre()` ?**
1. Les champs `nom`, `prenom`, `email`, `mot_de_passe` sont obligatoires (`champs_manquants`)
2. L'email ne doit pas déjà exister en base (`email_deja_utilise`)

**Q10. Pourquoi vérifier l'email dans le service et pas uniquement via une contrainte UNIQUE en base ?**
La contrainte UNIQUE en base est le filet de sécurité final, mais elle lèverait une exception PDO brute. Le service vérifie en amont pour retourner un message d'erreur clair (`'email_deja_utilise'`) que le controller peut transformer en JSON lisible.

**Q11. Que retourne `deleteMembre()` si le membre n'existe pas ?**
La string `'membre_introuvable'`. Le controller retourne alors HTTP 404.

**Q12. Que fait `updateMembre()` si le membre n'existe pas ?**
Il retourne `'membre_introuvable'`. S'il existe, il met à jour et retourne `true`.

**Q13. Pourquoi `InscriptionService` vérifie `isEstActif()` et non juste `!== null` ?**
`findById()` retourne l'objet même pour un membre inactif. La double condition :
```php
if ($membre === null || !$membre->isEstActif()) {
    return 'membre_introuvable';
}
```
traite un membre inactif exactement comme un membre inexistant — il ne peut pas s'inscrire.

---

## Controller `controllers/MembreController.php`

**Q14. Quelles routes expose `MembreController` ?**
| Méthode | Route | Description |
|---|---|---|
| GET | `/api/membres` | Liste tous les membres |
| GET | `/api/membres/{id}` | Retourne un membre |
| POST | `/api/membres` | Crée un membre |
| PUT | `/api/membres/{id}` | Met à jour un membre |
| DELETE | `/api/membres/{id}` | Désactive un membre (soft delete) |

**Q15. Que retourne le controller pour `POST /api/membres` si l'email est déjà utilisé ?**
HTTP 409 (Conflict) avec :
```json
{ "erreur": "Cet email est déjà utilisé" }
```

**Q16. Pourquoi HTTP 409 pour un email déjà utilisé et pas 400 ?**
400 (Bad Request) signifie que la requête est malformée (champ manquant, mauvais format). 409 (Conflict) signifie que la requête est valide mais entre en conflit avec l'état actuel du serveur (l'email existe déjà). La distinction aide le client à comprendre exactement ce qui s'est passé.

---

## Tests

**Q17. Comment tester que `findByEmail()` retourne `null` si l'email n'existe pas ?**
```php
$this->assertNull($this->repository->findByEmail('inexistant@test.com'));
```

**Q18. Comment simuler un email déjà pris dans `MembreServiceTest` ?**
```php
$membreExistant = new Membre(1, 'Doe', 'John', 'john@test.com', 'hash', true);
$membreRepo = $this->createStub(MembreRepository::class);
$membreRepo->method('findByEmail')->willReturn($membreExistant);
```
Le stub retourne un membre existant, donc le service détecte le doublon et retourne `'email_deja_utilise'`.

**Q19. Que teste `testDeleteDesactiveLeMembreEtNeLeSupprimePas()` ?**
Après `delete($id)`, on vérifie que `findById($id)` retourne toujours l'objet (non supprimé), et que `isEstActif()` retourne `false` (désactivé).

**Q20. Pourquoi tester séparément `membre_introuvable` et `email_deja_utilise` dans `MembreServiceTest` ?**
Ce sont deux règles indépendantes. Si on testait les deux ensemble, on ne saurait pas laquelle échoue en cas d'erreur. Un test = un comportement vérifié.
