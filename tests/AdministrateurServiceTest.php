<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../models/Administrateur.php';
require_once __DIR__ . '/../models/Site.php';
require_once __DIR__ . '/../repositories/AdministrateurRepository.php';
require_once __DIR__ . '/../repositories/SiteRepository.php';
require_once __DIR__ . '/../services/AdministrateurService.php';

class AdministrateurServiceTest extends TestCase {
    public function testCreateAdministrateurRetourneSiteInterdit(): void {
        $mockRepo = $this->createStub(AdministrateurRepository::class);
        $mockSite = $this->createStub(SiteRepository::class);
        $service = new AdministrateurService($mockRepo, $mockSite);
        $result  = $service->createAdministrateur([
            'login'        => 'admin.global2',
            'mot_de_passe' => 'secret',
            'type'         => 'GLOBAL',
            'site_id'      => 1,
        ]);
        $this->assertEquals('site_interdit', $result);
    }
}
