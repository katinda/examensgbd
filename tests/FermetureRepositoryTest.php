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

    // Vérifie que findById() retourne null quand l'ID n'existe pas
    public function testFindByIdRetourneNullSiInexistant(): void {
        $fermeture = $this->repository->findById(999);
        $this->assertNull($fermeture, "Un ID inexistant doit retourner null");
    }
}
