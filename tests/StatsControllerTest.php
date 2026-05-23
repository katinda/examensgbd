<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../services/StatsService.php';
require_once __DIR__ . '/../controllers/StatsController.php';

class StatsControllerTest extends TestCase {

    protected function setUp(): void {
        http_response_code(200);
        $_GET = [];
    }

    private function capturer(callable $fn): array {
        ob_start();
        $fn();
        return json_decode(ob_get_clean(), true) ?? [];
    }


    // getStats sans paramètre → retourne les stats globales
    public function testGetStatsRetourneLesStatsGlobales(): void {
        $stub = $this->createStub(StatsService::class);
        $stub->method('getStatsGlobales')->willReturn([
            'total_reservations' => 42,
            'chiffre_affaires'   => 2520.00,
        ]);

        $response = $this->capturer(fn() => (new StatsController($stub))->getStats());

        $this->assertEquals(200, http_response_code());
        $this->assertArrayHasKey('total_reservations', $response);
        $this->assertEquals(42, $response['total_reservations']);
    }


    // getStats?site_id=1 → retourne les stats d'un site précis
    public function testGetStatsRetourneLesStatsDUnSite(): void {
        $_GET['site_id'] = '1';
        $stub = $this->createStub(StatsService::class);
        $stub->method('getStatsBySite')->willReturn([
            'site_id'          => 1,
            'total_reservations' => 10,
            'chiffre_affaires'   => 600.00,
        ]);

        $response = $this->capturer(fn() => (new StatsController($stub))->getStats());

        $this->assertEquals(200, http_response_code());
        $this->assertArrayHasKey('site_id', $response);
        $this->assertEquals(1, $response['site_id']);
    }
}
