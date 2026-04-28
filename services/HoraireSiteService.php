<?php

require_once __DIR__ . '/../repositories/HoraireSiteRepository.php';

class HoraireSiteService {

    public function __construct(
        private HoraireSiteRepository $horaireRepository
    ) {}

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
}
