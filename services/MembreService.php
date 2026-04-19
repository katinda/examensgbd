<?php

require_once __DIR__ . '/../repositories/MembreRepository.php';
require_once __DIR__ . '/../repositories/SiteRepository.php';

class MembreService {

    public function __construct(
        private MembreRepository $membreRepository,
        private SiteRepository   $siteRepository
    ) {}

    public function getMembresByCategorie(string $categorie): array {
        $tous = $this->membreRepository->findByCategorie($categorie);
        return array_values(array_filter($tous, fn($m) => $m->isEstActif()));
    }
}
