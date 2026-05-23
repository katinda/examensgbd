<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Reservation.php';
require_once __DIR__ . '/../services/ReservationService.php';
require_once __DIR__ . '/../controllers/ReservationController.php';

class ReservationControllerTest extends TestCase {

    protected function setUp(): void {
        http_response_code(200);
        $_GET = [];
    }

    private function capturer(callable $fn): array {
        ob_start();
        $fn();
        return json_decode(ob_get_clean(), true) ?? [];
    }

    private function creerReservation(int $id): Reservation {
        return new Reservation($id, 1, 1, '2026-05-10', '10:00:00', '11:30:00', 'PRIVE');
    }


    // getById → retourne la réservation et 200 si trouvée
    public function testGetByIdRetourneLaReservation(): void {
        $stub = $this->createStub(ReservationService::class);
        $stub->method('getReservationById')->willReturn($this->creerReservation(1));

        $response = $this->capturer(fn() => (new ReservationController($stub))->getById(1));

        $this->assertEquals(200, http_response_code());
        $this->assertEquals(1, $response['id']);
    }


    // getById → 404 si réservation introuvable
    public function testGetByIdRetourne404SiIntrouvable(): void {
        $stub = $this->createStub(ReservationService::class);
        $stub->method('getReservationById')->willReturn(null);

        $response = $this->capturer(fn() => (new ReservationController($stub))->getById(99));

        $this->assertEquals(404, http_response_code());
        $this->assertArrayHasKey('erreur', $response);
    }


    // getByMembre → retourne la liste des réservations d'un membre
    public function testGetByMembreRetourneLesReservations(): void {
        $stub = $this->createStub(ReservationService::class);
        $stub->method('getReservationsByMembre')->willReturn([$this->creerReservation(1), $this->creerReservation(2)]);

        $response = $this->capturer(fn() => (new ReservationController($stub))->getByMembre(1));

        $this->assertCount(2, $response);
    }


    // getByTerrainAndDate → 400 si paramètre "date" absent
    public function testGetByTerrainAndDateRetourne400SiDateAbsente(): void {
        $stub = $this->createStub(ReservationService::class);

        $response = $this->capturer(fn() => (new ReservationController($stub))->getByTerrainAndDate(1));

        $this->assertEquals(400, http_response_code());
        $this->assertArrayHasKey('erreur', $response);
    }


    // getByTerrainAndDate → retourne les réservations si date fournie
    public function testGetByTerrainAndDateRetourneLesReservations(): void {
        $_GET['date'] = '2026-05-10';
        $stub = $this->createStub(ReservationService::class);
        $stub->method('getReservationsByTerrainAndDate')->willReturn([$this->creerReservation(1)]);

        $response = $this->capturer(fn() => (new ReservationController($stub))->getByTerrainAndDate(1));

        $this->assertEquals(200, http_response_code());
        $this->assertCount(1, $response);
    }


    // getPubliques → retourne les matchs publics disponibles
    public function testGetPubliquesRetourneLesMatchs(): void {
        $stub = $this->createStub(ReservationService::class);
        $stub->method('getMatchesPublics')->willReturn([['id' => 1, 'places_restantes' => 3]]);

        $response = $this->capturer(fn() => (new ReservationController($stub))->getPubliques());

        $this->assertCount(1, $response);
        $this->assertArrayHasKey('places_restantes', $response[0]);
    }


    // create → 400 si champs obligatoires absents
    public function testCreateRetourne400SiChampsManquants(): void {
        $stub = $this->createStub(ReservationService::class);

        $this->capturer(fn() => (new ReservationController($stub))->create());

        $this->assertEquals(400, http_response_code());
    }
}
