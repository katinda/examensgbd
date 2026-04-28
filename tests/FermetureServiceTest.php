<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Fermeture.php';
require_once __DIR__ . '/../repositories/FermetureRepository.php';
require_once __DIR__ . '/../services/FermetureService.php';

class FermetureServiceTest extends TestCase {

    // Vérifie que getFermetureById() retourne la bonne fermeture
    public function testGetFermetureByIdRetourneLaBonneFermeture(): void {
        $mockRepo = $this->createStub(FermetureRepository::class);
        $mockRepo->method('findById')->willReturn(
            new Fermeture(1, 1, '2026-08-01', '2026-08-07', 'Travaux')
        );

        $service = new FermetureService($mockRepo);
        $result  = $service->getFermetureById(1);

        $this->assertNotNull($result);
        $this->assertEquals('2026-08-01', $result->getDateDebut());
    }
}
