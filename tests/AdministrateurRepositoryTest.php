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
        $this->pdo->exec("INSERT INTO Administrateurs (Login, Mot_De_Passe_Hash, Nom, Prenom, Email, Type, Site_ID, Est_Actif) VALUES ('admin.global', 'hash1', 'Dupont', 'Jean', 'jean@padel.fr', 'GLOBAL', NULL, 1)");
        $this->repository = new AdministrateurRepository($this->pdo);
    }

    public function testDeleteSupprimeUnAdministrateur(): void {
        $this->repository->delete(1);
        $this->assertNull($this->repository->findById(1), "L'administrateur supprimé ne doit plus être trouvable");
    }
}
