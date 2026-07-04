<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Reservation.php';
require_once __DIR__ . '/../repositories/ReservationRepository.php';

// On teste toutes les méthodes SQL de ReservationRepository.
// On utilise une base SQLite en mémoire pour ne pas toucher à la vraie base.

class ReservationRepositoryTest extends TestCase {

    private PDO $pdo;
    private ReservationRepository $repository;

    // Crée une base temporaire avec 2 réservations avant chaque test
    protected function setUp(): void {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec("
            CREATE TABLE Reservations (
                Reservation_ID  INTEGER PRIMARY KEY AUTOINCREMENT,
                Terrain_ID      INTEGER NOT NULL,
                Organisateur_ID INTEGER NOT NULL,
                Date_Match      TEXT NOT NULL,
                Heure_Debut     TEXT NOT NULL,
                Heure_Fin       TEXT NOT NULL,
                Type            TEXT NOT NULL,
                Etat            TEXT NOT NULL DEFAULT 'EN_COURS',
                Prix_Total      REAL NOT NULL DEFAULT 60.00,
                Date_Creation   TEXT DEFAULT CURRENT_TIMESTAMP,
                LastUpdate      TEXT DEFAULT CURRENT_TIMESTAMP,
                UNIQUE (Terrain_ID, Date_Match, Heure_Debut)
            )
        ");

        // 2 réservations sur le terrain 1 à des créneaux différents
        $this->pdo->exec("
            INSERT INTO Reservations (Terrain_ID, Organisateur_ID, Date_Match, Heure_Debut, Heure_Fin, Type, Etat, Prix_Total)
            VALUES
                (1, 1, '2026-05-10', '10:00:00', '11:30:00', 'PRIVE',  'EN_COURS', 60.00),
                (1, 2, '2026-05-10', '14:00:00', '15:30:00', 'PUBLIC', 'EN_COURS', 60.00)
        ");

        $this->pdo->exec("
            CREATE TABLE Inscriptions (
                Inscription_ID   INTEGER PRIMARY KEY AUTOINCREMENT,
                Reservation_ID   INTEGER NOT NULL,
                Membre_ID        INTEGER NOT NULL,
                Est_Organisateur INTEGER NOT NULL DEFAULT 0
            )
        ");

        $this->pdo->exec("
            CREATE TABLE Paiements (
                Paiement_ID    INTEGER PRIMARY KEY AUTOINCREMENT,
                Inscription_ID INTEGER NOT NULL,
                Montant        REAL NOT NULL,
                Est_Annule     INTEGER NOT NULL DEFAULT 0
            )
        ");

        $this->repository = new ReservationRepository($this->pdo);
    }


    // Vérifie que findById() retourne la bonne réservation
    public function testFindByIdRetourneLaBonneReservation(): void {
        $reservation = $this->repository->findById(1);
        $this->assertNotNull($reservation);
        $this->assertEquals('10:00:00', $reservation->getHeureDebut());
        $this->assertEquals('PRIVE', $reservation->getType());
    }


    // Vérifie que findById() retourne null si l'ID n'existe pas
    public function testFindByIdRetourneNullSiInexistant(): void {
        $this->assertNull($this->repository->findById(999));
    }


    // Vérifie que findByOrganisateur() retourne les réservations du membre
    public function testFindByOrganisateurRetourneLesReservations(): void {
        $reservations = $this->repository->findByOrganisateur(1);
        $this->assertCount(1, $reservations);
        $this->assertEquals(1, $reservations[0]->getOrganisateurId());
    }


    // Vérifie que findByOrganisateur() retourne un tableau vide si le membre n'a pas de réservation
    public function testFindByOrganisateurRetourneVideSiAucune(): void {
        $this->assertCount(0, $this->repository->findByOrganisateur(999));
    }


    // Vérifie que findByTerrainAndDate() retourne les réservations du terrain à la date donnée
    public function testFindByTerrainAndDateRetourneLesReservations(): void {
        $reservations = $this->repository->findByTerrainAndDate(1, '2026-05-10');
        $this->assertCount(2, $reservations);
    }


    // Vérifie que findByTerrainAndDate() retourne un tableau vide pour une date sans réservation
    public function testFindByTerrainAndDateRetourneVideSiAucune(): void {
        $this->assertCount(0, $this->repository->findByTerrainAndDate(1, '2099-01-01'));
    }


    // Vérifie que findByTerrainDateHeure() retourne la réservation si le créneau est pris
    public function testFindByTerrainDateHeureRetourneLaReservation(): void {
        $reservation = $this->repository->findByTerrainDateHeure(1, '2026-05-10', '10:00:00');
        $this->assertNotNull($reservation);
        $this->assertEquals(1, $reservation->getReservationId());
    }


    // Vérifie que findByTerrainDateHeure() retourne null si le créneau est libre
    public function testFindByTerrainDateHeureRetourneNullSiLibre(): void {
        $this->assertNull($this->repository->findByTerrainDateHeure(1, '2026-05-10', '08:00:00'));
    }


    // Vérifie que insert() ajoute bien une réservation en base
    public function testInsertAjouteUneReservation(): void {
        $reservation = new Reservation(null, 1, 1, '2026-05-11', '09:00:00', '10:30:00', 'PRIVE');

        $id = $this->repository->insert($reservation);

        $this->assertGreaterThan(0, $id);
        $inserted = $this->repository->findById($id);
        $this->assertEquals('2026-05-11', $inserted->getDateMatch());
        $this->assertEquals('09:00:00', $inserted->getHeureDebut());
    }


    // Vérifie que update() modifie bien l'état et le prix d'une réservation
    public function testUpdateModifieUneReservation(): void {
        $reservation = $this->repository->findById(1);
        $reservation->setEtat('TERMINEE');
        $reservation->setPrixTotal(45.00);
        $this->repository->update($reservation);

        $modifie = $this->repository->findById(1);
        $this->assertEquals('TERMINEE', $modifie->getEtat());
        $this->assertEquals(45.00, $modifie->getPrixTotal());
    }


    // ─── Solde dû (matchs FORFAIT) ───────────────────────────────────────────

    // Insère une réservation FORFAIT pour un organisateur, avec un nombre de joueurs payés donné
    private function creerMatchForfait(int $organisateurId, int $nbJoueursPayes): void {
        $this->pdo->exec("
            INSERT INTO Reservations (Terrain_ID, Organisateur_ID, Date_Match, Heure_Debut, Heure_Fin, Type, Etat, Prix_Total)
            VALUES (1, $organisateurId, '2026-04-01', '09:00:00', '10:30:00', 'PUBLIC', 'FORFAIT', 60.00)
        ");
        $reservationId = (int) $this->pdo->lastInsertId();

        for ($i = 1; $i <= $nbJoueursPayes; $i++) {
            $this->pdo->exec("INSERT INTO Inscriptions (Reservation_ID, Membre_ID) VALUES ($reservationId, $i)");
            $inscriptionId = (int) $this->pdo->lastInsertId();
            $this->pdo->exec("INSERT INTO Paiements (Inscription_ID, Montant, Est_Annule) VALUES ($inscriptionId, 15.00, 0)");
        }
    }

    // Match FORFAIT avec seulement 2 joueurs payés sur 4 → déficit de 30€ → solde dû
    public function testHasSoldeDuRetourneTrueSiDeficit(): void {
        $this->creerMatchForfait(organisateurId: 10, nbJoueursPayes: 2);
        $this->assertTrue($this->repository->hasSoldeDu(10));
    }

    // Match FORFAIT mais les 4 joueurs ont finalement tous payé → déficit nul → pas de solde dû
    public function testHasSoldeDuRetourneFalseSiDeficitNul(): void {
        $this->creerMatchForfait(organisateurId: 11, nbJoueursPayes: 4);
        $this->assertFalse($this->repository->hasSoldeDu(11));
    }

    // Membre organisateur d'un match encore EN_COURS (pas FORFAIT) → pas de solde dû
    public function testHasSoldeDuRetourneFalseSiPasForfait(): void {
        $this->assertFalse($this->repository->hasSoldeDu(1));
    }

    // Membre sans aucune réservation → pas de solde dû
    public function testHasSoldeDuRetourneFalseSiAucuneReservation(): void {
        $this->assertFalse($this->repository->hasSoldeDu(999));
    }
}
