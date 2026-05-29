<?php

require_once __DIR__ . '/../repositories/SiteRepository.php';
require_once __DIR__ . '/../repositories/AdministrateurRepository.php';

class SiteService {

    public function __construct(
        private SiteRepository $siteRepository,
        private AdministrateurRepository $adminRepository
    ) {}


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


    // Crée un nouveau site. Seul un admin GLOBAL peut créer un site.
    // Retourne l'ID créé, ou une string décrivant l'erreur.
    //
    // Erreurs possibles :
    //   'admin_introuvable' → adminId inconnu → 404
    //   'acces_interdit'    → admin de type SITE → 403
    public function createSite(array $data, int $adminId): int|string {
        $admin = $this->adminRepository->findById($adminId);

        if ($admin === null) return 'admin_introuvable';
        if ($admin->getType() !== 'GLOBAL') return 'acces_interdit';

        $site = new Site(
            null,
            $data['nom'],
            $data['adresse'] ?? null,
            $data['ville'] ?? null,
            $data['code_postal'] ?? null,
            true
        );

        return $this->siteRepository->insert($site);
    }


    // Met à jour un site existant.
    // Un admin GLOBAL peut modifier n'importe quel site.
    // Un admin SITE ne peut modifier que son propre site.
    //
    // Retourne true, false (site inexistant), ou une string d'erreur.
    public function updateSite(int $id, array $data, int $adminId): bool|string {
        $admin = $this->adminRepository->findById($adminId);

        if ($admin === null) return 'admin_introuvable';
        if ($admin->getType() === 'SITE' && $admin->getSiteId() !== $id) return 'acces_interdit';

        $site = $this->siteRepository->findById($id);

        if ($site === null) return false;

        if (isset($data['nom']))         $site->setNom($data['nom']);
        if (isset($data['adresse']))     $site->setAdresse($data['adresse']);
        if (isset($data['ville']))       $site->setVille($data['ville']);
        if (isset($data['code_postal'])) $site->setCodePostal($data['code_postal']);
        if (isset($data['est_actif']))   $site->setEstActif((bool) $data['est_actif']);

        $this->siteRepository->update($site);
        return true;
    }


    // Supprime un site. Seul un admin GLOBAL peut supprimer un site.
    //
    // Retourne true, false (site inexistant), ou une string d'erreur.
    public function deleteSite(int $id, int $adminId): bool|string {
        $admin = $this->adminRepository->findById($adminId);

        if ($admin === null) return 'admin_introuvable';
        if ($admin->getType() !== 'GLOBAL') return 'acces_interdit';

        $site = $this->siteRepository->findById($id);

        if ($site === null) return false;

        $this->siteRepository->delete($id);
        return true;
    }
}
