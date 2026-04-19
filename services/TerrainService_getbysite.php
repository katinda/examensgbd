<?php

// Retourne tous les terrains actifs d'un site précis.
// Vérifie d'abord que le site existe, sinon retourne null.
// Utilisé pour la route imbriquée GET /sites/{siteId}/terrains
public function getTerrainsBySite(int $siteId): ?array {
    $site = $this->siteRepository->findById($siteId);

    if ($site === null) {
        return null;
    }

    $terrains = $this->terrainRepository->findBySiteId($siteId);
    return array_filter($terrains, fn($t) => $t->isEstActif());
}
