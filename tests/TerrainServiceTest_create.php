<?php

// Vérifie que createTerrain() retourne 'site_introuvable' si le site n'existe pas
public function testCreateTerrainRetourneSiteIntrouvable(): void {
    $mockTerrain = $this->createStub(TerrainRepository::class);
    $mockSite    = $this->createStub(SiteRepository::class);
    $mockSite->method('findById')->willReturn(null);

    $service = new TerrainService($mockTerrain, $mockSite);
    $this->assertEquals('site_introuvable', $service->createTerrain(['site_id' => 999, 'num_terrain' => 1]));
}

// Vérifie que createTerrain() retourne un ID si tout est OK
public function testCreateTerrainRetourneUnId(): void {
    $mockTerrain = $this->createStub(TerrainRepository::class);
    $mockTerrain->method('insert')->willReturn(3);
    $mockSite = $this->createStub(SiteRepository::class);
    $mockSite->method('findById')->willReturn($this->creerSite(1));

    $service = new TerrainService($mockTerrain, $mockSite);
    $this->assertEquals(3, $service->createTerrain(['site_id' => 1, 'num_terrain' => 3]));
}
