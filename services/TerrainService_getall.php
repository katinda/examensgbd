<?php

// Retourne tous les terrains actifs (Est_Actif = true)
public function getAllTerrains(): array {
    $tous = $this->terrainRepository->findAll();
    return array_filter($tous, fn($t) => $t->isEstActif());
}
