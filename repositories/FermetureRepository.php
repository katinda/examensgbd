<?php
require_once __DIR__ . '/../models/Fermeture.php';
class FermetureRepository {
    public function __construct(private PDO $pdo) {}
    public function update(Fermeture $fermeture): void { $stmt = $this->pdo->prepare("UPDATE Fermetures SET Site_ID = :siteId, Date_Debut = :dateDebut, Date_Fin = :dateFin, Raison = :raison WHERE Fermeture_ID = :id"); $stmt->execute([':id' => $fermeture->getFermetureId(), ':siteId' => $fermeture->getSiteId(), ':dateDebut' => $fermeture->getDateDebut(), ':dateFin' => $fermeture->getDateFin(), ':raison' => $fermeture->getRaison()]); }
    private function hydrateOne(array $row): Fermeture {
        return new Fermeture((int) $row['Fermeture_ID'], $row['Site_ID'] !== null ? (int) $row['Site_ID'] : null, $row['Date_Debut'], $row['Date_Fin'], $row['Raison'], $row['Date_Creation']);
    }
}
