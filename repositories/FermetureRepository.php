<?php

require_once __DIR__ . '/../models/Fermeture.php';

class FermetureRepository {

    public function __construct(private PDO $pdo) {}

    public function findAll(): array {
        $stmt = $this->pdo->query("SELECT * FROM Fermetures");
        return array_map(fn($row) => $this->hydrateOne($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function findById(int $id): ?Fermeture {
        $stmt = $this->pdo->prepare("SELECT * FROM Fermetures WHERE Fermeture_ID = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->hydrateOne($row) : null;
    }

    public function findBySiteId(int $siteId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM Fermetures WHERE Site_ID = :siteId");
        $stmt->execute([':siteId' => $siteId]);
        return array_map(fn($row) => $this->hydrateOne($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function findGlobales(): array {
        $stmt = $this->pdo->query("SELECT * FROM Fermetures WHERE Site_ID IS NULL");
        return array_map(fn($row) => $this->hydrateOne($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function insert(Fermeture $fermeture): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO Fermetures (Site_ID, Date_Debut, Date_Fin, Raison)
            VALUES (:siteId, :dateDebut, :dateFin, :raison)
        ");
        $stmt->execute([
            ':siteId'    => $fermeture->getSiteId(),
            ':dateDebut' => $fermeture->getDateDebut(),
            ':dateFin'   => $fermeture->getDateFin(),
            ':raison'    => $fermeture->getRaison(),
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(Fermeture $fermeture): void {
        $stmt = $this->pdo->prepare("
            UPDATE Fermetures
            SET Site_ID = :siteId, Date_Debut = :dateDebut, Date_Fin = :dateFin, Raison = :raison
            WHERE Fermeture_ID = :id
        ");
        $stmt->execute([
            ':id'        => $fermeture->getFermetureId(),
            ':siteId'    => $fermeture->getSiteId(),
            ':dateDebut' => $fermeture->getDateDebut(),
            ':dateFin'   => $fermeture->getDateFin(),
            ':raison'    => $fermeture->getRaison(),
        ]);
    }

    public function delete(int $id): void {
        $stmt = $this->pdo->prepare("DELETE FROM Fermetures WHERE Fermeture_ID = :id");
        $stmt->execute([':id' => $id]);
    }

    private function hydrateOne(array $row): Fermeture {
        return new Fermeture(
            (int) $row['Fermeture_ID'],
            $row['Site_ID'] !== null ? (int) $row['Site_ID'] : null,
            $row['Date_Debut'],
            $row['Date_Fin'],
            $row['Raison'],
            $row['Date_Creation']
        );
    }
}
