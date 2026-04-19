<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Terrain.php';
require_once __DIR__ . '/../repositories/TerrainRepository.php';

// On teste toutes les méthodes SQL de TerrainRepository.
// On utilise une base SQLite en mémoire pour ne pas toucher à la vraie base.

class TerrainRepositoryTest extends TestCase {

    private PDO $pdo;
    private TerrainRepository $repository;

    // Crée une base temporaire avec 2 terrains avant chaque test
    protected function setUp(): void {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec("
            CREATE TABLE Terrains (
                Terrain_ID  INTEGER PRIMARY KEY AUTOINCREMENT,
                Site_ID     INTEGER NOT NULL,
                Num_Terrain INTEGER NOT NULL,
                Libelle     VARCHAR(50),
                Est_Actif   INTEGER NOT NULL DEFAULT 1,
                UNIQUE (Site_ID, Num_Terrain)
            )
        ");

        // 2 terrains : un actif, un inactif
        $this->pdo->exec("
            INSERT INTO Terrains (Site_ID, Num_Terrain, Libelle, Est_Actif)
            VALUES
                (1, 1, 'Terrain Central', 1),
                (1, 2, 'Terrain Annexe',  0)
        ");

        $this->repository = new TerrainRepository($this->pdo);
    }


    // Vérifie que findAll() retourne tous les terrains (actifs ET inactifs)
    public function testFindAllRetourneTousLesTerrains(): void {
        $terrains = $this->repository->findAll();
        $this->assertCount(2, $terrains, "findAll() doit retourner 2 terrains");
    }


    // Vérifie que findById() retourne le bon terrain
    public function testFindByIdRetourneLesBonTerrain(): void {
        $terrain = $this->repository->findById(1);
        $this->assertNotNull($terrain);
        $this->assertEquals('Terrain Central', $terrain->getLibelle());
    }


    // Vérifie que findById() retourne null si l'ID n'existe pas
    public function testFindByIdRetourneNullSiInexistant(): void {
        $terrain = $this->repository->findById(999);
        $this->assertNull($terrain);
    }


    // Vérifie que findBySiteId() retourne uniquement les terrains du site demandé
    public function testFindBySiteIdRetourneLesTerrainsduSite(): void {
        $terrains = $this->repository->findBySiteId(1);
        $this->assertCount(2, $terrains, "findBySiteId() doit retourner les 2 terrains du site 1");
    }


    // Vérifie que findBySiteId() retourne un tableau vide si le site n'a pas de terrains
    public function testFindBySiteIdRetourneVideSiAucunTerrain(): void {
        $terrains = $this->repository->findBySiteId(999);
        $this->assertCount(0, $terrains);
    }


    // Vérifie que insert() ajoute bien un terrain en base
    public function testInsertAjouteUnTerrain(): void {
        $terrain = new Terrain(null, 1, 3, 'Terrain Nord', true);

        $id = $this->repository->insert($terrain);

        $this->assertGreaterThan(0, $id);
        $this->assertEquals('Terrain Nord', $this->repository->findById($id)->getLibelle());
    }


    // Vérifie que update() modifie bien les données d'un terrain
    public function testUpdateModifieUnTerrain(): void {
        $terrain = $this->repository->findById(1);
        $terrain->setLibelle('Terrain Modifie');
        $this->repository->update($terrain);

        $this->assertEquals('Terrain Modifie', $this->repository->findById(1)->getLibelle());
    }


    // Vérifie que delete() supprime bien un terrain
    public function testDeleteSupprimeUnTerrain(): void {
        $this->repository->delete(1);
        $this->assertNull($this->repository->findById(1));
    }
}
