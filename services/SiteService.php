<?php

require_once __DIR__ . '/../repositories/SiteRepository.php';

// Le service contient la logique métier liée aux sites.
// Il fait le lien entre le controller (qui reçoit la requête)
// et le repository (qui parle à la base de données).
// Le service ne fait jamais de SQL directement.

class SiteService {

    // Le repository est reçu en paramètre (injection de dépendance).
    public function __construct(private SiteRepository $siteRepository) {}


    // Retourne tous les sites actifs.
    public function getAllSites(): array {
        $tous = $this->siteRepository->findAll();
        return array_filter($tous, fn($site) => $site->isEstActif());
    }


    // Retourne un site par son ID, ou null s'il est inactif ou inexistant.
    public function getSiteById(int $id): ?Site {
        $site = $this->siteRepository->findById($id);

        if ($site === null || !$site->isEstActif()) {
            return null;
        }

        return $site;
    }


    // Crée un nouveau site à partir des données reçues et retourne son ID.
    public function createSite(array $data): int {
        $site = new Site();
        $site->setNom($data['nom']);
        $site->setAdresse($data['adresse'] ?? null);
        $site->setVille($data['ville'] ?? null);
        $site->setCodePostal($data['code_postal'] ?? null);
        $site->setEstActif(true);

        return $this->siteRepository->insert($site);
    }


    // Met à jour un site existant. Retourne false si le site n'existe pas.
    public function updateSite(int $id, array $data): bool {
        $site = $this->siteRepository->findById($id);

        if ($site === null) {
            return false;
        }

        if (isset($data['nom']))         $site->setNom($data['nom']);
        if (isset($data['adresse']))     $site->setAdresse($data['adresse']);
        if (isset($data['ville']))       $site->setVille($data['ville']);
        if (isset($data['code_postal'])) $site->setCodePostal($data['code_postal']);
        if (isset($data['est_actif']))   $site->setEstActif((bool) $data['est_actif']);

        $this->siteRepository->update($site);
        return true;
    }


    // Supprime un site par son ID. Retourne false si le site n'existe pas.
    public function deleteSite(int $id): bool {
        $site = $this->siteRepository->findById($id);

        if ($site === null) {
            return false;
        }

        $this->siteRepository->delete($id);
        return true;
    }
}
