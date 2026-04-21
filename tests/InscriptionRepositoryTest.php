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
        $this->repository = new InscriptionRepository($pdo);
    }
    public function testInsertAjouteUneInscription(): void {
        $id = $this->repository->insert(new Inscription(null, 1, 3, false));
        $this->assertGreaterThan(0, $id);
        $this->assertEquals(1, $this->repository->countByReservation(1));
    }
}
