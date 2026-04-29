<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../models/Administrateur.php';
require_once __DIR__ . '/../models/Site.php';
require_once __DIR__ . '/../repositories/AdministrateurRepository.php';
require_once __DIR__ . '/../repositories/SiteRepository.php';
require_once __DIR__ . '/../services/AdministrateurService.php';

class AdministrateurServiceTest extends TestCase {
    public function testUpdateAdministrateurRetourneTrueSiExiste(): void {
        $mockRepo = $this->createStub(AdministrateurRepository::class);
        $mockSite = $this->createStub(SiteRepository::class);
        $mockRepo->method('findById')->willReturn(
            new Administrateur(1, 'admin.global', 'h', 'A', 'B', null, 'GLOBAL', null, true)
        );
        $service = new AdministrateurService($mockRepo, $mockSite);
        $result  = $service->updateAdministrateur(1, ['email' => 'new@padel.fr']);
        $this->assertTrue($result, "updateAdministrateur() doit retourner true si l'admin existe");
    }
}
