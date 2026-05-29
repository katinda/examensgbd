<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Reservation.php';
require_once __DIR__ . '/../models/Terrain.php';
require_once __DIR__ . '/../models/Membre.php';
require_once __DIR__ . '/../repositories/ReservationRepository.php';
require_once __DIR__ . '/../repositories/TerrainRepository.php';
require_once __DIR__ . '/../repositories/MembreRepository.php';
require_once __DIR__ . '/../repositories/InscriptionRepository.php';
require_once __DIR__ . '/../repositories/AdministrateurRepository.php';
require_once __DIR__ . '/../models/Administrateur.php';
require_once __DIR__ . '/../services/ReservationService.php';

// On teste la logique métier du ReservationService.
// On utilise des stubs pour simuler les repositories.

class ReservationServiceTest extends TestCase {

    private function creerReservation(int $id, int $terrainId = 1): Reservation {
        return new Reservation($id, $terrainId, 1, '2026-05-10', '10:00:00', '11:30:00', 'PRIVE');
    }

    private function creerTerrain(int $id, bool $actif, int $siteId = 1): Terrain {
        return new Terrain($id, $siteId, $id, "Terrain $id", $actif);
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

        $service = new ReservationService($mockRepo, $this->createStub(TerrainRepository::class), $this->createStub(MembreRepository::class), $this->createStub(InscriptionRepository::class), $this->createStub(AdministrateurRepository::class), $this->creerPdo());
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

        $service = new ReservationService($mockRepo, $this->createStub(TerrainRepository::class), $this->createStub(MembreRepository::class), $this->createStub(InscriptionRepository::class), $this->createStub(AdministrateurRepository::class), $this->creerPdo());
        $this->assertCount(2, $service->getReservationsByMembre(1));
    }


    // Vérifie que getReservationsByTerrainAndDate() retourne la liste du repository
    public function testGetReservationsByTerrainAndDateRetourneLesReservations(): void {
        $mockRepo = $this->createStub(ReservationRepository::class);
        $mockRepo->method('findByTerrainAndDate')->willReturn([
            $this->creerReservation(1),
        ]);

        $service = new ReservationService($mockRepo, $this->createStub(TerrainRepository::class), $this->createStub(MembreRepository::class), $this->createStub(InscriptionRepository::class), $this->createStub(AdministrateurRepository::class), $this->creerPdo());
        $this->assertCount(1, $service->getReservationsByTerrainAndDate(1, '2026-05-10'));
    }


    // Vérifie que createReservation() retourne 'terrain_introuvable' si le terrain n'existe pas
    public function testCreateReservationRetourneTerrainIntrouvable(): void {
        $mockTerrain = $this->createStub(TerrainRepository::class);
        $mockTerrain->method('findById')->willReturn(null);

        $service = new ReservationService($this->createStub(ReservationRepository::class), $mockTerrain, $this->createStub(MembreRepository::class), $this->createStub(InscriptionRepository::class), $this->createStub(AdministrateurRepository::class), $this->creerPdo());
        $this->assertEquals('terrain_introuvable', $service->createReservation($this->creerData()));
    }


    // Vérifie que createReservation() retourne 'terrain_inactif' si le terrain est fermé
    public function testCreateReservationRetourneTerrainInactif(): void {
        $mockTerrain = $this->createStub(TerrainRepository::class);
        $mockTerrain->method('findById')->willReturn($this->creerTerrain(1, false));

        $service = new ReservationService($this->createStub(ReservationRepository::class), $mockTerrain, $this->createStub(MembreRepository::class), $this->createStub(InscriptionRepository::class), $this->createStub(AdministrateurRepository::class), $this->creerPdo());
        $this->assertEquals('terrain_inactif', $service->createReservation($this->creerData()));
    }


    // Vérifie que createReservation() retourne 'organisateur_introuvable' si le membre n'existe pas
    public function testCreateReservationRetourneOrganisateurIntrouvable(): void {
        $mockTerrain = $this->createStub(TerrainRepository::class);
        $mockTerrain->method('findById')->willReturn($this->creerTerrain(1, true));
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockMembre->method('findById')->willReturn(null);

        $service = new ReservationService($this->createStub(ReservationRepository::class), $mockTerrain, $mockMembre, $this->createStub(InscriptionRepository::class), $this->createStub(AdministrateurRepository::class), $this->creerPdo());
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

        $service = new ReservationService($mockRepo, $mockTerrain, $mockMembre, $this->createStub(InscriptionRepository::class), $this->createStub(AdministrateurRepository::class), $this->creerPdo());
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

        $service = new ReservationService($mockRepo, $mockTerrain, $mockMembre, $this->createStub(InscriptionRepository::class), $this->createStub(AdministrateurRepository::class), $this->creerPdo());
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

        $service = new ReservationService($mockRepo, $mockTerrain, $mockMembre, $this->createStub(InscriptionRepository::class), $this->createStub(AdministrateurRepository::class), $this->creerPdo());
        $service->createReservation($this->creerData(['heure_debut' => '09:00:00']));

        $this->assertNotNull($reservationInseree);
        $this->assertEquals('10:30:00', $reservationInseree->getHeureFin());
    }


    // ─── Filtrage par rôle admin ─────────────────────────────────────────────

    private function creerAdminRepo(string $type, ?int $siteId = null): AdministrateurRepository {
        $admin = new Administrateur(1, 'admin', 'hash', null, null, null, $type, $siteId, true);
        $mock  = $this->createStub(AdministrateurRepository::class);
        $mock->method('findById')->willReturn($admin);
        return $mock;
    }

    // Admin GLOBAL → voit toutes les réservations du membre
    public function testGetReservationsByMembreAdminGlobalVoitTout(): void {
        $mockRepo = $this->createStub(ReservationRepository::class);
        $mockRepo->method('findByOrganisateur')->willReturn([
            $this->creerReservation(1, 1),
            $this->creerReservation(2, 2),
        ]);
        $mockTerrain = $this->createStub(TerrainRepository::class);

        $service = new ReservationService($mockRepo, $mockTerrain, $this->createStub(MembreRepository::class), $this->createStub(InscriptionRepository::class), $this->creerAdminRepo('GLOBAL'), $this->creerPdo());
        $this->assertCount(2, $service->getReservationsByMembre(1, 1));
    }

    // Admin SITE → uniquement les réservations sur les terrains de son site
    public function testGetReservationsByMembreAdminSiteFiltreSonSite(): void {
        $mockRepo = $this->createStub(ReservationRepository::class);
        $mockRepo->method('findByOrganisateur')->willReturn([
            $this->creerReservation(1, 1),
            $this->creerReservation(2, 2),
        ]);
        $mockTerrain = $this->createStub(TerrainRepository::class);
        $mockTerrain->method('findById')->willReturnMap([
            [1, $this->creerTerrain(1, true, 1)], // terrain du site 1
            [2, $this->creerTerrain(2, true, 2)], // terrain du site 2
        ]);

        $service = new ReservationService($mockRepo, $mockTerrain, $this->createStub(MembreRepository::class), $this->createStub(InscriptionRepository::class), $this->creerAdminRepo('SITE', 1), $this->creerPdo());
        $result  = $service->getReservationsByMembre(1, 1);

        $this->assertCount(1, $result);
        $this->assertEquals(1, $result[0]->getTerrainId());
    }
}
