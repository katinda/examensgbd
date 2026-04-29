<?php

require_once __DIR__ . '/../repositories/AdministrateurRepository.php';
require_once __DIR__ . '/../repositories/SiteRepository.php';

class AdministrateurService {

    public function __construct(
        private AdministrateurRepository $adminRepository,
        private SiteRepository           $siteRepository
    ) {}

    // Retourne tous les administrateurs actifs
    public function getAllAdministrateurs(): array {
        $tous = $this->adminRepository->findAll();
        return array_values(array_filter($tous, fn($a) => $a->isEstActif()));
    }
}
