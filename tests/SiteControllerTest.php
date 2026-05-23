<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Site.php';
require_once __DIR__ . '/../services/SiteService.php';
require_once __DIR__ . '/../controllers/SiteController.php';

class SiteControllerTest extends TestCase {

    protected function setUp(): void {
        http_response_code(200);
        $_GET = [];
    }

    private function capturer(callable $fn): array {
        ob_start();
        $fn();
        return json_decode(ob_get_clean(), true) ?? [];
    }

    private function creerSite(int $id): Site {
        return new Site($id, 'Site Test', 'Rue A', 'Bruxelles', '1000');
    }


    // getAll → retourne un tableau JSON avec tous les sites
    public function testGetAllRetourneUnTableau(): void {
        $stub = $this->createStub(SiteService::class);
        $stub->method('getAllSites')->willReturn([$this->creerSite(1), $this->creerSite(2)]);

        $response = $this->capturer(fn() => (new SiteController($stub))->getAll());

        $this->assertCount(2, $response);
        $this->assertEquals('Site Test', $response[0]['nom']);
    }


    // getById → retourne le site et 200 si trouvé
    public function testGetByIdRetourneLeSite(): void {
        $stub = $this->createStub(SiteService::class);
        $stub->method('getSiteById')->willReturn($this->creerSite(1));

        $response = $this->capturer(fn() => (new SiteController($stub))->getById(1));

        $this->assertEquals(200, http_response_code());
        $this->assertEquals(1, $response['id']);
    }


    // getById → 404 si site introuvable
    public function testGetByIdRetourne404SiIntrouvable(): void {
        $stub = $this->createStub(SiteService::class);
        $stub->method('getSiteById')->willReturn(null);

        $response = $this->capturer(fn() => (new SiteController($stub))->getById(99));

        $this->assertEquals(404, http_response_code());
        $this->assertArrayHasKey('erreur', $response);
    }


    // create → 400 si le champ "nom" est absent (php://input vide en test)
    public function testCreateRetourne400SiNomManquant(): void {
        $stub = $this->createStub(SiteService::class);

        $this->capturer(fn() => (new SiteController($stub))->create());

        $this->assertEquals(400, http_response_code());
    }


    // update → 200 si mise à jour réussie
    public function testUpdateRetourne200SiReussi(): void {
        $stub = $this->createStub(SiteService::class);
        $stub->method('updateSite')->willReturn(true);

        $response = $this->capturer(fn() => (new SiteController($stub))->update(1));

        $this->assertEquals(200, http_response_code());
        $this->assertArrayHasKey('message', $response);
    }


    // update → 404 si site introuvable
    public function testUpdateRetourne404SiIntrouvable(): void {
        $stub = $this->createStub(SiteService::class);
        $stub->method('updateSite')->willReturn(false);

        $this->capturer(fn() => (new SiteController($stub))->update(99));

        $this->assertEquals(404, http_response_code());
    }


    // delete → 200 si suppression réussie
    public function testDeleteRetourne200SiReussi(): void {
        $stub = $this->createStub(SiteService::class);
        $stub->method('deleteSite')->willReturn(true);

        $response = $this->capturer(fn() => (new SiteController($stub))->delete(1));

        $this->assertEquals(200, http_response_code());
        $this->assertArrayHasKey('message', $response);
    }


    // delete → 404 si site introuvable
    public function testDeleteRetourne404SiIntrouvable(): void {
        $stub = $this->createStub(SiteService::class);
        $stub->method('deleteSite')->willReturn(false);

        $this->capturer(fn() => (new SiteController($stub))->delete(99));

        $this->assertEquals(404, http_response_code());
    }
}
