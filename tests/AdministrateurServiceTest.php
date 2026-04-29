<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../models/Administrateur.php';
require_once __DIR__ . '/../models/Site.php';
require_once __DIR__ . '/../repositories/AdministrateurRepository.php';
require_once __DIR__ . '/../repositories/SiteRepository.php';
require_once __DIR__ . '/../services/AdministrateurService.php';

class AdministrateurServiceTest extends TestCase {
    public function testDeleteAdministrateurRetourneFalseSiInexistant(): void {
        $mockRepo = $this->createStub(AdministrateurRepository::class);
        $mockSite = $this->createStub(SiteRepository::class);
        $mockRepo->method('findById')->willReturn(null);
        $service = new AdministrateurService($mockRepo, $mockSite);
        $result  = $service->deleteAdministrateur(999);
        $this->assertFalse($result, "deleteAdministrateur() doit retourner false si l'admin n'existe pas");
    }
}
