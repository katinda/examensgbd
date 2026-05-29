<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Site.php';
require_once __DIR__ . '/../models/Terrain.php';
require_once __DIR__ . '/../models/Administrateur.php';
require_once __DIR__ . '/../repositories/SiteRepository.php';
require_once __DIR__ . '/../repositories/TerrainRepository.php';
require_once __DIR__ . '/../repositories/AdministrateurRepository.php';
require_once __DIR__ . '/../services/TerrainService.php';

class TerrainServiceTest extends TestCase {

    private function creerTerrain(int $id, int $siteId, int $num, bool $actif): Terrain {
        return new Terrain($id, $siteId, $num, "Terrain $num", $actif);
    }

    private function creerSite(int $id): Site {
        return new Site($id, "Site $id", null, null, null, true, '2024-01-01 00:00:00');
    }

    private function creerAdmin(string $type, ?int $siteId = null): Administrateur {
        return new Administrateur(1, 'admin', 'hash', null, null, null, $type, $siteId, true);
    }

    private function creerAdminRepo(string $type, ?int $siteId = null): AdministrateurRepository {
        $mock = $this->createStub(AdministrateurRepository::class);
        $mock->method('findById')->willReturn($this->creerAdmin($type, $siteId));
        return $mock;
    }

    private function creerAdminRepoNull(): AdministrateurRepository {
        $mock = $this->createStub(AdministrateurRepository::class);
        $mock->method('findById')->willReturn(null);
        return $mock;
    }


    // ─── Lecture (inchangé) ──────────────────────────────────────────────────

    public function testGetAllTerrainsRetourneSeulementLesActifs(): void {
        $mockTerrain = $this->createStub(TerrainRepository::class);
        $mockTerrain->method('findAll')->willReturn([
            $this->creerTerrain(1, 1, 1, true),
            $this->creerTerrain(2, 1, 2, false),
            $this->creerTerrain(3, 1, 3, true),
        ]);
        $mockSite = $this->createStub(SiteRepository::class);

        $service = new TerrainService($mockTerrain, $mockSite, $this->creerAdminRepo('GLOBAL'));
        $result  = $service->getAllTerrains();

        $this->assertCount(2, $result);
    }

    public function testGetTerrainByIdRetourneNullSiInactif(): void {
        $mockTerrain = $this->createStub(TerrainRepository::class);
        $mockTerrain->method('findById')->willReturn($this->creerTerrain(2, 1, 2, false));
        $mockSite = $this->createStub(SiteRepository::class);

        $service = new TerrainService($mockTerrain, $mockSite, $this->creerAdminRepo('GLOBAL'));
        $this->assertNull($service->getTerrainById(2));
    }

    public function testGetTerrainByIdRetourneLeTerrainSiActif(): void {
        $mockTerrain = $this->createStub(TerrainRepository::class);
        $mockTerrain->method('findById')->willReturn($this->creerTerrain(1, 1, 1, true));
        $mockSite = $this->createStub(SiteRepository::class);

        $service = new TerrainService($mockTerrain, $mockSite, $this->creerAdminRepo('GLOBAL'));
        $result  = $service->getTerrainById(1);

        $this->assertNotNull($result);
        $this->assertEquals(1, $result->getNumTerrain());
    }

    public function testGetTerrainsBySiteRetourneNullSiSiteInexistant(): void {
        $mockTerrain = $this->createStub(TerrainRepository::class);
        $mockSite    = $this->createStub(SiteRepository::class);
        $mockSite->method('findById')->willReturn(null);

        $service = new TerrainService($mockTerrain, $mockSite, $this->creerAdminRepo('GLOBAL'));
        $this->assertNull($service->getTerrainsBySite(999));
    }

    public function testGetTerrainsBySiteRetourneLesTerrainsActifs(): void {
        $mockTerrain = $this->createStub(TerrainRepository::class);
        $mockTerrain->method('findBySiteId')->willReturn([
            $this->creerTerrain(1, 1, 1, true),
            $this->creerTerrain(2, 1, 2, false),
        ]);
        $mockSite = $this->createStub(SiteRepository::class);
        $mockSite->method('findById')->willReturn($this->creerSite(1));

        $service = new TerrainService($mockTerrain, $mockSite, $this->creerAdminRepo('GLOBAL'));
        $result  = $service->getTerrainsBySite(1);

        $this->assertCount(1, $result);
    }


    // ─── createTerrain ───────────────────────────────────────────────────────

    public function testCreateTerrainAccepteAdminGlobal(): void {
        $mockTerrain = $this->createStub(TerrainRepository::class);
        $mockTerrain->method('insert')->willReturn(3);
        $mockSite = $this->createStub(SiteRepository::class);
        $mockSite->method('findById')->willReturn($this->creerSite(1));

        $service = new TerrainService($mockTerrain, $mockSite, $this->creerAdminRepo('GLOBAL'));
        $result  = $service->createTerrain(['site_id' => 1, 'num_terrain' => 3], 1);

        $this->assertEquals(3, $result);
    }

    public function testCreateTerrainAccepteAdminSiteSurSonSite(): void {
        $mockTerrain = $this->createStub(TerrainRepository::class);
        $mockTerrain->method('insert')->willReturn(3);
        $mockSite = $this->createStub(SiteRepository::class);
        $mockSite->method('findById')->willReturn($this->creerSite(1));

        $service = new TerrainService($mockTerrain, $mockSite, $this->creerAdminRepo('SITE', 1));
        $result  = $service->createTerrain(['site_id' => 1, 'num_terrain' => 3], 1);

        $this->assertEquals(3, $result);
    }

    public function testCreateTerrainRefuseAdminSiteSurAutreSite(): void {
        $mockTerrain = $this->createStub(TerrainRepository::class);
        $mockSite    = $this->createStub(SiteRepository::class);

        $service = new TerrainService($mockTerrain, $mockSite, $this->creerAdminRepo('SITE', 2));
        $result  = $service->createTerrain(['site_id' => 1, 'num_terrain' => 3], 1);

        $this->assertEquals('acces_interdit', $result);
    }

    public function testCreateTerrainRetourneSiteIntrouvable(): void {
        $mockTerrain = $this->createStub(TerrainRepository::class);
        $mockSite    = $this->createStub(SiteRepository::class);
        $mockSite->method('findById')->willReturn(null);

        $service = new TerrainService($mockTerrain, $mockSite, $this->creerAdminRepo('GLOBAL'));
        $result  = $service->createTerrain(['site_id' => 999, 'num_terrain' => 1], 1);

        $this->assertEquals('site_introuvable', $result);
    }

    public function testCreateTerrainRetourneAdminIntrouvable(): void {
        $mockTerrain = $this->createStub(TerrainRepository::class);
        $mockSite    = $this->createStub(SiteRepository::class);

        $service = new TerrainService($mockTerrain, $mockSite, $this->creerAdminRepoNull());
        $result  = $service->createTerrain(['site_id' => 1, 'num_terrain' => 1], 999);

        $this->assertEquals('admin_introuvable', $result);
    }


    // ─── updateTerrain ───────────────────────────────────────────────────────

    public function testUpdateTerrainAccepteAdminGlobal(): void {
        $mockTerrain = $this->createStub(TerrainRepository::class);
        $mockTerrain->method('findById')->willReturn($this->creerTerrain(1, 1, 1, true));
        $mockSite = $this->createStub(SiteRepository::class);

        $service = new TerrainService($mockTerrain, $mockSite, $this->creerAdminRepo('GLOBAL'));
        $this->assertTrue($service->updateTerrain(1, ['libelle' => 'Nouveau nom'], 1));
    }

    public function testUpdateTerrainAccepteAdminSiteSurSonSite(): void {
        $mockTerrain = $this->createStub(TerrainRepository::class);
        $mockTerrain->method('findById')->willReturn($this->creerTerrain(1, 1, 1, true));
        $mockSite = $this->createStub(SiteRepository::class);

        $service = new TerrainService($mockTerrain, $mockSite, $this->creerAdminRepo('SITE', 1));
        $this->assertTrue($service->updateTerrain(1, ['libelle' => 'Nouveau nom'], 1));
    }

    public function testUpdateTerrainRefuseAdminSiteSurAutreSite(): void {
        $mockTerrain = $this->createStub(TerrainRepository::class);
        $mockTerrain->method('findById')->willReturn($this->creerTerrain(1, 1, 1, true));
        $mockSite = $this->createStub(SiteRepository::class);

        $service = new TerrainService($mockTerrain, $mockSite, $this->creerAdminRepo('SITE', 2));
        $result  = $service->updateTerrain(1, ['libelle' => 'X'], 1);

        $this->assertEquals('acces_interdit', $result);
    }

    public function testUpdateTerrainRetourneFalseSiInexistant(): void {
        $mockTerrain = $this->createStub(TerrainRepository::class);
        $mockTerrain->method('findById')->willReturn(null);
        $mockSite = $this->createStub(SiteRepository::class);

        $service = new TerrainService($mockTerrain, $mockSite, $this->creerAdminRepo('GLOBAL'));
        $this->assertFalse($service->updateTerrain(999, ['libelle' => 'X'], 1));
    }


    // ─── deleteTerrain ───────────────────────────────────────────────────────

    public function testDeleteTerrainAccepteAdminGlobal(): void {
        $mockTerrain = $this->createStub(TerrainRepository::class);
        $mockTerrain->method('findById')->willReturn($this->creerTerrain(1, 1, 1, true));
        $mockSite = $this->createStub(SiteRepository::class);

        $service = new TerrainService($mockTerrain, $mockSite, $this->creerAdminRepo('GLOBAL'));
        $this->assertTrue($service->deleteTerrain(1, 1));
    }

    public function testDeleteTerrainAccepteAdminSiteSurSonSite(): void {
        $mockTerrain = $this->createStub(TerrainRepository::class);
        $mockTerrain->method('findById')->willReturn($this->creerTerrain(1, 1, 1, true));
        $mockSite = $this->createStub(SiteRepository::class);

        $service = new TerrainService($mockTerrain, $mockSite, $this->creerAdminRepo('SITE', 1));
        $this->assertTrue($service->deleteTerrain(1, 1));
    }

    public function testDeleteTerrainRefuseAdminSiteSurAutreSite(): void {
        $mockTerrain = $this->createStub(TerrainRepository::class);
        $mockTerrain->method('findById')->willReturn($this->creerTerrain(1, 1, 1, true));
        $mockSite = $this->createStub(SiteRepository::class);

        $service = new TerrainService($mockTerrain, $mockSite, $this->creerAdminRepo('SITE', 2));
        $result  = $service->deleteTerrain(1, 1);

        $this->assertEquals('acces_interdit', $result);
    }

    public function testDeleteTerrainRetourneFalseSiInexistant(): void {
        $mockTerrain = $this->createStub(TerrainRepository::class);
        $mockTerrain->method('findById')->willReturn(null);
        $mockSite = $this->createStub(SiteRepository::class);

        $service = new TerrainService($mockTerrain, $mockSite, $this->creerAdminRepo('GLOBAL'));
        $this->assertFalse($service->deleteTerrain(999, 1));
    }
}
