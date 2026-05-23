<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Penalite.php';
require_once __DIR__ . '/../services/PenaliteService.php';
require_once __DIR__ . '/../controllers/PenaliteController.php';

class PenaliteControllerTest extends TestCase {

    protected function setUp(): void {
        http_response_code(200);
        $_GET = [];
    }

    private function capturer(callable $fn): array {
        ob_start();
        $fn();
        return json_decode(ob_get_clean(), true) ?? [];
    }

    private function creerPenalite(int $id): Penalite {
        return new Penalite($id, 1, null, '2026-05-01', '2026-05-08', 'PAYMENT_MISSING');
    }


    // getAll → retourne toutes les pénalités
    public function testGetAllRetourneToutesLesPenalites(): void {
        $stub = $this->createStub(PenaliteService::class);
        $stub->method('getAllPenalites')->willReturn([$this->creerPenalite(1), $this->creerPenalite(2)]);

        $response = $this->capturer(fn() => (new PenaliteController($stub))->getAll());

        $this->assertCount(2, $response);
    }


    // getAll?actives=1 → retourne uniquement les pénalités actives
    public function testGetAllRetourneLesPenalitesActives(): void {
        $_GET['actives'] = '1';
        $stub = $this->createStub(PenaliteService::class);
        $stub->method('getPenalitesActives')->willReturn([$this->creerPenalite(1)]);

        $response = $this->capturer(fn() => (new PenaliteController($stub))->getAll());

        $this->assertCount(1, $response);
    }


    // getAll?membre_id=1 → retourne les pénalités d'un membre
    public function testGetAllRetourneLesPenalitesDUnMembre(): void {
        $_GET['membre_id'] = '1';
        $stub = $this->createStub(PenaliteService::class);
        $stub->method('getPenalitesByMembreId')->willReturn([$this->creerPenalite(1)]);

        $response = $this->capturer(fn() => (new PenaliteController($stub))->getAll());

        $this->assertCount(1, $response);
    }


    // getById → retourne la pénalité et 200 si trouvée
    public function testGetByIdRetourneLaPenalite(): void {
        $stub = $this->createStub(PenaliteService::class);
        $stub->method('getPenaliteById')->willReturn($this->creerPenalite(1));

        $response = $this->capturer(fn() => (new PenaliteController($stub))->getById(1));

        $this->assertEquals(200, http_response_code());
        $this->assertEquals(1, $response['id']);
    }


    // getById → 404 si pénalité introuvable
    public function testGetByIdRetourne404SiIntrouvable(): void {
        $stub = $this->createStub(PenaliteService::class);
        $stub->method('getPenaliteById')->willReturn(null);

        $response = $this->capturer(fn() => (new PenaliteController($stub))->getById(99));

        $this->assertEquals(404, http_response_code());
        $this->assertArrayHasKey('erreur', $response);
    }


    // create → 400 si champs obligatoires absents
    public function testCreateRetourne400SiChampsManquants(): void {
        $stub = $this->createStub(PenaliteService::class);

        $this->capturer(fn() => (new PenaliteController($stub))->create());

        $this->assertEquals(400, http_response_code());
    }


    // lever → 404 si pénalité introuvable
    public function testLeverRetourne404SiIntrouvable(): void {
        $stub = $this->createStub(PenaliteService::class);
        $stub->method('leverPenalite')->willReturn('penalite_introuvable');

        $response = $this->capturer(fn() => (new PenaliteController($stub))->lever(99));

        $this->assertEquals(404, http_response_code());
        $this->assertArrayHasKey('erreur', $response);
    }


    // lever → 409 si pénalité déjà levée
    public function testLeverRetourne409SiDejaLevee(): void {
        $stub = $this->createStub(PenaliteService::class);
        $stub->method('leverPenalite')->willReturn('deja_levee');

        $response = $this->capturer(fn() => (new PenaliteController($stub))->lever(1));

        $this->assertEquals(409, http_response_code());
        $this->assertArrayHasKey('erreur', $response);
    }


    // lever → 403 si admin non global
    public function testLeverRetourne403SiAdminNonGlobal(): void {
        $stub = $this->createStub(PenaliteService::class);
        $stub->method('leverPenalite')->willReturn('admin_non_global');

        $response = $this->capturer(fn() => (new PenaliteController($stub))->lever(1));

        $this->assertEquals(403, http_response_code());
        $this->assertArrayHasKey('erreur', $response);
    }


    // lever → 200 si levée avec succès
    public function testLeverRetourne200SiReussi(): void {
        $stub = $this->createStub(PenaliteService::class);
        $stub->method('leverPenalite')->willReturn(true);

        $response = $this->capturer(fn() => (new PenaliteController($stub))->lever(1));

        $this->assertEquals(200, http_response_code());
        $this->assertArrayHasKey('message', $response);
    }


    // delete → 200 si suppression réussie
    public function testDeleteRetourne200SiReussi(): void {
        $stub = $this->createStub(PenaliteService::class);
        $stub->method('deletePenalite')->willReturn(true);

        $response = $this->capturer(fn() => (new PenaliteController($stub))->delete(1));

        $this->assertEquals(200, http_response_code());
        $this->assertArrayHasKey('message', $response);
    }


    // delete → 404 si pénalité introuvable
    public function testDeleteRetourne404SiIntrouvable(): void {
        $stub = $this->createStub(PenaliteService::class);
        $stub->method('deletePenalite')->willReturn(false);

        $this->capturer(fn() => (new PenaliteController($stub))->delete(99));

        $this->assertEquals(404, http_response_code());
    }
}
