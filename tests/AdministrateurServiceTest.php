<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Administrateur.php';
require_once __DIR__ . '/../models/Site.php';
require_once __DIR__ . '/../repositories/AdministrateurRepository.php';
require_once __DIR__ . '/../repositories/SiteRepository.php';
require_once __DIR__ . '/../services/AdministrateurService.php';

class AdministrateurServiceTest extends TestCase {

    private function creerAdmin(int $id, string $login, string $type, ?int $siteId = null, bool $actif = true): Administrateur {
        return new Administrateur($id, $login, 'hash', 'Nom', 'Prenom', null, $type, $siteId, $actif);
    }


    // Vérifie que getAllAdministrateurs() retourne uniquement les admins actifs
    public function testGetAllAdministrateursRetourneLesAdminsActifs(): void {
        $mockRepo  = $this->createStub(AdministrateurRepository::class);
        $mockSite  = $this->createStub(SiteRepository::class);
        $mockRepo->method('findAll')->willReturn([
            $this->creerAdmin(1, 'admin.global', 'GLOBAL', null, true),
            $this->creerAdmin(2, 'admin.inactif', 'GLOBAL', null, false),
        ]);

        $service = new AdministrateurService($mockRepo, $mockSite);
        $result  = $service->getAllAdministrateurs();

        $this->assertCount(1, $result, "getAllAdministrateurs() doit retourner uniquement les admins actifs");
    }


    // Vérifie que getAdministrateurById() retourne le bon admin
    public function testGetAdministrateurByIdRetourneLeBonAdmin(): void {
        $mockRepo = $this->createStub(AdministrateurRepository::class);
        $mockSite = $this->createStub(SiteRepository::class);
        $mockRepo->method('findById')->willReturn(
            $this->creerAdmin(1, 'admin.global', 'GLOBAL')
        );

        $service = new AdministrateurService($mockRepo, $mockSite);
        $result  = $service->getAdministrateurById(1);

        $this->assertNotNull($result);
        $this->assertEquals('admin.global', $result->getLogin());
    }


    // Vérifie que getAdministrateurById() retourne null si inexistant ou inactif
    public function testGetAdministrateurByIdRetourneNullSiInexistant(): void {
        $mockRepo = $this->createStub(AdministrateurRepository::class);
        $mockSite = $this->createStub(SiteRepository::class);
        $mockRepo->method('findById')->willReturn(null);

        $service = new AdministrateurService($mockRepo, $mockSite);
        $result  = $service->getAdministrateurById(999);

        $this->assertNull($result, "Un admin inexistant doit retourner null");
    }


    // Vérifie que getAdministrateurByLogin() retourne le bon admin
    public function testGetAdministrateurByLoginRetourneLeBonAdmin(): void {
        $mockRepo = $this->createStub(AdministrateurRepository::class);
        $mockSite = $this->createStub(SiteRepository::class);
        $mockRepo->method('findByLogin')->willReturn(
            $this->creerAdmin(1, 'admin.global', 'GLOBAL')
        );

        $service = new AdministrateurService($mockRepo, $mockSite);
        $result  = $service->getAdministrateurByLogin('admin.global');

        $this->assertNotNull($result);
        $this->assertEquals('GLOBAL', $result->getType());
    }


    // Vérifie que getAdministrateurByLogin() retourne null si inexistant
    public function testGetAdministrateurByLoginRetourneNullSiInexistant(): void {
        $mockRepo = $this->createStub(AdministrateurRepository::class);
        $mockSite = $this->createStub(SiteRepository::class);
        $mockRepo->method('findByLogin')->willReturn(null);

        $service = new AdministrateurService($mockRepo, $mockSite);
        $result  = $service->getAdministrateurByLogin('inconnu');

        $this->assertNull($result, "Un login inexistant doit retourner null");
    }


    // Vérifie que createAdministrateur() retourne un ID valide si tout est correct
    public function testCreateAdministrateurRetourneUnId(): void {
        $mockRepo = $this->createStub(AdministrateurRepository::class);
        $mockSite = $this->createStub(SiteRepository::class);
        $mockRepo->method('findByLogin')->willReturn(null);
        $mockRepo->method('insert')->willReturn(3);

        $service = new AdministrateurService($mockRepo, $mockSite);
        $result  = $service->createAdministrateur([
            'login'        => 'admin.new',
            'mot_de_passe' => 'secret',
            'type'         => 'GLOBAL',
        ]);

        $this->assertEquals(3, $result, "createAdministrateur() doit retourner l'ID créé");
    }


    // Vérifie que createAdministrateur() retourne 'type_invalide' si le type est inconnu
    public function testCreateAdministrateurRetourneTypeInvalide(): void {
        $mockRepo = $this->createStub(AdministrateurRepository::class);
        $mockSite = $this->createStub(SiteRepository::class);

        $service = new AdministrateurService($mockRepo, $mockSite);
        $result  = $service->createAdministrateur([
            'login'        => 'admin.test',
            'mot_de_passe' => 'secret',
            'type'         => 'SUPERADMIN',
        ]);

        $this->assertEquals('type_invalide', $result);
    }


    // Vérifie que createAdministrateur() retourne 'site_requis' pour type SITE sans site_id
    public function testCreateAdministrateurRetourneSiteRequis(): void {
        $mockRepo = $this->createStub(AdministrateurRepository::class);
        $mockSite = $this->createStub(SiteRepository::class);

        $service = new AdministrateurService($mockRepo, $mockSite);
        $result  = $service->createAdministrateur([
            'login'        => 'admin.site',
            'mot_de_passe' => 'secret',
            'type'         => 'SITE',
        ]);

        $this->assertEquals('site_requis', $result);
    }


    // Vérifie que createAdministrateur() retourne 'site_interdit' pour type GLOBAL avec site_id
    public function testCreateAdministrateurRetourneSiteInterdit(): void {
        $mockRepo = $this->createStub(AdministrateurRepository::class);
        $mockSite = $this->createStub(SiteRepository::class);

        $service = new AdministrateurService($mockRepo, $mockSite);
        $result  = $service->createAdministrateur([
            'login'        => 'admin.global2',
            'mot_de_passe' => 'secret',
            'type'         => 'GLOBAL',
            'site_id'      => 1,
        ]);

        $this->assertEquals('site_interdit', $result);
    }


    // Vérifie que createAdministrateur() retourne 'doublon_login' si le login existe déjà
    public function testCreateAdministrateurRetourneDoublonLogin(): void {
        $mockRepo = $this->createStub(AdministrateurRepository::class);
        $mockSite = $this->createStub(SiteRepository::class);
        $mockRepo->method('findByLogin')->willReturn(
            $this->creerAdmin(1, 'admin.global', 'GLOBAL')
        );

        $service = new AdministrateurService($mockRepo, $mockSite);
        $result  = $service->createAdministrateur([
            'login'        => 'admin.global',
            'mot_de_passe' => 'secret',
            'type'         => 'GLOBAL',
        ]);

        $this->assertEquals('doublon_login', $result);
    }


    // Vérifie que updateAdministrateur() retourne true quand l'admin existe
    public function testUpdateAdministrateurRetourneTrueSiExiste(): void {
        $mockRepo = $this->createStub(AdministrateurRepository::class);
        $mockSite = $this->createStub(SiteRepository::class);
        $mockRepo->method('findById')->willReturn(
            $this->creerAdmin(1, 'admin.global', 'GLOBAL')
        );

        $service = new AdministrateurService($mockRepo, $mockSite);
        $result  = $service->updateAdministrateur(1, ['email' => 'new@padel.fr']);

        $this->assertTrue($result, "updateAdministrateur() doit retourner true si l'admin existe");
    }


    // Vérifie que updateAdministrateur() retourne false quand l'admin n'existe pas
    public function testUpdateAdministrateurRetourneFalseSiInexistant(): void {
        $mockRepo = $this->createStub(AdministrateurRepository::class);
        $mockSite = $this->createStub(SiteRepository::class);
        $mockRepo->method('findById')->willReturn(null);

        $service = new AdministrateurService($mockRepo, $mockSite);
        $result  = $service->updateAdministrateur(999, ['email' => 'new@padel.fr']);

        $this->assertFalse($result, "updateAdministrateur() doit retourner false si l'admin n'existe pas");
    }


    // Vérifie que deleteAdministrateur() retourne true quand l'admin existe
    public function testDeleteAdministrateurRetourneTrueSiExiste(): void {
        $mockRepo = $this->createStub(AdministrateurRepository::class);
        $mockSite = $this->createStub(SiteRepository::class);
        $mockRepo->method('findById')->willReturn(
            $this->creerAdmin(1, 'admin.global', 'GLOBAL')
        );

        $service = new AdministrateurService($mockRepo, $mockSite);
        $result  = $service->deleteAdministrateur(1);

        $this->assertTrue($result, "deleteAdministrateur() doit retourner true si l'admin existe");
    }


    // Vérifie que deleteAdministrateur() retourne false quand l'admin n'existe pas
    public function testDeleteAdministrateurRetourneFalseSiInexistant(): void {
        $mockRepo = $this->createStub(AdministrateurRepository::class);
        $mockSite = $this->createStub(SiteRepository::class);
        $mockRepo->method('findById')->willReturn(null);

        $service = new AdministrateurService($mockRepo, $mockSite);
        $result  = $service->deleteAdministrateur(999);

        $this->assertFalse($result, "deleteAdministrateur() doit retourner false si l'admin n'existe pas");
    }
}
