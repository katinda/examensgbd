<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../models/Reservation.php';
require_once __DIR__ . '/../models/Terrain.php';
require_once __DIR__ . '/../models/Membre.php';
require_once __DIR__ . '/../repositories/ReservationRepository.php';
require_once __DIR__ . '/../repositories/TerrainRepository.php';
require_once __DIR__ . '/../repositories/MembreRepository.php';
require_once __DIR__ . '/../services/ReservationService.php';

class ReservationServiceTest extends TestCase {
    public function testCreateReservationRetourneCreneauPris(): void {
        $mockRepo = $this->createStub(ReservationRepository::class);
        $mockRepo->method('findByTerrainDateHeure')->willReturn(new Reservation(1, 1, 1, '2026-05-10', '10:00:00', '11:30:00', 'PRIVE'));
        $mockTerrain = $this->createStub(TerrainRepository::class);
        $mockTerrain->method('findById')->willReturn(new Terrain(1, 1, 1, 'Terrain 1', true));
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockMembre->method('findById')->willReturn(new Membre(1, 'G0001', 'Dupont', 'Jean', null, null, 'G', null, true));
        $service = new ReservationService($mockRepo, $mockTerrain, $mockMembre);
        $result = $service->createReservation(['terrain_id' => 1, 'organisateur_id' => 1, 'date_match' => '2026-05-10', 'heure_debut' => '10:00:00', 'type' => 'PRIVE']);
        $this->assertEquals('creneau_pris', $result);
    }
}
