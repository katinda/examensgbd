<?php

require_once __DIR__ . '/../models/Terrain.php';

// Le repository gère toutes les requêtes SQL sur la table Terrains.
// C'est le seul endroit où on écrit du SQL pour les terrains.
// Il ne contient aucune logique métier.

class TerrainRepository {

    // PDO est reçu en paramètre (injection de dépendance).
    // On ne l'instancie jamais ici.
    public function __construct(private PDO $pdo) {}


    // Retourne tous les terrains de la base de données
    public function findAll(): array {
        $stmt = $this->pdo->query("SELECT * FROM Terrains");
        return $this->hydrate($stmt->fetchAll(PDO::FETCH_ASSOC));
    }


    // Retourne un terrain par son ID, ou null s'il n'existe pas
    public function findById(int $id): ?Terrain {
        $stmt = $this->pdo->prepare("SELECT * FROM Terrains WHERE Terrain_ID = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->hydrateOne($row) : null;
    }


    // Retourne tous les terrains d'un site précis
    // Utilisé pour la route imbriquée GET /sites/{siteId}/terrains
    public function findBySiteId(int $siteId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM Terrains WHERE Site_ID = :siteId");
        $stmt->execute([':siteId' => $siteId]);
        return $this->hydrate($stmt->fetchAll(PDO::FETCH_ASSOC));
    }


    // Crée un nouveau terrain en base et retourne son ID
    public function insert(Terrain $terrain): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO Terrains (Site_ID, Num_Terrain, Libelle, Est_Actif)
            VALUES (:siteId, :numTerrain, :libelle, :estActif)
        ");
        $stmt->execute([
            ':siteId'     => $terrain->getSiteId(),
            ':numTerrain' => $terrain->getNumTerrain(),
            ':libelle'    => $terrain->getLibelle(),
            ':estActif'   => $terrain->isEstActif() ? 1 : 0,
        ]);
        return (int) $this->pdo->lastInsertId();
    }


    // Met à jour un terrain existant
    public function update(Terrain $terrain): void {
        $stmt = $this->pdo->prepare("
            UPDATE Terrains
            SET Site_ID = :siteId, Num_Terrain = :numTerrain,
                Libelle = :libelle, Est_Actif = :estActif
            WHERE Terrain_ID = :id
        ");
        $stmt->execute([
            ':id'         => $terrain->getTerrainId(),
            ':siteId'     => $terrain->getSiteId(),
            ':numTerrain' => $terrain->getNumTerrain(),
            ':libelle'    => $terrain->getLibelle(),
            ':estActif'   => $terrain->isEstActif() ? 1 : 0,
        ]);
    }


    // Supprime un terrain par son ID
    public function delete(int $id): void {
        $stmt = $this->pdo->prepare("DELETE FROM Terrains WHERE Terrain_ID = :id");
        $stmt->execute([':id' => $id]);
    }


    // Transforme plusieurs lignes SQL en tableau d'objets Terrain
    private function hydrate(array $rows): array {
        return array_map(fn($row) => $this->hydrateOne($row), $rows);
    }


    // Transforme une ligne SQL en objet Terrain
    // Exemple : $row['Libelle'] = "Terrain Central" → $terrain->setLibelle("Terrain Central")
    private function hydrateOne(array $row): Terrain {
        $terrain = new Terrain();
        $terrain->setTerrainId((int) $row['Terrain_ID']);
        $terrain->setSiteId((int) $row['Site_ID']);
        $terrain->setNumTerrain((int) $row['Num_Terrain']);
        $terrain->setLibelle($row['Libelle']);
        $terrain->setEstActif((bool) $row['Est_Actif']);
        return $terrain;
    }
}
