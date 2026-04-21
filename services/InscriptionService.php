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

    // Ajoute un joueur à une réservation.
    // Erreurs : 'reservation_introuvable', 'membre_introuvable', 'reservation_complete', 'deja_inscrit'
    public function addJoueur(int $reservationId, int $membreId): int|string {
        $reservation = $this->reservationRepository->findById($reservationId);
        if ($reservation === null) return 'reservation_introuvable';

        $membre = $this->membreRepository->findById($membreId);
        if ($membre === null || !$membre->isEstActif()) return 'membre_introuvable';

        if ($this->inscriptionRepository->countByReservation($reservationId) >= 4) return 'reservation_complete';

        if ($this->inscriptionRepository->findByReservationAndMembre($reservationId, $membreId) !== null) return 'deja_inscrit';

        $inscription = new Inscription(null, $reservationId, $membreId, false);
        return $this->inscriptionRepository->insert($inscription);
    }
}
