<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Inscription.php';
require_once __DIR__ . '/../models/Reservation.php';
require_once __DIR__ . '/../models/Membre.php';
require_once __DIR__ . '/../models/Paiement.php';
require_once __DIR__ . '/../repositories/InscriptionRepository.php';
require_once __DIR__ . '/../repositories/ReservationRepository.php';
require_once __DIR__ . '/../repositories/MembreRepository.php';
require_once __DIR__ . '/../repositories/PaiementRepository.php';
require_once __DIR__ . '/../services/InscriptionService.php';

class InscriptionServiceTest extends TestCase {

    private function creerInscription(int $id, int $reservationId, int $membreId, bool $organisateur = false): Inscription {
        return new Inscription($id, $reservationId, $membreId, $organisateur);
    }

    private function creerReservation(int $id, string $type = 'PRIVE', int $organisateurId = 1): Reservation {
        return new Reservation($id, 1, $organisateurId, '2026-05-10', '10:00:00', '11:30:00', $type);
    }

    private function creerMembre(int $id, bool $actif = true): Membre {
        return new Membre($id, 'G0001', 'Dupont', 'Jean', null, null, 'G', null, $actif);
    }

    private function creerPdo(): PDO {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }

    private function creerService(
        InscriptionRepository $inscriptionRepo,
        ReservationRepository $reservationRepo,
        MembreRepository      $membreRepo,
        ?PaiementRepository   $paiementRepo = null
    ): InscriptionService {
        return new InscriptionService(
            $inscriptionRepo,
            $reservationRepo,
            $membreRepo,
            $paiementRepo ?? $this->createStub(PaiementRepository::class),
            $this->creerPdo()
        );
    }


    // ─── getInscriptionsByReservation ────────────────────────────────────────

    public function testGetInscriptionsByReservationRetourneLesInscriptions(): void {
        $mockInscription = $this->createStub(InscriptionRepository::class);
        $mockInscription->method('findByReservation')->willReturn([
            $this->creerInscription(1, 1, 1, true),
            $this->creerInscription(2, 1, 2, false),
        ]);
        $service = $this->creerService($mockInscription, $this->createStub(ReservationRepository::class), $this->createStub(MembreRepository::class));
        $this->assertCount(2, $service->getInscriptionsByReservation(1));
    }


    // ─── addJoueur ───────────────────────────────────────────────────────────

    public function testAddJoueurRetourneReservationIntrouvable(): void {
        $mockReservation = $this->createStub(ReservationRepository::class);
        $mockReservation->method('findById')->willReturn(null);
        $service = $this->creerService($this->createStub(InscriptionRepository::class), $mockReservation, $this->createStub(MembreRepository::class));
        $this->assertEquals('reservation_introuvable', $service->addJoueur(999, 1));
    }

    public function testAddJoueurRetourneMembreIntrouvable(): void {
        $mockReservation = $this->createStub(ReservationRepository::class);
        $mockReservation->method('findById')->willReturn($this->creerReservation(1));
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockMembre->method('findById')->willReturn(null);
        $service = $this->creerService($this->createStub(InscriptionRepository::class), $mockReservation, $mockMembre);
        $this->assertEquals('membre_introuvable', $service->addJoueur(1, 999));
    }

    public function testAddJoueurRetourneReservationComplete(): void {
        $mockInscription = $this->createStub(InscriptionRepository::class);
        $mockInscription->method('countByReservation')->willReturn(4);
        $mockReservation = $this->createStub(ReservationRepository::class);
        $mockReservation->method('findById')->willReturn($this->creerReservation(1));
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockMembre->method('findById')->willReturn($this->creerMembre(1));
        $service = $this->creerService($mockInscription, $mockReservation, $mockMembre);
        $this->assertEquals('reservation_complete', $service->addJoueur(1, 5));
    }

    public function testAddJoueurRetourneDejaInscrit(): void {
        $mockInscription = $this->createStub(InscriptionRepository::class);
        $mockInscription->method('countByReservation')->willReturn(2);
        $mockInscription->method('findByReservationAndMembre')->willReturn($this->creerInscription(1, 1, 2));
        $mockReservation = $this->createStub(ReservationRepository::class);
        $mockReservation->method('findById')->willReturn($this->creerReservation(1));
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockMembre->method('findById')->willReturn($this->creerMembre(2));
        $service = $this->creerService($mockInscription, $mockReservation, $mockMembre);
        $this->assertEquals('deja_inscrit', $service->addJoueur(1, 2));
    }

    public function testAddJoueurRetourneUnId(): void {
        $mockInscription = $this->createStub(InscriptionRepository::class);
        $mockInscription->method('countByReservation')->willReturn(1);
        $mockInscription->method('findByReservationAndMembre')->willReturn(null);
        $mockInscription->method('insert')->willReturn(3);
        $mockReservation = $this->createStub(ReservationRepository::class);
        $mockReservation->method('findById')->willReturn($this->creerReservation(1));
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockMembre->method('findById')->willReturn($this->creerMembre(2));
        $service = $this->creerService($mockInscription, $mockReservation, $mockMembre);
        $this->assertEquals(3, $service->addJoueur(1, 2));
    }


    // ─── removeJoueur ────────────────────────────────────────────────────────

    public function testRemoveJoueurRetourneTrueSiInscrit(): void {
        $mockInscription = $this->createStub(InscriptionRepository::class);
        $mockInscription->method('findByReservationAndMembre')->willReturn($this->creerInscription(1, 1, 2));
        $service = $this->creerService($mockInscription, $this->createStub(ReservationRepository::class), $this->createStub(MembreRepository::class));
        $this->assertTrue($service->removeJoueur(1, 2));
    }

    public function testRemoveJoueurRetourneFalseSiNonInscrit(): void {
        $mockInscription = $this->createStub(InscriptionRepository::class);
        $mockInscription->method('findByReservationAndMembre')->willReturn(null);
        $service = $this->creerService($mockInscription, $this->createStub(ReservationRepository::class), $this->createStub(MembreRepository::class));
        $this->assertFalse($service->removeJoueur(1, 999));
    }


    // ─── Match PUBLIC : restriction organisateur ──────────────────────────────

    public function testAddJoueurInterditOrganisateurMatchPublic(): void {
        $mockReservation = $this->createStub(ReservationRepository::class);
        $mockReservation->method('findById')->willReturn($this->creerReservation(1, 'PUBLIC', 1));
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockMembre->method('findById')->willReturn(new Membre(2, 'S00002', 'Dupont', 'Jean', null, null, 'S', 1, true));
        $service = $this->creerService($this->createStub(InscriptionRepository::class), $mockReservation, $mockMembre);
        $this->assertEquals('inscription_interdite_organisateur', $service->addJoueur(1, 2, 1));
    }

    public function testAddJoueurOrganisateurPeutSInscrireLuiMeme(): void {
        $mockReservation = $this->createStub(ReservationRepository::class);
        $mockReservation->method('findById')->willReturn($this->creerReservation(1, 'PUBLIC', 1));
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockMembre->method('findById')->willReturn(new Membre(1, 'G0001', 'Dupont', 'Jean', null, null, 'G', null, true));
        $mockInscription = $this->createStub(InscriptionRepository::class);
        $mockInscription->method('countByReservation')->willReturn(1);
        $mockInscription->method('findByReservationAndMembre')->willReturn(null);
        $mockInscription->method('insert')->willReturn(3);
        $service = $this->creerService($mockInscription, $mockReservation, $mockMembre);
        $this->assertEquals(3, $service->addJoueur(1, 1, 1));
    }

    public function testAddJoueurOrganisateurPeutInscrireDansMatchPrive(): void {
        $mockReservation = $this->createStub(ReservationRepository::class);
        $mockReservation->method('findById')->willReturn($this->creerReservation(1, 'PRIVE', 1));
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockMembre->method('findById')->willReturn(new Membre(2, 'S00002', 'Dupont', 'Jean', null, null, 'S', 1, true));
        $mockInscription = $this->createStub(InscriptionRepository::class);
        $mockInscription->method('countByReservation')->willReturn(1);
        $mockInscription->method('findByReservationAndMembre')->willReturn(null);
        $mockInscription->method('insert')->willReturn(3);
        $service = $this->creerService($mockInscription, $mockReservation, $mockMembre);
        $this->assertEquals(3, $service->addJoueur(1, 2, 1));
    }


    // ─── rejoindreMatchPublic ─────────────────────────────────────────────────

    public function testRejoindreMatchPublicRetourneMatchNonPublic(): void {
        $mockReservation = $this->createStub(ReservationRepository::class);
        $mockReservation->method('findById')->willReturn($this->creerReservation(1, 'PRIVE'));
        $service = $this->creerService($this->createStub(InscriptionRepository::class), $mockReservation, $this->createStub(MembreRepository::class));
        $this->assertEquals('match_non_public', $service->rejoindreMatchPublic(1, 1, ['montant' => 15.00]));
    }

    public function testRejoindreMatchPublicRetourneMontantInvalide(): void {
        $mockReservation = $this->createStub(ReservationRepository::class);
        $mockReservation->method('findById')->willReturn($this->creerReservation(1, 'PUBLIC'));
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockMembre->method('findById')->willReturn($this->creerMembre(2));
        $mockInscription = $this->createStub(InscriptionRepository::class);
        $mockInscription->method('countByReservation')->willReturn(1);
        $mockInscription->method('findByReservationAndMembre')->willReturn(null);
        $service = $this->creerService($mockInscription, $mockReservation, $mockMembre);
        $this->assertEquals('montant_invalide', $service->rejoindreMatchPublic(1, 2, ['montant' => 10.00]));
    }

    public function testRejoindreMatchPublicRetourneMethodeInvalide(): void {
        $mockReservation = $this->createStub(ReservationRepository::class);
        $mockReservation->method('findById')->willReturn($this->creerReservation(1, 'PUBLIC'));
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockMembre->method('findById')->willReturn($this->creerMembre(2));
        $mockInscription = $this->createStub(InscriptionRepository::class);
        $mockInscription->method('countByReservation')->willReturn(1);
        $mockInscription->method('findByReservationAndMembre')->willReturn(null);
        $service = $this->creerService($mockInscription, $mockReservation, $mockMembre);
        $this->assertEquals('methode_invalide', $service->rejoindreMatchPublic(1, 2, ['montant' => 15.00, 'methode' => 'BITCOIN']));
    }

    public function testRejoindreMatchPublicRetourneInscriptionId(): void {
        $mockReservation = $this->createStub(ReservationRepository::class);
        $mockReservation->method('findById')->willReturn($this->creerReservation(1, 'PUBLIC'));
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockMembre->method('findById')->willReturn($this->creerMembre(2));
        $mockInscription = $this->createStub(InscriptionRepository::class);
        $mockInscription->method('countByReservation')->willReturn(1);
        $mockInscription->method('findByReservationAndMembre')->willReturn(null);
        $mockInscription->method('insert')->willReturn(4);
        $mockPaiement = $this->createStub(PaiementRepository::class);
        $mockPaiement->method('insert')->willReturn(7);

        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $service = new InscriptionService($mockInscription, $mockReservation, $mockMembre, $mockPaiement, $pdo);
        $result = $service->rejoindreMatchPublic(1, 2, ['montant' => 15.00, 'methode' => 'CARTE']);
        $this->assertEquals(4, $result);
    }
}
