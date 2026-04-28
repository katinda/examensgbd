<?php
require_once __DIR__ . '/../models/Fermeture.php';
class FermetureRepository {
    public function __construct(private PDO $pdo) {}
    public function insert(Fermeture $fermeture): int { $stmt = $this->pdo->prepare("INSERT INTO Fermetures (Site_ID, Date_Debut, Date_Fin, Raison) VALUES (:siteId, :dateDebut, :dateFin, :raison)"); $stmt->execute([':siteId' => $fermeture->getSiteId(), ':dateDebut' => $fermeture->getDateDebut(), ':dateFin' => $fermeture->getDateFin(), ':raison' => $fermeture->getRaison()]); return (int) $this->pdo->lastInsertId(); }
    private function hydrateOne(array $row): Fermeture {
        return new Fermeture((int) $row['Fermeture_ID'], $row['Site_ID'] !== null ? (int) $row['Site_ID'] : null, $row['Date_Debut'], $row['Date_Fin'], $row['Raison'], $row['Date_Creation']);
    }
}
