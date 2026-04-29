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

    // Lève une pénalité. Erreurs : 'penalite_introuvable', 'deja_levee', 'admin_introuvable', 'admin_non_global'
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
}
