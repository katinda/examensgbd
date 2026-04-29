<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Penalite.php';
require_once __DIR__ . '/../models/Membre.php';
require_once __DIR__ . '/../models/Administrateur.php';
require_once __DIR__ . '/../repositories/PenaliteRepository.php';
require_once __DIR__ . '/../repositories/MembreRepository.php';
require_once __DIR__ . '/../repositories/AdministrateurRepository.php';
require_once __DIR__ . '/../services/PenaliteService.php';

class PenaliteServiceTest extends TestCase {

    private function creerPenalite(int $id, int $membreId, string $cause, bool $levee = false): Penalite {
        return new Penalite($id, $membreId, null, '2026-05-01', '2026-05-15', $cause, $levee);
    }

    private function creerMembre(int $id): Membre {
        return new Membre($id, 'G0001', 'Dupont', 'Jean', null, null, 'G', null, true);
    }

    private function creerAdmin(int $id, string $type): Administrateur {
        return new Administrateur($id, 'admin', 'hash', 'A', 'B', null, $type, null, true);
    }


    // Vérifie que getAllPenalites() retourne toutes les pénalités
    public function testGetAllPenalitesRetourneToutesLesPenalites(): void {
        $mockRepo   = $this->createStub(PenaliteRepository::class);
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockAdmin  = $this->createStub(AdministrateurRepository::class);
        $mockRepo->method('findAll')->willReturn([
            $this->creerPenalite(1, 1, 'PRIVATE_INCOMPLETE'),
            $this->creerPenalite(2, 2, 'OTHER'),
        ]);

        $service = new PenaliteService($mockRepo, $mockMembre, $mockAdmin);
        $result  = $service->getAllPenalites();

        $this->assertCount(2, $result);
    }


    // Vérifie que getPenaliteById() retourne la bonne pénalité
    public function testGetPenaliteByIdRetourneLaBonnePenalite(): void {
        $mockRepo   = $this->createStub(PenaliteRepository::class);
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockAdmin  = $this->createStub(AdministrateurRepository::class);
        $mockRepo->method('findById')->willReturn($this->creerPenalite(1, 1, 'OTHER'));

        $service = new PenaliteService($mockRepo, $mockMembre, $mockAdmin);
        $result  = $service->getPenaliteById(1);

        $this->assertNotNull($result);
        $this->assertEquals('OTHER', $result->getCause());
    }


    // Vérifie que getPenaliteById() retourne null si inexistant
    public function testGetPenaliteByIdRetourneNullSiInexistant(): void {
        $mockRepo   = $this->createStub(PenaliteRepository::class);
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockAdmin  = $this->createStub(AdministrateurRepository::class);
        $mockRepo->method('findById')->willReturn(null);

        $service = new PenaliteService($mockRepo, $mockMembre, $mockAdmin);
        $this->assertNull($service->getPenaliteById(999));
    }


    // Vérifie que getPenalitesByMembreId() retourne les pénalités du membre
    public function testGetPenalitesByMembreIdRetourneLespenalites(): void {
        $mockRepo   = $this->createStub(PenaliteRepository::class);
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockAdmin  = $this->createStub(AdministrateurRepository::class);
        $mockRepo->method('findByMembreId')->willReturn([
            $this->creerPenalite(1, 1, 'PRIVATE_INCOMPLETE'),
        ]);

        $service = new PenaliteService($mockRepo, $mockMembre, $mockAdmin);
        $this->assertCount(1, $service->getPenalitesByMembreId(1));
    }


    // Vérifie que getPenalitesActives() retourne les pénalités non levées
    public function testGetPenalitesActivesRetourneLespenalitesActives(): void {
        $mockRepo   = $this->createStub(PenaliteRepository::class);
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockAdmin  = $this->createStub(AdministrateurRepository::class);
        $mockRepo->method('findActives')->willReturn([
            $this->creerPenalite(1, 1, 'OTHER', false),
        ]);

        $service = new PenaliteService($mockRepo, $mockMembre, $mockAdmin);
        $result  = $service->getPenalitesActives();

        $this->assertCount(1, $result);
        $this->assertFalse($result[0]->isLevee());
    }


    // Vérifie que createPenalite() retourne un ID valide si tout est correct
    public function testCreatePenaliteRetourneUnId(): void {
        $mockRepo   = $this->createStub(PenaliteRepository::class);
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockAdmin  = $this->createStub(AdministrateurRepository::class);
        $mockMembre->method('findById')->willReturn($this->creerMembre(1));
        $mockRepo->method('insert')->willReturn(3);

        $service = new PenaliteService($mockRepo, $mockMembre, $mockAdmin);
        $result  = $service->createPenalite([
            'membre_id'  => 1,
            'date_debut' => '2026-05-01',
            'date_fin'   => '2026-05-15',
            'cause'      => 'PRIVATE_INCOMPLETE',
        ]);

        $this->assertEquals(3, $result);
    }


    // Vérifie que createPenalite() retourne 'cause_invalide' si la cause est inconnue
    public function testCreatePenaliteRetourneCauseInvalide(): void {
        $mockRepo   = $this->createStub(PenaliteRepository::class);
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockAdmin  = $this->createStub(AdministrateurRepository::class);

        $service = new PenaliteService($mockRepo, $mockMembre, $mockAdmin);
        $result  = $service->createPenalite([
            'membre_id'  => 1,
            'date_debut' => '2026-05-01',
            'date_fin'   => '2026-05-15',
            'cause'      => 'MAUVAISE_CAUSE',
        ]);

        $this->assertEquals('cause_invalide', $result);
    }


    // Vérifie que createPenalite() retourne 'dates_invalides' si date_debut > date_fin
    public function testCreatePenaliteRetourneDatesInvalides(): void {
        $mockRepo   = $this->createStub(PenaliteRepository::class);
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockAdmin  = $this->createStub(AdministrateurRepository::class);

        $service = new PenaliteService($mockRepo, $mockMembre, $mockAdmin);
        $result  = $service->createPenalite([
            'membre_id'  => 1,
            'date_debut' => '2026-05-15',
            'date_fin'   => '2026-05-01',
            'cause'      => 'OTHER',
        ]);

        $this->assertEquals('dates_invalides', $result);
    }


    // Vérifie que createPenalite() retourne 'membre_introuvable' si le membre n'existe pas
    public function testCreatePenaliteRetourneMembreIntrouvable(): void {
        $mockRepo   = $this->createStub(PenaliteRepository::class);
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockAdmin  = $this->createStub(AdministrateurRepository::class);
        $mockMembre->method('findById')->willReturn(null);

        $service = new PenaliteService($mockRepo, $mockMembre, $mockAdmin);
        $result  = $service->createPenalite([
            'membre_id'  => 999,
            'date_debut' => '2026-05-01',
            'date_fin'   => '2026-05-15',
            'cause'      => 'OTHER',
        ]);

        $this->assertEquals('membre_introuvable', $result);
    }


    // Vérifie que leverPenalite() retourne true en cas de succès
    public function testLeverPenaliteRetourneTrueSiSucces(): void {
        $mockRepo   = $this->createStub(PenaliteRepository::class);
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockAdmin  = $this->createStub(AdministrateurRepository::class);
        $mockRepo->method('findById')->willReturn($this->creerPenalite(1, 1, 'OTHER', false));
        $mockAdmin->method('findById')->willReturn($this->creerAdmin(1, 'GLOBAL'));

        $service = new PenaliteService($mockRepo, $mockMembre, $mockAdmin);
        $result  = $service->leverPenalite(1, ['admin_id' => 1, 'raison' => 'Erreur constatée']);

        $this->assertTrue($result);
    }


    // Vérifie que leverPenalite() retourne 'penalite_introuvable' si la pénalité n'existe pas
    public function testLeverPenaliteRetournePenaliteIntrouvable(): void {
        $mockRepo   = $this->createStub(PenaliteRepository::class);
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockAdmin  = $this->createStub(AdministrateurRepository::class);
        $mockRepo->method('findById')->willReturn(null);

        $service = new PenaliteService($mockRepo, $mockMembre, $mockAdmin);
        $this->assertEquals('penalite_introuvable', $service->leverPenalite(999, ['admin_id' => 1, 'raison' => 'test']));
    }


    // Vérifie que leverPenalite() retourne 'deja_levee' si la pénalité est déjà levée
    public function testLeverPenaliteRetourneDejaLevee(): void {
        $mockRepo   = $this->createStub(PenaliteRepository::class);
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockAdmin  = $this->createStub(AdministrateurRepository::class);
        $mockRepo->method('findById')->willReturn($this->creerPenalite(1, 1, 'OTHER', true));

        $service = new PenaliteService($mockRepo, $mockMembre, $mockAdmin);
        $this->assertEquals('deja_levee', $service->leverPenalite(1, ['admin_id' => 1, 'raison' => 'test']));
    }


    // Vérifie que leverPenalite() retourne 'admin_introuvable' si l'admin n'existe pas
    public function testLeverPenaliteRetourneAdminIntrouvable(): void {
        $mockRepo   = $this->createStub(PenaliteRepository::class);
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockAdmin  = $this->createStub(AdministrateurRepository::class);
        $mockRepo->method('findById')->willReturn($this->creerPenalite(1, 1, 'OTHER', false));
        $mockAdmin->method('findById')->willReturn(null);

        $service = new PenaliteService($mockRepo, $mockMembre, $mockAdmin);
        $this->assertEquals('admin_introuvable', $service->leverPenalite(1, ['admin_id' => 999, 'raison' => 'test']));
    }


    // Vérifie que leverPenalite() retourne 'admin_non_global' si l'admin n'est pas GLOBAL
    public function testLeverPenaliteRetourneAdminNonGlobal(): void {
        $mockRepo   = $this->createStub(PenaliteRepository::class);
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockAdmin  = $this->createStub(AdministrateurRepository::class);
        $mockRepo->method('findById')->willReturn($this->creerPenalite(1, 1, 'OTHER', false));
        $mockAdmin->method('findById')->willReturn($this->creerAdmin(2, 'SITE'));

        $service = new PenaliteService($mockRepo, $mockMembre, $mockAdmin);
        $this->assertEquals('admin_non_global', $service->leverPenalite(1, ['admin_id' => 2, 'raison' => 'test']));
    }


    // Vérifie que deletePenalite() retourne true quand la pénalité existe
    public function testDeletePenaliteRetourneTrueSiExiste(): void {
        $mockRepo   = $this->createStub(PenaliteRepository::class);
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockAdmin  = $this->createStub(AdministrateurRepository::class);
        $mockRepo->method('findById')->willReturn($this->creerPenalite(1, 1, 'OTHER'));

        $service = new PenaliteService($mockRepo, $mockMembre, $mockAdmin);
        $this->assertTrue($service->deletePenalite(1));
    }


    // Vérifie que deletePenalite() retourne false quand la pénalité n'existe pas
    public function testDeletePenaliteRetourneFalseSiInexistant(): void {
        $mockRepo   = $this->createStub(PenaliteRepository::class);
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockAdmin  = $this->createStub(AdministrateurRepository::class);
        $mockRepo->method('findById')->willReturn(null);

        $service = new PenaliteService($mockRepo, $mockMembre, $mockAdmin);
        $this->assertFalse($service->deletePenalite(999));
    }
}
