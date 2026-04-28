<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . "/../models/HoraireSite.php";
require_once __DIR__ . "/../repositories/HoraireSiteRepository.php";
require_once __DIR__ . "/../services/HoraireSiteService.php";
class HoraireSiteServiceTest extends TestCase {
    private function creerHoraire(int $id, int $siteId, int $annee, string $debut, string $fin): HoraireSite {
        return new HoraireSite($id, $siteId, $annee, $debut, $fin);
    }
    public function testDeleteHoraireRetourneFalseSiInexistant(): void { $mockRepo = $this->createStub(HoraireSiteRepository::class); $mockRepo->method('findById')->willReturn(null); $service = new HoraireSiteService($mockRepo); $this->assertFalse($service->deleteHoraire(999)); }
}
