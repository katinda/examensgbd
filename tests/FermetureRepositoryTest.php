<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Fermeture.php';
require_once __DIR__ . '/../repositories/FermetureRepository.php';

class FermetureRepositoryTest extends TestCase {

    private PDO $pdo;
    private FermetureRepository $repository;

    protected function setUp(): void {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec("
            CREATE TABLE Fermetures (
                Fermeture_ID  INTEGER PRIMARY KEY AUTOINCREMENT,
                Site_ID       INTEGER,
                Date_Debut    TEXT NOT NULL,
                Date_Fin      TEXT NOT NULL,
                Raison        TEXT,
                Date_Creation TEXT DEFAULT CURRENT_DATE
            )
        ");

        $this->pdo->exec("
            INSERT INTO Fermetures (Site_ID, Date_Debut, Date_Fin, Raison)
            VALUES
                (1,    '2026-08-01', '2026-08-07', 'Travaux estivaux'),
                (NULL, '2026-12-25', '2026-12-25', 'Noël')
        ");

        $this->repository = new FermetureRepository($this->pdo);
    }


    // Vérifie que findAll() retourne bien toutes les fermetures
    public function testFindAllRetourneToutesLesFermetures(): void {
        $fermetures = $this->repository->findAll();
        $this->assertCount(2, $fermetures, "findAll() doit retourner 2 fermetures");
    }


    // Vérifie que findById() retourne la bonne fermeture quand l'ID existe
    public function testFindByIdRetourneLaBonneFermeture(): void {
        $fermeture = $this->repository->findById(1);
        $this->assertNotNull($fermeture, "La fermeture 1 doit exister");
        $this->assertEquals('2026-08-01', $fermeture->getDateDebut());
        $this->assertEquals('Travaux estivaux', $fermeture->getRaison());
    }


    // Vérifie que findById() retourne null quand l'ID n'existe pas
    public function testFindByIdRetourneNullSiInexistant(): void {
        $fermeture = $this->repository->findById(999);
        $this->assertNull($fermeture, "Un ID inexistant doit retourner null");
    }


    // Vérifie que findBySiteId() retourne les fermetures du site
    public function testFindBySiteIdRetourneLesfermeturesDuSite(): void {
        $fermetures = $this->repository->findBySiteId(1);
        $this->assertCount(1, $fermetures, "findBySiteId() doit retourner 1 fermeture pour le site 1");
        $this->assertEquals('2026-08-01', $fermetures[0]->getDateDebut());
    }


    // Vérifie que findGlobales() retourne uniquement les fermetures globales (Site_ID IS NULL)
    public function testFindGlobalesRetourneLesfermeturesGlobales(): void {
        $fermetures = $this->repository->findGlobales();
        $this->assertCount(1, $fermetures, "findGlobales() doit retourner 1 fermeture globale");
        $this->assertNull($fermetures[0]->getSiteId(), "Une fermeture globale a Site_ID null");
    }


    // Vérifie que insert() ajoute bien une nouvelle fermeture en base
    public function testInsertAjouteUneFermeture(): void {
        $fermeture = new Fermeture(null, 2, '2026-07-01', '2026-07-05', 'Congés');
        $id = $this->repository->insert($fermeture);

        $this->assertGreaterThan(0, $id, "insert() doit retourner un ID valide");

        $fermetureCree = $this->repository->findById($id);
        $this->assertEquals('2026-07-01', $fermetureCree->getDateDebut());
    }


    // Vérifie que update() modifie bien les données d'une fermeture existante
    public function testUpdateModifieUneFermeture(): void {
        $fermeture = $this->repository->findById(1);
        $fermeture->setDateFin('2026-08-14');
        $this->repository->update($fermeture);

        $fermetureModifiee = $this->repository->findById(1);
        $this->assertEquals('2026-08-14', $fermetureModifiee->getDateFin());
    }


    // Vérifie que delete() supprime bien une fermeture de la base
    public function testDeleteSupprimeUneFermeture(): void {
        $this->repository->delete(1);
        $fermeture = $this->repository->findById(1);
        $this->assertNull($fermeture, "La fermeture supprimée ne doit plus être trouvable");
    }
}
