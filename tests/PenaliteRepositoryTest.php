<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../models/Penalite.php';
require_once __DIR__ . '/../repositories/PenaliteRepository.php';

class PenaliteRepositoryTest extends TestCase {
    private PDO $pdo;
    private PenaliteRepository $repository;

    protected function setUp(): void {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec("CREATE TABLE Penalites (Penalite_ID INTEGER PRIMARY KEY AUTOINCREMENT, Membre_ID INTEGER NOT NULL, Reservation_ID INTEGER, Date_Debut TEXT NOT NULL, Date_Fin TEXT NOT NULL, Cause TEXT NOT NULL, Levee INTEGER NOT NULL DEFAULT 0, Levee_Par INTEGER, Levee_Le TEXT, Levee_Raison TEXT, Date_Creation TEXT DEFAULT CURRENT_TIMESTAMP)");
        
        $this->repository = new PenaliteRepository($this->pdo);
    }

    public function testInsertAjouteUnePenalite(): void {
        $p = new Penalite(null, 1, null, '2026-07-01', '2026-07-07', 'PAYMENT_MISSING');
        $id = $this->repository->insert($p);
        $this->assertGreaterThan(0, $id);
        $this->assertEquals('PAYMENT_MISSING', $this->repository->findById($id)->getCause());
    }
}
