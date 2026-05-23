<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Paiement.php';
require_once __DIR__ . '/../services/PaiementService.php';
require_once __DIR__ . '/../controllers/PaiementController.php';

class PaiementControllerTest extends TestCase {

    protected function setUp(): void {
        http_response_code(200);
        $_GET = [];
    }

    private function capturer(callable $fn): array {
        ob_start();
        $fn();
        return json_decode(ob_get_clean(), true) ?? [];
    }

    private function creerPaiement(int $id): Paiement {
        return new Paiement($id, 1, 15.00, '2026-05-01 10:00:00', 'CARTE');
    }


    // getByInscription → retourne le paiement si trouvé
    public function testGetByInscriptionRetourneLePaiement(): void {
        $stub = $this->createStub(PaiementService::class);
        $stub->method('getPaiementByInscription')->willReturn($this->creerPaiement(1));

        $response = $this->capturer(fn() => (new PaiementController($stub))->getByInscription(1));

        $this->assertEquals(200, http_response_code());
        $this->assertArrayHasKey('montant', $response);
    }


    // getByInscription → 404 si inscription introuvable
    public function testGetByInscriptionRetourne404SiInscriptionIntrouvable(): void {
        $stub = $this->createStub(PaiementService::class);
        $stub->method('getPaiementByInscription')->willReturn('inscription_introuvable');

        $response = $this->capturer(fn() => (new PaiementController($stub))->getByInscription(99));

        $this->assertEquals(404, http_response_code());
        $this->assertArrayHasKey('erreur', $response);
    }


    // getByInscription → 404 si aucun paiement pour cette inscription
    public function testGetByInscriptionRetourne404SiPaiementAbsent(): void {
        $stub = $this->createStub(PaiementService::class);
        $stub->method('getPaiementByInscription')->willReturn('paiement_introuvable');

        $response = $this->capturer(fn() => (new PaiementController($stub))->getByInscription(1));

        $this->assertEquals(404, http_response_code());
        $this->assertArrayHasKey('erreur', $response);
    }


    // annuler → 200 si annulation réussie
    public function testAnnulerRetourne200SiReussi(): void {
        $stub = $this->createStub(PaiementService::class);
        $stub->method('annulerPaiement')->willReturn(true);

        $response = $this->capturer(fn() => (new PaiementController($stub))->annuler(1));

        $this->assertEquals(200, http_response_code());
        $this->assertArrayHasKey('message', $response);
    }


    // annuler → 404 si paiement introuvable
    public function testAnnulerRetourne404SiIntrouvable(): void {
        $stub = $this->createStub(PaiementService::class);
        $stub->method('annulerPaiement')->willReturn('paiement_introuvable');

        $response = $this->capturer(fn() => (new PaiementController($stub))->annuler(99));

        $this->assertEquals(404, http_response_code());
        $this->assertArrayHasKey('erreur', $response);
    }


    // annuler → 409 si paiement déjà annulé
    public function testAnnulerRetourne409SiDejaAnnule(): void {
        $stub = $this->createStub(PaiementService::class);
        $stub->method('annulerPaiement')->willReturn('paiement_deja_annule');

        $response = $this->capturer(fn() => (new PaiementController($stub))->annuler(1));

        $this->assertEquals(409, http_response_code());
        $this->assertArrayHasKey('erreur', $response);
    }


    // create → 404 si inscription introuvable (service appelé avec php://input vide → inscription_introuvable)
    public function testCreateRetourne404SiInscriptionIntrouvable(): void {
        $stub = $this->createStub(PaiementService::class);
        $stub->method('createPaiement')->willReturn('inscription_introuvable');

        $response = $this->capturer(fn() => (new PaiementController($stub))->create(99));

        $this->assertEquals(404, http_response_code());
        $this->assertArrayHasKey('erreur', $response);
    }


    // create → 409 si paiement déjà existant
    public function testCreateRetourne409SiDejaExistant(): void {
        $stub = $this->createStub(PaiementService::class);
        $stub->method('createPaiement')->willReturn('paiement_deja_existant');

        $response = $this->capturer(fn() => (new PaiementController($stub))->create(1));

        $this->assertEquals(409, http_response_code());
        $this->assertArrayHasKey('erreur', $response);
    }
}
