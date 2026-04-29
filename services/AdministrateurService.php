<?php

require_once __DIR__ . '/../repositories/AdministrateurRepository.php';
require_once __DIR__ . '/../repositories/SiteRepository.php';

class AdministrateurService {

    public function __construct(
        private AdministrateurRepository $adminRepository,
        private SiteRepository           $siteRepository
    ) {}

    // Retourne un administrateur actif par son login, ou null s'il est inactif ou inexistant
    public function getAdministrateurByLogin(string $login): ?Administrateur {
        $admin = $this->adminRepository->findByLogin($login);

        if ($admin === null || !$admin->isEstActif()) {
            return null;
        }

        return $admin;
    }
}
