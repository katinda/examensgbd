<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../models/Administrateur.php';
require_once __DIR__ . '/../models/Site.php';
require_once __DIR__ . '/../repositories/AdministrateurRepository.php';
require_once __DIR__ . '/../repositories/SiteRepository.php';
require_once __DIR__ . '/../services/AdministrateurService.php';

class AdministrateurServiceTest extends TestCase {
    public function testCreateAdministrateurRetourneTypeInvalide(): void {
        $mockRepo = $this->createStub(AdministrateurRepository::class);
        $mockSite = $this->createStub(SiteRepository::class);
        $service = new AdministrateurService($mockRepo, $mockSite);
        $result  = $service->createAdministrateur([
            'login'        => 'admin.test',
            'mot_de_passe' => 'secret',
            'type'         => 'SUPERADMIN',
        ]);
        $this->assertEquals('type_invalide', $result);
    }
}
