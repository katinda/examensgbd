<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Terrain.php';
require_once __DIR__ . '/../services/TerrainService.php';
require_once __DIR__ . '/../controllers/TerrainController.php';

class TerrainControllerTest extends TestCase {

    protected function setUp(): void {
        http_response_code(200);
        $_GET = [];
    }

    private function capturer(callable $fn): array {
        ob_start();
        $fn();
        return json_decode(ob_get_clean(), true) ?? [];
    }

    private function creerTerrain(int $id): Terrain {
        return new Terrain($id, 1, $id, "Terrain $id", true);
    }


    // getAll → retourne un tableau JSON
    public function testGetAllRetourneUnTableau(): void {
        $stub = $this->createStub(TerrainService::class);
        $stub->method('getAllTerrains')->willReturn([$this->creerTerrain(1), $this->creerTerrain(2)]);

        $response = $this->capturer(fn() => (new TerrainController($stub))->getAll());

        $this->assertCount(2, $response);
    }


    // getById → retourne le terrain et 200 si trouvé
    public function testGetByIdRetourneLeTerrain(): void {
        $stub = $this->createStub(TerrainService::class);
        $stub->method('getTerrainById')->willReturn($this->creerTerrain(1));

        $response = $this->capturer(fn() => (new TerrainController($stub))->getById(1));

        $this->assertEquals(200, http_response_code());
        $this->assertEquals(1, $response['id']);
    }


    // getById → 404 si terrain introuvable
    public function testGetByIdRetourne404SiIntrouvable(): void {
        $stub = $this->createStub(TerrainService::class);
        $stub->method('getTerrainById')->willReturn(null);

        $response = $this->capturer(fn() => (new TerrainController($stub))->getById(99));

        $this->assertEquals(404, http_response_code());
        $this->assertArrayHasKey('erreur', $response);
    }


    // getBySite → retourne les terrains du site
    public function testGetBySiteRetourneLesTerrains(): void {
        $stub = $this->createStub(TerrainService::class);
        $stub->method('getTerrainsBySite')->willReturn([$this->creerTerrain(1)]);

        $response = $this->capturer(fn() => (new TerrainController($stub))->getBySite(1));

        $this->assertEquals(200, http_response_code());
        $this->assertCount(1, $response);
    }


    // getBySite → 404 si site introuvable
    public function testGetBySiteRetourne404SiSiteIntrouvable(): void {
        $stub = $this->createStub(TerrainService::class);
        $stub->method('getTerrainsBySite')->willReturn(null);

        $response = $this->capturer(fn() => (new TerrainController($stub))->getBySite(99));

        $this->assertEquals(404, http_response_code());
        $this->assertArrayHasKey('erreur', $response);
    }


    // create → 400 si champs obligatoires absents
    public function testCreateRetourne400SiChampsManquants(): void {
        $stub = $this->createStub(TerrainService::class);

        $this->capturer(fn() => (new TerrainController($stub))->create());

        $this->assertEquals(400, http_response_code());
    }


    // update → 200 si mise à jour réussie
    public function testUpdateRetourne200SiReussi(): void {
        $stub = $this->createStub(TerrainService::class);
        $stub->method('updateTerrain')->willReturn(true);

        $response = $this->capturer(fn() => (new TerrainController($stub))->update(1));

        $this->assertEquals(200, http_response_code());
        $this->assertArrayHasKey('message', $response);
    }


    // update → 404 si terrain introuvable
    public function testUpdateRetourne404SiIntrouvable(): void {
        $stub = $this->createStub(TerrainService::class);
        $stub->method('updateTerrain')->willReturn(false);

        $this->capturer(fn() => (new TerrainController($stub))->update(99));

        $this->assertEquals(404, http_response_code());
    }


    // delete → 200 si suppression réussie
    public function testDeleteRetourne200SiReussi(): void {
        $stub = $this->createStub(TerrainService::class);
        $stub->method('deleteTerrain')->willReturn(true);

        $response = $this->capturer(fn() => (new TerrainController($stub))->delete(1));

        $this->assertEquals(200, http_response_code());
        $this->assertArrayHasKey('message', $response);
    }


    // delete → 404 si terrain introuvable
    public function testDeleteRetourne404SiIntrouvable(): void {
        $stub = $this->createStub(TerrainService::class);
        $stub->method('deleteTerrain')->willReturn(false);

        $this->capturer(fn() => (new TerrainController($stub))->delete(99));

        $this->assertEquals(404, http_response_code());
    }
}
