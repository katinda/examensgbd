<?php
require_once __DIR__ . '/../models/Reservation.php';
class ReservationRepository {
    public function __construct(private PDO $pdo) {}
    public function findByTerrainDateHeure(int $terrainId, string $date, string $heureDebut): ?Reservation {
        $stmt = $this->pdo->prepare("SELECT * FROM Reservations WHERE Terrain_ID = :terrainId AND Date_Match = :date AND Heure_Debut = :heureDebut");
        $stmt->execute([':terrainId' => $terrainId, ':date' => $date, ':heureDebut' => $heureDebut]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->hydrateOne($row) : null;
    }
    private function hydrateOne(array $row): Reservation {
        return new Reservation((int)$row['Reservation_ID'], (int)$row['Terrain_ID'], (int)$row['Organisateur_ID'],
            $row['Date_Match'], $row['Heure_Debut'], $row['Heure_Fin'], $row['Type'],
            $row['Etat'], (float)$row['Prix_Total'], $row['Date_Creation'], $row['LastUpdate']);
    }
}
