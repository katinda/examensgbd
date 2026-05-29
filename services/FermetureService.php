<?php

require_once __DIR__ . '/../repositories/FermetureRepository.php';
require_once __DIR__ . '/../repositories/AdministrateurRepository.php';

class FermetureService {

    public function __construct(
        private FermetureRepository      $fermetureRepository,
        private AdministrateurRepository $adminRepository
    ) {}

    public function getAllFermetures(): array {
        return $this->fermetureRepository->findAll();
    }

    public function getFermetureById(int $id): ?Fermeture {
        return $this->fermetureRepository->findById($id);
    }

    public function getFermeturesBySiteId(int $siteId): array {
        return $this->fermetureRepository->findBySiteId($siteId);
    }

    public function getFermeturesGlobales(): array {
        return $this->fermetureRepository->findGlobales();
    }

    // Crée une fermeture.
    // Fermeture globale (site_id absent/null) → GLOBAL uniquement.
    // Fermeture de site → GLOBAL ou SITE (son site uniquement).
    //
    // Erreurs possibles :
    //   'admin_introuvable' → adminId inconnu → 404
    //   'acces_interdit'    → droits insuffisants → 403
    //   'dates_invalides'   → date_debut > date_fin → 400
    public function createFermeture(array $data, int $adminId): int|string {
        $admin = $this->adminRepository->findById($adminId);
        if ($admin === null) return 'admin_introuvable';

        $siteId = isset($data['site_id']) ? (int) $data['site_id'] : null;

        if ($siteId === null && $admin->getType() !== 'GLOBAL') {
            return 'acces_interdit'; // seul GLOBAL peut créer une fermeture globale
        }

        if ($siteId !== null && $admin->getType() === 'SITE' && $admin->getSiteId() !== $siteId) {
            return 'acces_interdit'; // SITE ne peut créer que sur son propre site
        }

        $dateDebut = $data['date_debut'] ?? '';
        $dateFin   = $data['date_fin']   ?? '';

        if ($dateDebut > $dateFin) return 'dates_invalides';

        $fermeture = new Fermeture(null, $siteId, $dateDebut, $dateFin, $data['raison'] ?? null);
        return $this->fermetureRepository->insert($fermeture);
    }

    // Met à jour une fermeture.
    // Fermeture globale → GLOBAL uniquement.
    // Fermeture de site → GLOBAL ou SITE (son site uniquement).
    //
    // Retourne true, false (inexistante), ou une string d'erreur.
    public function updateFermeture(int $id, array $data, int $adminId): bool|string {
        $admin = $this->adminRepository->findById($adminId);
        if ($admin === null) return 'admin_introuvable';

        $fermeture = $this->fermetureRepository->findById($id);
        if ($fermeture === null) return false;

        $siteId = $fermeture->getSiteId();

        if ($siteId === null && $admin->getType() !== 'GLOBAL') {
            return 'acces_interdit';
        }

        if ($siteId !== null && $admin->getType() === 'SITE' && $admin->getSiteId() !== $siteId) {
            return 'acces_interdit';
        }

        if (isset($data['date_debut'])) $fermeture->setDateDebut($data['date_debut']);
        if (isset($data['date_fin']))   $fermeture->setDateFin($data['date_fin']);
        if (isset($data['raison']))     $fermeture->setRaison($data['raison']);

        $this->fermetureRepository->update($fermeture);
        return true;
    }

    // Supprime une fermeture.
    // Fermeture globale → GLOBAL uniquement.
    // Fermeture de site → GLOBAL ou SITE (son site uniquement).
    //
    // Retourne true, false (inexistante), ou une string d'erreur.
    public function deleteFermeture(int $id, int $adminId): bool|string {
        $admin = $this->adminRepository->findById($adminId);
        if ($admin === null) return 'admin_introuvable';

        $fermeture = $this->fermetureRepository->findById($id);
        if ($fermeture === null) return false;

        $siteId = $fermeture->getSiteId();

        if ($siteId === null && $admin->getType() !== 'GLOBAL') {
            return 'acces_interdit';
        }

        if ($siteId !== null && $admin->getType() === 'SITE' && $admin->getSiteId() !== $siteId) {
            return 'acces_interdit';
        }

        $this->fermetureRepository->delete($id);
        return true;
    }
}
