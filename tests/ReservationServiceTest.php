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
    public function testCreateReservationRetourneTerrainInactif(): void {
        $mockTerrain = $this->createStub(TerrainRepository::class);
        $mockTerrain->method('findById')->willReturn(new Terrain(1, 1, 1, 'Terrain 1', false));
        $service = new ReservationService($this->createStub(ReservationRepository::class), $mockTerrain, $this->createStub(MembreRepository::class));
        $result = $service->createReservation(['terrain_id' => 1, 'organisateur_id' => 1, 'date_match' => '2026-05-10', 'heure_debut' => '10:00:00', 'type' => 'PRIVE']);
        $this->assertEquals('terrain_inactif', $result);
    }
}
