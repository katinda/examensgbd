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
    public function testGetReservationByIdRetourneLaReservation(): void {
        $reservation = new Reservation(1, 1, 1, '2026-05-10', '10:00:00', '11:30:00', 'PRIVE');
        $mockRepo = $this->createStub(ReservationRepository::class);
        $mockRepo->method('findById')->willReturn($reservation);
        $service = new ReservationService($mockRepo, $this->createStub(TerrainRepository::class), $this->createStub(MembreRepository::class));
        $result = $service->getReservationById(1);
        $this->assertNotNull($result);
        $this->assertEquals(1, $result->getReservationId());
    }
}
