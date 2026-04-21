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

    // Retire un joueur d'une réservation.
    // Retourne false si l'inscription n'existe pas.
    public function removeJoueur(int $reservationId, int $membreId): bool {
        $inscription = $this->inscriptionRepository->findByReservationAndMembre($reservationId, $membreId);
        if ($inscription === null) return false;
        $this->inscriptionRepository->delete($reservationId, $membreId);
        return true;
    }
}
