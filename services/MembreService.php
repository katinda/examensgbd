<?php

require_once __DIR__ . '/../repositories/MembreRepository.php';
require_once __DIR__ . '/../repositories/SiteRepository.php';

class MembreService {

    public function __construct(
        private MembreRepository $membreRepository,
        private SiteRepository   $siteRepository
    ) {}

    public function getMembreByMatricule(string $matricule): ?Membre {
        $membre = $this->membreRepository->findByMatricule($matricule);
        if ($membre === null || !$membre->isEstActif()) return null;
        return $membre;
    }
}
