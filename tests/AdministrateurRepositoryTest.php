<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Administrateur.php';
require_once __DIR__ . '/../repositories/AdministrateurRepository.php';

class AdministrateurRepositoryTest extends TestCase {

    private PDO $pdo;
    private AdministrateurRepository $repository;

    protected function setUp(): void {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec("
            CREATE TABLE Administrateurs (
                Admin_ID          INTEGER PRIMARY KEY AUTOINCREMENT,
                Login             TEXT NOT NULL UNIQUE,
                Mot_De_Passe_Hash TEXT NOT NULL,
                Nom               TEXT,
                Prenom            TEXT,
                Email             TEXT,
                Type              TEXT NOT NULL,
                Site_ID           INTEGER,
                Est_Actif         INTEGER NOT NULL DEFAULT 1,
                Date_Creation     TEXT DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $this->pdo->exec("
            INSERT INTO Administrateurs (Login, Mot_De_Passe_Hash, Nom, Prenom, Email, Type, Site_ID, Est_Actif)
            VALUES
                ('admin.global', 'hash1', 'Dupont', 'Jean',  'jean@padel.fr',  'GLOBAL', NULL, 1),
                ('admin.paris',  'hash2', 'Martin', 'Alice', 'alice@padel.fr', 'SITE',   1,    1)
        ");

        $this->repository = new AdministrateurRepository($this->pdo);
    }


    // Vérifie que findAll() retourne bien tous les administrateurs
    public function testFindAllRetourneTousLesAdministrateurs(): void {
        $admins = $this->repository->findAll();
        $this->assertCount(2, $admins, "findAll() doit retourner 2 administrateurs");
    }


    // Vérifie que findById() retourne le bon administrateur quand l'ID existe
    public function testFindByIdRetourneLeBonAdministrateur(): void {
        $admin = $this->repository->findById(1);
        $this->assertNotNull($admin, "L'administrateur 1 doit exister");
        $this->assertEquals('admin.global', $admin->getLogin());
        $this->assertEquals('GLOBAL', $admin->getType());
    }


    // Vérifie que findById() retourne null quand l'ID n'existe pas
    public function testFindByIdRetourneNullSiInexistant(): void {
        $admin = $this->repository->findById(999);
        $this->assertNull($admin, "Un ID inexistant doit retourner null");
    }


    // Vérifie que findByLogin() retourne le bon administrateur
    public function testFindByLoginRetourneLeBonAdministrateur(): void {
        $admin = $this->repository->findByLogin('admin.paris');
        $this->assertNotNull($admin, "L'administrateur admin.paris doit exister");
        $this->assertEquals('SITE', $admin->getType());
        $this->assertEquals(1, $admin->getSiteId());
    }


    // Vérifie que findByLogin() retourne null si le login n'existe pas
    public function testFindByLoginRetourneNullSiInexistant(): void {
        $admin = $this->repository->findByLogin('inconnu');
        $this->assertNull($admin, "Un login inexistant doit retourner null");
    }


    // Vérifie que insert() ajoute bien un nouvel administrateur en base
    public function testInsertAjouteUnAdministrateur(): void {
        $admin = new Administrateur(null, 'admin.lyon', 'hash3', 'Bernard', 'Paul', null, 'GLOBAL', null, true);
        $id = $this->repository->insert($admin);

        $this->assertGreaterThan(0, $id, "insert() doit retourner un ID valide");

        $adminCree = $this->repository->findById($id);
        $this->assertEquals('admin.lyon', $adminCree->getLogin());
    }


    // Vérifie que update() modifie bien les données d'un administrateur existant
    public function testUpdateModifieUnAdministrateur(): void {
        $admin = $this->repository->findById(1);
        $admin->setEmail('nouveau@padel.fr');
        $this->repository->update($admin);

        $adminModifie = $this->repository->findById(1);
        $this->assertEquals('nouveau@padel.fr', $adminModifie->getEmail());
    }


    // Vérifie que delete() supprime bien un administrateur de la base
    public function testDeleteSupprimeUnAdministrateur(): void {
        $this->repository->delete(1);
        $admin = $this->repository->findById(1);
        $this->assertNull($admin, "L'administrateur supprimé ne doit plus être trouvable");
    }
}
