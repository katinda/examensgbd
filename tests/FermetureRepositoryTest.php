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
        $this->pdo->exec("INSERT INTO Fermetures (Site_ID, Date_Debut, Date_Fin, Raison) VALUES (1, '2026-08-01', '2026-08-07', 'Travaux'), (NULL, '2026-12-25', '2026-12-25', 'Noël')");
        $this->repository = new FermetureRepository($this->pdo);
    }

    // Vérifie que findGlobales() retourne uniquement les fermetures globales
    public function testFindGlobalesRetourneLesfermeturesGlobales(): void {
        $fermetures = $this->repository->findGlobales();
        $this->assertCount(1, $fermetures, "findGlobales() doit retourner 1 fermeture globale");
        $this->assertNull($fermetures[0]->getSiteId(), "Une fermeture globale a Site_ID null");
    }
}
