<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/HoraireSite.php';
require_once __DIR__ . '/../repositories/HoraireSiteRepository.php';
require_once __DIR__ . '/../services/HoraireSiteService.php';

class HoraireSiteServiceTest extends TestCase {

    private function creerHoraire(int $id, int $siteId, int $annee, string $debut, string $fin): HoraireSite {
        return new HoraireSite($id, $siteId, $annee, $debut, $fin);
    }


    // Vérifie que getAllHoraires() retourne bien tous les horaires
    public function testGetAllHorairesRetourneTousLesHoraires(): void {
        $mockRepo = $this->createStub(HoraireSiteRepository::class);
        $mockRepo->method('findAll')->willReturn([
            $this->creerHoraire(1, 1, 2026, '08:00:00', '22:00:00'),
            $this->creerHoraire(2, 2, 2026, '09:00:00', '21:00:00'),
        ]);

        $service = new HoraireSiteService($mockRepo);
        $result  = $service->getAllHoraires();

        $this->assertCount(2, $result, "getAllHoraires() doit retourner 2 horaires");
    }


    // Vérifie que getHoraireById() retourne le bon horaire
    public function testGetHoraireByIdRetourneLeBonHoraire(): void {
        $mockRepo = $this->createStub(HoraireSiteRepository::class);
        $mockRepo->method('findById')->willReturn(
            $this->creerHoraire(1, 1, 2026, '08:00:00', '22:00:00')
        );

        $service = new HoraireSiteService($mockRepo);
        $result  = $service->getHoraireById(1);

        $this->assertNotNull($result);
        $this->assertEquals('08:00:00', $result->getHeureDebut());
    }


    // Vérifie que getHoraireById() retourne null si l'horaire n'existe pas
    public function testGetHoraireByIdRetourneNullSiInexistant(): void {
        $mockRepo = $this->createStub(HoraireSiteRepository::class);
        $mockRepo->method('findById')->willReturn(null);

        $service = new HoraireSiteService($mockRepo);
        $result  = $service->getHoraireById(999);

        $this->assertNull($result, "Un horaire inexistant doit retourner null");
    }


    // Vérifie que getHorairesBySiteId() retourne les horaires d'un site
    public function testGetHorairesBySiteIdRetourneLesHoraires(): void {
        $mockRepo = $this->createStub(HoraireSiteRepository::class);
        $mockRepo->method('findBySiteId')->willReturn([
            $this->creerHoraire(1, 1, 2026, '08:00:00', '22:00:00'),
        ]);

        $service = new HoraireSiteService($mockRepo);
        $result  = $service->getHorairesBySiteId(1);

        $this->assertCount(1, $result);
    }


    // Vérifie que getHoraireBySiteAndAnnee() retourne le bon horaire
    public function testGetHoraireBySiteAndAnneeRetourneLeBonHoraire(): void {
        $mockRepo = $this->createStub(HoraireSiteRepository::class);
        $mockRepo->method('findBySiteAndAnnee')->willReturn(
            $this->creerHoraire(1, 1, 2026, '08:00:00', '22:00:00')
        );

        $service = new HoraireSiteService($mockRepo);
        $result  = $service->getHoraireBySiteAndAnnee(1, 2026);

        $this->assertNotNull($result);
        $this->assertEquals(2026, $result->getAnnee());
    }


    // Vérifie que createHoraire() retourne un ID valide si tout est correct
    public function testCreateHoraireRetourneUnId(): void {
        $mockRepo = $this->createStub(HoraireSiteRepository::class);
        $mockRepo->method('findBySiteAndAnnee')->willReturn(null);
        $mockRepo->method('insert')->willReturn(3);

        $service = new HoraireSiteService($mockRepo);
        $result  = $service->createHoraire([
            'site_id'     => 1,
            'annee'       => 2026,
            'heure_debut' => '08:00:00',
            'heure_fin'   => '22:00:00',
        ]);

        $this->assertEquals(3, $result, "createHoraire() doit retourner l'ID créé");
    }


    // Vérifie que createHoraire() retourne 'annee_invalide' si l'année est hors plage
    public function testCreateHoraireRetourneAnneeInvalide(): void {
        $mockRepo = $this->createStub(HoraireSiteRepository::class);

        $service = new HoraireSiteService($mockRepo);
        $result  = $service->createHoraire([
            'site_id'     => 1,
            'annee'       => 1999,
            'heure_debut' => '08:00:00',
            'heure_fin'   => '22:00:00',
        ]);

        $this->assertEquals('annee_invalide', $result);
    }


    // Vérifie que createHoraire() retourne 'heures_invalides' si heure_debut >= heure_fin
    public function testCreateHoraireRetourneHeuresInvalides(): void {
        $mockRepo = $this->createStub(HoraireSiteRepository::class);

        $service = new HoraireSiteService($mockRepo);
        $result  = $service->createHoraire([
            'site_id'     => 1,
            'annee'       => 2026,
            'heure_debut' => '22:00:00',
            'heure_fin'   => '08:00:00',
        ]);

        $this->assertEquals('heures_invalides', $result);
    }


    // Vérifie que createHoraire() retourne 'doublon' si un horaire existe déjà
    public function testCreateHoraireRetourneDoublon(): void {
        $mockRepo = $this->createStub(HoraireSiteRepository::class);
        $mockRepo->method('findBySiteAndAnnee')->willReturn(
            $this->creerHoraire(1, 1, 2026, '08:00:00', '22:00:00')
        );

        $service = new HoraireSiteService($mockRepo);
        $result  = $service->createHoraire([
            'site_id'     => 1,
            'annee'       => 2026,
            'heure_debut' => '09:00:00',
            'heure_fin'   => '21:00:00',
        ]);

        $this->assertEquals('doublon', $result);
    }


    // Vérifie que updateHoraire() retourne true quand l'horaire existe
    public function testUpdateHoraireRetourneTrueSiExiste(): void {
        $mockRepo = $this->createStub(HoraireSiteRepository::class);
        $mockRepo->method('findById')->willReturn(
            $this->creerHoraire(1, 1, 2026, '08:00:00', '22:00:00')
        );

        $service = new HoraireSiteService($mockRepo);
        $result  = $service->updateHoraire(1, ['heure_debut' => '09:00:00']);

        $this->assertTrue($result, "updateHoraire() doit retourner true si l'horaire existe");
    }


    // Vérifie que updateHoraire() retourne false quand l'horaire n'existe pas
    public function testUpdateHoraireRetourneFalseSiInexistant(): void {
        $mockRepo = $this->createStub(HoraireSiteRepository::class);
        $mockRepo->method('findById')->willReturn(null);

        $service = new HoraireSiteService($mockRepo);
        $result  = $service->updateHoraire(999, ['heure_debut' => '09:00:00']);

        $this->assertFalse($result, "updateHoraire() doit retourner false si l'horaire n'existe pas");
    }


    // Vérifie que deleteHoraire() retourne true quand l'horaire existe
    public function testDeleteHoraireRetourneTrueSiExiste(): void {
        $mockRepo = $this->createStub(HoraireSiteRepository::class);
        $mockRepo->method('findById')->willReturn(
            $this->creerHoraire(1, 1, 2026, '08:00:00', '22:00:00')
        );

        $service = new HoraireSiteService($mockRepo);
        $result  = $service->deleteHoraire(1);

        $this->assertTrue($result, "deleteHoraire() doit retourner true si l'horaire existe");
    }


    // Vérifie que deleteHoraire() retourne false quand l'horaire n'existe pas
    public function testDeleteHoraireRetourneFalseSiInexistant(): void {
        $mockRepo = $this->createStub(HoraireSiteRepository::class);
        $mockRepo->method('findById')->willReturn(null);

        $service = new HoraireSiteService($mockRepo);
        $result  = $service->deleteHoraire(999);

        $this->assertFalse($result, "deleteHoraire() doit retourner false si l'horaire n'existe pas");
    }
}
