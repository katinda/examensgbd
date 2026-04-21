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
    public function testAddJoueurRetourneMembreIntrouvable(): void {
        $mockReservation = $this->createStub(ReservationRepository::class);
        $mockReservation->method('findById')->willReturn(new Reservation(1, 1, 1, '2026-05-10', '10:00:00', '11:30:00', 'PRIVE'));
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockMembre->method('findById')->willReturn(null);
        $service = new InscriptionService($this->createStub(InscriptionRepository::class), $mockReservation, $mockMembre);
        $this->assertEquals('membre_introuvable', $service->addJoueur(1, 999));
    }
}
