<?php

// Crée un nouveau terrain.
// Vérifie que le site existe (sinon retourne 'site_introuvable').
// Gère la contrainte UNIQUE (Site_ID, Num_Terrain) → retourne 'doublon' si déjà pris.
public function createTerrain(array $data): int|string {
    $site = $this->siteRepository->findById((int) $data['site_id']);
    if ($site === null) {
        return 'site_introuvable';
    }

    $terrain = new Terrain();
    $terrain->setSiteId((int) $data['site_id']);
    $terrain->setNumTerrain((int) $data['num_terrain']);
    $terrain->setLibelle($data['libelle'] ?? null);
    $terrain->setEstActif(true);

    try {
        return $this->terrainRepository->insert($terrain);
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            return 'doublon';
        }
        throw $e;
    }
}
