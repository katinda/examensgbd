# Padel Manager

Application de gestion de réservations de terrains de padel.
Développée en PHP avec une base de données MySQL.

---

## Connexion à la base de données

- Serveur : MAMP (localhost)
- Port MySQL : 8889
- Base de données : PadelManager
- Utilisateur : root

Pour tester la connexion :
```bash
php test_connexion.php
```

---

## Structure du projet

```
examensgbd/
├── config/         → Connexion PDO partagée avec tout le projet
├── models/         → Classes qui représentent les tables de la base de données
├── repositories/   → Tout le SQL du projet (SELECT, INSERT, UPDATE, DELETE)
├── services/       → Logique métier (délais, créneaux, pénalités)
├── controllers/    → Reçoit les requêtes HTTP et renvoie du JSON
├── padel_manager.sql   → Schéma complet de la base de données
└── test_connexion.php  → Script pour vérifier la connexion à MySQL
```

---

## Le flux de données

```
Requête HTTP
    ↓
Controller  →  reçoit la requête
    ↓
Service     →  applique la logique métier
    ↓
Repository  →  exécute le SQL
    ↓
Base de données MySQL
```

---

## Lancer le serveur PHP

```bash
php -S localhost:8000
```

---

## Lancer les tests unitaires

```bash
./vendor/bin/phpunit tests/
```

---

## Importer la base de données

```bash
mysql -u root -p < padel_manager.sql
```

---

## Tables de la base de données

| Table | Description |
|---|---|
| Sites | Les sites physiques de padel |
| Terrains | Les terrains rattachés à un site |
| Horaires_Sites | Les horaires d'ouverture par site et par année |
| Fermetures | Les jours de fermeture d'un site |
| Membres | Les joueurs (catégories G, S, L) |
| Administrateurs | Les comptes administrateurs |
| Reservations | Les réservations de créneaux |
| Inscriptions | Les joueurs inscrits à une réservation |
| Paiements | Les paiements effectués par les joueurs |
| Penalites | Les pénalités appliquées aux membres |
# Padel Manager
