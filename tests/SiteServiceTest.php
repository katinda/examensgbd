<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Site.php';
require_once __DIR__ . '/../models/Administrateur.php';
require_once __DIR__ . '/../repositories/SiteRepository.php';
require_once __DIR__ . '/../repositories/AdministrateurRepository.php';
require_once __DIR__ . '/../services/SiteService.php';

class SiteServiceTest extends TestCase {

    private function creerSite(int $id, string $nom, bool $actif): Site {
        return new Site($id, $nom, null, null, null, $actif, '2024-01-01 00:00:00');
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


    // ─── Tests lecture (inchangés) ──────────────────────────────────────────

    public function testGetAllSitesRetourneSeulementLesSitesActifs(): void {
        $mockRepo = $this->createStub(SiteRepository::class);
        $mockRepo->method('findAll')->willReturn([
            $this->creerSite(1, 'Club Paris', true),
            $this->creerSite(2, 'Club Ferme', false),
            $this->creerSite(3, 'Club Lyon',  true),
        ]);

        $service = new SiteService($mockRepo, $this->creerAdminRepo('GLOBAL'));
        $result  = $service->getAllSites();

        $this->assertCount(2, $result);
    }

    public function testGetSiteByIdRetourneNullSiSiteInactif(): void {
        $mockRepo = $this->createStub(SiteRepository::class);
        $mockRepo->method('findById')->willReturn($this->creerSite(2, 'Club Ferme', false));

        $service = new SiteService($mockRepo, $this->creerAdminRepo('GLOBAL'));
        $result  = $service->getSiteById(2);

        $this->assertNull($result);
    }

    public function testGetSiteByIdRetourneLeBosSiActif(): void {
        $mockRepo = $this->createStub(SiteRepository::class);
        $mockRepo->method('findById')->willReturn($this->creerSite(1, 'Club Paris', true));

        $service = new SiteService($mockRepo, $this->creerAdminRepo('GLOBAL'));
        $result  = $service->getSiteById(1);

        $this->assertNotNull($result);
        $this->assertEquals('Club Paris', $result->getNom());
    }

    public function testGetSiteByIdRetourneNullSiInexistant(): void {
        $mockRepo = $this->createStub(SiteRepository::class);
        $mockRepo->method('findById')->willReturn(null);

        $service = new SiteService($mockRepo, $this->creerAdminRepo('GLOBAL'));
        $result  = $service->getSiteById(999);

        $this->assertNull($result);
    }


    // ─── createSite ─────────────────────────────────────────────────────────

    public function testCreateSiteAccepteAdminGlobal(): void {
        $mockRepo = $this->createStub(SiteRepository::class);
        $mockRepo->method('insert')->willReturn(5);

        $service = new SiteService($mockRepo, $this->creerAdminRepo('GLOBAL'));
        $result  = $service->createSite(['nom' => 'Club Bordeaux'], 1);

        $this->assertEquals(5, $result);
    }

    public function testCreateSiteRefuseAdminSite(): void {
        $mockRepo = $this->createStub(SiteRepository::class);

        $service = new SiteService($mockRepo, $this->creerAdminRepo('SITE', 1));
        $result  = $service->createSite(['nom' => 'Club Bordeaux'], 1);

        $this->assertEquals('acces_interdit', $result);
    }

    public function testCreateSiteRetourneAdminIntrouvable(): void {
        $mockRepo = $this->createStub(SiteRepository::class);

        $service = new SiteService($mockRepo, $this->creerAdminRepoNull());
        $result  = $service->createSite(['nom' => 'Club Bordeaux'], 999);

        $this->assertEquals('admin_introuvable', $result);
    }


    // ─── updateSite ─────────────────────────────────────────────────────────

    public function testUpdateSiteAccepteAdminGlobal(): void {
        $mockRepo = $this->createStub(SiteRepository::class);
        $mockRepo->method('findById')->willReturn($this->creerSite(1, 'Club Paris', true));

        $service = new SiteService($mockRepo, $this->creerAdminRepo('GLOBAL'));
        $result  = $service->updateSite(1, ['nom' => 'Club Paris Modifie'], 1);

        $this->assertTrue($result);
    }

    public function testUpdateSiteAccepteAdminSiteSurSonProprieSite(): void {
        $mockRepo = $this->createStub(SiteRepository::class);
        $mockRepo->method('findById')->willReturn($this->creerSite(1, 'Club Paris', true));

        $service = new SiteService($mockRepo, $this->creerAdminRepo('SITE', 1));
        $result  = $service->updateSite(1, ['nom' => 'Club Paris Modifie'], 1);

        $this->assertTrue($result);
    }

    public function testUpdateSiteRefuseAdminSiteSurAutreSite(): void {
        $mockRepo = $this->createStub(SiteRepository::class);

        $service = new SiteService($mockRepo, $this->creerAdminRepo('SITE', 2));
        $result  = $service->updateSite(1, ['nom' => 'Club Paris Modifie'], 1);

        $this->assertEquals('acces_interdit', $result);
    }

    public function testUpdateSiteRetourneFalseSiSiteInexistant(): void {
        $mockRepo = $this->createStub(SiteRepository::class);
        $mockRepo->method('findById')->willReturn(null);

        $service = new SiteService($mockRepo, $this->creerAdminRepo('GLOBAL'));
        $result  = $service->updateSite(999, ['nom' => 'Club Inconnu'], 1);

        $this->assertFalse($result);
    }


    // ─── deleteSite ─────────────────────────────────────────────────────────

    public function testDeleteSiteAccepteAdminGlobal(): void {
        $mockRepo = $this->createStub(SiteRepository::class);
        $mockRepo->method('findById')->willReturn($this->creerSite(1, 'Club Paris', true));

        $service = new SiteService($mockRepo, $this->creerAdminRepo('GLOBAL'));
        $result  = $service->deleteSite(1, 1);

        $this->assertTrue($result);
    }

    public function testDeleteSiteRefuseAdminSite(): void {
        $mockRepo = $this->createStub(SiteRepository::class);

        $service = new SiteService($mockRepo, $this->creerAdminRepo('SITE', 1));
        $result  = $service->deleteSite(1, 1);

        $this->assertEquals('acces_interdit', $result);
    }

    public function testDeleteSiteRetourneFalseSiSiteInexistant(): void {
        $mockRepo = $this->createStub(SiteRepository::class);
        $mockRepo->method('findById')->willReturn(null);

        $service = new SiteService($mockRepo, $this->creerAdminRepo('GLOBAL'));
        $result  = $service->deleteSite(999, 1);

        $this->assertFalse($result);
    }
}
