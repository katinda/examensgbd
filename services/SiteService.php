<?php

require_once __DIR__ . '/../repositories/SiteRepository.php';

// Le service contient la logique métier liée aux sites.
// Il fait le lien entre le controller (qui reçoit la requête)
// et le repository (qui parle à la base de données).
// Le service ne fait jamais de SQL directement.

class SiteService {

    // Le repository est reçu en paramètre (injection de dépendance).
    // Le service ne crée jamais lui-même le repository.
    public function __construct(private SiteRepository $siteRepository) {}


    // Retourne tous les sites actifs.
    // C'est ici qu'on applique les règles métier :
    // on ne retourne que les sites avec Est_Actif = true, pas tous les sites.
    public function getAllSites(): array {
        $tous = $this->siteRepository->findAll();

        // On filtre pour ne garder que les sites actifs
        return array_filter($tous, fn($site) => $site->isEstActif());
    }


    // Retourne un site par son ID.
    // Retourne null si le site n'existe pas ou s'il est inactif.
    public function getSiteById(int $id): ?Site {
        $site = $this->siteRepository->findById($id);

        // Si le site n'existe pas ou est inactif, on retourne null
        if ($site === null || !$site->isEstActif()) {
            return null;
        }

        return $site;
    }
}
