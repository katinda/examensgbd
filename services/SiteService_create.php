<?php

// Crée un nouveau site à partir des données reçues et retourne son ID.
// Un nouveau site est actif par défaut.
public function createSite(array $data): int {
    $site = new Site();
    $site->setNom($data['nom']);
    $site->setAdresse($data['adresse'] ?? null);
    $site->setVille($data['ville'] ?? null);
    $site->setCodePostal($data['code_postal'] ?? null);
    $site->setEstActif(true);

    return $this->siteRepository->insert($site);
}
