# Dossier repositories/

Ce dossier contient les classes qui parlent à la base de données.

Chaque fichier = une table.
Exemple : SiteRepository.php fait les SELECT, INSERT, UPDATE, DELETE sur la table Sites.

C'est le seul endroit où on écrit du SQL dans le projet.
Les repositories ne contiennent aucune logique métier.
