<?php

require_once __DIR__ . '/../repositories/AdministrateurRepository.php';
require_once __DIR__ . '/../repositories/SiteRepository.php';

class AdministrateurService {

    public function __construct(
        private AdministrateurRepository $adminRepository,
        private SiteRepository           $siteRepository
    ) {}

    // Soft-delete : désactive l'administrateur. Retourne false si inexistant.
    public function deleteAdministrateur(int $id): bool {
        $admin = $this->adminRepository->findById($id);

        if ($admin === null) {
            return false;
        }

        $admin->setEstActif(false);
        $this->adminRepository->update($admin);
        return true;
    }
}
