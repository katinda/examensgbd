<?php
require_once __DIR__ . '/../models/Reservation.php';
class ReservationRepository {
    public function __construct(private PDO $pdo) {}
    public function findByTerrainAndDate(int $terrainId, string $date): array {
        $stmt = $this->pdo->prepare("SELECT * FROM Reservations WHERE Terrain_ID = :terrainId AND Date_Match = :date ORDER BY Heure_Debut");
        $stmt->execute([':terrainId' => $terrainId, ':date' => $date]);
        return array_map(fn($row) => $this->hydrateOne($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    private function hydrateOne(array $row): Reservation {
        return new Reservation((int)$row['Reservation_ID'], (int)$row['Terrain_ID'], (int)$row['Organisateur_ID'],
            $row['Date_Match'], $row['Heure_Debut'], $row['Heure_Fin'], $row['Type'],
            $row['Etat'], (float)$row['Prix_Total'], $row['Date_Creation'], $row['LastUpdate']);
    }
}
