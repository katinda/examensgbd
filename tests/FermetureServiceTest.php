<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Fermeture.php';
require_once __DIR__ . '/../repositories/FermetureRepository.php';
require_once __DIR__ . '/../services/FermetureService.php';

class FermetureServiceTest extends TestCase {

    // Vérifie que getFermetureById() retourne null si la fermeture n'existe pas
    public function testGetFermetureByIdRetourneNullSiInexistant(): void {
        $mockRepo = $this->createStub(FermetureRepository::class);
        $mockRepo->method('findById')->willReturn(null);

        $service = new FermetureService($mockRepo);
        $result  = $service->getFermetureById(999);

        $this->assertNull($result, "Une fermeture inexistante doit retourner null");
    }
}
