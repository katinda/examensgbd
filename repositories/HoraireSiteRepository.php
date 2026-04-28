<?php

require_once __DIR__ . '/../models/HoraireSite.php';

// Le repository gère toutes les requêtes SQL sur la table Horaires_Sites.
// C'est le seul endroit où on écrit du SQL pour les horaires de sites.
// Il ne contient aucune logique métier.

class HoraireSiteRepository {

    // PDO est reçu en paramètre (injection de dépendance).
    // On ne l'instancie jamais ici.
    public function __construct(private PDO $pdo) {}


    // Retourne tous les horaires de la base de données
    public function findAll(): array {
        $stmt = $this->pdo->query("SELECT * FROM Horaires_Sites");
        return $this->hydrate($stmt->fetchAll(PDO::FETCH_ASSOC));
    }


    // Retourne un horaire par son ID, ou null s'il n'existe pas
    public function findById(int $id): ?HoraireSite {
        $stmt = $this->pdo->prepare("SELECT * FROM Horaires_Sites WHERE Horaire_ID = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->hydrateOne($row) : null;
    }


    // Retourne tous les horaires d'un site (toutes années confondues)
    // Utilisé pour la route imbriquée GET /sites/{siteId}/horaires
    public function findBySiteId(int $siteId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM Horaires_Sites WHERE Site_ID = :siteId");
        $stmt->execute([':siteId' => $siteId]);
        return $this->hydrate($stmt->fetchAll(PDO::FETCH_ASSOC));
    }


    // Transforme plusieurs lignes SQL en tableau d'objets HoraireSite
    private function hydrate(array $rows): array {
        return array_map(fn($row) => $this->hydrateOne($row), $rows);
    }


    // Transforme une ligne SQL en objet HoraireSite
    // Exemple : $row['Heure_Debut'] = "08:00:00" → $horaire->setHeureDebut("08:00:00")
    private function hydrateOne(array $row): HoraireSite {
        return new HoraireSite(
            (int) $row['Horaire_ID'],
            (int) $row['Site_ID'],
            (int) $row['Annee'],
            $row['Heure_Debut'],
            $row['Heure_Fin']
        );
    }
}
