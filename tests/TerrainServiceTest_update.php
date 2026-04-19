<?php

// Vérifie que updateTerrain() retourne true si le terrain existe
public function testUpdateTerrainRetourneTrueSiExiste(): void {
    $mockTerrain = $this->createStub(TerrainRepository::class);
    $mockTerrain->method('findById')->willReturn($this->creerTerrain(1, 1, 1, true));
    $mockSite = $this->createStub(SiteRepository::class);

    $service = new TerrainService($mockTerrain, $mockSite);
    $this->assertTrue($service->updateTerrain(1, ['libelle' => 'Nouveau nom']));
}

// Vérifie que updateTerrain() retourne false si le terrain n'existe pas
public function testUpdateTerrainRetourneFalseSiInexistant(): void {
    $mockTerrain = $this->createStub(TerrainRepository::class);
    $mockTerrain->method('findById')->willReturn(null);
    $mockSite = $this->createStub(SiteRepository::class);

    $service = new TerrainService($mockTerrain, $mockSite);
    $this->assertFalse($service->updateTerrain(999, ['libelle' => 'X']));
}
