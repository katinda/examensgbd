<?php

// Met à jour un site existant avec les nouvelles données.
// Retourne false si le site n'existe pas.
// On met à jour uniquement les champs fournis.
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
