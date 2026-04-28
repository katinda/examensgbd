<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/HoraireSite.php';
require_once __DIR__ . '/../repositories/HoraireSiteRepository.php';

class HoraireSiteRepositoryTest extends TestCase {

    private PDO $pdo;
    private HoraireSiteRepository $repository;

    protected function setUp(): void {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec("
            CREATE TABLE Horaires_Sites (
                Horaire_ID  INTEGER PRIMARY KEY AUTOINCREMENT,
                Site_ID     INTEGER NOT NULL,
                Annee       INTEGER NOT NULL,
                Heure_Debut TEXT NOT NULL,
                Heure_Fin   TEXT NOT NULL,
                UNIQUE (Site_ID, Annee)
            )
        ");

        $this->pdo->exec("
            INSERT INTO Horaires_Sites (Site_ID, Annee, Heure_Debut, Heure_Fin)
            VALUES
                (1, 2026, '08:00:00', '22:00:00'),
                (2, 2026, '09:00:00', '21:00:00')
        ");

        $this->repository = new HoraireSiteRepository($this->pdo);
    }


    // Vérifie que findAll() retourne bien tous les horaires
    public function testFindAllRetourneTousLesHoraires(): void {
        $horaires = $this->repository->findAll();
        $this->assertCount(2, $horaires, "findAll() doit retourner 2 horaires");
    }


    // Vérifie que findById() retourne le bon horaire quand l'ID existe
    public function testFindByIdRetourneLeBonHoraire(): void {
        $horaire = $this->repository->findById(1);
        $this->assertNotNull($horaire, "L'horaire 1 doit exister");
        $this->assertEquals('08:00:00', $horaire->getHeureDebut());
        $this->assertEquals('22:00:00', $horaire->getHeureFin());
    }


    // Vérifie que findById() retourne null quand l'ID n'existe pas
    public function testFindByIdRetourneNullSiInexistant(): void {
        $horaire = $this->repository->findById(999);
        $this->assertNull($horaire, "Un ID inexistant doit retourner null");
    }


    // Vérifie que findBySiteId() retourne les horaires du site
    public function testFindBySiteIdRetourneLesHorairesDuSite(): void {
        $horaires = $this->repository->findBySiteId(1);
        $this->assertCount(1, $horaires, "findBySiteId() doit retourner 1 horaire pour le site 1");
        $this->assertEquals(2026, $horaires[0]->getAnnee());
    }


    // Vérifie que findBySiteAndAnnee() retourne le bon horaire
    public function testFindBySiteAndAnneeRetourneLeBonHoraire(): void {
        $horaire = $this->repository->findBySiteAndAnnee(1, 2026);
        $this->assertNotNull($horaire, "L'horaire du site 1 pour 2026 doit exister");
        $this->assertEquals('08:00:00', $horaire->getHeureDebut());
    }


    // Vérifie que findBySiteAndAnnee() retourne null si aucun horaire trouvé
    public function testFindBySiteAndAnneeRetourneNullSiInexistant(): void {
        $horaire = $this->repository->findBySiteAndAnnee(1, 2099);
        $this->assertNull($horaire, "Un horaire inexistant doit retourner null");
    }


    // Vérifie que insert() ajoute bien un nouvel horaire en base
    public function testInsertAjouteUnHoraire(): void {
        $horaire = new HoraireSite(null, 3, 2026, '07:00:00', '23:00:00');
        $id = $this->repository->insert($horaire);

        $this->assertGreaterThan(0, $id, "insert() doit retourner un ID valide");

        $horaireCree = $this->repository->findById($id);
        $this->assertEquals('07:00:00', $horaireCree->getHeureDebut());
    }


    // Vérifie que update() modifie bien les données d'un horaire existant
    public function testUpdateModifieUnHoraire(): void {
        $horaire = $this->repository->findById(1);
        $horaire->setHeureDebut('10:00:00');
        $this->repository->update($horaire);

        $horaireModifie = $this->repository->findById(1);
        $this->assertEquals('10:00:00', $horaireModifie->getHeureDebut());
    }


    // Vérifie que delete() supprime bien un horaire de la base
    public function testDeleteSupprimeUnHoraire(): void {
        $this->repository->delete(1);
        $horaire = $this->repository->findById(1);
        $this->assertNull($horaire, "L'horaire supprimé ne doit plus être trouvable");
    }
}
