<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Fermeture.php';
require_once __DIR__ . '/../repositories/FermetureRepository.php';
require_once __DIR__ . '/../services/FermetureService.php';

class FermetureServiceTest extends TestCase {

    // Vérifie que getFermeturesGlobales() retourne les fermetures globales
    public function testGetFermeturesGlobalesRetourneLesfermeturesGlobales(): void {
        $mockRepo = $this->createStub(FermetureRepository::class);
        $mockRepo->method('findGlobales')->willReturn([
            new Fermeture(2, null, '2026-12-25', '2026-12-25', 'Noël'),
        ]);

        $service = new FermetureService($mockRepo);
        $result  = $service->getFermeturesGlobales();

        $this->assertCount(1, $result);
        $this->assertNull($result[0]->getSiteId(), "Une fermeture globale a Site_ID null");
    }
}
