# Dossier config/

Ce dossier contient la configuration du projet.

Database.php : gère la connexion PDO partagée avec tout le projet.
Au lieu de se connecter à MySQL dans chaque fichier séparément,
on le fait une seule fois ici et on l'utilise partout.
