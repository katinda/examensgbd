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


    // getAll → retourne toutes les fermetures
    public function testGetAllRetourneToutesLesFermetures(): void {
        $stub = $this->createStub(FermetureService::class);
        $stub->method('getAllFermetures')->willReturn([$this->creerFermeture(1), $this->creerFermeture(2)]);

        $response = $this->capturer(fn() => (new FermetureController($stub))->getAll());

        $this->assertCount(2, $response);
    }


    // getAll?globales=1 → retourne uniquement les fermetures globales
    public function testGetAllRetourneLesFermeturesGlobales(): void {
        $_GET['globales'] = '1';
        $stub = $this->createStub(FermetureService::class);
        $stub->method('getFermeturesGlobales')->willReturn([$this->creerFermeture(1)]);

        $response = $this->capturer(fn() => (new FermetureController($stub))->getAll());

        $this->assertCount(1, $response);
    }


    // getAll?site_id=1 → retourne les fermetures d'un site
    public function testGetAllRetourneLesFermeturesDUnSite(): void {
        $_GET['site_id'] = '1';
        $stub = $this->createStub(FermetureService::class);
        $stub->method('getFermeturesBySiteId')->willReturn([$this->creerFermeture(1)]);

        $response = $this->capturer(fn() => (new FermetureController($stub))->getAll());

        $this->assertCount(1, $response);
    }


    // getById → retourne la fermeture et 200 si trouvée
    public function testGetByIdRetourneLaFermeture(): void {
        $stub = $this->createStub(FermetureService::class);
        $stub->method('getFermetureById')->willReturn($this->creerFermeture(1));

        $response = $this->capturer(fn() => (new FermetureController($stub))->getById(1));

        $this->assertEquals(200, http_response_code());
        $this->assertEquals(1, $response['id']);
    }


    // getById → 404 si fermeture introuvable
    public function testGetByIdRetourne404SiIntrouvable(): void {
        $stub = $this->createStub(FermetureService::class);
        $stub->method('getFermetureById')->willReturn(null);

        $response = $this->capturer(fn() => (new FermetureController($stub))->getById(99));

        $this->assertEquals(404, http_response_code());
        $this->assertArrayHasKey('erreur', $response);
    }


    // create → 400 si champs obligatoires absents
    public function testCreateRetourne400SiChampsManquants(): void {
        $stub = $this->createStub(FermetureService::class);

        $this->capturer(fn() => (new FermetureController($stub))->create());

        $this->assertEquals(400, http_response_code());
    }


    // update → 200 si mise à jour réussie
    public function testUpdateRetourne200SiReussi(): void {
        $stub = $this->createStub(FermetureService::class);
        $stub->method('updateFermeture')->willReturn(true);

        $response = $this->capturer(fn() => (new FermetureController($stub))->update(1));

        $this->assertEquals(200, http_response_code());
        $this->assertArrayHasKey('message', $response);
    }


    // update → 404 si fermeture introuvable
    public function testUpdateRetourne404SiIntrouvable(): void {
        $stub = $this->createStub(FermetureService::class);
        $stub->method('updateFermeture')->willReturn(false);

        $this->capturer(fn() => (new FermetureController($stub))->update(99));

        $this->assertEquals(404, http_response_code());
    }


    // delete → 200 si suppression réussie
    public function testDeleteRetourne200SiReussi(): void {
        $stub = $this->createStub(FermetureService::class);
        $stub->method('deleteFermeture')->willReturn(true);

        $response = $this->capturer(fn() => (new FermetureController($stub))->delete(1));

        $this->assertEquals(200, http_response_code());
        $this->assertArrayHasKey('message', $response);
    }


    // delete → 404 si fermeture introuvable
    public function testDeleteRetourne404SiIntrouvable(): void {
        $stub = $this->createStub(FermetureService::class);
        $stub->method('deleteFermeture')->willReturn(false);

        $this->capturer(fn() => (new FermetureController($stub))->delete(99));

        $this->assertEquals(404, http_response_code());
    }
}
