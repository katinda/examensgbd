<?php

require_once __DIR__ . '/../models/HoraireSite.php';

class HoraireSiteRepository {

    public function __construct(private PDO $pdo) {}

    public function findAll(): array {
        $stmt = $this->pdo->query("SELECT * FROM Horaires_Sites");
        return array_map(fn($row) => $this->hydrateOne($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function findById(int $id): ?HoraireSite {
        $stmt = $this->pdo->prepare("SELECT * FROM Horaires_Sites WHERE Horaire_ID = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->hydrateOne($row) : null;
    }

    public function findBySiteId(int $siteId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM Horaires_Sites WHERE Site_ID = :siteId");
        $stmt->execute([':siteId' => $siteId]);
        return array_map(fn($row) => $this->hydrateOne($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function findBySiteAndAnnee(int $siteId, int $annee): ?HoraireSite {
        $stmt = $this->pdo->prepare("SELECT * FROM Horaires_Sites WHERE Site_ID = :siteId AND Annee = :annee");
        $stmt->execute([':siteId' => $siteId, ':annee' => $annee]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->hydrateOne($row) : null;
    }

    public function insert(HoraireSite $horaire): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO Horaires_Sites (Site_ID, Annee, Heure_Debut, Heure_Fin)
            VALUES (:siteId, :annee, :heureDebut, :heureFin)
        ");
        $stmt->execute([
            ':siteId'     => $horaire->getSiteId(),
            ':annee'      => $horaire->getAnnee(),
            ':heureDebut' => $horaire->getHeureDebut(),
            ':heureFin'   => $horaire->getHeureFin(),
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(HoraireSite $horaire): void {
        $stmt = $this->pdo->prepare("
            UPDATE Horaires_Sites
            SET Site_ID = :siteId, Annee = :annee, Heure_Debut = :heureDebut, Heure_Fin = :heureFin
            WHERE Horaire_ID = :id
        ");
        $stmt->execute([
            ':id'         => $horaire->getHoraireId(),
            ':siteId'     => $horaire->getSiteId(),
            ':annee'      => $horaire->getAnnee(),
            ':heureDebut' => $horaire->getHeureDebut(),
            ':heureFin'   => $horaire->getHeureFin(),
        ]);
    }

    public function delete(int $id): void {
        $stmt = $this->pdo->prepare("DELETE FROM Horaires_Sites WHERE Horaire_ID = :id");
        $stmt->execute([':id' => $id]);
    }

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
