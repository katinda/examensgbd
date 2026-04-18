# Dossier controllers/

Ce dossier contient les classes qui reçoivent les requêtes HTTP.

Exemple : ReservationController.php reçoit la requête, appelle le bon service,
et renvoie une réponse en JSON.

Les controllers ne font jamais de SQL et ne contiennent pas de logique métier.
Ils servent juste de pont entre l'utilisateur et les services.
