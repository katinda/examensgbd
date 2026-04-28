<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Fermeture.php';
require_once __DIR__ . '/../repositories/FermetureRepository.php';
require_once __DIR__ . '/../services/FermetureService.php';

class FermetureServiceTest extends TestCase {

    // Vérifie que deleteFermeture() retourne true quand la fermeture existe
    public function testDeleteFermetureRetourneTrueSiExiste(): void {
        $mockRepo = $this->createStub(FermetureRepository::class);
        $mockRepo->method('findById')->willReturn(
            new Fermeture(1, 1, '2026-08-01', '2026-08-07', 'Travaux')
        );

        $service = new FermetureService($mockRepo);
        $result  = $service->deleteFermeture(1);

        $this->assertTrue($result, "deleteFermeture() doit retourner true si la fermeture existe");
    }
}
