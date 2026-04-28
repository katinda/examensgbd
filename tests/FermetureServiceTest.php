<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Fermeture.php';
require_once __DIR__ . '/../repositories/FermetureRepository.php';
require_once __DIR__ . '/../services/FermetureService.php';

class FermetureServiceTest extends TestCase {

    // Vérifie que deleteFermeture() retourne false quand la fermeture n'existe pas
    public function testDeleteFermetureRetourneFalseSiInexistant(): void {
        $mockRepo = $this->createStub(FermetureRepository::class);
        $mockRepo->method('findById')->willReturn(null);

        $service = new FermetureService($mockRepo);
        $result  = $service->deleteFermeture(999);

        $this->assertFalse($result, "deleteFermeture() doit retourner false si la fermeture n'existe pas");
    }
}
