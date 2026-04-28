<?php

require_once __DIR__ . '/../repositories/HoraireSiteRepository.php';

class HoraireSiteService {

    public function __construct(
        private HoraireSiteRepository $horaireRepository
    ) {}

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
}
