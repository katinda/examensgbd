<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Inscription.php';
require_once __DIR__ . '/../models/Reservation.php';
require_once __DIR__ . '/../models/Membre.php';
require_once __DIR__ . '/../repositories/InscriptionRepository.php';
require_once __DIR__ . '/../repositories/ReservationRepository.php';
require_once __DIR__ . '/../repositories/MembreRepository.php';
require_once __DIR__ . '/../services/InscriptionService.php';

// On teste la logique métier du InscriptionService.
// On utilise des stubs pour simuler les repositories.

class InscriptionServiceTest extends TestCase {

    private function creerInscription(int $id, int $reservationId, int $membreId, bool $organisateur = false): Inscription {
        return new Inscription($id, $reservationId, $membreId, $organisateur);
    }

    private function creerReservation(int $id): Reservation {
        return new Reservation($id, 1, 1, '2026-05-10', '10:00:00', '11:30:00', 'PRIVE');
    }

    private function creerMembre(int $id, bool $actif = true): Membre {
        return new Membre($id, 'G0001', 'Dupont', 'Jean', null, null, 'G', null, $actif);
    }


    // Vérifie que getInscriptionsByReservation() délègue bien au repository
    public function testGetInscriptionsByReservationRetourneLesInscriptions(): void {
        $mockInscription = $this->createStub(InscriptionRepository::class);
        $mockInscription->method('findByReservation')->willReturn([
            $this->creerInscription(1, 1, 1, true),
            $this->creerInscription(2, 1, 2, false),
        ]);
        $service = new InscriptionService($mockInscription, $this->createStub(ReservationRepository::class), $this->createStub(MembreRepository::class));
        $this->assertCount(2, $service->getInscriptionsByReservation(1));
    }


    // Vérifie que addJoueur() retourne 'reservation_introuvable' si la réservation n'existe pas
    public function testAddJoueurRetourneReservationIntrouvable(): void {
        $mockReservation = $this->createStub(ReservationRepository::class);
        $mockReservation->method('findById')->willReturn(null);
        $service = new InscriptionService($this->createStub(InscriptionRepository::class), $mockReservation, $this->createStub(MembreRepository::class));
        $this->assertEquals('reservation_introuvable', $service->addJoueur(999, 1));
    }


    // Vérifie que addJoueur() retourne 'membre_introuvable' si le membre n'existe pas
    public function testAddJoueurRetourneMembreIntrouvable(): void {
        $mockReservation = $this->createStub(ReservationRepository::class);
        $mockReservation->method('findById')->willReturn($this->creerReservation(1));
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockMembre->method('findById')->willReturn(null);
        $service = new InscriptionService($this->createStub(InscriptionRepository::class), $mockReservation, $mockMembre);
        $this->assertEquals('membre_introuvable', $service->addJoueur(1, 999));
    }


    // Vérifie que addJoueur() retourne 'reservation_complete' si la réservation a déjà 4 joueurs
    public function testAddJoueurRetourneReservationComplete(): void {
        $mockInscription = $this->createStub(InscriptionRepository::class);
        $mockInscription->method('countByReservation')->willReturn(4);
        $mockReservation = $this->createStub(ReservationRepository::class);
        $mockReservation->method('findById')->willReturn($this->creerReservation(1));
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockMembre->method('findById')->willReturn($this->creerMembre(1));
        $service = new InscriptionService($mockInscription, $mockReservation, $mockMembre);
        $this->assertEquals('reservation_complete', $service->addJoueur(1, 5));
    }


    // Vérifie que addJoueur() retourne 'deja_inscrit' si le membre est déjà inscrit
    public function testAddJoueurRetourneDejaInscrit(): void {
        $mockInscription = $this->createStub(InscriptionRepository::class);
        $mockInscription->method('countByReservation')->willReturn(2);
        $mockInscription->method('findByReservationAndMembre')->willReturn($this->creerInscription(1, 1, 2));
        $mockReservation = $this->createStub(ReservationRepository::class);
        $mockReservation->method('findById')->willReturn($this->creerReservation(1));
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockMembre->method('findById')->willReturn($this->creerMembre(2));
        $service = new InscriptionService($mockInscription, $mockReservation, $mockMembre);
        $this->assertEquals('deja_inscrit', $service->addJoueur(1, 2));
    }


    // Vérifie que addJoueur() retourne un ID si tout est valide
    public function testAddJoueurRetourneUnId(): void {
        $mockInscription = $this->createStub(InscriptionRepository::class);
        $mockInscription->method('countByReservation')->willReturn(1);
        $mockInscription->method('findByReservationAndMembre')->willReturn(null);
        $mockInscription->method('insert')->willReturn(3);
        $mockReservation = $this->createStub(ReservationRepository::class);
        $mockReservation->method('findById')->willReturn($this->creerReservation(1));
        $mockMembre = $this->createStub(MembreRepository::class);
        $mockMembre->method('findById')->willReturn($this->creerMembre(2));
        $service = new InscriptionService($mockInscription, $mockReservation, $mockMembre);
        $this->assertEquals(3, $service->addJoueur(1, 2));
    }


    // Vérifie que removeJoueur() retourne true si le joueur est inscrit
    public function testRemoveJoueurRetourneTrueSiInscrit(): void {
        $mockInscription = $this->createStub(InscriptionRepository::class);
        $mockInscription->method('findByReservationAndMembre')->willReturn($this->creerInscription(1, 1, 2));
        $service = new InscriptionService($mockInscription, $this->createStub(ReservationRepository::class), $this->createStub(MembreRepository::class));
        $this->assertTrue($service->removeJoueur(1, 2));
    }


    // Vérifie que removeJoueur() retourne false si le joueur n'est pas inscrit
    public function testRemoveJoueurRetourneFalseSiNonInscrit(): void {
        $mockInscription = $this->createStub(InscriptionRepository::class);
        $mockInscription->method('findByReservationAndMembre')->willReturn(null);
        $service = new InscriptionService($mockInscription, $this->createStub(ReservationRepository::class), $this->createStub(MembreRepository::class));
        $this->assertFalse($service->removeJoueur(1, 999));
    }
}
