<?php

require_once __DIR__ . '/../repositories/InscriptionRepository.php';
require_once __DIR__ . '/../repositories/ReservationRepository.php';
require_once __DIR__ . '/../repositories/MembreRepository.php';

// Le service contient la logique métier des inscriptions.
// Il fait le lien entre le controller et les repositories.
// Il utilise trois repositories :
// - InscriptionRepository pour gérer les inscriptions
// - ReservationRepository pour vérifier que la réservation existe
// - MembreRepository pour vérifier que le membre existe et est actif

class InscriptionService {

    public function __construct(
        private InscriptionRepository $inscriptionRepository,
        private ReservationRepository $reservationRepository,
        private MembreRepository      $membreRepository
    ) {}


    // Retourne la liste des joueurs inscrits à une réservation,
    // enrichie avec les informations du membre (nom, prénom, matricule).
    public function getInscriptionsByReservation(int $reservationId): array {
        $inscriptions = $this->inscriptionRepository->findByReservation($reservationId);

        return array_map(function($i) {
            $membre = $this->membreRepository->findById($i->getMembreId());
            return [
                'id'               => $i->getInscriptionId(),
                'reservation_id'   => $i->getReservationId(),
                'membre_id'        => $i->getMembreId(),
                'nom'              => $membre?->getNom(),
                'prenom'           => $membre?->getPrenom(),
                'matricule'        => $membre?->getMatricule(),
                'est_organisateur' => $i->isEstOrganisateur(),
            ];
        }, $inscriptions);
    }


    // Ajoute un joueur à une réservation.
    // Retourne l'ID de l'inscription créée, ou une string décrivant l'erreur.
    //
    // Erreurs possibles :
    //   'reservation_introuvable' → la réservation n'existe pas → 404
    //   'membre_introuvable'      → le membre n'existe pas ou est inactif → 404
    //   'reservation_complete'    → la réservation a déjà 4 joueurs → 409
    //   'deja_inscrit'            → ce membre est déjà inscrit à cette réservation → 409
    public function addJoueur(int $reservationId, int $membreId): int|string {
        // Règle 1 : la réservation doit exister
        $reservation = $this->reservationRepository->findById($reservationId);
        if ($reservation === null) {
            return 'reservation_introuvable';
        }

        // Règle 2 : le membre doit exister et être actif
        $membre = $this->membreRepository->findById($membreId);
        if ($membre === null || !$membre->isEstActif()) {
            return 'membre_introuvable';
        }

        // Règle 3 : la réservation ne peut pas dépasser 4 joueurs
        if ($this->inscriptionRepository->countByReservation($reservationId) >= 4) {
            return 'reservation_complete';
        }

        // Règle 4 : un même joueur ne peut pas s'inscrire deux fois
        if ($this->inscriptionRepository->findByReservationAndMembre($reservationId, $membreId) !== null) {
            return 'deja_inscrit';
        }

        $inscription = new Inscription(null, $reservationId, $membreId, false);
        return $this->inscriptionRepository->insert($inscription);
    }


    // Retire un joueur d'une réservation.
    // Retourne false si l'inscription n'existe pas (joueur non inscrit à cette réservation).
    public function removeJoueur(int $reservationId, int $membreId): bool {
        $inscription = $this->inscriptionRepository->findByReservationAndMembre($reservationId, $membreId);

        if ($inscription === null) {
            return false;
        }

        $this->inscriptionRepository->delete($reservationId, $membreId);
        return true;
    }
}
