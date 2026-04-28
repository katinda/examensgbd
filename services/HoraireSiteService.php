<?php

require_once __DIR__ . '/../repositories/HoraireSiteRepository.php';

class HoraireSiteService {

    public function __construct(
        private HoraireSiteRepository $horaireRepository
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

    // Erreurs possibles :
    //   'annee_invalide'   → année hors de la plage 2000-2100 → 400
    //   'heures_invalides' → Heure_Debut >= Heure_Fin → 400
    //   'doublon'          → un horaire existe déjà pour ce site et cette année → 409
    public function createHoraire(array $data): int|string {
        $annee      = (int) ($data['annee'] ?? 0);
        $heureDebut = $data['heure_debut'] ?? '';
        $heureFin   = $data['heure_fin'] ?? '';
        $siteId     = (int) ($data['site_id'] ?? 0);

        if ($annee < 2000 || $annee > 2100) {
            return 'annee_invalide';
        }

        if ($heureDebut >= $heureFin) {
            return 'heures_invalides';
        }

        if ($this->horaireRepository->findBySiteAndAnnee($siteId, $annee) !== null) {
            return 'doublon';
        }

        $horaire = new HoraireSite(null, $siteId, $annee, $heureDebut, $heureFin);
        return $this->horaireRepository->insert($horaire);
    }

    // Retourne false si l'horaire n'existe pas.
    public function updateHoraire(int $id, array $data): bool {
        $horaire = $this->horaireRepository->findById($id);

        if ($horaire === null) {
            return false;
        }

        if (isset($data['annee']))       $horaire->setAnnee((int) $data['annee']);
        if (isset($data['heure_debut'])) $horaire->setHeureDebut($data['heure_debut']);
        if (isset($data['heure_fin']))   $horaire->setHeureFin($data['heure_fin']);

        $this->horaireRepository->update($horaire);
        return true;
    }

    // Retourne false si l'horaire n'existe pas.
    public function deleteHoraire(int $id): bool {
        $horaire = $this->horaireRepository->findById($id);

        if ($horaire === null) {
            return false;
        }

        $this->horaireRepository->delete($id);
        return true;
    }
}
