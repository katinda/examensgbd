<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Fermeture.php';
require_once __DIR__ . '/../models/Administrateur.php';
require_once __DIR__ . '/../repositories/FermetureRepository.php';
require_once __DIR__ . '/../repositories/AdministrateurRepository.php';
require_once __DIR__ . '/../services/FermetureService.php';

class FermetureServiceTest extends TestCase {

    private function creerFermeture(int $id, ?int $siteId, string $debut, string $fin, ?string $raison = null): Fermeture {
        return new Fermeture($id, $siteId, $debut, $fin, $raison);
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

    public function testGetAllFermeturesRetourneToutesLesFermetures(): void {
        $mockRepo = $this->createStub(FermetureRepository::class);
        $mockRepo->method('findAll')->willReturn([
            $this->creerFermeture(1, 1,    '2026-08-01', '2026-08-07', 'Travaux'),
            $this->creerFermeture(2, null, '2026-12-25', '2026-12-25', 'Noël'),
        ]);

        $service = new FermetureService($mockRepo, $this->creerAdminRepo('GLOBAL'));
        $this->assertCount(2, $service->getAllFermetures());
    }

    public function testGetFermetureByIdRetourneLaBonneFermeture(): void {
        $mockRepo = $this->createStub(FermetureRepository::class);
        $mockRepo->method('findById')->willReturn($this->creerFermeture(1, 1, '2026-08-01', '2026-08-07', 'Travaux'));

        $service = new FermetureService($mockRepo, $this->creerAdminRepo('GLOBAL'));
        $result  = $service->getFermetureById(1);

        $this->assertNotNull($result);
        $this->assertEquals('2026-08-01', $result->getDateDebut());
    }

    public function testGetFermetureByIdRetourneNullSiInexistant(): void {
        $mockRepo = $this->createStub(FermetureRepository::class);
        $mockRepo->method('findById')->willReturn(null);

        $service = new FermetureService($mockRepo, $this->creerAdminRepo('GLOBAL'));
        $this->assertNull($service->getFermetureById(999));
    }

    public function testGetFermeturesBySiteIdRetourneLesfermetures(): void {
        $mockRepo = $this->createStub(FermetureRepository::class);
        $mockRepo->method('findBySiteId')->willReturn([$this->creerFermeture(1, 1, '2026-08-01', '2026-08-07', 'Travaux')]);

        $service = new FermetureService($mockRepo, $this->creerAdminRepo('GLOBAL'));
        $this->assertCount(1, $service->getFermeturesBySiteId(1));
    }

    public function testGetFermeturesGlobalesRetourneLesfermeturesGlobales(): void {
        $mockRepo = $this->createStub(FermetureRepository::class);
        $mockRepo->method('findGlobales')->willReturn([$this->creerFermeture(2, null, '2026-12-25', '2026-12-25', 'Noël')]);

        $service = new FermetureService($mockRepo, $this->creerAdminRepo('GLOBAL'));
        $result  = $service->getFermeturesGlobales();

        $this->assertCount(1, $result);
        $this->assertNull($result[0]->getSiteId());
    }


    // ─── createFermeture ─────────────────────────────────────────────────────

    public function testCreateFermetureGlobaleAccepteAdminGlobal(): void {
        $mockRepo = $this->createStub(FermetureRepository::class);
        $mockRepo->method('insert')->willReturn(3);

        $service = new FermetureService($mockRepo, $this->creerAdminRepo('GLOBAL'));
        $result  = $service->createFermeture(['date_debut' => '2026-12-25', 'date_fin' => '2026-12-25'], 1);

        $this->assertEquals(3, $result);
    }

    public function testCreateFermetureGlobaleRefuseAdminSite(): void {
        $mockRepo = $this->createStub(FermetureRepository::class);

        $service = new FermetureService($mockRepo, $this->creerAdminRepo('SITE', 1));
        $result  = $service->createFermeture(['date_debut' => '2026-12-25', 'date_fin' => '2026-12-25'], 1);

        $this->assertEquals('acces_interdit', $result);
    }

    public function testCreateFermetureSiteAccepteAdminSiteSonSite(): void {
        $mockRepo = $this->createStub(FermetureRepository::class);
        $mockRepo->method('insert')->willReturn(3);

        $service = new FermetureService($mockRepo, $this->creerAdminRepo('SITE', 1));
        $result  = $service->createFermeture(['site_id' => 1, 'date_debut' => '2026-08-01', 'date_fin' => '2026-08-07'], 1);

        $this->assertEquals(3, $result);
    }

    public function testCreateFermetureSiteRefuseAdminSiteAutreSite(): void {
        $mockRepo = $this->createStub(FermetureRepository::class);

        $service = new FermetureService($mockRepo, $this->creerAdminRepo('SITE', 2));
        $result  = $service->createFermeture(['site_id' => 1, 'date_debut' => '2026-08-01', 'date_fin' => '2026-08-07'], 1);

        $this->assertEquals('acces_interdit', $result);
    }

    public function testCreateFermetureRetourneDatesInvalides(): void {
        $mockRepo = $this->createStub(FermetureRepository::class);

        $service = new FermetureService($mockRepo, $this->creerAdminRepo('GLOBAL'));
        $result  = $service->createFermeture(['date_debut' => '2026-08-10', 'date_fin' => '2026-08-01'], 1);

        $this->assertEquals('dates_invalides', $result);
    }


    // ─── updateFermeture ─────────────────────────────────────────────────────

    public function testUpdateFermetureGlobaleAccepteAdminGlobal(): void {
        $mockRepo = $this->createStub(FermetureRepository::class);
        $mockRepo->method('findById')->willReturn($this->creerFermeture(1, null, '2026-12-25', '2026-12-25', 'Noël'));

        $service = new FermetureService($mockRepo, $this->creerAdminRepo('GLOBAL'));
        $this->assertTrue($service->updateFermeture(1, ['raison' => 'Fête'], 1));
    }

    public function testUpdateFermetureGlobaleRefuseAdminSite(): void {
        $mockRepo = $this->createStub(FermetureRepository::class);
        $mockRepo->method('findById')->willReturn($this->creerFermeture(1, null, '2026-12-25', '2026-12-25', 'Noël'));

        $service = new FermetureService($mockRepo, $this->creerAdminRepo('SITE', 1));
        $this->assertEquals('acces_interdit', $service->updateFermeture(1, ['raison' => 'X'], 1));
    }

    public function testUpdateFermetureSiteAccepteAdminSiteSonSite(): void {
        $mockRepo = $this->createStub(FermetureRepository::class);
        $mockRepo->method('findById')->willReturn($this->creerFermeture(1, 1, '2026-08-01', '2026-08-07', 'Travaux'));

        $service = new FermetureService($mockRepo, $this->creerAdminRepo('SITE', 1));
        $this->assertTrue($service->updateFermeture(1, ['date_fin' => '2026-08-14'], 1));
    }

    public function testUpdateFermetureSiteRefuseAdminSiteAutreSite(): void {
        $mockRepo = $this->createStub(FermetureRepository::class);
        $mockRepo->method('findById')->willReturn($this->creerFermeture(1, 1, '2026-08-01', '2026-08-07', 'Travaux'));

        $service = new FermetureService($mockRepo, $this->creerAdminRepo('SITE', 2));
        $this->assertEquals('acces_interdit', $service->updateFermeture(1, ['date_fin' => '2026-08-14'], 1));
    }

    public function testUpdateFermetureRetourneFalseSiInexistant(): void {
        $mockRepo = $this->createStub(FermetureRepository::class);
        $mockRepo->method('findById')->willReturn(null);

        $service = new FermetureService($mockRepo, $this->creerAdminRepo('GLOBAL'));
        $this->assertFalse($service->updateFermeture(999, ['date_fin' => '2026-08-14'], 1));
    }


    // ─── deleteFermeture ─────────────────────────────────────────────────────

    public function testDeleteFermetureGlobaleAccepteAdminGlobal(): void {
        $mockRepo = $this->createStub(FermetureRepository::class);
        $mockRepo->method('findById')->willReturn($this->creerFermeture(1, null, '2026-12-25', '2026-12-25', 'Noël'));

        $service = new FermetureService($mockRepo, $this->creerAdminRepo('GLOBAL'));
        $this->assertTrue($service->deleteFermeture(1, 1));
    }

    public function testDeleteFermetureGlobaleRefuseAdminSite(): void {
        $mockRepo = $this->createStub(FermetureRepository::class);
        $mockRepo->method('findById')->willReturn($this->creerFermeture(1, null, '2026-12-25', '2026-12-25', 'Noël'));

        $service = new FermetureService($mockRepo, $this->creerAdminRepo('SITE', 1));
        $this->assertEquals('acces_interdit', $service->deleteFermeture(1, 1));
    }

    public function testDeleteFermetureSiteAccepteAdminSiteSonSite(): void {
        $mockRepo = $this->createStub(FermetureRepository::class);
        $mockRepo->method('findById')->willReturn($this->creerFermeture(1, 1, '2026-08-01', '2026-08-07', 'Travaux'));

        $service = new FermetureService($mockRepo, $this->creerAdminRepo('SITE', 1));
        $this->assertTrue($service->deleteFermeture(1, 1));
    }

    public function testDeleteFermetureSiteRefuseAdminSiteAutreSite(): void {
        $mockRepo = $this->createStub(FermetureRepository::class);
        $mockRepo->method('findById')->willReturn($this->creerFermeture(1, 1, '2026-08-01', '2026-08-07', 'Travaux'));

        $service = new FermetureService($mockRepo, $this->creerAdminRepo('SITE', 2));
        $this->assertEquals('acces_interdit', $service->deleteFermeture(1, 1));
    }

    public function testDeleteFermetureRetourneFalseSiInexistant(): void {
        $mockRepo = $this->createStub(FermetureRepository::class);
        $mockRepo->method('findById')->willReturn(null);

        $service = new FermetureService($mockRepo, $this->creerAdminRepo('GLOBAL'));
        $this->assertFalse($service->deleteFermeture(999, 1));
    }
}
