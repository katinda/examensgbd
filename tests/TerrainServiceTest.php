<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Site.php';
require_once __DIR__ . '/../models/Terrain.php';
require_once __DIR__ . '/../repositories/SiteRepository.php';
require_once __DIR__ . '/../repositories/TerrainRepository.php';
require_once __DIR__ . '/../services/TerrainService.php';

// On teste la logique métier du TerrainService.
// On utilise des stubs pour simuler les repositories.

class TerrainServiceTest extends TestCase {

    // Crée un faux terrain pour les tests
    private function creerTerrain(int $id, int $siteId, int $num, bool $actif): Terrain {
        $t = new Terrain();
        $t->setTerrainId($id);
        $t->setSiteId($siteId);
        $t->setNumTerrain($num);
        $t->setLibelle("Terrain $num");
        $t->setEstActif($actif);
        return $t;
    }

    // Crée un faux site pour les tests
    private function creerSite(int $id): Site {
        $s = new Site();
        $s->setSiteId($id);
        $s->setNom("Site $id");
        $s->setAdresse(null);
        $s->setVille(null);
        $s->setCodePostal(null);
        $s->setEstActif(true);
        $s->setDateCreation('2024-01-01 00:00:00');
        return $s;
    }


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

        $this->assertCount(2, $result, "getAllTerrains() doit retourner uniquement les terrains actifs");
    }


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
        $result  = $service->getTerrainsBySite(1);

        $this->assertCount(1, $result);
    }


    // Vérifie que createTerrain() retourne 'site_introuvable' si le site n'existe pas
    public function testCreateTerrainRetourneSiteIntrouvable(): void {
        $mockTerrain = $this->createStub(TerrainRepository::class);
        $mockSite    = $this->createStub(SiteRepository::class);
        $mockSite->method('findById')->willReturn(null);

        $service = new TerrainService($mockTerrain, $mockSite);
        $result  = $service->createTerrain(['site_id' => 999, 'num_terrain' => 1]);

        $this->assertEquals('site_introuvable', $result);
    }


    // Vérifie que createTerrain() retourne un ID si tout est OK
    public function testCreateTerrainRetourneUnId(): void {
        $mockTerrain = $this->createStub(TerrainRepository::class);
        $mockTerrain->method('insert')->willReturn(3);
        $mockSite = $this->createStub(SiteRepository::class);
        $mockSite->method('findById')->willReturn($this->creerSite(1));

        $service = new TerrainService($mockTerrain, $mockSite);
        $result  = $service->createTerrain(['site_id' => 1, 'num_terrain' => 3]);

        $this->assertEquals(3, $result);
    }


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
}
