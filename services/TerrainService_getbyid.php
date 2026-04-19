<?php

// Retourne un terrain par son ID, ou null s'il est inactif ou inexistant
public function getTerrainById(int $id): ?Terrain {
    $terrain = $this->terrainRepository->findById($id);

    if ($terrain === null || !$terrain->isEstActif()) {
        return null;
    }

    return $terrain;
}
