<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Paiement.php';
require_once __DIR__ . '/../repositories/PaiementRepository.php';

// On teste toutes les méthodes SQL de PaiementRepository.
// On utilise une base SQLite en mémoire pour ne pas toucher à la vraie base.

class PaiementRepositoryTest extends TestCase {

    private PDO $pdo;
    private PaiementRepository $repository;

    // Crée une base temporaire avec 1 paiement avant chaque test
    protected function setUp(): void {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec("
            CREATE TABLE Paiements (
                Paiement_ID       INTEGER PRIMARY KEY AUTOINCREMENT,
                Inscription_ID    INTEGER NOT NULL UNIQUE,
                Montant           REAL NOT NULL,
                Date_Paiement     TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                Methode           TEXT NULL,
                Est_Annule        INTEGER NOT NULL DEFAULT 0,
                Montant_Rembourse REAL NULL,
                Date_Annulation   TEXT NULL
            )
        ");

        // 1 paiement pour l'inscription 1 (avec méthode CARTE)
        $this->pdo->exec("
            INSERT INTO Paiements (Inscription_ID, Montant, Date_Paiement, Methode)
            VALUES (1, 15.00, '2026-04-22 10:00:00', 'CARTE')
        ");

        $this->repository = new PaiementRepository($this->pdo);
    }


    // Vérifie que findByInscription() retourne le paiement existant
    public function testFindByInscriptionRetourneLePaiement(): void {
        $paiement = $this->repository->findByInscription(1);
        $this->assertNotNull($paiement);
        $this->assertEquals(15.00, $paiement->getMontant());
        $this->assertEquals('CARTE', $paiement->getMethode());
        $this->assertFalse($paiement->isEstAnnule());
    }


    // Vérifie que findByInscription() retourne null si aucun paiement
    public function testFindByInscriptionRetourneNullSiAucunPaiement(): void {
        $this->assertNull($this->repository->findByInscription(999));
    }


    // Vérifie que findById() retourne le paiement correspondant
    public function testFindByIdRetourneLePaiement(): void {
        $paiement = $this->repository->findById(1);
        $this->assertNotNull($paiement);
        $this->assertEquals(1, $paiement->getInscriptionId());
    }


    // Vérifie que findById() retourne null si le paiement n'existe pas
    public function testFindByIdRetourneNullSiInexistant(): void {
        $this->assertNull($this->repository->findById(999));
    }


    // Vérifie que insert() ajoute bien un paiement en base
    public function testInsertAjouteUnPaiement(): void {
        $paiement = new Paiement(null, 2, 15.00, null, 'VIREMENT');
        $id = $this->repository->insert($paiement);

        $this->assertGreaterThan(0, $id);
        $this->assertNotNull($this->repository->findByInscription(2));
    }


    // Vérifie que update() enregistre bien l'annulation
    public function testUpdateEnregistreLAnnulation(): void {
        $paiement = $this->repository->findById(1);
        $paiement->annuler(15.00, '2026-04-22 11:00:00');
        $this->repository->update($paiement);

        $maj = $this->repository->findById(1);
        $this->assertTrue($maj->isEstAnnule());
        $this->assertEquals(15.00, $maj->getMontantRembourse());
        $this->assertEquals('2026-04-22 11:00:00', $maj->getDateAnnulation());
    }
}
