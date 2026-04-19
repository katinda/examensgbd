<?php

require_once __DIR__ . '/../repositories/MembreRepository.php';
require_once __DIR__ . '/../repositories/SiteRepository.php';

class MembreService {

    public function __construct(
        private MembreRepository $membreRepository,
        private SiteRepository   $siteRepository
    ) {}

    public function createMembre(array $data): int|string {
        $categorie = $data['categorie'] ?? '';
        $matricule = $data['matricule'] ?? '';
        $siteId    = isset($data['site_id']) ? (int) $data['site_id'] : null;

        $prefixes = ['G' => '/^G\d+$/', 'S' => '/^S\d+$/', 'L' => '/^L\d+$/'];
        if (!isset($prefixes[$categorie]) || !preg_match($prefixes[$categorie], $matricule)) {
            return 'matricule_invalide';
        }

        if ($categorie === 'S' && $siteId === null) return 'site_requis';
        if (in_array($categorie, ['G', 'L']) && $siteId !== null) return 'site_interdit';

        if ($categorie === 'S') {
            if ($this->siteRepository->findById($siteId) === null) return 'site_introuvable';
        }

        if ($this->membreRepository->findByMatricule($matricule) !== null) return 'doublon_matricule';

        $membre = new Membre(null, $matricule, $data['nom'], $data['prenom'],
            $data['email'] ?? null, $data['telephone'] ?? null, $categorie, $siteId, true);

        return $this->membreRepository->insert($membre);
    }
}
