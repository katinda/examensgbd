<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Administrateur.php';
require_once __DIR__ . '/../services/AdministrateurService.php';
require_once __DIR__ . '/../controllers/AdministrateurController.php';

class AdministrateurControllerTest extends TestCase {

    protected function setUp(): void {
        http_response_code(200);
        $_GET = [];
    }

    private function capturer(callable $fn): array {
        ob_start();
        $fn();
        return json_decode(ob_get_clean(), true) ?? [];
    }

    private function creerAdmin(int $id): Administrateur {
        return new Administrateur($id, 'admin1', 'hash', 'Dupont', 'Jean', null, 'GLOBAL', null);
    }


    // getAll → retourne tous les administrateurs actifs
    public function testGetAllRetourneTousLesAdmins(): void {
        $stub = $this->createStub(AdministrateurService::class);
        $stub->method('getAllAdministrateurs')->willReturn([$this->creerAdmin(1), $this->creerAdmin(2)]);

        $response = $this->capturer(fn() => (new AdministrateurController($stub))->getAll());

        $this->assertCount(2, $response);
    }


    // getAll?inactifs=1 → retourne les administrateurs inactifs
    public function testGetAllRetourneLesInactifsAdmin(): void {
        $_GET['inactifs'] = '1';
        $stub = $this->createStub(AdministrateurService::class);
        $stub->method('getInactifsAdministrateurs')->willReturn([$this->creerAdmin(1)]);

        $response = $this->capturer(fn() => (new AdministrateurController($stub))->getAll());

        $this->assertCount(1, $response);
    }


    // getById → retourne l'admin et 200 si trouvé
    public function testGetByIdRetourneLAdmin(): void {
        $stub = $this->createStub(AdministrateurService::class);
        $stub->method('getAdministrateurById')->willReturn($this->creerAdmin(1));

        $response = $this->capturer(fn() => (new AdministrateurController($stub))->getById(1));

        $this->assertEquals(200, http_response_code());
        $this->assertEquals(1, $response['id']);
    }


    // getById → 404 si admin introuvable
    public function testGetByIdRetourne404SiIntrouvable(): void {
        $stub = $this->createStub(AdministrateurService::class);
        $stub->method('getAdministrateurById')->willReturn(null);

        $response = $this->capturer(fn() => (new AdministrateurController($stub))->getById(99));

        $this->assertEquals(404, http_response_code());
        $this->assertArrayHasKey('erreur', $response);
    }


    // create → 400 si champs obligatoires absents
    public function testCreateRetourne400SiChampsManquants(): void {
        $stub = $this->createStub(AdministrateurService::class);

        $this->capturer(fn() => (new AdministrateurController($stub))->create());

        $this->assertEquals(400, http_response_code());
    }


    // update → 200 si mise à jour réussie
    public function testUpdateRetourne200SiReussi(): void {
        $stub = $this->createStub(AdministrateurService::class);
        $stub->method('updateAdministrateur')->willReturn(true);

        $response = $this->capturer(fn() => (new AdministrateurController($stub))->update(1));

        $this->assertEquals(200, http_response_code());
        $this->assertArrayHasKey('message', $response);
    }


    // update → 404 si admin introuvable
    public function testUpdateRetourne404SiIntrouvable(): void {
        $stub = $this->createStub(AdministrateurService::class);
        $stub->method('updateAdministrateur')->willReturn(false);

        $this->capturer(fn() => (new AdministrateurController($stub))->update(99));

        $this->assertEquals(404, http_response_code());
    }


    // delete → 200 si désactivation réussie
    public function testDeleteRetourne200SiReussi(): void {
        $stub = $this->createStub(AdministrateurService::class);
        $stub->method('deleteAdministrateur')->willReturn(true);

        $response = $this->capturer(fn() => (new AdministrateurController($stub))->delete(1));

        $this->assertEquals(200, http_response_code());
        $this->assertArrayHasKey('message', $response);
    }


    // delete → 404 si admin introuvable
    public function testDeleteRetourne404SiIntrouvable(): void {
        $stub = $this->createStub(AdministrateurService::class);
        $stub->method('deleteAdministrateur')->willReturn(false);

        $this->capturer(fn() => (new AdministrateurController($stub))->delete(99));

        $this->assertEquals(404, http_response_code());
    }
}
