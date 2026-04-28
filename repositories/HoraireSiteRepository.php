<?php

require_once __DIR__ . '/../models/HoraireSite.php';

class HoraireSiteRepository {

    public function __construct(private PDO $pdo) {}

    public function findBySiteId(int $siteId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM Horaires_Sites WHERE Site_ID = :siteId");
        $stmt->execute([':siteId' => $siteId]);
        return array_map(fn($row) => $this->hydrateOne($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
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
