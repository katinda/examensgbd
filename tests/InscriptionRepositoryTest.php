<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Inscription.php';
require_once __DIR__ . '/../repositories/InscriptionRepository.php';

// On teste toutes les méthodes SQL de InscriptionRepository.
// On utilise une base SQLite en mémoire pour ne pas toucher à la vraie base.

class InscriptionRepositoryTest extends TestCase {

    private PDO $pdo;
    private InscriptionRepository $repository;

    // Crée une base temporaire avec 2 inscriptions avant chaque test
    protected function setUp(): void {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec("
            CREATE TABLE Inscriptions (
                Inscription_ID  INTEGER PRIMARY KEY AUTOINCREMENT,
                Reservation_ID  INTEGER NOT NULL,
                Membre_ID       INTEGER NOT NULL,
                Est_Organisateur INTEGER NOT NULL DEFAULT 0,
                UNIQUE (Reservation_ID, Membre_ID)
            )
        ");

        // 2 inscriptions : l'organisateur (membre 1) et un joueur invité (membre 2)
        $this->pdo->exec("
            INSERT INTO Inscriptions (Reservation_ID, Membre_ID, Est_Organisateur)
            VALUES
                (1, 1, 1),
                (1, 2, 0)
        ");

        $this->repository = new InscriptionRepository($this->pdo);
    }


    // Vérifie que findByReservation() retourne toutes les inscriptions de la réservation
    public function testFindByReservationRetourneLesInscriptions(): void {
        $inscriptions = $this->repository->findByReservation(1);
        $this->assertCount(2, $inscriptions);
    }


    // Vérifie que findByReservation() retourne un tableau vide si la réservation n'a pas d'inscriptions
    public function testFindByReservationRetourneVideSiAucune(): void {
        $this->assertCount(0, $this->repository->findByReservation(999));
    }


    // Vérifie que findByReservationAndMembre() retourne l'inscription si le membre est inscrit
    public function testFindByReservationAndMembreRetourneLInscription(): void {
        $inscription = $this->repository->findByReservationAndMembre(1, 1);
        $this->assertNotNull($inscription);
        $this->assertTrue($inscription->isEstOrganisateur());
    }


    // Vérifie que findByReservationAndMembre() retourne null si le membre n'est pas inscrit
    public function testFindByReservationAndMembreRetourneNullSiNonInscrit(): void {
        $this->assertNull($this->repository->findByReservationAndMembre(1, 999));
    }


    // Vérifie que countByReservation() retourne le bon nombre d'inscrits
    public function testCountByReservationRetourneLeBonNombre(): void {
        $this->assertEquals(2, $this->repository->countByReservation(1));
        $this->assertEquals(0, $this->repository->countByReservation(999));
    }


    // Vérifie que insert() ajoute bien une inscription en base
    public function testInsertAjouteUneInscription(): void {
        $inscription = new Inscription(null, 1, 3, false);
        $id = $this->repository->insert($inscription);

        $this->assertGreaterThan(0, $id);
        $this->assertEquals(3, $this->repository->countByReservation(1));
    }


    // Vérifie que delete() supprime bien une inscription
    public function testDeleteSupprimeUneInscription(): void {
        $this->repository->delete(1, 2);
        $this->assertNull($this->repository->findByReservationAndMembre(1, 2));
        $this->assertEquals(1, $this->repository->countByReservation(1));
    }
}
