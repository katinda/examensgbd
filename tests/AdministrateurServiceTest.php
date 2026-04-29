<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../models/Administrateur.php';
require_once __DIR__ . '/../models/Site.php';
require_once __DIR__ . '/../repositories/AdministrateurRepository.php';
require_once __DIR__ . '/../repositories/SiteRepository.php';
require_once __DIR__ . '/../services/AdministrateurService.php';

class AdministrateurServiceTest extends TestCase {
    public function testGetAllAdministrateursRetourneLesAdminsActifs(): void {
        $mockRepo  = $this->createStub(AdministrateurRepository::class);
        $mockSite  = $this->createStub(SiteRepository::class);
        $mockRepo->method('findAll')->willReturn([
            new Administrateur(1, 'admin.actif',   'h', 'A', 'B', null, 'GLOBAL', null, true),
            new Administrateur(2, 'admin.inactif', 'h', 'C', 'D', null, 'GLOBAL', null, false),
        ]);
        $service = new AdministrateurService($mockRepo, $mockSite);
        $result  = $service->getAllAdministrateurs();
        $this->assertCount(1, $result, "Doit retourner uniquement les admins actifs");
    }
}
