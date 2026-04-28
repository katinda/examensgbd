<?php
require_once __DIR__ . '/../models/Fermeture.php';
class FermetureRepository {
    public function __construct(private PDO $pdo) {}
    public function findAll(): array { $stmt = $this->pdo->query("SELECT * FROM Fermetures"); return array_map(fn($row) => $this->hydrateOne($row), $stmt->fetchAll(PDO::FETCH_ASSOC)); }
    private function hydrateOne(array $row): Fermeture {
        return new Fermeture((int) $row['Fermeture_ID'], $row['Site_ID'] !== null ? (int) $row['Site_ID'] : null, $row['Date_Debut'], $row['Date_Fin'], $row['Raison'], $row['Date_Creation']);
    }
}
