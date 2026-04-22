<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Paiement.php';
require_once __DIR__ . '/../models/Inscription.php';
require_once __DIR__ . '/../repositories/PaiementRepository.php';
require_once __DIR__ . '/../repositories/InscriptionRepository.php';
require_once __DIR__ . '/../services/PaiementService.php';

// On teste la logique métier de PaiementService avec des stubs.
// Aucune base de données réelle n'est utilisée.

class PaiementServiceTest extends TestCase {

    // Crée un service avec des stubs configurables
    private function creerService(
        PaiementRepository    $paiementRepo,
        InscriptionRepository $inscriptionRepo
    ): PaiementService {
        return new PaiementService($paiementRepo, $inscriptionRepo);
    }

    private function unePaiement(): Paiement {
        return new Paiement(1, 1, 15.00, '2026-04-22 10:00:00', 'CARTE');
    }

    private function uneInscription(): Inscription {
        return new Inscription(1, 1, 1, true);
    }


    // --- getPaiementByInscription ---

    // Vérifie que getPaiementByInscription() retourne 'inscription_introuvable' si l'inscription n'existe pas
    public function testGetPaiementByInscriptionRetourneInscriptionIntrouvable(): void {
        $inscriptionRepo = $this->createStub(InscriptionRepository::class);
        $inscriptionRepo->method('findById')->willReturn(null);

        $paiementRepo = $this->createStub(PaiementRepository::class);

        $service = $this->creerService($paiementRepo, $inscriptionRepo);
        $this->assertEquals('inscription_introuvable', $service->getPaiementByInscription(999));
    }


    // Vérifie que getPaiementByInscription() retourne 'paiement_introuvable' si l'inscription existe mais n'a pas de paiement
    public function testGetPaiementByInscriptionRetournePaiementIntrouvable(): void {
        $inscriptionRepo = $this->createStub(InscriptionRepository::class);
        $inscriptionRepo->method('findById')->willReturn($this->uneInscription());

        $paiementRepo = $this->createStub(PaiementRepository::class);
        $paiementRepo->method('findByInscription')->willReturn(null);

        $service = $this->creerService($paiementRepo, $inscriptionRepo);
        $this->assertEquals('paiement_introuvable', $service->getPaiementByInscription(1));
    }


    // Vérifie que getPaiementByInscription() retourne le paiement si tout va bien
    public function testGetPaiementByInscriptionRetourneLePaiement(): void {
        $inscriptionRepo = $this->createStub(InscriptionRepository::class);
        $inscriptionRepo->method('findById')->willReturn($this->uneInscription());

        $paiementRepo = $this->createStub(PaiementRepository::class);
        $paiementRepo->method('findByInscription')->willReturn($this->unePaiement());

        $service = $this->creerService($paiementRepo, $inscriptionRepo);
        $result = $service->getPaiementByInscription(1);
        $this->assertInstanceOf(Paiement::class, $result);
    }


    // --- createPaiement ---

    // Vérifie que createPaiement() retourne 'inscription_introuvable' si l'inscription n'existe pas
    public function testCreatePaiementRetourneInscriptionIntrouvable(): void {
        $inscriptionRepo = $this->createStub(InscriptionRepository::class);
        $inscriptionRepo->method('findById')->willReturn(null);

        $paiementRepo = $this->createStub(PaiementRepository::class);

        $service = $this->creerService($paiementRepo, $inscriptionRepo);
        $this->assertEquals('inscription_introuvable', $service->createPaiement(999, ['montant' => 15.00]));
    }


    // Vérifie que createPaiement() retourne 'paiement_deja_existant' si l'inscription a déjà un paiement
    public function testCreatePaiementRetournePaiementDejaExistant(): void {
        $inscriptionRepo = $this->createStub(InscriptionRepository::class);
        $inscriptionRepo->method('findById')->willReturn($this->uneInscription());

        $paiementRepo = $this->createStub(PaiementRepository::class);
        $paiementRepo->method('findByInscription')->willReturn($this->unePaiement());

        $service = $this->creerService($paiementRepo, $inscriptionRepo);
        $this->assertEquals('paiement_deja_existant', $service->createPaiement(1, ['montant' => 15.00]));
    }


    // Vérifie que createPaiement() retourne 'montant_invalide' si le montant n'est pas 15.00
    public function testCreatePaiementRetourneMontantInvalide(): void {
        $inscriptionRepo = $this->createStub(InscriptionRepository::class);
        $inscriptionRepo->method('findById')->willReturn($this->uneInscription());

        $paiementRepo = $this->createStub(PaiementRepository::class);
        $paiementRepo->method('findByInscription')->willReturn(null);

        $service = $this->creerService($paiementRepo, $inscriptionRepo);
        $this->assertEquals('montant_invalide', $service->createPaiement(1, ['montant' => 10.00]));
    }


    // Vérifie que createPaiement() retourne 'methode_invalide' si la méthode n'est pas dans la liste
    public function testCreatePaiementRetourneMethodeInvalide(): void {
        $inscriptionRepo = $this->createStub(InscriptionRepository::class);
        $inscriptionRepo->method('findById')->willReturn($this->uneInscription());

        $paiementRepo = $this->createStub(PaiementRepository::class);
        $paiementRepo->method('findByInscription')->willReturn(null);

        $service = $this->creerService($paiementRepo, $inscriptionRepo);
        $this->assertEquals('methode_invalide', $service->createPaiement(1, ['montant' => 15.00, 'methode' => 'PAYPAL']));
    }


    // Vérifie que createPaiement() retourne un ID si tout va bien
    public function testCreatePaiementRetourneUnId(): void {
        $inscriptionRepo = $this->createStub(InscriptionRepository::class);
        $inscriptionRepo->method('findById')->willReturn($this->uneInscription());

        $paiementRepo = $this->createStub(PaiementRepository::class);
        $paiementRepo->method('findByInscription')->willReturn(null);
        $paiementRepo->method('insert')->willReturn(3);

        $service = $this->creerService($paiementRepo, $inscriptionRepo);
        $result = $service->createPaiement(1, ['montant' => 15.00, 'methode' => 'CARTE']);
        $this->assertEquals(3, $result);
    }


    // --- annulerPaiement ---

    // Vérifie que annulerPaiement() retourne 'paiement_introuvable' si le paiement n'existe pas
    public function testAnnulerPaiementRetournePaiementIntrouvable(): void {
        $paiementRepo = $this->createStub(PaiementRepository::class);
        $paiementRepo->method('findById')->willReturn(null);

        $inscriptionRepo = $this->createStub(InscriptionRepository::class);

        $service = $this->creerService($paiementRepo, $inscriptionRepo);
        $this->assertEquals('paiement_introuvable', $service->annulerPaiement(999));
    }


    // Vérifie que annulerPaiement() retourne 'paiement_deja_annule' si le paiement est déjà annulé
    public function testAnnulerPaiementRetournePaiementDejaAnnule(): void {
        $paiementAnnule = new Paiement(1, 1, 15.00, '2026-04-22 10:00:00', 'CARTE', true, 15.00, '2026-04-22 11:00:00');

        $paiementRepo = $this->createStub(PaiementRepository::class);
        $paiementRepo->method('findById')->willReturn($paiementAnnule);

        $inscriptionRepo = $this->createStub(InscriptionRepository::class);

        $service = $this->creerService($paiementRepo, $inscriptionRepo);
        $this->assertEquals('paiement_deja_annule', $service->annulerPaiement(1));
    }


    // Vérifie que annulerPaiement() retourne true si l'annulation réussit
    public function testAnnulerPaiementRetourneTrue(): void {
        $paiementRepo = $this->createStub(PaiementRepository::class);
        $paiementRepo->method('findById')->willReturn($this->unePaiement());

        $inscriptionRepo = $this->createStub(InscriptionRepository::class);

        $service = $this->creerService($paiementRepo, $inscriptionRepo);
        $this->assertTrue($service->annulerPaiement(1));
    }
}
