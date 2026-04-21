<?php
require_once __DIR__ . '/../models/Reservation.php';
class ReservationRepository {
    public function __construct(private PDO $pdo) {}
    public function findById(int $id): ?Reservation {
        $stmt = $this->pdo->prepare("SELECT * FROM Reservations WHERE Reservation_ID = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->hydrateOne($row) : null;
    }
    private function hydrateOne(array $row): Reservation {
        return new Reservation((int)$row['Reservation_ID'], (int)$row['Terrain_ID'], (int)$row['Organisateur_ID'],
            $row['Date_Match'], $row['Heure_Debut'], $row['Heure_Fin'], $row['Type'],
            $row['Etat'], (float)$row['Prix_Total'], $row['Date_Creation'], $row['LastUpdate']);
    }
}
