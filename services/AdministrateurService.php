<?php

require_once __DIR__ . '/../repositories/AdministrateurRepository.php';
require_once __DIR__ . '/../repositories/SiteRepository.php';

class AdministrateurService {

    public function __construct(
        private AdministrateurRepository $adminRepository,
        private SiteRepository           $siteRepository
    ) {}

    // Retourne un administrateur actif par son ID, ou null s'il est inactif ou inexistant
    public function getAdministrateurById(int $id): ?Administrateur {
        $admin = $this->adminRepository->findById($id);

        if ($admin === null || !$admin->isEstActif()) {
            return null;
        }

        return $admin;
    }
}
