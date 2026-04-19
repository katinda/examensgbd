<?php

require_once __DIR__ . '/../repositories/TerrainRepository.php';
require_once __DIR__ . '/../repositories/SiteRepository.php';

// Le service contient la logique métier liée aux terrains.
// Il a besoin de DEUX repositories :
// - TerrainRepository pour gérer les terrains
// - SiteRepository pour vérifier qu'un site existe avant d'y ajouter un terrain
// C'est ce qu'on appelle composer les dépendances : on reçoit les deux dans le constructeur.

class TerrainService {

    // Les deux repositories sont reçus en paramètre (injection de dépendance).
    public function __construct(
        private TerrainRepository $terrainRepository,
        private SiteRepository    $siteRepository
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


    // Retourne tous les terrains actifs d'un site précis.
    // Vérifie d'abord que le site existe, sinon retourne null.
    // Utilisé pour la route imbriquée GET /sites/{siteId}/terrains
    public function getTerrainsBySite(int $siteId): ?array {
        // On vérifie que le site existe avant de chercher ses terrains
        $site = $this->siteRepository->findById($siteId);

        if ($site === null) {
            return null; // site introuvable → le controller renverra 404
        }

        $terrains = $this->terrainRepository->findBySiteId($siteId);
        return array_filter($terrains, fn($t) => $t->isEstActif());
    }


    // Crée un nouveau terrain.
    // Retourne l'ID créé, ou une erreur si le site n'existe pas ou si le numéro est déjà pris.
    // $data['site_id'] et $data['num_terrain'] sont obligatoires.
    public function createTerrain(array $data): int|string {
        // Règle 1 : vérifier que le site existe avant d'insérer
        $site = $this->siteRepository->findById((int) $data['site_id']);
        if ($site === null) {
            return 'site_introuvable'; // le controller traduira ça en 404
        }

        $terrain = new Terrain();
        $terrain->setSiteId((int) $data['site_id']);
        $terrain->setNumTerrain((int) $data['num_terrain']);
        $terrain->setLibelle($data['libelle'] ?? null);
        $terrain->setEstActif(true);

        try {
            return $this->terrainRepository->insert($terrain);
        } catch (PDOException $e) {
            // Règle 2 : si MySQL refuse à cause de la contrainte UNIQUE (Site_ID, Num_Terrain)
            // on retourne un message clair au lieu de laisser crasher l'application
            if ($e->getCode() === '23000') {
                return 'doublon'; // le controller traduira ça en 409 Conflict
            }
            throw $e; // autre erreur inattendue : on la remonte
        }
    }


    // Met à jour un terrain existant.
    // Retourne false si le terrain n'existe pas.
    public function updateTerrain(int $id, array $data): bool {
        $terrain = $this->terrainRepository->findById($id);

        if ($terrain === null) {
            return false;
        }

        if (isset($data['num_terrain'])) $terrain->setNumTerrain((int) $data['num_terrain']);
        if (isset($data['libelle']))     $terrain->setLibelle($data['libelle']);
        if (isset($data['est_actif']))   $terrain->setEstActif((bool) $data['est_actif']);

        $this->terrainRepository->update($terrain);
        return true;
    }


    // Supprime un terrain par son ID.
    // Retourne false si le terrain n'existe pas.
    public function deleteTerrain(int $id): bool {
        $terrain = $this->terrainRepository->findById($id);

        if ($terrain === null) {
            return false;
        }

        $this->terrainRepository->delete($id);
        return true;
    }
}
