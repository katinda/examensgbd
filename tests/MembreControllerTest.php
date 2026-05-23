<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Membre.php';
require_once __DIR__ . '/../services/MembreService.php';
require_once __DIR__ . '/../controllers/MembreController.php';

class MembreControllerTest extends TestCase {

    protected function setUp(): void {
        http_response_code(200);
        $_GET = [];
    }

    private function capturer(callable $fn): array {
        ob_start();
        $fn();
        return json_decode(ob_get_clean(), true) ?? [];
    }

    private function creerMembre(int $id): Membre {
        return new Membre($id, 'G0001', 'Dupont', 'Jean', null, null, 'G', null, true);
    }


    // getAll → retourne tous les membres
    public function testGetAllRetourneTousLesMembres(): void {
        $stub = $this->createStub(MembreService::class);
        $stub->method('getAllMembres')->willReturn([$this->creerMembre(1), $this->creerMembre(2)]);

        $response = $this->capturer(fn() => (new MembreController($stub))->getAll());

        $this->assertCount(2, $response);
    }


    // getAll?inactifs=1 → retourne les membres inactifs
    public function testGetAllRetourneLesInactifsSiDemande(): void {
        $_GET['inactifs'] = '1';
        $stub = $this->createStub(MembreService::class);
        $stub->method('getInactifsMembres')->willReturn([$this->creerMembre(1)]);

        $response = $this->capturer(fn() => (new MembreController($stub))->getAll());

        $this->assertCount(1, $response);
    }


    // getAll?categorie=G → retourne les membres filtrés par catégorie
    public function testGetAllRetourneLesMembresParCategorie(): void {
        $_GET['categorie'] = 'G';
        $stub = $this->createStub(MembreService::class);
        $stub->method('getMembresByCategorie')->willReturn([$this->creerMembre(1)]);

        $response = $this->capturer(fn() => (new MembreController($stub))->getAll());

        $this->assertCount(1, $response);
    }


    // getById → retourne le membre et 200 si trouvé
    public function testGetByIdRetourneLeMembre(): void {
        $stub = $this->createStub(MembreService::class);
        $stub->method('getMembreById')->willReturn($this->creerMembre(1));

        $response = $this->capturer(fn() => (new MembreController($stub))->getById(1));

        $this->assertEquals(200, http_response_code());
        $this->assertEquals(1, $response['id']);
    }


    // getById → 404 si membre introuvable
    public function testGetByIdRetourne404SiIntrouvable(): void {
        $stub = $this->createStub(MembreService::class);
        $stub->method('getMembreById')->willReturn(null);

        $response = $this->capturer(fn() => (new MembreController($stub))->getById(99));

        $this->assertEquals(404, http_response_code());
        $this->assertArrayHasKey('erreur', $response);
    }


    // getByMatricule → retourne le membre si trouvé
    public function testGetByMatriculeRetourneLeMembre(): void {
        $stub = $this->createStub(MembreService::class);
        $stub->method('getMembreByMatricule')->willReturn($this->creerMembre(1));

        $response = $this->capturer(fn() => (new MembreController($stub))->getByMatricule('G0001'));

        $this->assertEquals(200, http_response_code());
        $this->assertArrayHasKey('matricule', $response);
    }


    // getByMatricule → 404 si matricule introuvable
    public function testGetByMatriculeRetourne404SiIntrouvable(): void {
        $stub = $this->createStub(MembreService::class);
        $stub->method('getMembreByMatricule')->willReturn(null);

        $response = $this->capturer(fn() => (new MembreController($stub))->getByMatricule('INCONNU'));

        $this->assertEquals(404, http_response_code());
        $this->assertArrayHasKey('erreur', $response);
    }


    // create → 400 si champs obligatoires absents
    public function testCreateRetourne400SiChampsManquants(): void {
        $stub = $this->createStub(MembreService::class);

        $this->capturer(fn() => (new MembreController($stub))->create());

        $this->assertEquals(400, http_response_code());
    }


    // update → 200 si mise à jour réussie
    public function testUpdateRetourne200SiReussi(): void {
        $stub = $this->createStub(MembreService::class);
        $stub->method('updateMembre')->willReturn(true);

        $response = $this->capturer(fn() => (new MembreController($stub))->update(1));

        $this->assertEquals(200, http_response_code());
        $this->assertArrayHasKey('message', $response);
    }


    // update → 404 si membre introuvable
    public function testUpdateRetourne404SiIntrouvable(): void {
        $stub = $this->createStub(MembreService::class);
        $stub->method('updateMembre')->willReturn(false);

        $this->capturer(fn() => (new MembreController($stub))->update(99));

        $this->assertEquals(404, http_response_code());
    }


    // delete → 200 si désactivation réussie
    public function testDeleteRetourne200SiReussi(): void {
        $stub = $this->createStub(MembreService::class);
        $stub->method('deleteMembre')->willReturn(true);

        $response = $this->capturer(fn() => (new MembreController($stub))->delete(1));

        $this->assertEquals(200, http_response_code());
        $this->assertArrayHasKey('message', $response);
    }


    // delete → 404 si membre introuvable
    public function testDeleteRetourne404SiIntrouvable(): void {
        $stub = $this->createStub(MembreService::class);
        $stub->method('deleteMembre')->willReturn(false);

        $this->capturer(fn() => (new MembreController($stub))->delete(99));

        $this->assertEquals(404, http_response_code());
    }
}
