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
    public function testAddJoueurRetourneReservationIntrouvable(): void {
        $mockReservation = $this->createStub(ReservationRepository::class);
        $mockReservation->method('findById')->willReturn(null);
        $service = new InscriptionService($this->createStub(InscriptionRepository::class), $mockReservation, $this->createStub(MembreRepository::class));
        $this->assertEquals('reservation_introuvable', $service->addJoueur(999, 1));
    }
}
