<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Reservation.php';
require_once __DIR__ . '/../models/Terrain.php';
require_once __DIR__ . '/../models/Membre.php';
require_once __DIR__ . '/../repositories/ReservationRepository.php';
require_once __DIR__ . '/../repositories/TerrainRepository.php';
require_once __DIR__ . '/../repositories/MembreRepository.php';
require_once __DIR__ . '/../repositories/InscriptionRepository.php';
require_once __DIR__ . '/../repositories/AdministrateurRepository.php';
require_once __DIR__ . '/../repositories/HoraireSiteRepository.php';
require_once __DIR__ . '/../repositories/FermetureRepository.php';
require_once __DIR__ . '/../models/Administrateur.php';
require_once __DIR__ . '/../models/HoraireSite.php';
require_once __DIR__ . '/../models/Fermeture.php';
require_once __DIR__ . '/../services/ReservationService.php';

// On teste la logique métier du ReservationService.
// On utilise des stubs pour simuler les repositories.

class ReservationServiceTest extends TestCase {

    private function creerReservation(int $id, int $terrainId = 1): Reservation {
        return new Reservation($id, $terrainId, 1, '2026-05-10', '10:00:00', '11:30:00', 'PRIVE');
    }

    private function creerTerrain(int $id, bool $actif, int $siteId = 1): Terrain {
        return new Terrain($id, $siteId, $id, "Terrain $id", $actif);
    }

    private function creerMembre(int $id, bool $actif = true, string $categorie = 'G', ?int $siteId = null): Membre {
        $matricule = $categorie . '0001';
        return new Membre($id, $matricule, 'Dupont', 'Jean', null, null, $categorie, $siteId, $actif);
    }

    private function creerData(array $overrides = []): array {
        return array_merge([
            'terrain_id'      => 1,
            'organisateur_id' => 1,
            'date_match'      => (new DateTime('+3 days'))->format('Y-m-d'),
            'heure_debut'     => '08:00:00', // premier créneau valide (Heure_Debut du site)
            'type'            => 'PRIVE',
        ], $overrides);
    }

    // Horaire par défaut : 08:00 - 22:00
    private function creerHoraireRepo(?HoraireSite $horaire = null): HoraireSiteRepository {
        $mock = $this->createStub(HoraireSiteRepository::class);
        $mock->method('findBySiteAndAnnee')->willReturn(
            $horaire ?? new HoraireSite(1, 1, (int) date('Y'), '08:00:00', '22:00:00')
        );
        return $mock;
    }

    // Aucune fermeture par défaut
    private function creerFermetureRepo(): FermetureRepository {
        $mock = $this->createStub(FermetureRepository::class);
        $mock->method('findBySiteId')->willReturn([]);
        $mock->method('findGlobales')->willReturn([]);
        return $mock;
    }

    // PDO SQLite en mémoire — léger, supporte les transactions, utilisé par createReservation()
    private function creerPdo(): PDO {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }


    // Vérifie que getReservationById() délègue bien au repository
    public function testGetReservationByIdRetourneLaReservation(): void {
        $mockRepo = $this->createStub(ReservationRepository::class);
        $mockRepo->method('findById')->willReturn($this->creerReservation(1));

        $service = new ReservationService($mockRepo, $this->createStub(TerrainRepository::class), $this->createStub(MembreRepository::class), $this->createStub(InscriptionRepository::class), $this->createStub(AdministrateurRepository::class), $this->creerHoraireRepo(), $this->creerFermetureRepo(), $this->creerPdo());
        $result  = $service->getReservationById(1);

        $this->assertNotNull($result);
        $this->assertEquals(1, $result->getReservationId());
    }


    // Vérifie que getReservationsByMembre() retourne la liste du repository
    public function testGetReservationsByMembreRetourneLesReservations(): void {
        $mockRepo = $this->createStub(ReservationRepository::class);
        $mockRepo->method('findByOrganisateur')->willReturn([
            $this->creerReservation(1),
            $this->creerReservation(2),
        ]);

        $service = new ReservationService($mockRepo, $this->createStub(TerrainRepository::class), $this->createStub(MembreRepository::class), $this->createStub(InscriptionRepository::class), $this->createStub(AdministrateurRepository::class), $this->creerHoraireRepo(), $this->creerFermetureRepo(), $this->creerPdo());
        $this->assertCount(2, $service->getReservationsByMembre(1));
    }


    // Vérifie que getReservationsByTerrainAndDate() retourne la liste du repository
    public function testGetReservationsByTerrainAndDateRetourneLesReservations(): void {
        $mockRepo = $this->createStub(ReservationRepository::class);
        $mockRepo->method('findByTerrainAndDate')->willReturn([
            $this->creerReservation(1),
        ]);

        $service = new ReservationService($mockRepo, $this->createStub(TerrainRepository::class), $this->createStub(MembreRepository::class), $this->createStub(InscriptionRepository::class), $this->createStub(AdministrateurRepository::class), $this->creerHoraireRepo(), $this->creerFermetureRepo(), $this->creerPdo());
        $this->assertCount(1, $service->getReservationsByTerrainAndDate(1, '2026-05-10'));
    }


    // Vérifie que createReservation() retourne 'terrain_introuvable' si le terrain n'existe pas
    public function testCreateReservationRetourneTerrainIntrouvable(): void {
        $mockTerrain = $this->createStub(TerrainRepository::class);
        $mockTerrain->method('findById')->willReturn(null);

        $service = new ReservationService($this->createStub(ReservationRepository::class), $mockTerrain, $this->createStub(MembreRepository::class), $this->createStub(InscriptionRepository::class), $this->createStub(AdministrateurRepository::class), $this->creerHoraireRepo(), $this->creerFermetureRepo(), $this->creerPdo());
        $this->assertEquals('terrain_introuvable', $service->createReservation($this->creerData()));
    }


    // Vérifie que createReservation() retourne 'terrain_inactif' si le terrain est fermé
    public function testCreateReservationRetourneTerrainInactif(): void {
        $mockTerrain = $this->createStub(TerrainRepository::class);
        $mockTerrain->method('findById')->willReturn($this->creerTerrain(1, false));

        $service = new ReservationService($this->createStub(ReservationRepository::class), $mockTerrain, $this->createStub(MembreRepository::class), $this->createStub(InscriptionRepository::class), $this->createStub(AdministrateurRepository::class), $this->creerHoraireRepo(), $this->creerFermetureRepo(), $this->creerPdo());
        $this->assertEquals('terrain_inactif', $service->createReservation($this->creerData()));
    }


    // Vérifie que createReservation() retourne 'organisateur_introuvable' si le membre n'existe pas
    public function testCreateReservationRetourneOrganisateurIntrouvable(): void {
        $mockTerrain = $this->createStub(TerrainRepository::class);
        $mockTerrain->method('findById')->willReturn($this->creerTerrain(1, true));
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockMembre->method('findById')->willReturn(null);

        $service = new ReservationService($this->createStub(ReservationRepository::class), $mockTerrain, $mockMembre, $this->createStub(InscriptionRepository::class), $this->createStub(AdministrateurRepository::class), $this->creerHoraireRepo(), $this->creerFermetureRepo(), $this->creerPdo());
        $this->assertEquals('organisateur_introuvable', $service->createReservation($this->creerData()));
    }


    // Vérifie que createReservation() retourne 'creneau_pris' si le créneau est déjà réservé
    public function testCreateReservationRetourneCreneauPris(): void {
        $mockRepo = $this->createStub(ReservationRepository::class);
        $mockRepo->method('findByTerrainDateHeure')->willReturn($this->creerReservation(1));
        $mockTerrain = $this->createStub(TerrainRepository::class);
        $mockTerrain->method('findById')->willReturn($this->creerTerrain(1, true));
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockMembre->method('findById')->willReturn($this->creerMembre(1));

        $service = new ReservationService($mockRepo, $mockTerrain, $mockMembre, $this->createStub(InscriptionRepository::class), $this->createStub(AdministrateurRepository::class), $this->creerHoraireRepo(), $this->creerFermetureRepo(), $this->creerPdo());
        $this->assertEquals('creneau_pris', $service->createReservation($this->creerData()));
    }


    // Vérifie que createReservation() retourne un ID si tout est valide
    public function testCreateReservationRetourneUnId(): void {
        $mockRepo = $this->createStub(ReservationRepository::class);
        $mockRepo->method('findByTerrainDateHeure')->willReturn(null);
        $mockRepo->method('insert')->willReturn(5);
        $mockTerrain = $this->createStub(TerrainRepository::class);
        $mockTerrain->method('findById')->willReturn($this->creerTerrain(1, true));
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockMembre->method('findById')->willReturn($this->creerMembre(1));

        $service = new ReservationService($mockRepo, $mockTerrain, $mockMembre, $this->createStub(InscriptionRepository::class), $this->createStub(AdministrateurRepository::class), $this->creerHoraireRepo(), $this->creerFermetureRepo(), $this->creerPdo());
        $this->assertEquals(5, $service->createReservation($this->creerData()));
    }


    // Vérifie que createReservation() calcule correctement Heure_Fin = Heure_Debut + 1h30
    public function testCreateReservationCalculeHeureFin(): void {
        $reservationInseree = null;

        $mockRepo = $this->createStub(ReservationRepository::class);
        $mockRepo->method('findByTerrainDateHeure')->willReturn(null);
        $mockRepo->method('insert')->willReturnCallback(function (Reservation $r) use (&$reservationInseree) {
            $reservationInseree = $r;
            return 1;
        });
        $mockTerrain = $this->createStub(TerrainRepository::class);
        $mockTerrain->method('findById')->willReturn($this->creerTerrain(1, true));
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockMembre->method('findById')->willReturn($this->creerMembre(1));

        $service = new ReservationService($mockRepo, $mockTerrain, $mockMembre, $this->createStub(InscriptionRepository::class), $this->createStub(AdministrateurRepository::class), $this->creerHoraireRepo(), $this->creerFermetureRepo(), $this->creerPdo());
        $service->createReservation($this->creerData(['heure_debut' => '08:00:00']));

        $this->assertNotNull($reservationInseree);
        $this->assertEquals('09:30:00', $reservationInseree->getHeureFin());
    }


    // ─── Filtrage par rôle admin ─────────────────────────────────────────────

    private function creerAdminRepo(string $type, ?int $siteId = null): AdministrateurRepository {
        $admin = new Administrateur(1, 'admin', 'hash', null, null, null, $type, $siteId, true);
        $mock  = $this->createStub(AdministrateurRepository::class);
        $mock->method('findById')->willReturn($admin);
        return $mock;
    }

    // ─── Horaires et fermetures ──────────────────────────────────────────────

    // Aucun horaire pour ce site/année → horaire_introuvable
    public function testCreateReservationRetourneHoraireIntrouvable(): void {
        $mockHoraire = $this->createStub(HoraireSiteRepository::class);
        $mockHoraire->method('findBySiteAndAnnee')->willReturn(null);
        $mockTerrain = $this->createStub(TerrainRepository::class);
        $mockTerrain->method('findById')->willReturn($this->creerTerrain(1, true));
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockMembre->method('findById')->willReturn($this->creerMembre(1));

        $service = new ReservationService($this->createStub(ReservationRepository::class), $mockTerrain, $mockMembre, $this->createStub(InscriptionRepository::class), $this->createStub(AdministrateurRepository::class), $mockHoraire, $this->creerFermetureRepo(), $this->creerPdo());
        $result  = $service->createReservation($this->creerData());
        $this->assertEquals('horaire_introuvable', $result);
    }

    // Heure en dehors des horaires du site → hors_horaires
    public function testCreateReservationRetourneHorsHoraires(): void {
        $mockHoraire = $this->createStub(HoraireSiteRepository::class);
        $mockHoraire->method('findBySiteAndAnnee')->willReturn(new HoraireSite(1, 1, (int)date('Y'), '10:00:00', '20:00:00'));
        $mockTerrain = $this->createStub(TerrainRepository::class);
        $mockTerrain->method('findById')->willReturn($this->creerTerrain(1, true));
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockMembre->method('findById')->willReturn($this->creerMembre(1));

        $service = new ReservationService($this->createStub(ReservationRepository::class), $mockTerrain, $mockMembre, $this->createStub(InscriptionRepository::class), $this->createStub(AdministrateurRepository::class), $mockHoraire, $this->creerFermetureRepo(), $this->creerPdo());
        // 08:00 < 10:00 (Heure_Debut du site) → hors_horaires
        $result = $service->createReservation($this->creerData(['heure_debut' => '08:00:00']));
        $this->assertEquals('hors_horaires', $result);
    }

    // Heure valide mais pas sur un créneau (ex: 08:30 avec site débutant à 08:00) → creneau_invalide
    public function testCreateReservationRetourneCreneauInvalide(): void {
        $mockTerrain = $this->createStub(TerrainRepository::class);
        $mockTerrain->method('findById')->willReturn($this->creerTerrain(1, true));
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockMembre->method('findById')->willReturn($this->creerMembre(1));

        $service = new ReservationService($this->createStub(ReservationRepository::class), $mockTerrain, $mockMembre, $this->createStub(InscriptionRepository::class), $this->createStub(AdministrateurRepository::class), $this->creerHoraireRepo(), $this->creerFermetureRepo(), $this->creerPdo());
        // 08:30 → 30 min après 08:00 → 30 % 105 ≠ 0 → creneau_invalide
        $result = $service->createReservation($this->creerData(['heure_debut' => '08:30:00']));
        $this->assertEquals('creneau_invalide', $result);
    }

    // Fermeture active sur la date du match → site_ferme
    public function testCreateReservationRetourneSiteFerme(): void {
        $dateMatch = (new DateTime('+3 days'))->format('Y-m-d');
        $mockFermeture = $this->createStub(FermetureRepository::class);
        $mockFermeture->method('findBySiteId')->willReturn([
            new Fermeture(1, 1, $dateMatch, $dateMatch, 'Travaux'),
        ]);
        $mockFermeture->method('findGlobales')->willReturn([]);
        $mockTerrain = $this->createStub(TerrainRepository::class);
        $mockTerrain->method('findById')->willReturn($this->creerTerrain(1, true));
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockMembre->method('findById')->willReturn($this->creerMembre(1));

        $service = new ReservationService($this->createStub(ReservationRepository::class), $mockTerrain, $mockMembre, $this->createStub(InscriptionRepository::class), $this->createStub(AdministrateurRepository::class), $this->creerHoraireRepo(), $mockFermeture, $this->creerPdo());
        $result = $service->createReservation($this->creerData());
        $this->assertEquals('site_ferme', $result);
    }

    // ─── Délai de réservation ────────────────────────────────────────────────

    private function creerServiceSimple(Terrain $terrain, Membre $membre, ?string $dateHeure = null): ReservationService {
        $mockRepo = $this->createStub(ReservationRepository::class);
        $mockRepo->method('findByTerrainDateHeure')->willReturn(null);
        $mockTerrain = $this->createStub(TerrainRepository::class);
        $mockTerrain->method('findById')->willReturn($terrain);
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockMembre->method('findById')->willReturn($membre);
        return new ReservationService($mockRepo, $mockTerrain, $mockMembre, $this->createStub(InscriptionRepository::class), $this->createStub(AdministrateurRepository::class), $this->creerHoraireRepo(), $this->creerFermetureRepo(), $this->creerPdo());
    }

    // Date dans le passé → date_passee
    public function testCreateReservationRetourneDatePassee(): void {
        $service = $this->creerServiceSimple($this->creerTerrain(1, true), $this->creerMembre(1));
        $result  = $service->createReservation($this->creerData(['date_match' => '2020-01-01']));
        $this->assertEquals('date_passee', $result);
    }

    // Membre G, match dans 25 jours → trop_tot (max 21j)
    public function testCreateReservationTropTotMembreG(): void {
        $service = $this->creerServiceSimple($this->creerTerrain(1, true), $this->creerMembre(1, true, 'G'));
        $result  = $service->createReservation($this->creerData(['date_match' => (new DateTime('+25 days'))->format('Y-m-d')]));
        $this->assertEquals('trop_tot', $result);
    }

    // Membre S, match dans 16 jours → trop_tot (max 14j)
    public function testCreateReservationTropTotMembreS(): void {
        $service = $this->creerServiceSimple($this->creerTerrain(1, true, 1), $this->creerMembre(1, true, 'S', 1));
        $result  = $service->createReservation($this->creerData(['date_match' => (new DateTime('+16 days'))->format('Y-m-d')]));
        $this->assertEquals('trop_tot', $result);
    }

    // Membre L, match dans 6 jours → trop_tot (max 5j)
    public function testCreateReservationTropTotMembreL(): void {
        $service = $this->creerServiceSimple($this->creerTerrain(1, true), $this->creerMembre(1, true, 'L'));
        $result  = $service->createReservation($this->creerData(['date_match' => (new DateTime('+6 days'))->format('Y-m-d')]));
        $this->assertEquals('trop_tot', $result);
    }

    // Membre S, terrain d'un autre site → site_non_autorise
    public function testCreateReservationSiteNonAutoriseMembreS(): void {
        $service = $this->creerServiceSimple($this->creerTerrain(1, true, 2), $this->creerMembre(1, true, 'S', 1));
        $result  = $service->createReservation($this->creerData(['date_match' => (new DateTime('+3 days'))->format('Y-m-d')]));
        $this->assertEquals('site_non_autorise', $result);
    }

    // Membre S, terrain de son site → OK (délai respecté)
    public function testCreateReservationMembreSAccepteSonSite(): void {
        $mockRepo = $this->createStub(ReservationRepository::class);
        $mockRepo->method('findByTerrainDateHeure')->willReturn(null);
        $mockRepo->method('insert')->willReturn(1);
        $mockTerrain = $this->createStub(TerrainRepository::class);
        $mockTerrain->method('findById')->willReturn($this->creerTerrain(1, true, 1));
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockMembre->method('findById')->willReturn($this->creerMembre(1, true, 'S', 1));
        $mockInscription = $this->createStub(InscriptionRepository::class);

        $pdo = new PDO('sqlite::memory:');
        $pdo->exec('CREATE TABLE Reservations (Reservation_ID INTEGER PRIMARY KEY AUTOINCREMENT, Terrain_ID INT, Organisateur_ID INT, Date_Match TEXT, Heure_Debut TEXT, Heure_Fin TEXT, Type TEXT, Etat TEXT DEFAULT "EN_COURS", Prix_Total REAL DEFAULT 60.0, Date_Creation TEXT, LastUpdate TEXT)');
        $pdo->exec('CREATE TABLE Inscriptions (Inscription_ID INTEGER PRIMARY KEY AUTOINCREMENT, Reservation_ID INT, Membre_ID INT, Est_Organisateur INT DEFAULT 0)');

        $service = new ReservationService($mockRepo, $mockTerrain, $mockMembre, $mockInscription, $this->createStub(AdministrateurRepository::class), $this->creerHoraireRepo(), $this->creerFermetureRepo(), $pdo);
        $result  = $service->createReservation($this->creerData(['date_match' => (new DateTime('+3 days'))->format('Y-m-d')]));
        $this->assertEquals(1, $result);
    }

    // Admin GLOBAL → voit toutes les réservations du membre
    public function testGetReservationsByMembreAdminGlobalVoitTout(): void {
        $mockRepo = $this->createStub(ReservationRepository::class);
        $mockRepo->method('findByOrganisateur')->willReturn([
            $this->creerReservation(1, 1),
            $this->creerReservation(2, 2),
        ]);
        $mockTerrain = $this->createStub(TerrainRepository::class);

        $service = new ReservationService($mockRepo, $mockTerrain, $this->createStub(MembreRepository::class), $this->createStub(InscriptionRepository::class), $this->creerAdminRepo('GLOBAL'), $this->creerHoraireRepo(), $this->creerFermetureRepo(), $this->creerPdo());
        $this->assertCount(2, $service->getReservationsByMembre(1, 1));
    }

    // Admin SITE → uniquement les réservations sur les terrains de son site
    public function testGetReservationsByMembreAdminSiteFiltreSonSite(): void {
        $mockRepo = $this->createStub(ReservationRepository::class);
        $mockRepo->method('findByOrganisateur')->willReturn([
            $this->creerReservation(1, 1),
            $this->creerReservation(2, 2),
        ]);
        $mockTerrain = $this->createStub(TerrainRepository::class);
        $mockTerrain->method('findById')->willReturnMap([
            [1, $this->creerTerrain(1, true, 1)], // terrain du site 1
            [2, $this->creerTerrain(2, true, 2)], // terrain du site 2
        ]);

        $service = new ReservationService($mockRepo, $mockTerrain, $this->createStub(MembreRepository::class), $this->createStub(InscriptionRepository::class), $this->creerAdminRepo('SITE', 1), $this->creerHoraireRepo(), $this->creerFermetureRepo(), $this->creerPdo());
        $result  = $service->getReservationsByMembre(1, 1);

        $this->assertCount(1, $result);
        $this->assertEquals(1, $result[0]->getTerrainId());
    }
}
