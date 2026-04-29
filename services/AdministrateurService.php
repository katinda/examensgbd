<?php

require_once __DIR__ . '/../repositories/AdministrateurRepository.php';
require_once __DIR__ . '/../repositories/SiteRepository.php';

class AdministrateurService {

    public function __construct(
        private AdministrateurRepository $adminRepository,
        private SiteRepository           $siteRepository
    ) {}

    // Met à jour un administrateur existant. Retourne false si inexistant.
    public function updateAdministrateur(int $id, array $data): bool {
        $admin = $this->adminRepository->findById($id);

        if ($admin === null) {
            return false;
        }

        if (isset($data['nom']))       $admin->setNom($data['nom']);
        if (isset($data['prenom']))    $admin->setPrenom($data['prenom']);
        if (isset($data['email']))     $admin->setEmail($data['email']);
        if (isset($data['est_actif'])) $admin->setEstActif((bool) $data['est_actif']);

        $this->adminRepository->update($admin);
        return true;
    }
}
