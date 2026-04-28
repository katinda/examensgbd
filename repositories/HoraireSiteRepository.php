<?php

require_once __DIR__ . '/../models/HoraireSite.php';

class HoraireSiteRepository {

    public function __construct(private PDO $pdo) {}

    public function findById(int $id): ?HoraireSite {
        $stmt = $this->pdo->prepare("SELECT * FROM Horaires_Sites WHERE Horaire_ID = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->hydrateOne($row) : null;
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
