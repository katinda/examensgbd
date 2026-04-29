<?php

require_once __DIR__ . '/../repositories/PenaliteRepository.php';
require_once __DIR__ . '/../repositories/MembreRepository.php';
require_once __DIR__ . '/../repositories/AdministrateurRepository.php';

class PenaliteService {

    private const CAUSES_VALIDES = ['PRIVATE_INCOMPLETE', 'PAYMENT_MISSING', 'OTHER'];

    public function __construct(
        private PenaliteRepository       $penaliteRepository,
        private MembreRepository         $membreRepository,
        private AdministrateurRepository $adminRepository
    ) {}

    // Crée une pénalité. Erreurs : 'cause_invalide', 'dates_invalides', 'membre_introuvable'
    public function createPenalite(array $data): int|string {
        $cause     = $data['cause']      ?? '';
        $dateDebut = $data['date_debut'] ?? '';
        $dateFin   = $data['date_fin']   ?? '';
        $membreId  = isset($data['membre_id']) ? (int) $data['membre_id'] : 0;

        if (!in_array($cause, self::CAUSES_VALIDES, true)) {
            return 'cause_invalide';
        }

        if ($dateDebut > $dateFin) {
            return 'dates_invalides';
        }

        if ($this->membreRepository->findById($membreId) === null) {
            return 'membre_introuvable';
        }

        $penalite = new Penalite(
            null,
            $membreId,
            isset($data['reservation_id']) ? (int) $data['reservation_id'] : null,
            $dateDebut,
            $dateFin,
            $cause
        );

        return $this->penaliteRepository->insert($penalite);
    }
}
