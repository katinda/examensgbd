<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../models/Administrateur.php';
require_once __DIR__ . '/../models/Site.php';
require_once __DIR__ . '/../repositories/AdministrateurRepository.php';
require_once __DIR__ . '/../repositories/SiteRepository.php';
require_once __DIR__ . '/../services/AdministrateurService.php';

class AdministrateurServiceTest extends TestCase {
    public function testCreateAdministrateurRetourneDoublonLogin(): void {
        $mockRepo = $this->createStub(AdministrateurRepository::class);
        $mockSite = $this->createStub(SiteRepository::class);
        $mockRepo->method('findByLogin')->willReturn(
            new Administrateur(1, 'admin.global', 'h', 'A', 'B', null, 'GLOBAL', null, true)
        );
        $service = new AdministrateurService($mockRepo, $mockSite);
        $result  = $service->createAdministrateur([
            'login'        => 'admin.global',
            'mot_de_passe' => 'secret',
            'type'         => 'GLOBAL',
        ]);
        $this->assertEquals('doublon_login', $result);
    }
}
