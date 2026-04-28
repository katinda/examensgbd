<?php

require_once __DIR__ . '/../models/HoraireSite.php';

class HoraireSiteRepository {

    public function __construct(private PDO $pdo) {}

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
