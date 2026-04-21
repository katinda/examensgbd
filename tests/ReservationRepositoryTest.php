<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../models/Reservation.php';
require_once __DIR__ . '/../repositories/ReservationRepository.php';

class ReservationRepositoryTest extends TestCase {
    private PDO $pdo;
    private ReservationRepository $repository;
    protected function setUp(): void {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec("CREATE TABLE Reservations (Reservation_ID INTEGER PRIMARY KEY AUTOINCREMENT, Terrain_ID INTEGER NOT NULL, Organisateur_ID INTEGER NOT NULL, Date_Match TEXT NOT NULL, Heure_Debut TEXT NOT NULL, Heure_Fin TEXT NOT NULL, Type TEXT NOT NULL, Etat TEXT NOT NULL DEFAULT 'EN_COURS', Prix_Total REAL NOT NULL DEFAULT 60.00, Date_Creation TEXT DEFAULT CURRENT_TIMESTAMP, LastUpdate TEXT DEFAULT CURRENT_TIMESTAMP, UNIQUE (Terrain_ID, Date_Match, Heure_Debut))");
        $this->pdo->exec("INSERT INTO Reservations (Terrain_ID, Organisateur_ID, Date_Match, Heure_Debut, Heure_Fin, Type, Etat, Prix_Total) VALUES (1, 1, '2026-05-10', '10:00:00', '11:30:00', 'PRIVE', 'EN_COURS', 60.00)");
        $this->repository = new ReservationRepository($this->pdo);
    }
    public function testFindByIdRetourneLaBonneReservation(): void {
        $reservation = $this->repository->findById(1);
        $this->assertNotNull($reservation);
        $this->assertEquals('10:00:00', $reservation->getHeureDebut());
        $this->assertEquals('PRIVE', $reservation->getType());
    }
}
