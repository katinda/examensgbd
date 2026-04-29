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

    // Retourne les pénalités non levées
    public function getPenalitesActives(): array {
        return $this->penaliteRepository->findActives();
    }
}
