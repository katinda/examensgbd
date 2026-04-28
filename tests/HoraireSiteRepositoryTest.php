<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . "/../models/HoraireSite.php";
require_once __DIR__ . "/../repositories/HoraireSiteRepository.php";
class HoraireSiteRepositoryTest extends TestCase {
    private PDO $pdo; private HoraireSiteRepository $repository; protected function setUp(): void { $this->pdo = new PDO("sqlite::memory:"); $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); $this->pdo->exec("CREATE TABLE Horaires_Sites (Horaire_ID INTEGER PRIMARY KEY AUTOINCREMENT, Site_ID INTEGER NOT NULL, Annee INTEGER NOT NULL, Heure_Debut TEXT NOT NULL, Heure_Fin TEXT NOT NULL, UNIQUE (Site_ID, Annee))"); $this->pdo->exec("INSERT INTO Horaires_Sites (Site_ID, Annee, Heure_Debut, Heure_Fin) VALUES (1, 2026, \"08:00:00\", \"22:00:00\"), (2, 2026, \"09:00:00\", \"21:00:00\")"); $this->repository = new HoraireSiteRepository($this->pdo); }
    public function testInsertAjouteUnHoraire(): void { $horaire = new HoraireSite(null, 3, 2026, '07:00:00', '23:00:00'); $id = $this->repository->insert($horaire); $this->assertGreaterThan(0, $id); $this->assertEquals('07:00:00', $this->repository->findById($id)->getHeureDebut()); }
}
