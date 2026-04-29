<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../models/Administrateur.php';
require_once __DIR__ . '/../models/Site.php';
require_once __DIR__ . '/../repositories/AdministrateurRepository.php';
require_once __DIR__ . '/../repositories/SiteRepository.php';
require_once __DIR__ . '/../services/AdministrateurService.php';

class AdministrateurServiceTest extends TestCase {
    public function testCreateAdministrateurRetourneUnId(): void {
        $mockRepo = $this->createStub(AdministrateurRepository::class);
        $mockSite = $this->createStub(SiteRepository::class);
        $mockRepo->method('findByLogin')->willReturn(null);
        $mockRepo->method('insert')->willReturn(3);
        $service = new AdministrateurService($mockRepo, $mockSite);
        $result  = $service->createAdministrateur([
            'login'        => 'admin.new',
            'mot_de_passe' => 'secret',
            'type'         => 'GLOBAL',
        ]);
        $this->assertEquals(3, $result, "createAdministrateur() doit retourner l'ID créé");
    }
}
