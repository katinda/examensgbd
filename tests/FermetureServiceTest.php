<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Fermeture.php';
require_once __DIR__ . '/../repositories/FermetureRepository.php';
require_once __DIR__ . '/../services/FermetureService.php';

class FermetureServiceTest extends TestCase {

    private function creerFermeture(int $id, ?int $siteId, string $debut, string $fin, ?string $raison = null): Fermeture {
        return new Fermeture($id, $siteId, $debut, $fin, $raison);
    }


    // Vérifie que getAllFermetures() retourne bien toutes les fermetures
    public function testGetAllFermeturesRetourneToutesLesFermetures(): void {
        $mockRepo = $this->createStub(FermetureRepository::class);
        $mockRepo->method('findAll')->willReturn([
            $this->creerFermeture(1, 1,    '2026-08-01', '2026-08-07', 'Travaux'),
            $this->creerFermeture(2, null, '2026-12-25', '2026-12-25', 'Noël'),
        ]);

        $service = new FermetureService($mockRepo);
        $result  = $service->getAllFermetures();

        $this->assertCount(2, $result, "getAllFermetures() doit retourner 2 fermetures");
    }


    // Vérifie que getFermetureById() retourne la bonne fermeture
    public function testGetFermetureByIdRetourneLaBonneFermeture(): void {
        $mockRepo = $this->createStub(FermetureRepository::class);
        $mockRepo->method('findById')->willReturn(
            $this->creerFermeture(1, 1, '2026-08-01', '2026-08-07', 'Travaux')
        );

        $service = new FermetureService($mockRepo);
        $result  = $service->getFermetureById(1);

        $this->assertNotNull($result);
        $this->assertEquals('2026-08-01', $result->getDateDebut());
    }


    // Vérifie que getFermetureById() retourne null si la fermeture n'existe pas
    public function testGetFermetureByIdRetourneNullSiInexistant(): void {
        $mockRepo = $this->createStub(FermetureRepository::class);
        $mockRepo->method('findById')->willReturn(null);

        $service = new FermetureService($mockRepo);
        $result  = $service->getFermetureById(999);

        $this->assertNull($result, "Une fermeture inexistante doit retourner null");
    }


    // Vérifie que getFermeturesBySiteId() retourne les fermetures d'un site
    public function testGetFermeturesBySiteIdRetourneLesfermetures(): void {
        $mockRepo = $this->createStub(FermetureRepository::class);
        $mockRepo->method('findBySiteId')->willReturn([
            $this->creerFermeture(1, 1, '2026-08-01', '2026-08-07', 'Travaux'),
        ]);

        $service = new FermetureService($mockRepo);
        $result  = $service->getFermeturesBySiteId(1);

        $this->assertCount(1, $result);
    }


    // Vérifie que getFermeturesGlobales() retourne les fermetures globales
    public function testGetFermeturesGlobalesRetourneLesfermeturesGlobales(): void {
        $mockRepo = $this->createStub(FermetureRepository::class);
        $mockRepo->method('findGlobales')->willReturn([
            $this->creerFermeture(2, null, '2026-12-25', '2026-12-25', 'Noël'),
        ]);

        $service = new FermetureService($mockRepo);
        $result  = $service->getFermeturesGlobales();

        $this->assertCount(1, $result);
        $this->assertNull($result[0]->getSiteId(), "Une fermeture globale a Site_ID null");
    }


    // Vérifie que createFermeture() retourne un ID valide si tout est correct
    public function testCreateFermetureRetourneUnId(): void {
        $mockRepo = $this->createStub(FermetureRepository::class);
        $mockRepo->method('insert')->willReturn(3);

        $service = new FermetureService($mockRepo);
        $result  = $service->createFermeture([
            'site_id'    => 1,
            'date_debut' => '2026-08-01',
            'date_fin'   => '2026-08-07',
            'raison'     => 'Travaux',
        ]);

        $this->assertEquals(3, $result, "createFermeture() doit retourner l'ID créé");
    }


    // Vérifie que createFermeture() retourne 'dates_invalides' si date_debut > date_fin
    public function testCreateFermetureRetourneDatesInvalides(): void {
        $mockRepo = $this->createStub(FermetureRepository::class);

        $service = new FermetureService($mockRepo);
        $result  = $service->createFermeture([
            'date_debut' => '2026-08-10',
            'date_fin'   => '2026-08-01',
            'raison'     => 'Erreur',
        ]);

        $this->assertEquals('dates_invalides', $result);
    }


    // Vérifie que updateFermeture() retourne true quand la fermeture existe
    public function testUpdateFermetureRetourneTrueSiExiste(): void {
        $mockRepo = $this->createStub(FermetureRepository::class);
        $mockRepo->method('findById')->willReturn(
            $this->creerFermeture(1, 1, '2026-08-01', '2026-08-07', 'Travaux')
        );

        $service = new FermetureService($mockRepo);
        $result  = $service->updateFermeture(1, ['date_fin' => '2026-08-14']);

        $this->assertTrue($result, "updateFermeture() doit retourner true si la fermeture existe");
    }


    // Vérifie que updateFermeture() retourne false quand la fermeture n'existe pas
    public function testUpdateFermetureRetourneFalseSiInexistant(): void {
        $mockRepo = $this->createStub(FermetureRepository::class);
        $mockRepo->method('findById')->willReturn(null);

        $service = new FermetureService($mockRepo);
        $result  = $service->updateFermeture(999, ['date_fin' => '2026-08-14']);

        $this->assertFalse($result, "updateFermeture() doit retourner false si la fermeture n'existe pas");
    }


    // Vérifie que deleteFermeture() retourne true quand la fermeture existe
    public function testDeleteFermetureRetourneTrueSiExiste(): void {
        $mockRepo = $this->createStub(FermetureRepository::class);
        $mockRepo->method('findById')->willReturn(
            $this->creerFermeture(1, 1, '2026-08-01', '2026-08-07', 'Travaux')
        );

        $service = new FermetureService($mockRepo);
        $result  = $service->deleteFermeture(1);

        $this->assertTrue($result, "deleteFermeture() doit retourner true si la fermeture existe");
    }


    // Vérifie que deleteFermeture() retourne false quand la fermeture n'existe pas
    public function testDeleteFermetureRetourneFalseSiInexistant(): void {
        $mockRepo = $this->createStub(FermetureRepository::class);
        $mockRepo->method('findById')->willReturn(null);

        $service = new FermetureService($mockRepo);
        $result  = $service->deleteFermeture(999);

        $this->assertFalse($result, "deleteFermeture() doit retourner false si la fermeture n'existe pas");
    }
}
