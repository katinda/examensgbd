<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../models/Administrateur.php';
require_once __DIR__ . '/../models/Site.php';
require_once __DIR__ . '/../repositories/AdministrateurRepository.php';
require_once __DIR__ . '/../repositories/SiteRepository.php';
require_once __DIR__ . '/../services/AdministrateurService.php';

class AdministrateurServiceTest extends TestCase {
    public function testGetAdministrateurByIdRetourneNullSiInexistant(): void {
        $mockRepo = $this->createStub(AdministrateurRepository::class);
        $mockSite = $this->createStub(SiteRepository::class);
        $mockRepo->method('findById')->willReturn(null);
        $service = new AdministrateurService($mockRepo, $mockSite);
        $this->assertNull($service->getAdministrateurById(999));
    }
}
