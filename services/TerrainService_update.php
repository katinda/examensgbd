<?php

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
