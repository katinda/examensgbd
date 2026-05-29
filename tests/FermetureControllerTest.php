<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Fermeture.php';
require_once __DIR__ . '/../services/FermetureService.php';
require_once __DIR__ . '/../controllers/FermetureController.php';

class FermetureControllerTest extends TestCase {

    protected function setUp(): void {
        http_response_code(200);
        $_GET = [];
    }

    private function capturer(callable $fn): array {
        ob_start();
        $fn();
        return json_decode(ob_get_clean(), true) ?? [];
    }

    private function creerFermeture(int $id): Fermeture {
        return new Fermeture($id, null, '2026-08-01', '2026-08-15', 'Congés annuels');
    }


    // ─── Lecture (inchangé) ──────────────────────────────────────────────────

    public function testGetAllRetourneToutesLesFermetures(): void {
        $stub = $this->createStub(FermetureService::class);
        $stub->method('getAllFermetures')->willReturn([$this->creerFermeture(1), $this->creerFermeture(2)]);

        $response = $this->capturer(fn() => (new FermetureController($stub))->getAll());
        $this->assertCount(2, $response);
    }

    public function testGetAllRetourneLesFermeturesGlobales(): void {
        $_GET['globales'] = '1';
        $stub = $this->createStub(FermetureService::class);
        $stub->method('getFermeturesGlobales')->willReturn([$this->creerFermeture(1)]);

        $response = $this->capturer(fn() => (new FermetureController($stub))->getAll());
        $this->assertCount(1, $response);
    }

    public function testGetAllRetourneLesFermeturesDUnSite(): void {
        $_GET['site_id'] = '1';
        $stub = $this->createStub(FermetureService::class);
        $stub->method('getFermeturesBySiteId')->willReturn([$this->creerFermeture(1)]);

        $response = $this->capturer(fn() => (new FermetureController($stub))->getAll());
        $this->assertCount(1, $response);
    }

    public function testGetByIdRetourneLaFermeture(): void {
        $stub = $this->createStub(FermetureService::class);
        $stub->method('getFermetureById')->willReturn($this->creerFermeture(1));

        $response = $this->capturer(fn() => (new FermetureController($stub))->getById(1));
        $this->assertEquals(200, http_response_code());
        $this->assertEquals(1, $response['id']);
    }

    public function testGetByIdRetourne404SiIntrouvable(): void {
        $stub = $this->createStub(FermetureService::class);
        $stub->method('getFermetureById')->willReturn(null);

        $this->capturer(fn() => (new FermetureController($stub))->getById(99));
        $this->assertEquals(404, http_response_code());
    }


    // ─── create ─────────────────────────────────────────────────────────────

    public function testCreateRetourne400SiAdminIdManquant(): void {
        $stub = $this->createStub(FermetureService::class);

        $this->capturer(fn() => (new FermetureController($stub))->create());
        $this->assertEquals(400, http_response_code());
    }

    public function testCreateRetourne400SiChampsManquants(): void {
        $stub = $this->createStub(FermetureService::class);
        $_GET['admin_id'] = '1';

        $this->capturer(fn() => (new FermetureController($stub))->create());
        $this->assertEquals(400, http_response_code());
    }


    // ─── update ─────────────────────────────────────────────────────────────

    public function testUpdateRetourne400SiAdminIdManquant(): void {
        $stub = $this->createStub(FermetureService::class);

        $this->capturer(fn() => (new FermetureController($stub))->update(1));
        $this->assertEquals(400, http_response_code());
    }

    public function testUpdateRetourne200SiReussi(): void {
        $stub = $this->createStub(FermetureService::class);
        $stub->method('updateFermeture')->willReturn(true);
        $_GET['admin_id'] = '1';

        $response = $this->capturer(fn() => (new FermetureController($stub))->update(1));
        $this->assertEquals(200, http_response_code());
        $this->assertArrayHasKey('message', $response);
    }

    public function testUpdateRetourne403SiAccesInterdit(): void {
        $stub = $this->createStub(FermetureService::class);
        $stub->method('updateFermeture')->willReturn('acces_interdit');
        $_GET['admin_id'] = '1';

        $response = $this->capturer(fn() => (new FermetureController($stub))->update(1));
        $this->assertEquals(403, http_response_code());
        $this->assertArrayHasKey('erreur', $response);
    }

    public function testUpdateRetourne404SiIntrouvable(): void {
        $stub = $this->createStub(FermetureService::class);
        $stub->method('updateFermeture')->willReturn(false);
        $_GET['admin_id'] = '1';

        $this->capturer(fn() => (new FermetureController($stub))->update(99));
        $this->assertEquals(404, http_response_code());
    }


    // ─── delete ─────────────────────────────────────────────────────────────

    public function testDeleteRetourne400SiAdminIdManquant(): void {
        $stub = $this->createStub(FermetureService::class);

        $this->capturer(fn() => (new FermetureController($stub))->delete(1));
        $this->assertEquals(400, http_response_code());
    }

    public function testDeleteRetourne200SiReussi(): void {
        $stub = $this->createStub(FermetureService::class);
        $stub->method('deleteFermeture')->willReturn(true);
        $_GET['admin_id'] = '1';

        $response = $this->capturer(fn() => (new FermetureController($stub))->delete(1));
        $this->assertEquals(200, http_response_code());
        $this->assertArrayHasKey('message', $response);
    }

    public function testDeleteRetourne403SiAccesInterdit(): void {
        $stub = $this->createStub(FermetureService::class);
        $stub->method('deleteFermeture')->willReturn('acces_interdit');
        $_GET['admin_id'] = '1';

        $response = $this->capturer(fn() => (new FermetureController($stub))->delete(1));
        $this->assertEquals(403, http_response_code());
        $this->assertArrayHasKey('erreur', $response);
    }

    public function testDeleteRetourne404SiIntrouvable(): void {
        $stub = $this->createStub(FermetureService::class);
        $stub->method('deleteFermeture')->willReturn(false);
        $_GET['admin_id'] = '1';

        $this->capturer(fn() => (new FermetureController($stub))->delete(99));
        $this->assertEquals(404, http_response_code());
    }
}
