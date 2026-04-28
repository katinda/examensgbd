<?php

require_once __DIR__ . '/../models/HoraireSite.php';

class HoraireSiteRepository {

    public function __construct(private PDO $pdo) {}

    public function findBySiteAndAnnee(int $siteId, int $annee): ?HoraireSite {
        $stmt = $this->pdo->prepare("SELECT * FROM Horaires_Sites WHERE Site_ID = :siteId AND Annee = :annee");
        $stmt->execute([':siteId' => $siteId, ':annee' => $annee]);
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
