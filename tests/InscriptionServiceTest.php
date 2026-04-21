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
    public function testRemoveJoueurRetourneFalseSiNonInscrit(): void {
        $mockInscription = $this->createStub(InscriptionRepository::class);
        $mockInscription->method('findByReservationAndMembre')->willReturn(null);
        $service = new InscriptionService($mockInscription, $this->createStub(ReservationRepository::class), $this->createStub(MembreRepository::class));
        $this->assertFalse($service->removeJoueur(1, 999));
    }
}
