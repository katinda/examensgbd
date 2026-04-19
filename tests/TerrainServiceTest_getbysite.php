<?php

// Vérifie que getTerrainsBySite() retourne null si le site n'existe pas
public function testGetTerrainsBySiteRetourneNullSiSiteInexistant(): void {
    $mockTerrain = $this->createStub(TerrainRepository::class);
    $mockSite    = $this->createStub(SiteRepository::class);
    $mockSite->method('findById')->willReturn(null);

    $service = new TerrainService($mockTerrain, $mockSite);
    $this->assertNull($service->getTerrainsBySite(999));
}

// Vérifie que getTerrainsBySite() retourne uniquement les terrains actifs du site
public function testGetTerrainsBySiteRetourneLesTerrainsActifs(): void {
    $mockTerrain = $this->createStub(TerrainRepository::class);
    $mockTerrain->method('findBySiteId')->willReturn([
        $this->creerTerrain(1, 1, 1, true),
        $this->creerTerrain(2, 1, 2, false),
    ]);
    $mockSite = $this->createStub(SiteRepository::class);
    $mockSite->method('findById')->willReturn($this->creerSite(1));

    $service = new TerrainService($mockTerrain, $mockSite);
    $this->assertCount(1, $service->getTerrainsBySite(1));
}
