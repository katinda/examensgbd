<?php

require_once __DIR__ . '/../repositories/PenaliteRepository.php';
require_once __DIR__ . '/../repositories/MembreRepository.php';
require_once __DIR__ . '/../repositories/AdministrateurRepository.php';

class PenaliteService {

    private const CAUSES_VALIDES = ['PRIVATE_INCOMPLETE', 'PAYMENT_MISSING', 'OTHER'];

    public function __construct(
        private PenaliteRepository       $penaliteRepository,
        private MembreRepository         $membreRepository,
        private AdministrateurRepository $adminRepository
    ) {}


    // Retourne toutes les pénalités
    public function getAllPenalites(): array {
        return $this->penaliteRepository->findAll();
    }


    // Retourne une pénalité par son ID, ou null si elle n'existe pas
    public function getPenaliteById(int $id): ?Penalite {
        return $this->penaliteRepository->findById($id);
    }


    // Retourne toutes les pénalités d'un membre
    public function getPenalitesByMembreId(int $membreId): array {
        return $this->penaliteRepository->findByMembreId($membreId);
    }


    // Retourne les pénalités non levées
    public function getPenalitesActives(): array {
        return $this->penaliteRepository->findActives();
    }


    // Crée une nouvelle pénalité.
    // Retourne l'ID créé, ou une string décrivant l'erreur.
    //
    // Erreurs possibles :
    //   'cause_invalide'    → cause pas dans PRIVATE_INCOMPLETE / PAYMENT_MISSING / OTHER → 400
    //   'dates_invalides'   → date_debut > date_fin → 400
    //   'membre_introuvable'→ Membre_ID inexistant → 404
    public function createPenalite(array $data): int|string {
        $cause     = $data['cause']      ?? '';
        $dateDebut = $data['date_debut'] ?? '';
        $dateFin   = $data['date_fin']   ?? '';
        $membreId  = isset($data['membre_id']) ? (int) $data['membre_id'] : 0;

        if (!in_array($cause, self::CAUSES_VALIDES, true)) {
            return 'cause_invalide';
        }

        if ($dateDebut > $dateFin) {
            return 'dates_invalides';
        }

        if ($this->membreRepository->findById($membreId) === null) {
            return 'membre_introuvable';
        }

        $penalite = new Penalite(
            null,
            $membreId,
            isset($data['reservation_id']) ? (int) $data['reservation_id'] : null,
            $dateDebut,
            $dateFin,
            $cause
        );

        return $this->penaliteRepository->insert($penalite);
    }


    // Lève une pénalité manuellement.
    // Retourne true en cas de succès, ou une string décrivant l'erreur.
    //
    // Erreurs possibles :
    //   'penalite_introuvable' → la pénalité n'existe pas → 404
    //   'deja_levee'           → la pénalité est déjà levée → 409
    //   'admin_introuvable'    → l'admin n'existe pas → 404
    //   'admin_non_global'     → seul un admin GLOBAL peut lever une pénalité → 403
    public function leverPenalite(int $id, array $data): bool|string {
        $penalite = $this->penaliteRepository->findById($id);

        if ($penalite === null) {
            return 'penalite_introuvable';
        }

        if ($penalite->isLevee()) {
            return 'deja_levee';
        }

        $adminId = isset($data['admin_id']) ? (int) $data['admin_id'] : 0;
        $admin   = $this->adminRepository->findById($adminId);

        if ($admin === null) {
            return 'admin_introuvable';
        }

        if ($admin->getType() !== 'GLOBAL') {
            return 'admin_non_global';
        }

        $penalite->setLevee(true);
        $penalite->setLeveePar($adminId);
        $penalite->setLeveeLe(date('Y-m-d H:i:s'));
        $penalite->setLeveeRaison($data['raison'] ?? '');

        $this->penaliteRepository->update($penalite);
        return true;
    }


    // Supprime une pénalité.
    // Retourne false si la pénalité n'existe pas.
    public function deletePenalite(int $id): bool {
        $penalite = $this->penaliteRepository->findById($id);

        if ($penalite === null) {
            return false;
        }

        $this->penaliteRepository->delete($id);
        return true;
    }
}
