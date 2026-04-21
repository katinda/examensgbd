<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../models/Inscription.php';
require_once __DIR__ . '/../models/Reservation.php';
require_once __DIR__ . '/../models/Membre.php';
require_once __DIR__ . '/../repositories/InscriptionRepository.php';
require_once __DIR__ . '/../repositories/ReservationRepository.php';
require_once __DIR__ . '/../repositories/MembreRepository.php';
require_once __DIR__ . '/../services/InscriptionService.php';
class InscriptionServiceTest extends TestCase {
    public function testGetInscriptionsByReservationRetourneLesInscriptions(): void {
        $mockInscription = $this->createStub(InscriptionRepository::class);
        $mockInscription->method('findByReservation')->willReturn([new Inscription(1, 1, 1, true), new Inscription(2, 1, 2, false)]);
        $service = new InscriptionService($mockInscription, $this->createStub(ReservationRepository::class), $this->createStub(MembreRepository::class));
        $this->assertCount(2, $service->getInscriptionsByReservation(1));
    }
}
