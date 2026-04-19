<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Site.php';
require_once __DIR__ . '/../repositories/SiteRepository.php';
require_once __DIR__ . '/../services/SiteService.php';

// On teste ici la logique métier du SiteService.
// On utilise un "stub" (faux repository) pour simuler la base de données.
// Comme ça on teste UNIQUEMENT la logique du service, pas le SQL.

class SiteServiceTest extends TestCase {

    // Crée un faux site pour les tests
    private function creerSite(int $id, string $nom, bool $actif): Site {
        $site = new Site();
        $site->setSiteId($id);
        $site->setNom($nom);
        $site->setAdresse(null);
        $site->setVille(null);
        $site->setCodePostal(null);
        $site->setEstActif($actif);
        $site->setDateCreation('2024-01-01 00:00:00');
        return $site;
    }


    // Vérifie que getAllSites() retourne uniquement les sites actifs
    public function testGetAllSitesRetourneSeulementLesSitesActifs(): void {
        $mockRepo = $this->createStub(SiteRepository::class);
        $mockRepo->method('findAll')->willReturn([
            $this->creerSite(1, 'Club Paris', true),
            $this->creerSite(2, 'Club Ferme', false),
            $this->creerSite(3, 'Club Lyon',  true),
        ]);

        $service = new SiteService($mockRepo);
        $result  = $service->getAllSites();

        $this->assertCount(2, $result, "getAllSites() doit retourner uniquement les sites actifs");
    }


    // Vérifie que getSiteById() retourne null pour un site inactif
    public function testGetSiteByIdRetourneNullSiSiteInactif(): void {
        $mockRepo = $this->createStub(SiteRepository::class);
        $mockRepo->method('findById')->willReturn(
            $this->creerSite(2, 'Club Ferme', false)
        );

        $service = new SiteService($mockRepo);
        $result  = $service->getSiteById(2);

        $this->assertNull($result, "Un site inactif doit retourner null");
    }


    // Vérifie que getSiteById() retourne bien le site s'il est actif
    public function testGetSiteByIdRetourneLeBosSiActif(): void {
        $mockRepo = $this->createStub(SiteRepository::class);
        $mockRepo->method('findById')->willReturn(
            $this->creerSite(1, 'Club Paris', true)
        );

        $service = new SiteService($mockRepo);
        $result  = $service->getSiteById(1);

        $this->assertNotNull($result, "Un site actif doit être retourné");
        $this->assertEquals('Club Paris', $result->getNom());
    }


    // Vérifie que getSiteById() retourne null si le site n'existe pas
    public function testGetSiteByIdRetourneNullSiInexistant(): void {
        $mockRepo = $this->createStub(SiteRepository::class);
        $mockRepo->method('findById')->willReturn(null);

        $service = new SiteService($mockRepo);
        $result  = $service->getSiteById(999);

        $this->assertNull($result, "Un site inexistant doit retourner null");
    }


    // Vérifie que createSite() appelle bien insert() et retourne un ID
    public function testCreateSiteRetourneUnId(): void {
        $mockRepo = $this->createStub(SiteRepository::class);
        $mockRepo->method('insert')->willReturn(5);

        $service = new SiteService($mockRepo);
        $result  = $service->createSite([
            'nom'         => 'Club Bordeaux',
            'adresse'     => '1 avenue du Vin',
            'ville'       => 'Bordeaux',
            'code_postal' => '33000',
        ]);

        $this->assertEquals(5, $result, "createSite() doit retourner l'ID du site créé");
    }


    // Vérifie que updateSite() retourne true quand le site existe
    public function testUpdateSiteRetourneTrueSiSiteExiste(): void {
        $mockRepo = $this->createStub(SiteRepository::class);
        $mockRepo->method('findById')->willReturn(
            $this->creerSite(1, 'Club Paris', true)
        );

        $service = new SiteService($mockRepo);
        $result  = $service->updateSite(1, ['nom' => 'Club Paris Modifie']);

        $this->assertTrue($result, "updateSite() doit retourner true si le site existe");
    }


    // Vérifie que updateSite() retourne false quand le site n'existe pas
    public function testUpdateSiteRetourneFalseSiSiteInexistant(): void {
        $mockRepo = $this->createStub(SiteRepository::class);
        $mockRepo->method('findById')->willReturn(null);

        $service = new SiteService($mockRepo);
        $result  = $service->updateSite(999, ['nom' => 'Club Inconnu']);

        $this->assertFalse($result, "updateSite() doit retourner false si le site n'existe pas");
    }


    // Vérifie que deleteSite() retourne true quand le site existe
    public function testDeleteSiteRetourneTrueSiSiteExiste(): void {
        $mockRepo = $this->createStub(SiteRepository::class);
        $mockRepo->method('findById')->willReturn(
            $this->creerSite(1, 'Club Paris', true)
        );

        $service = new SiteService($mockRepo);
        $result  = $service->deleteSite(1);

        $this->assertTrue($result, "deleteSite() doit retourner true si le site existe");
    }


    // Vérifie que deleteSite() retourne false quand le site n'existe pas
    public function testDeleteSiteRetourneFalseSiSiteInexistant(): void {
        $mockRepo = $this->createStub(SiteRepository::class);
        $mockRepo->method('findById')->willReturn(null);

        $service = new SiteService($mockRepo);
        $result  = $service->deleteSite(999);

        $this->assertFalse($result, "deleteSite() doit retourner false si le site n'existe pas");
    }
}
