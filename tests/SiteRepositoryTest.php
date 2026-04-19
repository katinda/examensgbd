<?php

use PHPUnit\Framework\TestCase;

// On charge les fichiers dont on a besoin pour les tests
require_once __DIR__ . '/../models/Site.php';
require_once __DIR__ . '/../repositories/SiteRepository.php';

// Un test c'est comme un contrôle qualité :
// on vérifie que chaque méthode fait bien ce qu'elle est censée faire.
// On utilise une "fausse" base de données (SQLite en mémoire) pour ne pas toucher aux vraies données.

class SiteRepositoryTest extends TestCase {

    private PDO $pdo;
    private SiteRepository $repository;

    // Cette méthode s'exécute AVANT chaque test.
    // Elle crée une base de données temporaire en mémoire (SQLite) pour les tests.
    // Comme ça on ne touche jamais à la vraie base PadelManager.
    protected function setUp(): void {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // On crée une table Sites simplifiée pour les tests
        $this->pdo->exec("
            CREATE TABLE Sites (
                Site_ID       INTEGER PRIMARY KEY AUTOINCREMENT,
                Nom           VARCHAR(100) NOT NULL,
                Adresse       VARCHAR(255),
                Ville         VARCHAR(100),
                Code_Postal   VARCHAR(10),
                Est_Actif     INTEGER NOT NULL DEFAULT 1,
                Date_Creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // On insère 2 sites de test : un actif, un inactif
        $this->pdo->exec("
            INSERT INTO Sites (Nom, Adresse, Ville, Code_Postal, Est_Actif)
            VALUES
                ('Club Paris',  '10 rue de la Paix', 'Paris',  '75001', 1),
                ('Club Ferme',  '5 rue du Stade',    'Lyon',   '69001', 0)
        ");

        $this->repository = new SiteRepository($this->pdo);
    }


    // Vérifie que findAll() retourne bien tous les sites (actifs ET inactifs)
    public function testFindAllRetourneTousLesSites(): void {
        $sites = $this->repository->findAll();
        $this->assertCount(2, $sites, "findAll() doit retourner 2 sites");
    }


    // Vérifie que findById() retourne le bon site quand l'ID existe
    public function testFindByIdRetourneLeBosSite(): void {
        $site = $this->repository->findById(1);
        $this->assertNotNull($site, "Le site 1 doit exister");
        $this->assertEquals('Club Paris', $site->getNom());
    }


    // Vérifie que findById() retourne null quand l'ID n'existe pas
    public function testFindByIdRetourneNullSiInexistant(): void {
        $site = $this->repository->findById(999);
        $this->assertNull($site, "Un ID inexistant doit retourner null");
    }


    // Vérifie que insert() ajoute bien un nouveau site en base
    public function testInsertAjouteUnSite(): void {
        $site = new Site(null, 'Club Bordeaux', '1 avenue du Vin', 'Bordeaux', '33000', true);

        $id = $this->repository->insert($site);

        $this->assertGreaterThan(0, $id, "insert() doit retourner un ID valide");

        $siteCree = $this->repository->findById($id);
        $this->assertEquals('Club Bordeaux', $siteCree->getNom());
    }


    // Vérifie que update() modifie bien les données d'un site existant
    public function testUpdateModifieUnSite(): void {
        $site = $this->repository->findById(1);
        $site->setNom('Club Paris Modifie');
        $this->repository->update($site);

        $siteModifie = $this->repository->findById(1);
        $this->assertEquals('Club Paris Modifie', $siteModifie->getNom());
    }


    // Vérifie que delete() supprime bien un site de la base
    public function testDeleteSupprimeUnSite(): void {
        $this->repository->delete(1);
        $site = $this->repository->findById(1);
        $this->assertNull($site, "Le site supprimé ne doit plus être trouvable");
    }
}
