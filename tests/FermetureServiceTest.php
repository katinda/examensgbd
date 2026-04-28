<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Fermeture.php';
require_once __DIR__ . '/../repositories/FermetureRepository.php';
require_once __DIR__ . '/../services/FermetureService.php';

class FermetureServiceTest extends TestCase {

    // Vérifie que updateFermeture() retourne false quand la fermeture n'existe pas
    public function testUpdateFermetureRetourneFalseSiInexistant(): void {
        $mockRepo = $this->createStub(FermetureRepository::class);
        $mockRepo->method('findById')->willReturn(null);

        $service = new FermetureService($mockRepo);
        $result  = $service->updateFermeture(999, ['date_fin' => '2026-08-14']);

        $this->assertFalse($result, "updateFermeture() doit retourner false si la fermeture n'existe pas");
    }
}
