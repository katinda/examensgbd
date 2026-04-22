<?php

require_once __DIR__ . '/../models/Paiement.php';
require_once __DIR__ . '/../repositories/PaiementRepository.php';
require_once __DIR__ . '/../repositories/InscriptionRepository.php';

// Contient la logique métier des paiements.
// Utilise deux repositories : PaiementRepository + InscriptionRepository.

class PaiementService {

    private const MONTANT_PART    = 15.00;
    private const METHODES_VALIDES = ['CARTE', 'VIREMENT', 'ESPECES', 'MOBILE'];

    public function __construct(
        private PaiementRepository   $paiementRepository,
        private InscriptionRepository $inscriptionRepository
    ) {}


    // Retourne le paiement d'une inscription, ou une string d'erreur
    //
    // Erreurs possibles :
    //   'inscription_introuvable' → l'inscription n'existe pas → 404
    //   'paiement_introuvable'    → l'inscription existe mais n'a pas encore de paiement → 404
    public function getPaiementByInscription(int $inscriptionId): Paiement|string {
        if ($this->inscriptionRepository->findById($inscriptionId) === null) {
            return 'inscription_introuvable';
        }

        $paiement = $this->paiementRepository->findByInscription($inscriptionId);

        if ($paiement === null) {
            return 'paiement_introuvable';
        }

        return $paiement;
    }


    // Enregistre le paiement d'une inscription.
    // Retourne l'ID du paiement créé, ou une string décrivant l'erreur.
    //
    // Erreurs possibles :
    //   'inscription_introuvable' → l'inscription n'existe pas → 404
    //   'paiement_deja_existant'  → cette inscription a déjà un paiement → 409
    //   'montant_invalide'        → le montant n'est pas 15.00 → 400
    //   'methode_invalide'        → la méthode n'est pas dans la liste autorisée → 400
    public function createPaiement(int $inscriptionId, array $data): int|string {
        // Règle 1 : l'inscription doit exister
        $inscription = $this->inscriptionRepository->findById($inscriptionId);
        if ($inscription === null) {
            return 'inscription_introuvable';
        }

        // Règle 2 : l'inscription ne doit pas déjà avoir un paiement
        if ($this->paiementRepository->findByInscription($inscriptionId) !== null) {
            return 'paiement_deja_existant';
        }

        // Règle 3 : le montant doit être exactement 15.00
        $montant = isset($data['montant']) ? (float) $data['montant'] : null;
        if ($montant === null || $montant !== self::MONTANT_PART) {
            return 'montant_invalide';
        }

        // Règle 4 : la méthode de paiement doit être valide si fournie
        $methode = $data['methode'] ?? null;
        if ($methode !== null && !in_array($methode, self::METHODES_VALIDES, true)) {
            return 'methode_invalide';
        }

        $paiement = new Paiement(null, $inscriptionId, self::MONTANT_PART, null, $methode);
        return $this->paiementRepository->insert($paiement);
    }


    // Annule un paiement : remboursement total + horodatage.
    // Retourne true si annulé, ou une string décrivant l'erreur.
    //
    // Erreurs possibles :
    //   'paiement_introuvable' → le paiement n'existe pas → 404
    //   'paiement_deja_annule' → le paiement est déjà annulé → 409
    public function annulerPaiement(int $paiementId): bool|string {
        $paiement = $this->paiementRepository->findById($paiementId);

        if ($paiement === null) {
            return 'paiement_introuvable';
        }

        if ($paiement->isEstAnnule()) {
            return 'paiement_deja_annule';
        }

        $paiement->annuler(
            montantRembourse: $paiement->getMontant(),
            dateAnnulation:   (new DateTime())->format('Y-m-d H:i:s')
        );

        $this->paiementRepository->update($paiement);
        return true;
    }
}
