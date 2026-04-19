<?php

// Supprime un site par son ID.
// Retourne false si le site n'existe pas.
public function deleteSite(int $id): bool {
    $site = $this->siteRepository->findById($id);

    if ($site === null) {
        return false;
    }

    $this->siteRepository->delete($id);
    return true;
}
