<?php

require_once __DIR__ . '/../repositories/TerrainRepository.php';
require_once __DIR__ . '/../repositories/SiteRepository.php';
require_once __DIR__ . '/../repositories/AdministrateurRepository.php';

class TerrainService {

    public function __construct(
        private TerrainRepository        $terrainRepository,
        private SiteRepository           $siteRepository,
        private AdministrateurRepository $adminRepository
    ) {}


    // Retourne tous les terrains actifs
    public function getAllTerrains(): array {
        $tous = $this->terrainRepository->findAll();
        return array_filter($tous, fn($t) => $t->isEstActif());
    }


    // Retourne un terrain par son ID, ou null s'il est inactif ou inexistant
    public function getTerrainById(int $id): ?Terrain {
        $terrain = $this->terrainRepository->findById($id);

        if ($terrain === null || !$terrain->isEstActif()) {
            return null;
        }

        return $terrain;
    }


    // Retourne tous les terrains actifs d'un site précis, ou null si le site n'existe pas
    public function getTerrainsBySite(int $siteId): ?array {
        $site = $this->siteRepository->findById($siteId);

        if ($site === null) {
            return null;
        }

        $terrains = $this->terrainRepository->findBySiteId($siteId);
        return array_filter($terrains, fn($t) => $t->isEstActif());
    }


    // Crée un nouveau terrain.
    // GLOBAL peut créer sur n'importe quel site.
    // SITE ne peut créer que sur son propre site.
    //
    // Erreurs possibles :
    //   'admin_introuvable' → adminId inconnu → 404
    //   'acces_interdit'    → admin SITE essaie un autre site → 403
    //   'site_introuvable'  → site_id inconnu → 404
    //   'doublon'           → numéro déjà pris sur ce site → 409
    public function createTerrain(array $data, int $adminId): int|string {
        $admin = $this->adminRepository->findById($adminId);
        if ($admin === null) return 'admin_introuvable';

        $siteId = (int) $data['site_id'];

        if ($admin->getType() === 'SITE' && $admin->getSiteId() !== $siteId) {
            return 'acces_interdit';
        }

        $site = $this->siteRepository->findById($siteId);
        if ($site === null) return 'site_introuvable';

        $terrain = new Terrain(
            null,
            $siteId,
            (int) $data['num_terrain'],
            $data['libelle'] ?? null,
            true
        );

        try {
            return $this->terrainRepository->insert($terrain);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') return 'doublon';
            throw $e;
        }
    }


    // Met à jour un terrain existant.
    // GLOBAL peut modifier n'importe quel terrain.
    // SITE ne peut modifier que les terrains de son site.
    //
    // Retourne true, false (terrain inexistant), ou une string d'erreur.
    public function updateTerrain(int $id, array $data, int $adminId): bool|string {
        $admin = $this->adminRepository->findById($adminId);
        if ($admin === null) return 'admin_introuvable';

        $terrain = $this->terrainRepository->findById($id);
        if ($terrain === null) return false;

        if ($admin->getType() === 'SITE' && $admin->getSiteId() !== $terrain->getSiteId()) {
            return 'acces_interdit';
        }

        if (isset($data['num_terrain'])) $terrain->setNumTerrain((int) $data['num_terrain']);
        if (isset($data['libelle']))     $terrain->setLibelle($data['libelle']);
        if (isset($data['est_actif']))   $terrain->setEstActif((bool) $data['est_actif']);

        $this->terrainRepository->update($terrain);
        return true;
    }


    // Supprime un terrain.
    // GLOBAL peut supprimer n'importe quel terrain.
    // SITE ne peut supprimer que les terrains de son site.
    //
    // Retourne true, false (terrain inexistant), ou une string d'erreur.
    public function deleteTerrain(int $id, int $adminId): bool|string {
        $admin = $this->adminRepository->findById($adminId);
        if ($admin === null) return 'admin_introuvable';

        $terrain = $this->terrainRepository->findById($id);
        if ($terrain === null) return false;

        if ($admin->getType() === 'SITE' && $admin->getSiteId() !== $terrain->getSiteId()) {
            return 'acces_interdit';
        }

        $this->terrainRepository->delete($id);
        return true;
    }
}
