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
    public function testGetReservationsByMembreRetourneLesReservations(): void {
        $mockRepo = $this->createStub(ReservationRepository::class);
        $mockRepo->method('findByOrganisateur')->willReturn([
            new Reservation(1, 1, 1, '2026-05-10', '10:00:00', '11:30:00', 'PRIVE'),
            new Reservation(2, 1, 1, '2026-05-11', '10:00:00', '11:30:00', 'PRIVE'),
        ]);
        $service = new ReservationService($mockRepo, $this->createStub(TerrainRepository::class), $this->createStub(MembreRepository::class));
        $this->assertCount(2, $service->getReservationsByMembre(1));
    }
}
