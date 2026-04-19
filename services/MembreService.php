<?php

require_once __DIR__ . '/../repositories/MembreRepository.php';
require_once __DIR__ . '/../repositories/SiteRepository.php';

class MembreService {

    public function __construct(
        private MembreRepository $membreRepository,
        private SiteRepository   $siteRepository
    ) {}

    public function updateMembre(int $id, array $data): bool {
        $membre = $this->membreRepository->findById($id);
        if ($membre === null) return false;

        if (isset($data['nom']))       $membre->setNom($data['nom']);
        if (isset($data['prenom']))    $membre->setPrenom($data['prenom']);
        if (isset($data['email']))     $membre->setEmail($data['email']);
        if (isset($data['telephone'])) $membre->setTelephone($data['telephone']);
        if (isset($data['est_actif'])) $membre->setEstActif((bool) $data['est_actif']);

        $this->membreRepository->update($membre);
        return true;
    }
}
