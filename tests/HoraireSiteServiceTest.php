<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . "/../models/HoraireSite.php";
require_once __DIR__ . "/../repositories/HoraireSiteRepository.php";
require_once __DIR__ . "/../services/HoraireSiteService.php";
class HoraireSiteServiceTest extends TestCase {
    private function creerHoraire(int $id, int $siteId, int $annee, string $debut, string $fin): HoraireSite {
        return new HoraireSite($id, $siteId, $annee, $debut, $fin);
    }
    public function testCreateHoraireRetourneAnneeInvalide(): void { $mockRepo = $this->createStub(HoraireSiteRepository::class); $service = new HoraireSiteService($mockRepo); $result = $service->createHoraire(['site_id'=>1,'annee'=>1999,'heure_debut'=>'08:00:00','heure_fin'=>'22:00:00']); $this->assertEquals('annee_invalide', $result); }
}
