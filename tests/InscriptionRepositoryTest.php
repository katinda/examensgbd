<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../models/Inscription.php';
require_once __DIR__ . '/../repositories/InscriptionRepository.php';
class InscriptionRepositoryTest extends TestCase {
    private InscriptionRepository $repository;
    protected function setUp(): void {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec("CREATE TABLE Inscriptions (Inscription_ID INTEGER PRIMARY KEY AUTOINCREMENT, Reservation_ID INTEGER NOT NULL, Membre_ID INTEGER NOT NULL, Est_Organisateur INTEGER NOT NULL DEFAULT 0, UNIQUE (Reservation_ID, Membre_ID))");
        $pdo->exec("INSERT INTO Inscriptions (Reservation_ID, Membre_ID, Est_Organisateur) VALUES (1, 1, 1), (1, 2, 0)");
        $this->repository = new InscriptionRepository($pdo);
    }
    public function testCountByReservationRetourneLeBonNombre(): void {
        $this->assertEquals(2, $this->repository->countByReservation(1));
        $this->assertEquals(0, $this->repository->countByReservation(999));
    }
}
