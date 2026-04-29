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
        $this->pdo->exec("CREATE TABLE Administrateurs (Admin_ID INTEGER PRIMARY KEY AUTOINCREMENT, Login TEXT NOT NULL UNIQUE, Mot_De_Passe_Hash TEXT NOT NULL, Nom TEXT, Prenom TEXT, Email TEXT, Type TEXT NOT NULL, Site_ID INTEGER, Est_Actif INTEGER NOT NULL DEFAULT 1, Date_Creation TEXT DEFAULT CURRENT_TIMESTAMP)");
        $this->repository = new AdministrateurRepository($this->pdo);
    }

    public function testInsertAjouteUnAdministrateur(): void {
        $admin = new Administrateur(null, 'admin.lyon', 'hash3', 'Bernard', 'Paul', null, 'GLOBAL', null, true);
        $id = $this->repository->insert($admin);
        $this->assertGreaterThan(0, $id, "insert() doit retourner un ID valide");
        $this->assertEquals('admin.lyon', $this->repository->findById($id)->getLogin());
    }
}
