<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../models/Inscription.php';
require_once __DIR__ . '/../repositories/InscriptionRepository.php';
class InscriptionRepositoryTest extends TestCase {
    private PDO $pdo;
    private InscriptionRepository $repository;
    protected function setUp(): void {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec("CREATE TABLE Inscriptions (Inscription_ID INTEGER PRIMARY KEY AUTOINCREMENT, Reservation_ID INTEGER NOT NULL, Membre_ID INTEGER NOT NULL, Est_Organisateur INTEGER NOT NULL DEFAULT 0, UNIQUE (Reservation_ID, Membre_ID))");
        $this->pdo->exec("INSERT INTO Inscriptions (Reservation_ID, Membre_ID, Est_Organisateur) VALUES (1, 1, 1), (1, 2, 0)");
        $this->repository = new InscriptionRepository($this->pdo);
    }
    public function testFindByReservationRetourneLesInscriptions(): void {
        $this->assertCount(2, $this->repository->findByReservation(1));
    }
}
