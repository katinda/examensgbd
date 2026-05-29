<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/HoraireSite.php';
require_once __DIR__ . '/../models/Administrateur.php';
require_once __DIR__ . '/../repositories/HoraireSiteRepository.php';
require_once __DIR__ . '/../repositories/AdministrateurRepository.php';
require_once __DIR__ . '/../services/HoraireSiteService.php';

class HoraireSiteServiceTest extends TestCase {

    private function creerHoraire(int $id, int $siteId, int $annee, string $debut, string $fin): HoraireSite {
        return new HoraireSite($id, $siteId, $annee, $debut, $fin);
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

    public function testGetAllHorairesRetourneTousLesHoraires(): void {
        $mockRepo = $this->createStub(HoraireSiteRepository::class);
        $mockRepo->method('findAll')->willReturn([
            $this->creerHoraire(1, 1, 2026, '08:00:00', '22:00:00'),
            $this->creerHoraire(2, 2, 2026, '09:00:00', '21:00:00'),
        ]);

        $service = new HoraireSiteService($mockRepo, $this->creerAdminRepo('GLOBAL'));
        $this->assertCount(2, $service->getAllHoraires());
    }

    public function testGetHoraireByIdRetourneLeBonHoraire(): void {
        $mockRepo = $this->createStub(HoraireSiteRepository::class);
        $mockRepo->method('findById')->willReturn($this->creerHoraire(1, 1, 2026, '08:00:00', '22:00:00'));

        $service = new HoraireSiteService($mockRepo, $this->creerAdminRepo('GLOBAL'));
        $result  = $service->getHoraireById(1);

        $this->assertNotNull($result);
        $this->assertEquals('08:00:00', $result->getHeureDebut());
    }

    public function testGetHoraireByIdRetourneNullSiInexistant(): void {
        $mockRepo = $this->createStub(HoraireSiteRepository::class);
        $mockRepo->method('findById')->willReturn(null);

        $service = new HoraireSiteService($mockRepo, $this->creerAdminRepo('GLOBAL'));
        $this->assertNull($service->getHoraireById(999));
    }

    public function testGetHorairesBySiteIdRetourneLesHoraires(): void {
        $mockRepo = $this->createStub(HoraireSiteRepository::class);
        $mockRepo->method('findBySiteId')->willReturn([$this->creerHoraire(1, 1, 2026, '08:00:00', '22:00:00')]);

        $service = new HoraireSiteService($mockRepo, $this->creerAdminRepo('GLOBAL'));
        $this->assertCount(1, $service->getHorairesBySiteId(1));
    }

    public function testGetHoraireBySiteAndAnneeRetourneLeBonHoraire(): void {
        $mockRepo = $this->createStub(HoraireSiteRepository::class);
        $mockRepo->method('findBySiteAndAnnee')->willReturn($this->creerHoraire(1, 1, 2026, '08:00:00', '22:00:00'));

        $service = new HoraireSiteService($mockRepo, $this->creerAdminRepo('GLOBAL'));
        $result  = $service->getHoraireBySiteAndAnnee(1, 2026);

        $this->assertNotNull($result);
        $this->assertEquals(2026, $result->getAnnee());
    }


    // ─── createHoraire ───────────────────────────────────────────────────────

    public function testCreateHoraireAccepteAdminGlobal(): void {
        $mockRepo = $this->createStub(HoraireSiteRepository::class);
        $mockRepo->method('findBySiteAndAnnee')->willReturn(null);
        $mockRepo->method('insert')->willReturn(3);

        $service = new HoraireSiteService($mockRepo, $this->creerAdminRepo('GLOBAL'));
        $result  = $service->createHoraire(['site_id' => 1, 'annee' => 2026, 'heure_debut' => '08:00:00', 'heure_fin' => '22:00:00'], 1);

        $this->assertEquals(3, $result);
    }

    public function testCreateHoraireAccepteAdminSiteSurSonSite(): void {
        $mockRepo = $this->createStub(HoraireSiteRepository::class);
        $mockRepo->method('findBySiteAndAnnee')->willReturn(null);
        $mockRepo->method('insert')->willReturn(3);

        $service = new HoraireSiteService($mockRepo, $this->creerAdminRepo('SITE', 1));
        $result  = $service->createHoraire(['site_id' => 1, 'annee' => 2026, 'heure_debut' => '08:00:00', 'heure_fin' => '22:00:00'], 1);

        $this->assertEquals(3, $result);
    }

    public function testCreateHoraireRefuseAdminSiteSurAutreSite(): void {
        $mockRepo = $this->createStub(HoraireSiteRepository::class);

        $service = new HoraireSiteService($mockRepo, $this->creerAdminRepo('SITE', 2));
        $result  = $service->createHoraire(['site_id' => 1, 'annee' => 2026, 'heure_debut' => '08:00:00', 'heure_fin' => '22:00:00'], 1);

        $this->assertEquals('acces_interdit', $result);
    }

    public function testCreateHoraireRetourneAnneeInvalide(): void {
        $mockRepo = $this->createStub(HoraireSiteRepository::class);

        $service = new HoraireSiteService($mockRepo, $this->creerAdminRepo('GLOBAL'));
        $result  = $service->createHoraire(['site_id' => 1, 'annee' => 1999, 'heure_debut' => '08:00:00', 'heure_fin' => '22:00:00'], 1);

        $this->assertEquals('annee_invalide', $result);
    }

    public function testCreateHoraireRetourneHeuresInvalides(): void {
        $mockRepo = $this->createStub(HoraireSiteRepository::class);

        $service = new HoraireSiteService($mockRepo, $this->creerAdminRepo('GLOBAL'));
        $result  = $service->createHoraire(['site_id' => 1, 'annee' => 2026, 'heure_debut' => '22:00:00', 'heure_fin' => '08:00:00'], 1);

        $this->assertEquals('heures_invalides', $result);
    }

    public function testCreateHoraireRetourneDoublon(): void {
        $mockRepo = $this->createStub(HoraireSiteRepository::class);
        $mockRepo->method('findBySiteAndAnnee')->willReturn($this->creerHoraire(1, 1, 2026, '08:00:00', '22:00:00'));

        $service = new HoraireSiteService($mockRepo, $this->creerAdminRepo('GLOBAL'));
        $result  = $service->createHoraire(['site_id' => 1, 'annee' => 2026, 'heure_debut' => '09:00:00', 'heure_fin' => '21:00:00'], 1);

        $this->assertEquals('doublon', $result);
    }


    // ─── updateHoraire ───────────────────────────────────────────────────────

    public function testUpdateHoraireAccepteAdminGlobal(): void {
        $mockRepo = $this->createStub(HoraireSiteRepository::class);
        $mockRepo->method('findById')->willReturn($this->creerHoraire(1, 1, 2026, '08:00:00', '22:00:00'));

        $service = new HoraireSiteService($mockRepo, $this->creerAdminRepo('GLOBAL'));
        $this->assertTrue($service->updateHoraire(1, ['heure_debut' => '09:00:00'], 1));
    }

    public function testUpdateHoraireAccepteAdminSiteSurSonSite(): void {
        $mockRepo = $this->createStub(HoraireSiteRepository::class);
        $mockRepo->method('findById')->willReturn($this->creerHoraire(1, 1, 2026, '08:00:00', '22:00:00'));

        $service = new HoraireSiteService($mockRepo, $this->creerAdminRepo('SITE', 1));
        $this->assertTrue($service->updateHoraire(1, ['heure_debut' => '09:00:00'], 1));
    }

    public function testUpdateHoraireRefuseAdminSiteSurAutreSite(): void {
        $mockRepo = $this->createStub(HoraireSiteRepository::class);
        $mockRepo->method('findById')->willReturn($this->creerHoraire(1, 1, 2026, '08:00:00', '22:00:00'));

        $service = new HoraireSiteService($mockRepo, $this->creerAdminRepo('SITE', 2));
        $this->assertEquals('acces_interdit', $service->updateHoraire(1, ['heure_debut' => '09:00:00'], 1));
    }

    public function testUpdateHoraireRetourneFalseSiInexistant(): void {
        $mockRepo = $this->createStub(HoraireSiteRepository::class);
        $mockRepo->method('findById')->willReturn(null);

        $service = new HoraireSiteService($mockRepo, $this->creerAdminRepo('GLOBAL'));
        $this->assertFalse($service->updateHoraire(999, ['heure_debut' => '09:00:00'], 1));
    }


    // ─── deleteHoraire ───────────────────────────────────────────────────────

    public function testDeleteHoraireAccepteAdminGlobal(): void {
        $mockRepo = $this->createStub(HoraireSiteRepository::class);
        $mockRepo->method('findById')->willReturn($this->creerHoraire(1, 1, 2026, '08:00:00', '22:00:00'));

        $service = new HoraireSiteService($mockRepo, $this->creerAdminRepo('GLOBAL'));
        $this->assertTrue($service->deleteHoraire(1, 1));
    }

    public function testDeleteHoraireAccepteAdminSiteSurSonSite(): void {
        $mockRepo = $this->createStub(HoraireSiteRepository::class);
        $mockRepo->method('findById')->willReturn($this->creerHoraire(1, 1, 2026, '08:00:00', '22:00:00'));

        $service = new HoraireSiteService($mockRepo, $this->creerAdminRepo('SITE', 1));
        $this->assertTrue($service->deleteHoraire(1, 1));
    }

    public function testDeleteHoraireRefuseAdminSiteSurAutreSite(): void {
        $mockRepo = $this->createStub(HoraireSiteRepository::class);
        $mockRepo->method('findById')->willReturn($this->creerHoraire(1, 1, 2026, '08:00:00', '22:00:00'));

        $service = new HoraireSiteService($mockRepo, $this->creerAdminRepo('SITE', 2));
        $this->assertEquals('acces_interdit', $service->deleteHoraire(1, 1));
    }

    public function testDeleteHoraireRetourneFalseSiInexistant(): void {
        $mockRepo = $this->createStub(HoraireSiteRepository::class);
        $mockRepo->method('findById')->willReturn(null);

        $service = new HoraireSiteService($mockRepo, $this->creerAdminRepo('GLOBAL'));
        $this->assertFalse($service->deleteHoraire(999, 1));
    }
}
