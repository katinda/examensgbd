<?php

require_once __DIR__ . '/../models/HoraireSite.php';

class HoraireSiteRepository {

    public function __construct(private PDO $pdo) {}

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
