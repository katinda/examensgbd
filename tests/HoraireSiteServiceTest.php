<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . "/../models/HoraireSite.php";
require_once __DIR__ . "/../repositories/HoraireSiteRepository.php";
require_once __DIR__ . "/../services/HoraireSiteService.php";
class HoraireSiteServiceTest extends TestCase {
    private function creerHoraire(int $id, int $siteId, int $annee, string $debut, string $fin): HoraireSite {
        return new HoraireSite($id, $siteId, $annee, $debut, $fin);
    }
    public function testGetAllHorairesRetourneTousLesHoraires(): void { $mockRepo = $this->createStub(HoraireSiteRepository::class); $mockRepo->method('findAll')->willReturn([$this->creerHoraire(1,1,2026,'08:00:00','22:00:00'),$this->creerHoraire(2,2,2026,'09:00:00','21:00:00')]); $service = new HoraireSiteService($mockRepo); $this->assertCount(2, $service->getAllHoraires()); }
}
