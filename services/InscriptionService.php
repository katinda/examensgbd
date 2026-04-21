<?php
require_once __DIR__ . '/../repositories/InscriptionRepository.php';
require_once __DIR__ . '/../repositories/ReservationRepository.php';
require_once __DIR__ . '/../repositories/MembreRepository.php';

class InscriptionService {
    public function __construct(
        private InscriptionRepository $inscriptionRepository,
        private ReservationRepository $reservationRepository,
        private MembreRepository      $membreRepository
    ) {}

    // Retourne la liste des joueurs inscrits à une réservation
    public function getInscriptionsByReservation(int $reservationId): array {
        return $this->inscriptionRepository->findByReservation($reservationId);
    }
}
