<?php

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
