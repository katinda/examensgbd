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


    // ─── Lecture (inchangé) ──────────────────────────────────────────────────

    public function testGetAllRetourneTousLesHoraires(): void {
        $stub = $this->createStub(HoraireSiteService::class);
        $stub->method('getAllHoraires')->willReturn([$this->creerHoraire(1), $this->creerHoraire(2)]);

        $response = $this->capturer(fn() => (new HoraireSiteController($stub))->getAll());
        $this->assertCount(2, $response);
    }

    public function testGetByIdRetourneLHoraire(): void {
        $stub = $this->createStub(HoraireSiteService::class);
        $stub->method('getHoraireById')->willReturn($this->creerHoraire(1));

        $response = $this->capturer(fn() => (new HoraireSiteController($stub))->getById(1));
        $this->assertEquals(200, http_response_code());
        $this->assertEquals(1, $response['id']);
    }

    public function testGetByIdRetourne404SiIntrouvable(): void {
        $stub = $this->createStub(HoraireSiteService::class);
        $stub->method('getHoraireById')->willReturn(null);

        $this->capturer(fn() => (new HoraireSiteController($stub))->getById(99));
        $this->assertEquals(404, http_response_code());
    }

    public function testGetBySiteIdRetourneLesHoraires(): void {
        $stub = $this->createStub(HoraireSiteService::class);
        $stub->method('getHorairesBySiteId')->willReturn([$this->creerHoraire(1)]);

        $response = $this->capturer(fn() => (new HoraireSiteController($stub))->getBySiteId(1));
        $this->assertCount(1, $response);
    }

    public function testGetBySiteAndAnneeRetourneLHoraire(): void {
        $stub = $this->createStub(HoraireSiteService::class);
        $stub->method('getHoraireBySiteAndAnnee')->willReturn($this->creerHoraire(1));

        $response = $this->capturer(fn() => (new HoraireSiteController($stub))->getBySiteAndAnnee(1, 2026));
        $this->assertEquals(200, http_response_code());
        $this->assertEquals(2026, $response['annee']);
    }

    public function testGetBySiteAndAnneeRetourne404SiIntrouvable(): void {
        $stub = $this->createStub(HoraireSiteService::class);
        $stub->method('getHoraireBySiteAndAnnee')->willReturn(null);

        $this->capturer(fn() => (new HoraireSiteController($stub))->getBySiteAndAnnee(99, 2099));
        $this->assertEquals(404, http_response_code());
    }


    // ─── create ─────────────────────────────────────────────────────────────

    public function testCreateRetourne400SiAdminIdManquant(): void {
        $stub = $this->createStub(HoraireSiteService::class);

        $this->capturer(fn() => (new HoraireSiteController($stub))->create());
        $this->assertEquals(400, http_response_code());
    }

    public function testCreateRetourne400SiChampsManquants(): void {
        $stub = $this->createStub(HoraireSiteService::class);
        $_GET['admin_id'] = '1';

        $this->capturer(fn() => (new HoraireSiteController($stub))->create());
        $this->assertEquals(400, http_response_code());
    }


    // ─── update ─────────────────────────────────────────────────────────────

    public function testUpdateRetourne400SiAdminIdManquant(): void {
        $stub = $this->createStub(HoraireSiteService::class);

        $this->capturer(fn() => (new HoraireSiteController($stub))->update(1));
        $this->assertEquals(400, http_response_code());
    }

    public function testUpdateRetourne200SiReussi(): void {
        $stub = $this->createStub(HoraireSiteService::class);
        $stub->method('updateHoraire')->willReturn(true);
        $_GET['admin_id'] = '1';

        $response = $this->capturer(fn() => (new HoraireSiteController($stub))->update(1));
        $this->assertEquals(200, http_response_code());
        $this->assertArrayHasKey('message', $response);
    }

    public function testUpdateRetourne403SiAccesInterdit(): void {
        $stub = $this->createStub(HoraireSiteService::class);
        $stub->method('updateHoraire')->willReturn('acces_interdit');
        $_GET['admin_id'] = '1';

        $response = $this->capturer(fn() => (new HoraireSiteController($stub))->update(1));
        $this->assertEquals(403, http_response_code());
        $this->assertArrayHasKey('erreur', $response);
    }

    public function testUpdateRetourne404SiIntrouvable(): void {
        $stub = $this->createStub(HoraireSiteService::class);
        $stub->method('updateHoraire')->willReturn(false);
        $_GET['admin_id'] = '1';

        $this->capturer(fn() => (new HoraireSiteController($stub))->update(99));
        $this->assertEquals(404, http_response_code());
    }


    // ─── delete ─────────────────────────────────────────────────────────────

    public function testDeleteRetourne400SiAdminIdManquant(): void {
        $stub = $this->createStub(HoraireSiteService::class);

        $this->capturer(fn() => (new HoraireSiteController($stub))->delete(1));
        $this->assertEquals(400, http_response_code());
    }

    public function testDeleteRetourne200SiReussi(): void {
        $stub = $this->createStub(HoraireSiteService::class);
        $stub->method('deleteHoraire')->willReturn(true);
        $_GET['admin_id'] = '1';

        $response = $this->capturer(fn() => (new HoraireSiteController($stub))->delete(1));
        $this->assertEquals(200, http_response_code());
        $this->assertArrayHasKey('message', $response);
    }

    public function testDeleteRetourne403SiAccesInterdit(): void {
        $stub = $this->createStub(HoraireSiteService::class);
        $stub->method('deleteHoraire')->willReturn('acces_interdit');
        $_GET['admin_id'] = '1';

        $response = $this->capturer(fn() => (new HoraireSiteController($stub))->delete(1));
        $this->assertEquals(403, http_response_code());
        $this->assertArrayHasKey('erreur', $response);
    }

    public function testDeleteRetourne404SiIntrouvable(): void {
        $stub = $this->createStub(HoraireSiteService::class);
        $stub->method('deleteHoraire')->willReturn(false);
        $_GET['admin_id'] = '1';

        $this->capturer(fn() => (new HoraireSiteController($stub))->delete(99));
        $this->assertEquals(404, http_response_code());
    }
}
