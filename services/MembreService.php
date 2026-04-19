<?php

require_once __DIR__ . '/../repositories/MembreRepository.php';
require_once __DIR__ . '/../repositories/SiteRepository.php';

class MembreService {

    public function __construct(
        private MembreRepository $membreRepository,
        private SiteRepository   $siteRepository
    ) {}

    public function deleteMembre(int $id): bool {
        $membre = $this->membreRepository->findById($id);
        if ($membre === null) return false;

        $membre->setEstActif(false);
        $this->membreRepository->update($membre);
        return true;
    }
}
