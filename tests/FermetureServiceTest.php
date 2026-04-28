<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Fermeture.php';
require_once __DIR__ . '/../repositories/FermetureRepository.php';
require_once __DIR__ . '/../services/FermetureService.php';

class FermetureServiceTest extends TestCase {

    // Vérifie que createFermeture() retourne 'dates_invalides' si date_debut > date_fin
    public function testCreateFermetureRetourneDatesInvalides(): void {
        $mockRepo = $this->createStub(FermetureRepository::class);

        $service = new FermetureService($mockRepo);
        $result  = $service->createFermeture([
            'date_debut' => '2026-08-10',
            'date_fin'   => '2026-08-01',
            'raison'     => 'Erreur',
        ]);

        $this->assertEquals('dates_invalides', $result);
    }
}
