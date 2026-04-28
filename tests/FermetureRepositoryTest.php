<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Fermeture.php';
require_once __DIR__ . '/../repositories/FermetureRepository.php';

class FermetureRepositoryTest extends TestCase {

    private PDO $pdo;
    private FermetureRepository $repository;

    protected function setUp(): void {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec("CREATE TABLE Fermetures (Fermeture_ID INTEGER PRIMARY KEY AUTOINCREMENT, Site_ID INTEGER, Date_Debut TEXT NOT NULL, Date_Fin TEXT NOT NULL, Raison TEXT, Date_Creation TEXT DEFAULT CURRENT_DATE)");
        $this->repository = new FermetureRepository($this->pdo);
    }

    // Vérifie que insert() ajoute bien une nouvelle fermeture en base
    public function testInsertAjouteUneFermeture(): void {
        $fermeture = new Fermeture(null, 2, '2026-07-01', '2026-07-05', 'Congés');
        $id = $this->repository->insert($fermeture);

        $this->assertGreaterThan(0, $id, "insert() doit retourner un ID valide");

        $fermetureCree = $this->repository->findById($id);
        $this->assertEquals('2026-07-01', $fermetureCree->getDateDebut());
    }
}
