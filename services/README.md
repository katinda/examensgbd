# Dossier services/

Ce dossier contient les classes qui gèrent la logique métier.

Exemple : ReservationService.php vérifie les délais d'anticipation,
calcule les créneaux disponibles, applique les pénalités...

Les services utilisent les repositories pour accéder aux données
mais n'écrivent jamais de SQL directement.
