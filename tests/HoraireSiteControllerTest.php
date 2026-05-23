<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/HoraireSite.php';
require_once __DIR__ . '/../services/HoraireSiteService.php';
require_once __DIR__ . '/../controllers/HoraireSiteController.php';

class HoraireSiteControllerTest extends TestCase {

    protected function setUp(): void {
        http_response_code(200);
        $_GET = [];
    }

    private function capturer(callable $fn): array {
        ob_start();
        $fn();
        return json_decode(ob_get_clean(), true) ?? [];
    }

    private function creerHoraire(int $id): HoraireSite {
        return new HoraireSite($id, 1, 2026, '08:00:00', '22:00:00');
    }


    // getAll → retourne tous les horaires
    public function testGetAllRetourneTousLesHoraires(): void {
        $stub = $this->createStub(HoraireSiteService::class);
        $stub->method('getAllHoraires')->willReturn([$this->creerHoraire(1), $this->creerHoraire(2)]);

        $response = $this->capturer(fn() => (new HoraireSiteController($stub))->getAll());

        $this->assertCount(2, $response);
    }


    // getById → retourne l'horaire et 200 si trouvé
    public function testGetByIdRetourneLHoraire(): void {
        $stub = $this->createStub(HoraireSiteService::class);
        $stub->method('getHoraireById')->willReturn($this->creerHoraire(1));

        $response = $this->capturer(fn() => (new HoraireSiteController($stub))->getById(1));

        $this->assertEquals(200, http_response_code());
        $this->assertEquals(1, $response['id']);
    }


    // getById → 404 si horaire introuvable
    public function testGetByIdRetourne404SiIntrouvable(): void {
        $stub = $this->createStub(HoraireSiteService::class);
        $stub->method('getHoraireById')->willReturn(null);

        $response = $this->capturer(fn() => (new HoraireSiteController($stub))->getById(99));

        $this->assertEquals(404, http_response_code());
        $this->assertArrayHasKey('erreur', $response);
    }


    // getBySiteId → retourne les horaires du site
    public function testGetBySiteIdRetourneLesHoraires(): void {
        $stub = $this->createStub(HoraireSiteService::class);
        $stub->method('getHorairesBySiteId')->willReturn([$this->creerHoraire(1)]);

        $response = $this->capturer(fn() => (new HoraireSiteController($stub))->getBySiteId(1));

        $this->assertCount(1, $response);
    }


    // getBySiteAndAnnee → retourne l'horaire si trouvé
    public function testGetBySiteAndAnneeRetourneLHoraire(): void {
        $stub = $this->createStub(HoraireSiteService::class);
        $stub->method('getHoraireBySiteAndAnnee')->willReturn($this->creerHoraire(1));

        $response = $this->capturer(fn() => (new HoraireSiteController($stub))->getBySiteAndAnnee(1, 2026));

        $this->assertEquals(200, http_response_code());
        $this->assertEquals(2026, $response['annee']);
    }


    // getBySiteAndAnnee → 404 si aucun horaire pour ce site et cette année
    public function testGetBySiteAndAnneeRetourne404SiIntrouvable(): void {
        $stub = $this->createStub(HoraireSiteService::class);
        $stub->method('getHoraireBySiteAndAnnee')->willReturn(null);

        $response = $this->capturer(fn() => (new HoraireSiteController($stub))->getBySiteAndAnnee(99, 2099));

        $this->assertEquals(404, http_response_code());
        $this->assertArrayHasKey('erreur', $response);
    }


    // create → 400 si champs obligatoires absents
    public function testCreateRetourne400SiChampsManquants(): void {
        $stub = $this->createStub(HoraireSiteService::class);

        $this->capturer(fn() => (new HoraireSiteController($stub))->create());

        $this->assertEquals(400, http_response_code());
    }


    // update → 200 si mise à jour réussie
    public function testUpdateRetourne200SiReussi(): void {
        $stub = $this->createStub(HoraireSiteService::class);
        $stub->method('updateHoraire')->willReturn(true);

        $response = $this->capturer(fn() => (new HoraireSiteController($stub))->update(1));

        $this->assertEquals(200, http_response_code());
        $this->assertArrayHasKey('message', $response);
    }


    // update → 404 si horaire introuvable
    public function testUpdateRetourne404SiIntrouvable(): void {
        $stub = $this->createStub(HoraireSiteService::class);
        $stub->method('updateHoraire')->willReturn(false);

        $this->capturer(fn() => (new HoraireSiteController($stub))->update(99));

        $this->assertEquals(404, http_response_code());
    }


    // delete → 200 si suppression réussie
    public function testDeleteRetourne200SiReussi(): void {
        $stub = $this->createStub(HoraireSiteService::class);
        $stub->method('deleteHoraire')->willReturn(true);

        $response = $this->capturer(fn() => (new HoraireSiteController($stub))->delete(1));

        $this->assertEquals(200, http_response_code());
        $this->assertArrayHasKey('message', $response);
    }


    // delete → 404 si horaire introuvable
    public function testDeleteRetourne404SiIntrouvable(): void {
        $stub = $this->createStub(HoraireSiteService::class);
        $stub->method('deleteHoraire')->willReturn(false);

        $this->capturer(fn() => (new HoraireSiteController($stub))->delete(99));

        $this->assertEquals(404, http_response_code());
    }
}
