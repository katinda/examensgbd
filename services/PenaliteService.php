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


    // Admin SITE → uniquement les pénalités des membres S de son site.
    private function filtrerParAdmin(array $penalites, ?int $adminId): array {
        if ($adminId === null) return $penalites;

        $admin = $this->adminRepository->findById($adminId);
        if ($admin === null || $admin->getType() === 'GLOBAL') return $penalites;

        return array_values(array_filter($penalites, function($p) use ($admin) {
            $membre = $this->membreRepository->findById($p->getMembreId());
            return $membre !== null
                && $membre->getCategorie() === 'S'
                && $membre->getSiteId() === $admin->getSiteId();
        }));
    }


    // Retourne toutes les pénalités, filtrées selon le rôle admin si fourni.
    public function getAllPenalites(?int $adminId = null): array {
        return $this->filtrerParAdmin($this->penaliteRepository->findAll(), $adminId);
    }


    // Retourne une pénalité par son ID, ou null si elle n'existe pas
    public function getPenaliteById(int $id): ?Penalite {
        return $this->penaliteRepository->findById($id);
    }


    // Retourne toutes les pénalités d'un membre, filtrées selon le rôle admin si fourni.
    public function getPenalitesByMembreId(int $membreId, ?int $adminId = null): array {
        return $this->filtrerParAdmin($this->penaliteRepository->findByMembreId($membreId), $adminId);
    }


    // Retourne les pénalités non levées, filtrées selon le rôle admin si fourni.
    public function getPenalitesActives(?int $adminId = null): array {
        return $this->filtrerParAdmin($this->penaliteRepository->findActives(), $adminId);
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
