<?php

// Vérifie que getTerrainById() retourne null pour un terrain inactif
public function testGetTerrainByIdRetourneNullSiInactif(): void {
    $mockTerrain = $this->createStub(TerrainRepository::class);
    $mockTerrain->method('findById')->willReturn($this->creerTerrain(2, 1, 2, false));
    $mockSite = $this->createStub(SiteRepository::class);

    $service = new TerrainService($mockTerrain, $mockSite);
    $this->assertNull($service->getTerrainById(2));
}

// Vérifie que getTerrainById() retourne le terrain s'il est actif
public function testGetTerrainByIdRetourneLeTerrainSiActif(): void {
    $mockTerrain = $this->createStub(TerrainRepository::class);
    $mockTerrain->method('findById')->willReturn($this->creerTerrain(1, 1, 1, true));
    $mockSite = $this->createStub(SiteRepository::class);

    $service = new TerrainService($mockTerrain, $mockSite);
    $result  = $service->getTerrainById(1);

    $this->assertNotNull($result);
    $this->assertEquals(1, $result->getNumTerrain());
}
