<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Fermeture.php';
require_once __DIR__ . '/../repositories/FermetureRepository.php';
require_once __DIR__ . '/../services/FermetureService.php';

class FermetureServiceTest extends TestCase {

    // Vérifie que updateFermeture() retourne true quand la fermeture existe
    public function testUpdateFermetureRetourneTrueSiExiste(): void {
        $mockRepo = $this->createStub(FermetureRepository::class);
        $mockRepo->method('findById')->willReturn(
            new Fermeture(1, 1, '2026-08-01', '2026-08-07', 'Travaux')
        );

        $service = new FermetureService($mockRepo);
        $result  = $service->updateFermeture(1, ['date_fin' => '2026-08-14']);

        $this->assertTrue($result, "updateFermeture() doit retourner true si la fermeture existe");
    }
}
