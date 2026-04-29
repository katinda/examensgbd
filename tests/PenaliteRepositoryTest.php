<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Penalite.php';
require_once __DIR__ . '/../repositories/PenaliteRepository.php';

class PenaliteRepositoryTest extends TestCase {

    private PDO $pdo;
    private PenaliteRepository $repository;

    protected function setUp(): void {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec("
            CREATE TABLE Penalites (
                Penalite_ID   INTEGER PRIMARY KEY AUTOINCREMENT,
                Membre_ID     INTEGER NOT NULL,
                Reservation_ID INTEGER,
                Date_Debut    TEXT NOT NULL,
                Date_Fin      TEXT NOT NULL,
                Cause         TEXT NOT NULL,
                Levee         INTEGER NOT NULL DEFAULT 0,
                Levee_Par     INTEGER,
                Levee_Le      TEXT,
                Levee_Raison  TEXT,
                Date_Creation TEXT DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $this->pdo->exec("
            INSERT INTO Penalites (Membre_ID, Reservation_ID, Date_Debut, Date_Fin, Cause, Levee)
            VALUES
                (1, 1, '2026-05-01', '2026-05-15', 'PRIVATE_INCOMPLETE', 0),
                (2, NULL, '2026-06-01', '2026-06-07', 'OTHER', 1)
        ");

        $this->repository = new PenaliteRepository($this->pdo);
    }


    // Vérifie que findAll() retourne bien toutes les pénalités
    public function testFindAllRetourneToutesLesPenalites(): void {
        $penalites = $this->repository->findAll();
        $this->assertCount(2, $penalites, "findAll() doit retourner 2 pénalités");
    }


    // Vérifie que findById() retourne la bonne pénalité quand l'ID existe
    public function testFindByIdRetourneLaBonnePenalite(): void {
        $penalite = $this->repository->findById(1);
        $this->assertNotNull($penalite, "La pénalité 1 doit exister");
        $this->assertEquals('PRIVATE_INCOMPLETE', $penalite->getCause());
        $this->assertFalse($penalite->isLevee());
    }


    // Vérifie que findById() retourne null quand l'ID n'existe pas
    public function testFindByIdRetourneNullSiInexistant(): void {
        $penalite = $this->repository->findById(999);
        $this->assertNull($penalite, "Un ID inexistant doit retourner null");
    }


    // Vérifie que findByMembreId() retourne les pénalités du membre
    public function testFindByMembreIdRetourneLespenalitesDuMembre(): void {
        $penalites = $this->repository->findByMembreId(1);
        $this->assertCount(1, $penalites, "findByMembreId() doit retourner 1 pénalité pour le membre 1");
        $this->assertEquals('PRIVATE_INCOMPLETE', $penalites[0]->getCause());
    }


    // Vérifie que findActives() retourne uniquement les pénalités non levées
    public function testFindActivesRetourneLespenalitesNonLevees(): void {
        $penalites = $this->repository->findActives();
        $this->assertCount(1, $penalites, "findActives() doit retourner 1 pénalité non levée");
        $this->assertFalse($penalites[0]->isLevee());
    }


    // Vérifie que insert() ajoute bien une nouvelle pénalité en base
    public function testInsertAjouteUnePenalite(): void {
        $penalite = new Penalite(null, 3, null, '2026-07-01', '2026-07-07', 'PAYMENT_MISSING');
        $id = $this->repository->insert($penalite);

        $this->assertGreaterThan(0, $id, "insert() doit retourner un ID valide");

        $penaliteCree = $this->repository->findById($id);
        $this->assertEquals('PAYMENT_MISSING', $penaliteCree->getCause());
    }


    // Vérifie que update() modifie bien les données d'une pénalité existante
    public function testUpdateModifieUnePenalite(): void {
        $penalite = $this->repository->findById(1);
        $penalite->setLevee(true);
        $penalite->setLeveePar(1);
        $penalite->setLeveeLe('2026-05-10 10:00:00');
        $penalite->setLeveeRaison('Erreur constatée');
        $this->repository->update($penalite);

        $penaliteModifiee = $this->repository->findById(1);
        $this->assertTrue($penaliteModifiee->isLevee());
        $this->assertEquals('Erreur constatée', $penaliteModifiee->getLeveeRaison());
    }


    // Vérifie que delete() supprime bien une pénalité de la base
    public function testDeleteSupprimeUnePenalite(): void {
        $this->repository->delete(1);
        $penalite = $this->repository->findById(1);
        $this->assertNull($penalite, "La pénalité supprimée ne doit plus être trouvable");
    }
}
