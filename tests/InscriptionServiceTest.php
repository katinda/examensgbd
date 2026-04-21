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
    public function testAddJoueurRetourneUnId(): void {
        $mockInscription = $this->createStub(InscriptionRepository::class);
        $mockInscription->method('countByReservation')->willReturn(1);
        $mockInscription->method('findByReservationAndMembre')->willReturn(null);
        $mockInscription->method('insert')->willReturn(3);
        $mockReservation = $this->createStub(ReservationRepository::class);
        $mockReservation->method('findById')->willReturn(new Reservation(1, 1, 1, '2026-05-10', '10:00:00', '11:30:00', 'PRIVE'));
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockMembre->method('findById')->willReturn(new Membre(2, 'G0002', 'Martin', 'Alice', null, null, 'G', null, true));
        $service = new InscriptionService($mockInscription, $mockReservation, $mockMembre);
        $this->assertEquals(3, $service->addJoueur(1, 2));
    }
}
