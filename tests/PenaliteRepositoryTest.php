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
                $this->pdo->exec("INSERT INTO Penalites (Membre_ID, Date_Debut, Date_Fin, Cause, Levee) VALUES (1, '2026-05-01', '2026-05-15', 'PRIVATE_INCOMPLETE', 0), (2, '2026-06-01', '2026-06-07', 'OTHER', 1)");
        $this->repository = new PenaliteRepository($this->pdo);
    }

    public function testFindActivesRetourneLespenalitesNonLevees(): void {
        $penalites = $this->repository->findActives();
        $this->assertCount(1, $penalites);
        $this->assertFalse($penalites[0]->isLevee());
    }
}
