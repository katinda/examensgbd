<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Membre.php';
require_once __DIR__ . '/../repositories/MembreRepository.php';

class MembreRepositoryTest extends TestCase {

    private PDO $pdo;
    private MembreRepository $repository;

    protected function setUp(): void {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec("
            CREATE TABLE Membres (
                Membre_ID     INTEGER PRIMARY KEY AUTOINCREMENT,
                Matricule     VARCHAR(10) NOT NULL UNIQUE,
                Nom           VARCHAR(100) NOT NULL,
                Prenom        VARCHAR(100) NOT NULL,
                Email         VARCHAR(255),
                Telephone     VARCHAR(20),
                Categorie     CHAR(1) NOT NULL,
                Site_ID       INTEGER NULL,
                Est_Actif     INTEGER NOT NULL DEFAULT 1,
                Date_Creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $this->pdo->exec("
            INSERT INTO Membres (Matricule, Nom, Prenom, Email, Telephone, Categorie, Site_ID, Est_Actif)
            VALUES
                ('G0001', 'Dupont',  'Jean',  'jean@email.com',  '0601020304', 'G', NULL, 1),
                ('S00001','Martin',  'Alice', 'alice@email.com', '0605060708', 'S', 1,    1),
                ('L00001','Bernard', 'Paul',  NULL,              NULL,         'L', NULL, 0)
        ");

        $this->repository = new MembreRepository($this->pdo);
    }


    // Vérifie que findAll() retourne tous les membres (actifs ET inactifs)
    public function testFindAllRetourneTousLesMembres(): void {
        $membres = $this->repository->findAll();
        $this->assertCount(3, $membres);
    }


    // Vérifie que findById() retourne le bon membre
    public function testFindByIdRetourneLeMembreCorrect(): void {
        $membre = $this->repository->findById(1);
        $this->assertNotNull($membre);
        $this->assertEquals('Dupont', $membre->getNom());
        $this->assertEquals('G0001', $membre->getMatricule());
    }


    // Vérifie que findById() retourne null si l'ID n'existe pas
    public function testFindByIdRetourneNullSiInexistant(): void {
        $this->assertNull($this->repository->findById(999));
    }


    // Vérifie que findByMatricule() retourne le bon membre
    public function testFindByMatriculeRetourneLeMembreCorrect(): void {
        $membre = $this->repository->findByMatricule('S00001');
        $this->assertNotNull($membre);
        $this->assertEquals('Martin', $membre->getNom());
    }


    // Vérifie que findByMatricule() retourne null si le matricule n'existe pas
    public function testFindByMatriculeRetourneNullSiInexistant(): void {
        $this->assertNull($this->repository->findByMatricule('X9999'));
    }


    // Vérifie que findByCategorie() retourne uniquement les membres de la catégorie demandée
    public function testFindByCategorieRetourneLesBonsMembres(): void {
        $membres = $this->repository->findByCategorie('G');
        $this->assertCount(1, $membres);
        $this->assertEquals('G0001', $membres[0]->getMatricule());
    }


    // Vérifie que insert() ajoute bien un membre en base
    public function testInsertAjouteUnMembre(): void {
        $membre = new Membre(null, 'G0002', 'Durand', 'Marie', null, null, 'G', null, true);
        $id = $this->repository->insert($membre);

        $this->assertGreaterThan(0, $id);
        $this->assertEquals('Durand', $this->repository->findById($id)->getNom());
    }


    // Vérifie que update() modifie bien les données d'un membre
    public function testUpdateModifieUnMembre(): void {
        $membre = $this->repository->findById(1);
        $membre->setNom('Dupont Modifié');
        $this->repository->update($membre);

        $this->assertEquals('Dupont Modifié', $this->repository->findById(1)->getNom());
    }


    // Vérifie que delete() supprime bien un membre
    public function testDeleteSupprimeUnMembre(): void {
        $this->repository->delete(1);
        $this->assertNull($this->repository->findById(1));
    }
}
