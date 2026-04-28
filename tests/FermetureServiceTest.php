<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Fermeture.php';
require_once __DIR__ . '/../repositories/FermetureRepository.php';
require_once __DIR__ . '/../services/FermetureService.php';

class FermetureServiceTest extends TestCase {

    // Vérifie que getAllFermetures() retourne bien toutes les fermetures
    public function testGetAllFermeturesRetourneToutesLesFermetures(): void {
        $mockRepo = $this->createStub(FermetureRepository::class);
        $mockRepo->method('findAll')->willReturn([
            new Fermeture(1, 1,    '2026-08-01', '2026-08-07', 'Travaux'),
            new Fermeture(2, null, '2026-12-25', '2026-12-25', 'Noël'),
        ]);

        $service = new FermetureService($mockRepo);
        $result  = $service->getAllFermetures();

        $this->assertCount(2, $result, "getAllFermetures() doit retourner 2 fermetures");
    }
}
