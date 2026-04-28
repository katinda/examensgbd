<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Fermeture.php';
require_once __DIR__ . '/../repositories/FermetureRepository.php';
require_once __DIR__ . '/../services/FermetureService.php';

class FermetureServiceTest extends TestCase {

    // Vérifie que createFermeture() retourne un ID valide si tout est correct
    public function testCreateFermetureRetourneUnId(): void {
        $mockRepo = $this->createStub(FermetureRepository::class);
        $mockRepo->method('insert')->willReturn(3);

        $service = new FermetureService($mockRepo);
        $result  = $service->createFermeture([
            'site_id'    => 1,
            'date_debut' => '2026-08-01',
            'date_fin'   => '2026-08-07',
            'raison'     => 'Travaux',
        ]);

        $this->assertEquals(3, $result, "createFermeture() doit retourner l'ID créé");
    }
}
