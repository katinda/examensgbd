# Questions d'oral — PadelManager

Questions basées uniquement sur le code existant : Sites, Terrains, Membres, Réservations, Inscriptions.

---

## Architecture générale

1. Décris l'architecture en couches de ton projet (Model → Repository → Service → Controller).
2. Pourquoi séparer le Repository du Service ? Qu'est-ce que ça apporte concrètement ?
3. Qu'est-ce que l'injection de dépendance ? Donne un exemple dans ton code.
4. Pourquoi le Controller ne fait jamais de SQL directement ?
5. Qu'est-ce qu'un singleton ? Où l'utilises-tu dans ton projet ?
6. Pourquoi utilises-tu PDO plutôt que mysqli ?
7. Qu'est-ce que `PDO::ATTR_ERRMODE` et pourquoi tu le configures à `PDO::ERRMODE_EXCEPTION` ?
8. Comment fonctionne le routeur dans `index.php` ? Comment il identifie quelle route appeler ?
9. Pourquoi les `require_once` sont dans `index.php` et pas dans chaque fichier ?
10. Qu'est-ce que `PDO::FETCH_ASSOC` ? Pourquoi tu l'utilises plutôt que `PDO::FETCH_OBJ` ?

---

## PHP 8 — Syntaxe et fonctionnalités

11. C'est quoi le "constructor property promotion" en PHP 8 ? Montre un exemple dans ton code.
12. À quoi sert le type `?int` (nullable) ? Où l'utilises-tu ?
13. Qu'est-ce qu'un type de retour `int|string` (union type) ? Donne un exemple dans ton code.
14. C'est quoi une arrow function (`fn`) ? Où tu l'utilises dans ton projet ?
15. À quoi sert l'expression `match()` ? Montre son usage dans un controller.
16. Quelle est la différence entre `match()` et `switch()` en PHP ?
17. Qu'est-ce que le type `void` comme type de retour d'une méthode ?
18. À quoi sert le mot-clé `readonly` en PHP 8.1 ? L'utilises-tu dans ton projet ?
19. Qu'est-ce qu'une fonction fléchée (`fn`) comparée à une closure anonyme ?
20. Comment fonctionne `array_map()` avec une arrow function dans ton `hydrate()` ?
21. À quoi sert `$this` en PHP ? Donne un exemple dans ton code.
22. À quoi sert `isset()` ? Dans quel contexte l'utilises-tu ou pourrais-tu l'utiliser ?

---

## Modèles (Models)

23. Pourquoi les modèles ne contiennent aucun SQL ?
24. Qu'est-ce qu'un getter ? Pourquoi ne pas accéder directement aux propriétés ?
25. Pourquoi `Inscription_ID` est `?int` (nullable) dans le modèle `Inscription` ?
26. Dans le modèle `Inscription`, pourquoi `estOrganisateur` vaut `false` par défaut ?
27. Quelle est la différence entre `getInscriptionId()` et `getReservationId()` ? Pourquoi deux getters distincts ?
28. Pourquoi le modèle `Membre` a un champ `estActif` au lieu de supprimer l'enregistrement ?
29. C'est quoi le soft delete ? Avantages vs suppression physique ?
30. Dans `Reservation`, `heureFin` est-elle stockée en base ou calculée ? Explique.
31. Pourquoi le modèle `Terrain` a un champ `estActif` ?
32. Que retourne `isEstOrganisateur()` dans le modèle `Inscription` ?

---

## Repositories

31. Qu'est-ce que la méthode `hydrate()` dans un repository ? Pourquoi l'utiliser ?
32. Pourquoi utiliser des requêtes préparées (`prepare` + `execute`) plutôt que des requêtes directes ?
33. Qu'est-ce qu'une injection SQL ? Comment les requêtes préparées la préviennent-elles ?
34. Que retourne `findById()` si l'enregistrement n'existe pas ? Quel type PHP ?
35. Pourquoi `countByReservation()` retourne un `int` et pas un tableau ?
36. Que fait `lastInsertId()` ? Dans quel repository l'utilises-tu ?
37. Quelle est la différence entre `findByReservation()` et `findByReservationAndMembre()` ?
38. Pourquoi `delete()` dans `InscriptionRepository` prend deux paramètres (`reservationId` + `membreId`) ?
39. Pourquoi les repositories reçoivent `$pdo` en paramètre plutôt que d'appeler `Database::getConnection()` directement ?
40. Que se passe-t-il si `fetch()` ne trouve aucune ligne ? Que retourne PDO ?

---

## Services

41. Quelle est la responsabilité d'un service par rapport à un repository ?
42. Quelles règles métier valide `addJoueur()` dans `InscriptionService` ? Cite-les toutes.
43. Pourquoi vérifier `reservation_introuvable` avant `reservation_complete` ? L'ordre a-t-il de l'importance ?
44. Que retourne `addJoueur()` si tout se passe bien ? Et si une règle est violée ?
45. Pourquoi `removeJoueur()` retourne un `bool` et pas `void` ?
46. Qu'est-ce qu'une transaction PDO ? Pourquoi l'utiliser dans `createReservation()` ?
47. Que se passe-t-il si l'insertion de l'inscription échoue dans `createReservation()` ?
48. Qu'est-ce que `beginTransaction()`, `commit()`, `rollBack()` ? Dans quel ordre les appelle-tu ?
48b. Explique ce bloc de code dans `ReservationService::createReservation()` :
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
49. Pourquoi `InscriptionService` utilise trois repositories différents ?
50. Comment `ReservationService` calcule-t-il `heureFin` automatiquement ?

---

## Controllers

51. Pourquoi les controllers ne contiennent aucune logique métier ?
52. À quoi sert `json_encode()` dans les controllers ?
53. Pourquoi utiliser `http_response_code()` ? Que se passe-t-il si tu ne le fais pas ?
54. Que fait `file_get_contents('php://input')` dans `addJoueur()` ?
55. Pourquoi vérifier `empty($data['membre_id'])` avant d'appeler le service ?
56. Quelle est la différence entre un code HTTP 404 et 409 ? Dans quels cas les utilises-tu ?
57. Pourquoi le controller `InscriptionController` a une méthode privée `toArray()` ?
58. Comment le controller gère-t-il les différents cas d'erreur retournés par le service ?
59. Pourquoi mettre `header('Content-Type: application/json')` dans chaque réponse ?
60. Que retourne le controller si `removeJoueur()` renvoie `false` ?

---

## Routeur (index.php)

61. Comment le routeur identifie la différence entre `GET /api/reservations/1` et `GET /api/reservations/1/inscriptions` ?
62. Pourquoi les routes Inscription doivent être définies AVANT la route générale `/api/reservations/{id}` ?
63. Comment extrais-tu l'ID depuis une URL comme `/api/reservations/3/inscriptions/7` ?
64. Que se passe-t-il si aucune route ne correspond à la requête ?
65. Comment le routeur détermine-t-il la méthode HTTP (GET, POST, DELETE) ?
66. Pourquoi utilise-tu `explode('/', $uri)` pour parser les routes ?
67. Comment le `$pdo` singleton est-il partagé entre tous les repositories via le routeur ?
68. Pourquoi instancier les controllers dans `index.php` plutôt que dans chaque route ?
69. Quelle est l'ordre des `require_once` dans `index.php` et pourquoi cet ordre est-il important ?
70. Comment tester manuellement une route DELETE dans `api.http` ?

---

## Tests unitaires — PHPUnit

71. C'est quoi un test unitaire ? Quelle est sa différence avec un test d'intégration ?
72. Pourquoi utilises-tu SQLite en mémoire dans les tests de Repository ?
73. Que fait `setUp()` dans une classe de test PHPUnit ?
74. Pourquoi chaque test recrée la base SQLite depuis zéro (dans `setUp()`) ?
75. Qu'est-ce qu'un stub (`createStub()`) ? Pourquoi l'utilises-tu dans les tests de Service ?
76. Comment simuler qu'un membre n'existe pas avec un stub dans `InscriptionServiceTest` ?
77. Quelle méthode PHPUnit utilises-tu pour vérifier qu'un tableau a 2 éléments ?
78. Quelle méthode PHPUnit utilises-tu pour vérifier qu'une valeur est `null` ?
79. Quelle méthode PHPUnit utilises-tu pour vérifier qu'un entier est supérieur à 0 ?
80. Pourquoi les tests de Service utilisent des stubs plutôt que de vraies instances de Repository ?
81. Combien de tests as-tu en tout ? Combien d'assertions ?
82. Que se passerait-il si tu n'utilisais pas de transactions PDO dans les tests et qu'un test échoue à mi-chemin ?
83. Pourquoi tester `testAddJoueurRetourneReservationComplete` séparément de `testAddJoueurRetourneDejaInscrit` ?
84. Comment configures-tu le stub pour que `findById()` retourne `null` ?
85. À quoi sert `assertEquals()` vs `assertSame()` en PHPUnit ?

---

## Inscription — Logique métier

86. Pourquoi un match de padel nécessite exactement 4 joueurs ?
87. Quelle est la différence entre un match privé et un match public dans ton modèle ?
88. Pourquoi l'organisateur est automatiquement inscrit lors de la création d'une réservation ?
89. Que se passe-t-il si on essaie d'inscrire un membre inactif à une réservation ?
90. Pourquoi la contrainte UNIQUE `(Reservation_ID, Membre_ID)` existe en base ? Et pourquoi vérifier aussi côté service ?
91. Que retourne `getInscriptionsByReservation()` si personne n'est inscrit ?
92. Peut-on retirer l'organisateur d'une réservation ? Que se passe-t-il dans ton code actuel ?

---

## Git et bonnes pratiques

93. Pourquoi créer une branche par méthode/fonctionnalité plutôt qu'une seule branche ?
94. Qu'est-ce qu'une branche orpheline (`git checkout --orphan`) ? Pourquoi l'utilises-tu ?
95. Quel est le lien entre une branche Git et une issue GitHub dans ton workflow ?
96. Pourquoi ne jamais committer directement sur `main` ?
97. Comment tu reviens sur `main` sans perdre les fichiers que tu as créés dans une branche orpheline ?
98. Qu'est-ce que `git status` et pourquoi le consulter avant chaque commit ?
99. Quelle est la différence entre `git add` et `git commit` ?
100. Si un test casse après une modification, quelle est ta démarche pour diagnostiquer le problème ?
