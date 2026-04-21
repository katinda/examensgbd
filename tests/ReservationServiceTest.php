<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Reservation.php';
require_once __DIR__ . '/../models/Terrain.php';
require_once __DIR__ . '/../models/Membre.php';
require_once __DIR__ . '/../repositories/ReservationRepository.php';
require_once __DIR__ . '/../repositories/TerrainRepository.php';
require_once __DIR__ . '/../repositories/MembreRepository.php';
require_once __DIR__ . '/../repositories/InscriptionRepository.php';
require_once __DIR__ . '/../services/ReservationService.php';

// On teste la logique métier du ReservationService.
// On utilise des stubs pour simuler les repositories.

class ReservationServiceTest extends TestCase {

    private function creerReservation(int $id): Reservation {
        return new Reservation($id, 1, 1, '2026-05-10', '10:00:00', '11:30:00', 'PRIVE');
    }

    private function creerTerrain(int $id, bool $actif): Terrain {
        return new Terrain($id, 1, $id, "Terrain $id", $actif);
    }

    private function creerMembre(int $id, bool $actif = true): Membre {
        return new Membre($id, 'G0001', 'Dupont', 'Jean', null, null, 'G', null, $actif);
    }

    private function creerData(array $overrides = []): array {
        return array_merge([
            'terrain_id'      => 1,
            'organisateur_id' => 1,
            'date_match'      => '2026-05-10',
            'heure_debut'     => '10:00:00',
            'type'            => 'PRIVE',
        ], $overrides);
    }

    // PDO SQLite en mémoire — léger, supporte les transactions, utilisé par createReservation()
    private function creerPdo(): PDO {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }


    // Vérifie que getReservationById() délègue bien au repository
    public function testGetReservationByIdRetourneLaReservation(): void {
        $mockRepo = $this->createStub(ReservationRepository::class);
        $mockRepo->method('findById')->willReturn($this->creerReservation(1));

        $service = new ReservationService($mockRepo, $this->createStub(TerrainRepository::class), $this->createStub(MembreRepository::class), $this->createStub(InscriptionRepository::class), $this->creerPdo());
        $result  = $service->getReservationById(1);

        $this->assertNotNull($result);
        $this->assertEquals(1, $result->getReservationId());
    }


    // Vérifie que getReservationsByMembre() retourne la liste du repository
    public function testGetReservationsByMembreRetourneLesReservations(): void {
        $mockRepo = $this->createStub(ReservationRepository::class);
        $mockRepo->method('findByOrganisateur')->willReturn([
            $this->creerReservation(1),
            $this->creerReservation(2),
        ]);

        $service = new ReservationService($mockRepo, $this->createStub(TerrainRepository::class), $this->createStub(MembreRepository::class), $this->createStub(InscriptionRepository::class), $this->creerPdo());
        $this->assertCount(2, $service->getReservationsByMembre(1));
    }


    // Vérifie que getReservationsByTerrainAndDate() retourne la liste du repository
    public function testGetReservationsByTerrainAndDateRetourneLesReservations(): void {
        $mockRepo = $this->createStub(ReservationRepository::class);
        $mockRepo->method('findByTerrainAndDate')->willReturn([
            $this->creerReservation(1),
        ]);

        $service = new ReservationService($mockRepo, $this->createStub(TerrainRepository::class), $this->createStub(MembreRepository::class), $this->createStub(InscriptionRepository::class), $this->creerPdo());
        $this->assertCount(1, $service->getReservationsByTerrainAndDate(1, '2026-05-10'));
    }


    // Vérifie que createReservation() retourne 'terrain_introuvable' si le terrain n'existe pas
    public function testCreateReservationRetourneTerrainIntrouvable(): void {
        $mockTerrain = $this->createStub(TerrainRepository::class);
        $mockTerrain->method('findById')->willReturn(null);

        $service = new ReservationService($this->createStub(ReservationRepository::class), $mockTerrain, $this->createStub(MembreRepository::class), $this->createStub(InscriptionRepository::class), $this->creerPdo());
        $this->assertEquals('terrain_introuvable', $service->createReservation($this->creerData()));
    }


    // Vérifie que createReservation() retourne 'terrain_inactif' si le terrain est fermé
    public function testCreateReservationRetourneTerrainInactif(): void {
        $mockTerrain = $this->createStub(TerrainRepository::class);
        $mockTerrain->method('findById')->willReturn($this->creerTerrain(1, false));

        $service = new ReservationService($this->createStub(ReservationRepository::class), $mockTerrain, $this->createStub(MembreRepository::class), $this->createStub(InscriptionRepository::class), $this->creerPdo());
        $this->assertEquals('terrain_inactif', $service->createReservation($this->creerData()));
    }


    // Vérifie que createReservation() retourne 'organisateur_introuvable' si le membre n'existe pas
    public function testCreateReservationRetourneOrganisateurIntrouvable(): void {
        $mockTerrain = $this->createStub(TerrainRepository::class);
        $mockTerrain->method('findById')->willReturn($this->creerTerrain(1, true));
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockMembre->method('findById')->willReturn(null);

        $service = new ReservationService($this->createStub(ReservationRepository::class), $mockTerrain, $mockMembre, $this->createStub(InscriptionRepository::class), $this->creerPdo());
        $this->assertEquals('organisateur_introuvable', $service->createReservation($this->creerData()));
    }


    // Vérifie que createReservation() retourne 'creneau_pris' si le créneau est déjà réservé
    public function testCreateReservationRetourneCreneauPris(): void {
        $mockRepo = $this->createStub(ReservationRepository::class);
        $mockRepo->method('findByTerrainDateHeure')->willReturn($this->creerReservation(1));
        $mockTerrain = $this->createStub(TerrainRepository::class);
        $mockTerrain->method('findById')->willReturn($this->creerTerrain(1, true));
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockMembre->method('findById')->willReturn($this->creerMembre(1));

        $service = new ReservationService($mockRepo, $mockTerrain, $mockMembre, $this->createStub(InscriptionRepository::class), $this->creerPdo());
        $this->assertEquals('creneau_pris', $service->createReservation($this->creerData()));
    }


    // Vérifie que createReservation() retourne un ID si tout est valide
    public function testCreateReservationRetourneUnId(): void {
        $mockRepo = $this->createStub(ReservationRepository::class);
        $mockRepo->method('findByTerrainDateHeure')->willReturn(null);
        $mockRepo->method('insert')->willReturn(5);
        $mockTerrain = $this->createStub(TerrainRepository::class);
        $mockTerrain->method('findById')->willReturn($this->creerTerrain(1, true));
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockMembre->method('findById')->willReturn($this->creerMembre(1));

        $service = new ReservationService($mockRepo, $mockTerrain, $mockMembre, $this->createStub(InscriptionRepository::class), $this->creerPdo());
        $this->assertEquals(5, $service->createReservation($this->creerData()));
    }


    // Vérifie que createReservation() calcule correctement Heure_Fin = Heure_Debut + 1h30
    public function testCreateReservationCalculeHeureFin(): void {
        $reservationInseree = null;

        $mockRepo = $this->createStub(ReservationRepository::class);
        $mockRepo->method('findByTerrainDateHeure')->willReturn(null);
        $mockRepo->method('insert')->willReturnCallback(function (Reservation $r) use (&$reservationInseree) {
            $reservationInseree = $r;
            return 1;
        });
        $mockTerrain = $this->createStub(TerrainRepository::class);
        $mockTerrain->method('findById')->willReturn($this->creerTerrain(1, true));
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockMembre->method('findById')->willReturn($this->creerMembre(1));

        $service = new ReservationService($mockRepo, $mockTerrain, $mockMembre, $this->createStub(InscriptionRepository::class), $this->creerPdo());
        $service->createReservation($this->creerData(['heure_debut' => '09:00:00']));

        $this->assertNotNull($reservationInseree);
        $this->assertEquals('10:30:00', $reservationInseree->getHeureFin());
    }
}
