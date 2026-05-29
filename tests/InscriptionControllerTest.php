<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Inscription.php';
require_once __DIR__ . '/../services/InscriptionService.php';
require_once __DIR__ . '/../controllers/InscriptionController.php';

class InscriptionControllerTest extends TestCase {

    protected function setUp(): void {
        http_response_code(200);
        $_GET = [];
    }

    private function capturer(callable $fn): array {
        ob_start();
        $fn();
        return json_decode(ob_get_clean(), true) ?? [];
    }

    private function creerInscription(int $id): array {
        return [
            'id'               => $id,
            'reservation_id'   => 1,
            'membre_id'        => $id,
            'nom'              => 'Dupont',
            'prenom'           => 'Jean',
            'matricule'        => "S0000$id",
            'est_organisateur' => false,
        ];
    }


    // getByReservation → retourne la liste des joueurs inscrits
    public function testGetByReservationRetourneLesInscriptions(): void {
        $stub = $this->createStub(InscriptionService::class);
        $stub->method('getInscriptionsByReservation')->willReturn([
            $this->creerInscription(1),
            $this->creerInscription(2),
        ]);

        $response = $this->capturer(fn() => (new InscriptionController($stub))->getByReservation(1));

        $this->assertCount(2, $response);
        $this->assertArrayHasKey('membre_id', $response[0]);
    }


    // addJoueur → 400 si "membre_id" absent
    public function testAddJoueurRetourne400SiMembreIdAbsent(): void {
        $stub = $this->createStub(InscriptionService::class);

        $response = $this->capturer(fn() => (new InscriptionController($stub))->addJoueur(1));

        $this->assertEquals(400, http_response_code());
        $this->assertArrayHasKey('erreur', $response);
    }


    // removeJoueur → 200 si joueur retiré avec succès
    public function testRemoveJoueurRetourne200SiReussi(): void {
        $stub = $this->createStub(InscriptionService::class);
        $stub->method('removeJoueur')->willReturn(true);

        $response = $this->capturer(fn() => (new InscriptionController($stub))->removeJoueur(1, 1));

        $this->assertEquals(200, http_response_code());
        $this->assertArrayHasKey('message', $response);
    }


    // removeJoueur → 404 si joueur non inscrit à cette réservation
    public function testRemoveJoueurRetourne404SiNonInscrit(): void {
        $stub = $this->createStub(InscriptionService::class);
        $stub->method('removeJoueur')->willReturn(false);

        $response = $this->capturer(fn() => (new InscriptionController($stub))->removeJoueur(1, 99));

        $this->assertEquals(404, http_response_code());
        $this->assertArrayHasKey('erreur', $response);
    }
}
