<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Site.php';
require_once __DIR__ . '/../models/Membre.php';
require_once __DIR__ . '/../repositories/SiteRepository.php';
require_once __DIR__ . '/../repositories/MembreRepository.php';
require_once __DIR__ . '/../services/MembreService.php';

class MembreServiceTest extends TestCase {

    private function creerMembre(int $id, string $matricule, string $categorie, ?int $siteId, bool $actif): Membre {
        return new Membre($id, $matricule, 'Nom', 'Prenom', null, null, $categorie, $siteId, $actif);
    }

    private function creerSite(int $id): Site {
        return new Site($id, "Site $id", null, null, null, true, '2024-01-01 00:00:00');
    }


    // Vérifie que getAllMembres() retourne uniquement les membres actifs
    public function testGetAllMembresRetourneSeulementLesActifs(): void {
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockMembre->method('findAll')->willReturn([
            $this->creerMembre(1, 'G0001', 'G', null, true),
            $this->creerMembre(2, 'S00001', 'S', 1,   false),
            $this->creerMembre(3, 'L00001', 'L', null, true),
        ]);
        $mockSite = $this->createStub(SiteRepository::class);

        $service = new MembreService($mockMembre, $mockSite);
        $this->assertCount(2, $service->getAllMembres());
    }


    // Vérifie que getMembresByCategorie() retourne uniquement les membres actifs de la catégorie
    public function testGetMembresByCategorieRetourneLesBonsActifs(): void {
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockMembre->method('findByCategorie')->willReturn([
            $this->creerMembre(1, 'G0001', 'G', null, true),
            $this->creerMembre(2, 'G0002', 'G', null, false),
        ]);
        $mockSite = $this->createStub(SiteRepository::class);

        $service = new MembreService($mockMembre, $mockSite);
        $this->assertCount(1, $service->getMembresByCategorie('G'));
    }


    // Vérifie que getMembreById() retourne null si le membre est inactif
    public function testGetMembreByIdRetourneNullSiInactif(): void {
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockMembre->method('findById')->willReturn($this->creerMembre(1, 'G0001', 'G', null, false));
        $mockSite = $this->createStub(SiteRepository::class);

        $service = new MembreService($mockMembre, $mockSite);
        $this->assertNull($service->getMembreById(1));
    }


    // Vérifie que getMembreById() retourne le membre s'il est actif
    public function testGetMembreByIdRetourneLeMembreSiActif(): void {
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockMembre->method('findById')->willReturn($this->creerMembre(1, 'G0001', 'G', null, true));
        $mockSite = $this->createStub(SiteRepository::class);

        $service = new MembreService($mockMembre, $mockSite);
        $this->assertNotNull($service->getMembreById(1));
    }


    // Vérifie que getMembreByMatricule() retourne null si le membre est inactif
    public function testGetMembreByMatriculeRetourneNullSiInactif(): void {
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockMembre->method('findByMatricule')->willReturn($this->creerMembre(1, 'G0001', 'G', null, false));
        $mockSite = $this->createStub(SiteRepository::class);

        $service = new MembreService($mockMembre, $mockSite);
        $this->assertNull($service->getMembreByMatricule('G0001'));
    }


    // Vérifie que getMembreByMatricule() retourne le membre s'il est actif
    public function testGetMembreByMatriculeRetourneLeMembreSiActif(): void {
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockMembre->method('findByMatricule')->willReturn($this->creerMembre(1, 'G0001', 'G', null, true));
        $mockSite = $this->createStub(SiteRepository::class);

        $service = new MembreService($mockMembre, $mockSite);
        $this->assertNotNull($service->getMembreByMatricule('G0001'));
    }


    // Vérifie que createMembre() retourne 'matricule_invalide' si le format est mauvais
    public function testCreateMembreRetourneMatriculeInvalide(): void {
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockSite   = $this->createStub(SiteRepository::class);

        $service = new MembreService($mockMembre, $mockSite);
        // matricule G alors que catégorie S → invalide
        $this->assertEquals('matricule_invalide', $service->createMembre([
            'matricule' => 'G0001', 'nom' => 'Test', 'prenom' => 'Test', 'categorie' => 'S', 'site_id' => 1
        ]));
    }


    // Vérifie que createMembre() retourne 'site_requis' pour catégorie S sans site_id
    public function testCreateMembreRetourneSiteRequis(): void {
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockSite   = $this->createStub(SiteRepository::class);

        $service = new MembreService($mockMembre, $mockSite);
        $this->assertEquals('site_requis', $service->createMembre([
            'matricule' => 'S00001', 'nom' => 'Test', 'prenom' => 'Test', 'categorie' => 'S'
        ]));
    }


    // Vérifie que createMembre() retourne 'site_interdit' pour catégorie G avec un site_id
    public function testCreateMembreRetourneSiteInterdit(): void {
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockSite   = $this->createStub(SiteRepository::class);

        $service = new MembreService($mockMembre, $mockSite);
        $this->assertEquals('site_interdit', $service->createMembre([
            'matricule' => 'G0001', 'nom' => 'Test', 'prenom' => 'Test', 'categorie' => 'G', 'site_id' => 1
        ]));
    }


    // Vérifie que createMembre() retourne 'site_introuvable' si le site n'existe pas
    public function testCreateMembreRetourneSiteIntrouvable(): void {
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockSite   = $this->createStub(SiteRepository::class);
        $mockSite->method('findById')->willReturn(null);

        $service = new MembreService($mockMembre, $mockSite);
        $this->assertEquals('site_introuvable', $service->createMembre([
            'matricule' => 'S00001', 'nom' => 'Test', 'prenom' => 'Test', 'categorie' => 'S', 'site_id' => 999
        ]));
    }


    // Vérifie que createMembre() retourne un ID si tout est valide
    public function testCreateMembreRetourneUnId(): void {
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockMembre->method('findByMatricule')->willReturn(null);
        $mockMembre->method('insert')->willReturn(5);
        $mockSite = $this->createStub(SiteRepository::class);
        $mockSite->method('findById')->willReturn($this->creerSite(1));

        $service = new MembreService($mockMembre, $mockSite);
        $this->assertEquals(5, $service->createMembre([
            'matricule' => 'S00001', 'nom' => 'Martin', 'prenom' => 'Alice', 'categorie' => 'S', 'site_id' => 1
        ]));
    }


    // Vérifie que updateMembre() retourne true si le membre existe
    public function testUpdateMembreRetourneTrueSiExiste(): void {
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockMembre->method('findById')->willReturn($this->creerMembre(1, 'G0001', 'G', null, true));
        $mockSite = $this->createStub(SiteRepository::class);

        $service = new MembreService($mockMembre, $mockSite);
        $this->assertTrue($service->updateMembre(1, ['nom' => 'Nouveau nom']));
    }


    // Vérifie que updateMembre() retourne false si le membre n'existe pas
    public function testUpdateMembreRetourneFalseSiInexistant(): void {
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockMembre->method('findById')->willReturn(null);
        $mockSite = $this->createStub(SiteRepository::class);

        $service = new MembreService($mockMembre, $mockSite);
        $this->assertFalse($service->updateMembre(999, ['nom' => 'X']));
    }


    // Vérifie que deleteMembre() retourne true et désactive le membre
    public function testDeleteMembreRetourneTrueSiExiste(): void {
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockMembre->method('findById')->willReturn($this->creerMembre(1, 'G0001', 'G', null, true));
        $mockSite = $this->createStub(SiteRepository::class);

        $service = new MembreService($mockMembre, $mockSite);
        $this->assertTrue($service->deleteMembre(1));
    }


    // Vérifie que deleteMembre() retourne false si le membre n'existe pas
    public function testDeleteMembreRetourneFalseSiInexistant(): void {
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockMembre->method('findById')->willReturn(null);
        $mockSite = $this->createStub(SiteRepository::class);

        $service = new MembreService($mockMembre, $mockSite);
        $this->assertFalse($service->deleteMembre(999));
    }
}
