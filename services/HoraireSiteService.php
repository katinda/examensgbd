<?php

require_once __DIR__ . '/../repositories/HoraireSiteRepository.php';
require_once __DIR__ . '/../repositories/AdministrateurRepository.php';

class HoraireSiteService {

    public function __construct(
        private HoraireSiteRepository    $horaireRepository,
        private AdministrateurRepository $adminRepository
    ) {}

    public function getAllHoraires(): array {
        return $this->horaireRepository->findAll();
    }

    public function getHoraireById(int $id): ?HoraireSite {
        return $this->horaireRepository->findById($id);
    }

    public function getHorairesBySiteId(int $siteId): array {
        return $this->horaireRepository->findBySiteId($siteId);
    }

    public function getHoraireBySiteAndAnnee(int $siteId, int $annee): ?HoraireSite {
        return $this->horaireRepository->findBySiteAndAnnee($siteId, $annee);
    }

    // Crée un horaire.
    // GLOBAL peut créer sur n'importe quel site.
    // SITE ne peut créer que sur son propre site.
    //
    // Erreurs possibles :
    //   'admin_introuvable' → adminId inconnu → 404
    //   'acces_interdit'    → admin SITE essaie un autre site → 403
    //   'annee_invalide'    → année hors 2000-2100 → 400
    //   'heures_invalides'  → heure_debut >= heure_fin → 400
    //   'doublon'           → horaire déjà existant pour ce site/année → 409
    public function createHoraire(array $data, int $adminId): int|string {
        $admin = $this->adminRepository->findById($adminId);
        if ($admin === null) return 'admin_introuvable';

        $siteId = (int) ($data['site_id'] ?? 0);

        if ($admin->getType() === 'SITE' && $admin->getSiteId() !== $siteId) {
            return 'acces_interdit';
        }

        $annee      = (int) ($data['annee'] ?? 0);
        $heureDebut = $data['heure_debut'] ?? '';
        $heureFin   = $data['heure_fin']   ?? '';

        if ($annee < 2000 || $annee > 2100) return 'annee_invalide';
        if ($heureDebut >= $heureFin)        return 'heures_invalides';

        if ($this->horaireRepository->findBySiteAndAnnee($siteId, $annee) !== null) {
            return 'doublon';
        }

        $horaire = new HoraireSite(null, $siteId, $annee, $heureDebut, $heureFin);
        return $this->horaireRepository->insert($horaire);
    }

    // Met à jour un horaire.
    // GLOBAL peut modifier n'importe quel horaire.
    // SITE ne peut modifier que les horaires de son propre site.
    //
    // Retourne true, false (inexistant), ou une string d'erreur.
    public function updateHoraire(int $id, array $data, int $adminId): bool|string {
        $admin = $this->adminRepository->findById($adminId);
        if ($admin === null) return 'admin_introuvable';

        $horaire = $this->horaireRepository->findById($id);
        if ($horaire === null) return false;

        if ($admin->getType() === 'SITE' && $admin->getSiteId() !== $horaire->getSiteId()) {
            return 'acces_interdit';
        }

        if (isset($data['annee']))       $horaire->setAnnee((int) $data['annee']);
        if (isset($data['heure_debut'])) $horaire->setHeureDebut($data['heure_debut']);
        if (isset($data['heure_fin']))   $horaire->setHeureFin($data['heure_fin']);

        $this->horaireRepository->update($horaire);
        return true;
    }

    // Supprime un horaire.
    // GLOBAL peut supprimer n'importe quel horaire.
    // SITE ne peut supprimer que les horaires de son propre site.
    //
    // Retourne true, false (inexistant), ou une string d'erreur.
    public function deleteHoraire(int $id, int $adminId): bool|string {
        $admin = $this->adminRepository->findById($adminId);
        if ($admin === null) return 'admin_introuvable';

        $horaire = $this->horaireRepository->findById($id);
        if ($horaire === null) return false;

        if ($admin->getType() === 'SITE' && $admin->getSiteId() !== $horaire->getSiteId()) {
            return 'acces_interdit';
        }

        $this->horaireRepository->delete($id);
        return true;
    }
}
