<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Fermeture.php';
require_once __DIR__ . '/../repositories/FermetureRepository.php';
require_once __DIR__ . '/../services/FermetureService.php';

class FermetureServiceTest extends TestCase {

    // Vérifie que getFermeturesBySiteId() retourne les fermetures d'un site
    public function testGetFermeturesBySiteIdRetourneLesfermetures(): void {
        $mockRepo = $this->createStub(FermetureRepository::class);
        $mockRepo->method('findBySiteId')->willReturn([
            new Fermeture(1, 1, '2026-08-01', '2026-08-07', 'Travaux'),
        ]);

        $service = new FermetureService($mockRepo);
        $result  = $service->getFermeturesBySiteId(1);

        $this->assertCount(1, $result);
    }
}
