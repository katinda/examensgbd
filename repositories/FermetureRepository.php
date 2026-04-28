<?php
require_once __DIR__ . '/../models/Fermeture.php';
class FermetureRepository {
    public function __construct(private PDO $pdo) {}
    public function findById(int $id): ?Fermeture { $stmt = $this->pdo->prepare("SELECT * FROM Fermetures WHERE Fermeture_ID = :id"); $stmt->execute([':id' => $id]); $row = $stmt->fetch(PDO::FETCH_ASSOC); return $row ? $this->hydrateOne($row) : null; }
    private function hydrateOne(array $row): Fermeture {
        return new Fermeture((int) $row['Fermeture_ID'], $row['Site_ID'] !== null ? (int) $row['Site_ID'] : null, $row['Date_Debut'], $row['Date_Fin'], $row['Raison'], $row['Date_Creation']);
    }
}
