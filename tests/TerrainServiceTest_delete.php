<?php

// Vérifie que deleteTerrain() retourne true si le terrain existe
public function testDeleteTerrainRetourneTrueSiExiste(): void {
    $mockTerrain = $this->createStub(TerrainRepository::class);
    $mockTerrain->method('findById')->willReturn($this->creerTerrain(1, 1, 1, true));
    $mockSite = $this->createStub(SiteRepository::class);

    $service = new TerrainService($mockTerrain, $mockSite);
    $this->assertTrue($service->deleteTerrain(1));
}

// Vérifie que deleteTerrain() retourne false si le terrain n'existe pas
public function testDeleteTerrainRetourneFalseSiInexistant(): void {
    $mockTerrain = $this->createStub(TerrainRepository::class);
    $mockTerrain->method('findById')->willReturn(null);
    $mockSite = $this->createStub(SiteRepository::class);

    $service = new TerrainService($mockTerrain, $mockSite);
    $this->assertFalse($service->deleteTerrain(999));
}
