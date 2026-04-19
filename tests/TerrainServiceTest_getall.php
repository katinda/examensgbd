<?php

// Vérifie que getAllTerrains() retourne uniquement les terrains actifs
public function testGetAllTerrainsRetourneSeulementLesActifs(): void {
    $mockTerrain = $this->createStub(TerrainRepository::class);
    $mockTerrain->method('findAll')->willReturn([
        $this->creerTerrain(1, 1, 1, true),
        $this->creerTerrain(2, 1, 2, false),
        $this->creerTerrain(3, 1, 3, true),
    ]);
    $mockSite = $this->createStub(SiteRepository::class);

    $service = new TerrainService($mockTerrain, $mockSite);
    $result  = $service->getAllTerrains();

    $this->assertCount(2, $result);
}
