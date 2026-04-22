# Réponses — Questions d'oral PadelManager

---

## Architecture générale

**1. Décris l'architecture en couches de ton projet.**
Le projet suit l'architecture MVC adaptée en 4 couches :
- **Model** : stocke les données (ex: `Inscription.php`). Pas de SQL, juste des propriétés et getters.
- **Repository** : gère tout le SQL d'une table. Reçoit PDO en injection de dépendance.
- **Service** : contient la logique métier. Utilise les repositories. Ne fait pas de SQL directement.
- **Controller** : reçoit la requête HTTP, appelle le service, retourne du JSON.

**2. Pourquoi séparer le Repository du Service ?**
Le Repository gère uniquement le SQL. Le Service gère la logique métier (règles de validation, conditions). Si on change de base de données, on ne touche qu'aux repositories. Si on change les règles métier, on ne touche qu'aux services. Chaque couche a une seule responsabilité (principe SRP).

**3. Qu'est-ce que l'injection de dépendance ? Donne un exemple.**
Au lieu de créer les objets à l'intérieur d'une classe, on les reçoit en paramètre du constructeur. Exemple : `InscriptionService` reçoit ses 3 repositories en paramètre :
```php
public function __construct(
    private InscriptionRepository $inscriptionRepository,
    private ReservationRepository $reservationRepository,
    private MembreRepository      $membreRepository
) {}
```
Avantage : plus facile à tester (on injecte des stubs à la place des vrais objets).

**4. Pourquoi le Controller ne fait jamais de SQL directement ?**
Le controller a une seule responsabilité : recevoir la requête HTTP et retourner une réponse JSON. Si on mélange SQL et HTTP dans le même fichier, le code devient difficile à maintenir et à tester. La séparation des responsabilités rend le code plus lisible et modulaire.

**5. Qu'est-ce qu'un singleton ? Où l'utilises-tu ?**
Un singleton est une classe qui ne peut être instanciée qu'une seule fois. Dans le projet, `Database::getConnection()` retourne toujours la même instance PDO. Ainsi, tous les repositories partagent la même connexion à la base de données.

**6. Pourquoi utilises-tu PDO plutôt que mysqli ?**
PDO (PHP Data Objects) est une abstraction qui supporte plusieurs bases de données (MySQL, SQLite, PostgreSQL...). Dans le projet, les tests utilisent SQLite en mémoire et la production utilise MySQL — le même code Repository fonctionne dans les deux cas grâce à PDO.

**7. Qu'est-ce que `PDO::ATTR_ERRMODE` et pourquoi `PDO::ERRMODE_EXCEPTION` ?**
`ATTR_ERRMODE` configure comment PDO signale les erreurs. Avec `ERRMODE_EXCEPTION`, PDO lance une exception PHP en cas d'erreur SQL. Sans ça, les erreurs sont silencieuses et difficiles à détecter. Ça permet aussi d'utiliser `try/catch` pour gérer les transactions.

**8. Comment fonctionne le routeur dans `index.php` ?**
Il récupère l'URI et la méthode HTTP, découpe l'URI avec `explode('/')`, puis compare les segments avec des conditions `if`. Par exemple, si l'URI a 4 segments et le 3e est `reservations` et le 5e est `inscriptions`, il appelle le bon controller. Les routes plus spécifiques sont définies avant les routes générales.

**9. Pourquoi les `require_once` sont dans `index.php` et pas dans chaque fichier ?**
`index.php` est le point d'entrée unique de l'API. Il charge tous les fichiers nécessaires une seule fois. Si chaque fichier chargeait ses dépendances lui-même, on risquerait des doublons ou des erreurs d'ordre de chargement. `require_once` garantit qu'un fichier n'est chargé qu'une seule fois même si plusieurs fichiers essaient de le charger.

**10. Qu'est-ce que `PDO::FETCH_ASSOC` ? Pourquoi l'utiliser ?**
`FETCH_ASSOC` retourne chaque ligne de résultat sous forme de tableau associatif (`['Inscription_ID' => 1, ...]`). On l'utilise plutôt que `FETCH_OBJ` parce qu'on hydrate manuellement nos propres objets PHP (modèles) dans la méthode `hydrateOne()`. C'est plus explicite et on contrôle exactement ce qui est créé.

---

## PHP 8 — Syntaxe et fonctionnalités

**11. C'est quoi le "constructor property promotion" en PHP 8 ?**
C'est une syntaxe qui permet de déclarer et initialiser une propriété directement dans les paramètres du constructeur avec `private`, `protected` ou `public`. Au lieu de :
```php
private InscriptionRepository $repo;
public function __construct(InscriptionRepository $repo) { $this->repo = $repo; }
```
On écrit :
```php
public function __construct(private InscriptionRepository $repo) {}
```

**12. À quoi sert le type `?int` (nullable) ?**
Le `?` signifie que la valeur peut être du type indiqué OU `null`. Dans `Inscription`, `?int $inscriptionId` permet de passer `null` pour une nouvelle inscription (avant qu'elle soit enregistrée en base et reçoive un ID auto-généré).

**13. Qu'est-ce qu'un type de retour `int|string` (union type) ?**
PHP 8 permet de déclarer plusieurs types possibles pour un retour. Dans `addJoueur()`, la méthode retourne un `int` (l'ID de la nouvelle inscription) si tout va bien, ou une `string` décrivant l'erreur (`'reservation_introuvable'`, `'deja_inscrit'`, etc.).

**14. C'est quoi une arrow function (`fn`) ?**
Une fonction fléchée est une syntaxe courte pour les fonctions anonymes simples. Elle capture automatiquement les variables du contexte parent. Exemple dans `hydrate()` :
```php
return array_map(fn($row) => $this->hydrateOne($row), $rows);
```
Équivalent à `function($row) use ($this) { return $this->hydrateOne($row); }`.

**15. À quoi sert l'expression `match()` ? Montre un exemple.**
`match()` est comme un `switch` mais retourne une valeur et utilise la comparaison stricte (`===`). Dans `InscriptionController::addJoueur()` :
```php
match ($result) {
    'reservation_introuvable' => (function() { http_response_code(404); ... })(),
    'deja_inscrit'            => (function() { http_response_code(409); ... })(),
    default                   => (function() use ($result) { http_response_code(201); ... })(),
};
```

**16. Quelle est la différence entre `match()` et `switch()` ?**
- `match()` utilise la comparaison stricte (`===`), `switch` utilise `==` (comparaison lâche).
- `match()` retourne une valeur, `switch` ne retourne rien.
- `match()` lève une `UnhandledMatchError` si aucun cas ne correspond (sans `default`), `switch` fait rien.
- Pas de `break` nécessaire dans `match()`.

**17. Qu'est-ce que le type `void` comme type de retour ?**
`void` signifie que la méthode ne retourne aucune valeur. Par exemple, `delete()` dans `InscriptionRepository` : elle exécute le SQL mais ne retourne rien. C'est une déclaration d'intention explicite.

**18. À quoi sert `readonly` en PHP 8.1 ? L'utilises-tu ?**
`readonly` empêche qu'une propriété soit modifiée après avoir été initialisée. Non utilisé dans ce projet — les modèles ont des setters qui permettent la modification. `readonly` serait utile si on voulait des objets immuables.

**19. Fonction fléchée vs closure anonyme ?**
La closure anonyme doit explicitement capturer les variables avec `use ($var)`. La fonction fléchée capture automatiquement toutes les variables du scope parent. La fonction fléchée ne peut contenir qu'une seule expression (pas de `{}`).

**20. Comment fonctionne `array_map()` avec une arrow function dans `hydrate()` ?**
`array_map()` applique une fonction à chaque élément d'un tableau et retourne un nouveau tableau. Dans `hydrate()` :
```php
return array_map(fn($row) => $this->hydrateOne($row), $rows);
```
Pour chaque ligne SQL brute (`$row`), elle crée un objet `Inscription` via `hydrateOne()`. Le résultat est un tableau d'objets `Inscription`.

**21. À quoi sert `$this` en PHP ?**
`$this` désigne l'instance courante de la classe — c'est une référence à l'objet sur lequel la méthode est appelée. Il permet d'accéder aux propriétés et méthodes de l'objet depuis l'intérieur de la classe. Exemples dans le projet :
```php
// Dans InscriptionRepository — accéder à la connexion PDO de l'objet
$stmt = $this->pdo->prepare("SELECT ...");

// Dans InscriptionService — appeler une méthode du même objet
return $this->inscriptionRepository->findByReservation($reservationId);

// Dans le modèle Inscription — retourner une propriété de l'objet
public function getMembreId(): int {
    return $this->membreId;
}
```
Sans `$this`, PHP ne saurait pas à quelle instance appartient la propriété ou la méthode. `$this` n'existe que dans les méthodes non-statiques.

**22. À quoi sert `isset()` ?**
`isset()` vérifie si une variable existe ET qu'elle n'est pas `null`. Elle retourne `true` si la variable est définie et non nulle, `false` sinon. Elle ne génère pas d'erreur si la variable n'existe pas (contrairement à `empty()` qui peut générer un warning dans certains contextes).

Dans le projet, on pourrait l'utiliser dans les controllers pour vérifier la présence d'un champ dans le body JSON :
```php
// Au lieu de :
if (empty($data['membre_id'])) { ... }

// On pourrait écrire :
if (!isset($data['membre_id'])) { ... }
```
Différence clé : `isset()` retourne `false` uniquement si la clé est absente ou `null`. `empty()` retourne `true` aussi pour `0`, `""`, `[]`, `false` — ce qui peut être trop restrictif si `0` est une valeur valide.

---

## Modèles (Models)

**21. Pourquoi les modèles ne contiennent aucun SQL ?**
Le modèle représente uniquement les données d'une entité. Le SQL appartient au Repository (couche d'accès aux données). Si le modèle faisait du SQL, on mélange deux responsabilités différentes. En séparant, on peut changer la structure SQL sans toucher au modèle.

**22. Qu'est-ce qu'un getter ? Pourquoi ne pas accéder directement aux propriétés ?**
Un getter est une méthode publique qui retourne la valeur d'une propriété privée. Exemple : `getMembreId()`. Les propriétés privées protègent l'état interne de l'objet. Les getters permettent de contrôler l'accès (on peut ajouter de la logique, de la validation) sans changer l'interface publique.

**23. Pourquoi `Inscription_ID` est `?int` dans le modèle ?**
Quand on crée une nouvelle inscription (avant `INSERT`), on n'a pas encore d'ID — c'est la base de données qui l'auto-génère. On passe `null` au constructeur. Après le `INSERT`, `lastInsertId()` retourne l'ID réel. Le `?int` permet de représenter cet état "pas encore persisté".

**24. Pourquoi `estOrganisateur` vaut `false` par défaut ?**
Dans 99% des cas, on crée une inscription pour un joueur normal (pas l'organisateur). Seul `ReservationService` crée une inscription avec `Est_Organisateur = true`. La valeur par défaut évite d'avoir à passer `false` explicitement à chaque appel normal.

**25. Pourquoi deux getters distincts `getInscriptionId()` et `getReservationId()` ?**
Ce sont deux données différentes : l'ID de l'inscription elle-même et l'ID de la réservation à laquelle elle appartient. Deux getters distincts rendent le code explicite. Confondre les deux serait une erreur difficile à déboguer.

**26. Pourquoi le modèle `Membre` a `estActif` au lieu de supprimer l'enregistrement ?**
C'est le pattern "soft delete". On ne supprime jamais physiquement un membre parce que d'autres tables (Inscriptions, Réservations) peuvent référencer ce membre. Supprimer physiquement casserait les contraintes de clé étrangère et ferait perdre l'historique.

**27. C'est quoi le soft delete ? Avantages vs suppression physique ?**
Le soft delete marque un enregistrement comme inactif (`Est_Actif = 0`) au lieu de le supprimer. Avantages : préserve l'historique, maintient l'intégrité référentielle, permet la réactivation. Inconvénient : les requêtes doivent toujours filtrer sur `Est_Actif = 1`.

**28. Dans `Reservation`, `heureFin` est-elle calculée ou stockée ?**
Elle est calculée dans `ReservationService::createReservation()` : `heureFin = heureDebut + 1h30`. La valeur calculée est ensuite stockée en base. Le modèle accepte les deux valeurs via le constructeur — c'est le service qui impose la règle métier de 1h30.

**29. Pourquoi le modèle `Terrain` a `estActif` ?**
Même raison que `Membre` : soft delete. Un terrain peut être mis hors service sans supprimer les réservations passées qui y sont liées. On vérifie `estActif` dans `ReservationService` avant de créer une réservation.

**30. Que retourne `isEstOrganisateur()` dans `Inscription` ?**
Un `bool` : `true` si ce membre est l'organisateur de la réservation (celui qui l'a créée), `false` s'il est un joueur invité ou inscrit librement. Cette information est utilisée dans les tests pour vérifier que l'auto-inscription de l'organisateur est correcte.

---

## Repositories

**31. Qu'est-ce que la méthode `hydrate()` ?**
`hydrate()` transforme un tableau de lignes SQL brutes (tableaux associatifs) en tableau d'objets PHP (modèles). Elle délègue à `hydrateOne()` pour chaque ligne. C'est le seul endroit où on crée des objets depuis des données SQL — centralisé et réutilisable.

**32. Pourquoi des requêtes préparées (`prepare` + `execute`) ?**
Les requêtes préparées séparent la structure SQL des données. Les valeurs sont envoyées séparément et jamais interprétées comme du SQL. C'est la protection principale contre les injections SQL. De plus, la requête compilée est plus performante si exécutée plusieurs fois.

**33. Qu'est-ce qu'une injection SQL ? Comment les requêtes préparées protègent-elles ?**
Une injection SQL consiste à insérer du code SQL malveillant dans une valeur utilisateur pour manipuler la requête. Exemple : `membre_id = "1 OR 1=1"`. Avec les requêtes préparées, PDO échappe automatiquement les valeurs — elles ne sont jamais interprétées comme du SQL, juste comme des données.

**34. Que retourne `findById()` si l'enregistrement n'existe pas ?**
`null`. Le type de retour est `?Objet` (nullable). Le service vérifie ensuite `if ($membre === null)` pour gérer ce cas. C'est plus propre que retourner `false` ou lancer une exception pour un cas normal (objet non trouvé).

**35. Pourquoi `countByReservation()` retourne un `int` et pas un tableau ?**
Parce qu'on a besoin d'un simple nombre pour comparer avec 4 (limite de joueurs). Retourner un tableau entier d'inscriptions juste pour les compter serait inefficace. `SELECT COUNT(*)` est une requête SQL optimisée qui retourne directement le nombre.

**36. Que fait `lastInsertId()` ? Où l'utilises-tu ?**
`lastInsertId()` retourne l'ID auto-généré par la base lors du dernier `INSERT`. Utilisé dans `insert()` de chaque repository (Membre, Terrain, Reservation, Inscription) pour retourner l'ID de la nouvelle ligne. Ce n'est valide que si la table a une colonne `AUTO_INCREMENT`.

**37. Différence entre `findByReservation()` et `findByReservationAndMembre()` ?**
- `findByReservation($reservationId)` retourne **toutes** les inscriptions d'une réservation (tableau).
- `findByReservationAndMembre($reservationId, $membreId)` retourne **une** inscription précise (objet ou null). Utilisée pour vérifier si un membre est déjà inscrit avant d'en ajouter un nouveau.

**38. Pourquoi `delete()` prend deux paramètres ?**
La clé primaire d'une inscription dans ce contexte est la combinaison `(Reservation_ID, Membre_ID)` — c'est la contrainte UNIQUE. On n'utilise pas l'`Inscription_ID` seul parce que dans les routes HTTP, on a l'ID de réservation et l'ID du membre (pas l'ID d'inscription).

**39. Pourquoi les repositories reçoivent `$pdo` en paramètre ?**
C'est l'injection de dépendance. Avantages : dans les tests, on injecte un PDO SQLite en mémoire au lieu du vrai MySQL. En production, on injecte le PDO MySQL. Le repository ne sait pas d'où vient la connexion — il l'utilise juste.

**40. Que retourne `fetch()` si aucune ligne n'est trouvée ?**
`fetch()` retourne `false` si aucune ligne n'est trouvée. C'est pourquoi dans `findByReservationAndMembre()` on écrit :
```php
$row = $stmt->fetch(PDO::FETCH_ASSOC);
return $row ? $this->hydrateOne($row) : null;
```
On convertit le `false` PDO en `null` pour respecter le type de retour `?Inscription`.

---

## Services

**41. Responsabilité d'un service vs un repository ?**
Le repository exécute du SQL sans aucune logique. Le service applique les règles métier : il valide les données, coordonne plusieurs repositories, décide quoi faire selon les résultats. Le service ne sait pas comment les données sont stockées — il délègue au repository.

**42. Quelles règles valide `addJoueur()` ? Cite-les toutes.**
Dans l'ordre :
1. `reservation_introuvable` → la réservation doit exister (404)
2. `membre_introuvable` → le membre doit exister et être actif (404)
3. `reservation_complete` → la réservation ne peut pas dépasser 4 joueurs (409)
4. `deja_inscrit` → un membre ne peut pas s'inscrire deux fois à la même réservation (409)

**43. Pourquoi vérifier `reservation_introuvable` avant `reservation_complete` ?**
L'ordre est logique : inutile de compter les inscrits si la réservation n'existe même pas. Si on inversait, `countByReservation(999)` retournerait 0 (pas d'inscrits pour une réservation inexistante) et on ne détecterait jamais que la réservation est introuvable. Les règles s'appliquent dans l'ordre du plus fondamental au plus spécifique.

**44. Que retourne `addJoueur()` ?**
- Un `int` (l'ID de la nouvelle inscription) si tout va bien.
- Une `string` décrivant l'erreur si une règle est violée : `'reservation_introuvable'`, `'membre_introuvable'`, `'reservation_complete'`, ou `'deja_inscrit'`.
Le controller lit le type du retour pour décider quel code HTTP envoyer.

**45. Pourquoi `removeJoueur()` retourne un `bool` et pas `void` ?**
Le controller doit savoir si la suppression a réussi ou non. Si le membre n'est pas inscrit, le controller retourne 404. Si le `bool` était `void`, on ne pourrait pas distinguer "supprimé avec succès" de "membre non trouvé". Le `bool` communique le résultat de l'opération.

**46. Qu'est-ce qu'une transaction PDO ? Pourquoi dans `createReservation()` ?**
Une transaction regroupe plusieurs opérations SQL en un bloc atomique : soit tout réussit, soit tout est annulé. Dans `createReservation()`, on crée d'abord la réservation puis l'inscription de l'organisateur. Si l'inscription échoue, `rollBack()` annule aussi la réservation — on n'a pas de réservation sans organisateur.

**47. Que se passe-t-il si l'insertion de l'inscription échoue ?**
Le `catch (Exception $e)` appelle `$this->pdo->rollBack()`, ce qui annule l'insertion de la réservation aussi. Puis l'exception est relancée (`throw $e`). La base reste dans un état cohérent — pas de réservation orpheline sans organisateur.

**48. Ordre de `beginTransaction()`, `commit()`, `rollBack()` ?**
1. `beginTransaction()` — démarre la transaction (tout ce qui suit est mis en attente)
2. Opérations SQL (`INSERT`, etc.)
3. `commit()` — valide toutes les opérations en une fois
4. `rollBack()` — annule tout si une erreur survient (dans le `catch`)

**48b. Explique ce bloc de code dans `ReservationService::createReservation()` :**
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

Ce bloc regroupe deux `INSERT` SQL dans une seule transaction atomique :

1. **`beginTransaction()`** — démarre la transaction. Les deux `INSERT` qui suivent sont mis en attente et ne sont pas encore écrits définitivement en base.

2. **Premier `INSERT`** — insère la réservation et récupère son ID auto-généré (`$reservationId`). Cet ID n'est utilisable que parce qu'on est dans la même transaction.

3. **Création de l'inscription** — on crée un objet `Inscription` avec `null` comme ID (pas encore persisté), l'ID de la réservation qui vient d'être créée, l'ID de l'organisateur, et `true` pour `Est_Organisateur`.

4. **Deuxième `INSERT`** — insère l'inscription de l'organisateur dans la table `Inscriptions`.

5. **`commit()`** — valide les deux `INSERT` en même temps. À partir de ce moment, les deux lignes sont visibles en base.

6. **`catch` + `rollBack()`** — si n'importe quelle étape lève une exception (erreur SQL, contrainte violée, etc.), `rollBack()` annule **les deux** `INSERT`. La base revient à son état avant `beginTransaction()`. La réservation ne peut pas exister sans son organisateur inscrit — c'est l'invariant garanti par cette transaction.

7. **`throw $e`** — après le rollback, on relance l'exception pour que l'appelant sache qu'il y a eu une erreur.

**49. Pourquoi `InscriptionService` utilise trois repositories ?**
- `InscriptionRepository` : pour créer/supprimer/lire les inscriptions
- `ReservationRepository` : pour vérifier que la réservation existe (règle 1)
- `MembreRepository` : pour vérifier que le membre existe et est actif (règle 2)
Le service orchestre les trois pour appliquer toutes les règles métier.

**50. Comment `ReservationService` calcule-t-il `heureFin` ?**
Dans `createReservation()`, après avoir récupéré `heureDebut` depuis `$data`, il crée un objet `DateTime`, appelle `modify('+90 minutes')`, puis formate le résultat en `'H:i:s'`. Cette valeur est passée au constructeur de `Reservation` puis stockée en base.

---

## Controllers

**51. Pourquoi les controllers ne contiennent aucune logique métier ?**
Le controller a une seule responsabilité : faire le lien entre HTTP et le service. Si on met de la logique dans le controller, on ne peut plus la réutiliser (par exemple depuis une commande CLI ou un autre controller). De plus, les controllers sont difficiles à tester unitairement.

**52. À quoi sert `json_encode()` ?**
`json_encode()` convertit un tableau PHP en chaîne JSON. C'est nécessaire car l'API retourne du JSON. On l'utilise avec `JSON_PRETTY_PRINT` pour les GET (lisibilité) et `JSON_UNESCAPED_UNICODE` pour que les caractères accentués ne soient pas encodés (`é` au lieu de `\u00e9`).

**53. Pourquoi utiliser `http_response_code()` ?**
Sans ça, PHP retourne le code 200 par défaut pour toutes les réponses. Un client API (frontend, mobile) lit le code HTTP pour savoir si la requête a réussi ou échoué. 201 = créé, 400 = requête invalide, 404 = non trouvé, 409 = conflit.

**54. Que fait `file_get_contents('php://input')` ?**
Lit le corps brut de la requête HTTP (le JSON envoyé par le client). Pour les requêtes POST/PUT, les données ne sont pas dans `$_POST` mais dans le corps. On décode ensuite avec `json_decode(..., true)` pour obtenir un tableau PHP.

**55. Pourquoi vérifier `empty($data['membre_id'])` avant d'appeler le service ?**
Si `membre_id` est absent ou vide, le service planterait avec une erreur PHP (undefined array key) ou produirait un comportement inattendu. Le controller valide les données d'entrée (champs obligatoires) avant de déléguer au service. C'est la validation de la frontière HTTP.

**56. Différence entre 404 et 409 ?**
- **404 Not Found** : la ressource demandée n'existe pas (réservation ou membre introuvable).
- **409 Conflict** : la ressource existe mais l'opération est impossible à cause d'un conflit d'état (réservation déjà complète, membre déjà inscrit).

**57. Pourquoi `toArray()` est privée dans `InscriptionController` ?**
C'est un helper interne qui transforme un objet `Inscription` en tableau pour `json_encode()`. Elle n'est utile qu'à l'intérieur du controller, donc `private`. Si elle était publique, on exposerait une méthode qui n'a pas de sens hors contexte.

**58. Comment le controller gère les cas d'erreur du service ?**
Il utilise `match()` sur la valeur retournée par le service. Chaque cas d'erreur (`string`) mappe vers un code HTTP et un message JSON spécifique. Le cas `default` correspond au succès (retour d'un `int`).

**59. Pourquoi `header('Content-Type: application/json')` dans chaque réponse ?**
Ce header indique au client que la réponse est du JSON. Sans lui, le navigateur ou l'application cliente peut mal interpréter la réponse (la traiter comme du HTML). C'est une bonne pratique pour toute API REST.

**60. Que retourne le controller si `removeJoueur()` renvoie `false` ?**
HTTP 404 avec le message : `"Le membre X n'est pas inscrit à la réservation Y"`. Si `removeJoueur()` retourne `true`, le controller retourne HTTP 200 avec `{"message": "Joueur retiré avec succès"}`.

---

## Routeur (index.php)

**61. Comment distinguer `GET /api/reservations/1` de `GET /api/reservations/1/inscriptions` ?**
En comptant le nombre de segments de l'URI après `explode('/')`. `/api/reservations/1` a 3 segments, `/api/reservations/1/inscriptions` en a 4. Le routeur vérifie le nombre de segments ET la valeur du dernier segment.

**62. Pourquoi les routes Inscription sont définies AVANT la route générale `reservations/{id}` ?**
Le routeur parcourt les routes dans l'ordre. Si la route `reservations/{id}` est définie en premier, elle capturerait aussi `/reservations/1/inscriptions` (en lisant `1` comme ID). Les routes plus spécifiques doivent être testées avant les routes générales.

**63. Comment extraire l'ID depuis `/api/reservations/3/inscriptions/7` ?**
```php
$parts = explode('/', trim($uri, '/'));
// $parts = ['api', 'reservations', '3', 'inscriptions', '7']
$reservationId = (int) $parts[2]; // 3
$membreId      = (int) $parts[4]; // 7
```

**64. Que se passe-t-il si aucune route ne correspond ?**
Le routeur retourne HTTP 404 avec un message JSON `{"erreur": "Route non trouvée"}`. C'est le cas `else` final dans `index.php`.

**65. Comment le routeur détermine la méthode HTTP ?**
Via `$_SERVER['REQUEST_METHOD']` qui contient `'GET'`, `'POST'`, `'DELETE'`, etc. Le routeur combine URI + méthode pour identifier la route exacte.

**66. Pourquoi `explode('/', $uri)` ?**
Pour découper l'URL en segments. `/api/reservations/3/inscriptions` devient `['', 'api', 'reservations', '3', 'inscriptions']`. On peut ensuite accéder à chaque segment par index pour identifier la route et extraire les IDs.

**67. Comment le `$pdo` singleton est-il partagé entre tous les repositories ?**
Dans `index.php`, `Database::getConnection()` est appelé une seule fois et retourne toujours la même instance. Ce `$pdo` est passé à chaque repository via leur constructeur. Tous les repositories utilisent donc physiquement la même connexion MySQL — ce qui est nécessaire pour que les transactions PDO fonctionnent.

**68. Pourquoi instancier les controllers dans `index.php` ?**
`index.php` est le seul endroit qui connaît toutes les dépendances (PDO, repositories, services). C'est là qu'on construit le graphe d'objets (DI manuel). Si chaque route créait ses propres instances, on aurait des connexions PDO multiples et du code dupliqué.

**69. Quel est l'ordre des `require_once` et pourquoi ?**
D'abord les modèles, puis les repositories (qui dépendent des modèles), puis les services (qui dépendent des repositories), puis les controllers (qui dépendent des services). PHP charge et exécute chaque fichier immédiatement — si on charge le service avant le repository qu'il utilise, PHP lève une erreur "class not found".

**70. Comment tester une route DELETE dans `api.http` ?**
```
DELETE http://localhost:8888/api/reservations/1/inscriptions/3
```
Dans `api.http`, on spécifie la méthode `DELETE` et l'URL avec les deux IDs. Pas de body nécessaire pour un DELETE.

---

## Tests unitaires — PHPUnit

**71. Test unitaire vs test d'intégration ?**
- **Test unitaire** : teste une seule classe en isolation. Les dépendances sont remplacées par des stubs (faux objets). Ex: `InscriptionServiceTest`.
- **Test d'intégration** : teste plusieurs composants ensemble, souvent avec une vraie base de données. Ex: `InscriptionRepositoryTest` avec SQLite réel.

**72. Pourquoi SQLite en mémoire dans les tests Repository ?**
SQLite est une base de données embarquée qui fonctionne en mémoire — pas besoin d'installer MySQL pour les tests. La base est créée et détruite automatiquement à chaque test. Les tests sont donc rapides, isolés, et fonctionnent sur n'importe quelle machine.

**73. Que fait `setUp()` dans PHPUnit ?**
`setUp()` est exécutée automatiquement AVANT chaque test de la classe. Elle initialise l'état nécessaire : crée la base SQLite, insère des données de test, instancie le repository. Chaque test repart d'un état propre et prévisible.

**74. Pourquoi recréer la base SQLite depuis zéro à chaque test ?**
Pour que les tests soient indépendants. Si un test modifie la base (insert, delete), ça ne doit pas affecter les tests suivants. Sans `setUp()`, un test qui insère une ligne ferait échouer un test qui s'attend à trouver exactement 2 lignes.

**75. Qu'est-ce qu'un stub (`createStub()`) ?**
Un stub est un faux objet qui remplace une vraie dépendance pendant les tests. On configure ce qu'il doit retourner pour chaque appel de méthode. Dans les tests de Service, on remplace les repositories par des stubs pour tester la logique du service sans base de données.

**76. Comment simuler qu'un membre n'existe pas avec un stub ?**
```php
$membreRepo = $this->createStub(MembreRepository::class);
$membreRepo->method('findById')->willReturn(null);
```
Le stub retourne toujours `null` quand `findById()` est appelé, simulant un membre introuvable.

**77. Quelle méthode PHPUnit pour vérifier qu'un tableau a 2 éléments ?**
`$this->assertCount(2, $tableau);`

**78. Quelle méthode PHPUnit pour vérifier qu'une valeur est `null` ?**
`$this->assertNull($valeur);`

**79. Quelle méthode PHPUnit pour vérifier qu'un entier est supérieur à 0 ?**
`$this->assertGreaterThan(0, $valeur);`

**80. Pourquoi des stubs plutôt que de vraies instances dans les tests Service ?**
Les services dépendent des repositories qui dépendent de PDO. Utiliser de vraies instances nécessiterait une base de données dans les tests de service. Les stubs permettent de tester uniquement la logique du service (les règles métier) sans infrastructure.

**81. Combien de tests et d'assertions au total ?**
Le projet a **92 tests** et **116 assertions** (tous passent).

**82. Problème si pas de transactions dans les tests et qu'un test échoue à mi-chemin ?**
La base resterait dans un état partiel (ex: réservation créée sans inscription). Les tests suivants trouveraient une base dans un état inattendu et pourraient échouer pour des raisons non liées à ce qu'ils testent. `setUp()` évite ça en recréant la base propre.

**83. Pourquoi tester `reservation_complete` et `deja_inscrit` séparément ?**
Ce sont deux règles métier distinctes avec des causes différentes. `reservation_complete` = 4 joueurs déjà inscrits (peu importe qui). `deja_inscrit` = CE membre est déjà inscrit (peu importe si la réservation est pleine). Tester séparément garantit que chaque règle fonctionne indépendamment.

**84. Comment configurer un stub pour que `findById()` retourne `null` ?**
```php
$stub = $this->createStub(MembreRepository::class);
$stub->method('findById')->willReturn(null);
```

**85. `assertEquals()` vs `assertSame()` ?**
- `assertEquals()` : comparaison lâche (`==`). `assertEquals(1, "1")` passe.
- `assertSame()` : comparaison stricte (`===`). `assertSame(1, "1")` échoue.
Dans les tests PHP, préférer `assertSame()` pour les types primitifs afin d'éviter les surprises de coercition de type.

---

## Inscription — Logique métier

**86. Pourquoi exactement 4 joueurs pour un match de padel ?**
Le padel se joue en double — deux équipes de 2 joueurs. Un match nécessite donc exactement 4 joueurs. Ni plus (les terrains sont conçus pour 4), ni moins (match incomplet). La règle `>= 4` dans le service reflète cette contrainte sportive réelle.

**87. Différence entre match privé et match public ?**
- **Match privé** : l'organisateur invite 3 joueurs spécifiques via `POST /inscriptions` avec leurs `membre_id`.
- **Match public** : n'importe quel membre peut appeler `POST /inscriptions` avec son propre `membre_id` jusqu'à ce que les 4 places soient remplies.
Les deux utilisent le même endpoint — c'est la logique applicative qui distingue les cas.

**88. Pourquoi l'organisateur est automatiquement inscrit à la création ?**
Un match de padel ne peut pas exister sans au moins l'organisateur. Si on ne l'inscrivait pas automatiquement, il faudrait appeler deux endpoints pour créer une réservation valide. La transaction dans `createReservation()` garantit que les deux opérations réussissent ou échouent ensemble.

**89. Que se passe-t-il si on inscrit un membre inactif ?**
`findById()` dans `MembreRepository` retourne le membre même s'il est inactif. Dans le service, la condition est :
```php
if ($membre === null || !$membre->isEstActif()) {
    return 'membre_introuvable';
}
```
Un membre inactif est traité comme s'il n'existait pas — code 404.

**90. Contrainte UNIQUE en base + vérification côté service : pourquoi les deux ?**
La contrainte UNIQUE en base est le filet de sécurité final — elle garantit l'intégrité même si du code défectueux bypasse le service. La vérification côté service permet de retourner un message d'erreur clair (`'deja_inscrit'`) plutôt qu'une exception PDO brute et incompréhensible.

**91. Que retourne `getInscriptionsByReservation()` si personne n'est inscrit ?**
Un tableau vide `[]`. `findByReservation()` retourne `$this->hydrate([])` qui appelle `array_map` sur un tableau vide — résultat : `[]`. Le controller encode ça en JSON `[]`. Jamais `null`.

**92. Peut-on retirer l'organisateur d'une réservation ?**
Dans le code actuel, oui — `removeJoueur()` ne vérifie pas si le membre est l'organisateur. C'est une limite intentionnelle du projet (règle non implémentée). Dans un système réel, on vérifierait `isEstOrganisateur()` avant de permettre la suppression.

---

## Git et bonnes pratiques

**93. Pourquoi une branche par méthode/fonctionnalité ?**
Chaque branche correspond à une unité de travail isolée. En cas de problème, on peut revert une seule fonctionnalité sans affecter les autres. Ça facilite la revue de code (une PR = un seul changement) et permet de travailler en parallèle sur plusieurs fonctionnalités.

**94. Qu'est-ce qu'une branche orpheline ?**
`git checkout --orphan nom-branche` crée une branche sans historique commun avec les autres branches. Chaque branche du projet contient uniquement les fichiers liés à cette fonctionnalité, sans tout l'historique de `main`. Ça garde les branches propres et focalisées.

**95. Lien entre branche Git et issue GitHub ?**
Chaque issue GitHub décrit le "quoi" (feature à implémenter). La branche porte le même nom que l'issue. Quand la branche est mergée, l'issue est fermée. Ça permet de tracer chaque changement de code à une exigence.

**96. Pourquoi ne jamais committer directement sur `main` ?**
`main` est la branche de référence — elle doit toujours contenir du code fonctionnel. Travailler sur des branches isole les changements en cours. Si quelque chose casse sur une branche, `main` reste stable. On peut aussi faire de la revue de code via PR avant de merger.

**97. Comment revenir sur `main` sans perdre les fichiers d'une branche orpheline ?**
```bash
cp fichier.php /tmp/fichier.php     # sauvegarde
git checkout main
cp /tmp/fichier.php ./chemin/       # restauration
```
Les branches orphelines n'ont pas d'historique commun avec `main`, donc `git checkout main` ne ramène pas les fichiers. Il faut les copier manuellement.

**98. Qu'est-ce que `git status` ?**
`git status` montre l'état du répertoire de travail : fichiers modifiés, fichiers en staging (prêts à committer), fichiers non suivis. À consulter avant chaque `git add` et `git commit` pour s'assurer de ne committer que ce qu'on veut.

**99. Différence entre `git add` et `git commit` ?**
- `git add fichier.php` : place le fichier dans la zone de staging (zone intermédiaire). Le fichier est "prêt à être committé".
- `git commit -m "message"` : crée un snapshot permanent des fichiers en staging dans l'historique Git.
Les deux étapes existent pour pouvoir choisir précisément ce qui entre dans chaque commit.

**100. Démarche si un test casse après une modification ?**
1. Lire le message d'erreur PHPUnit — il indique la classe, la méthode, et la ligne.
2. Vérifier ce qui a changé récemment (`git diff`).
3. Relire le test pour comprendre ce qu'il attend.
4. Relire le code modifié pour trouver la divergence.
5. Corriger soit le code (si c'est un bug), soit le test (si la spec a changé).
6. Relancer les tests pour confirmer que tout repasse.
