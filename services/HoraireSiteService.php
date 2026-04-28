<?php

require_once __DIR__ . '/../repositories/HoraireSiteRepository.php';

class HoraireSiteService {

    public function __construct(
        private HoraireSiteRepository $horaireRepository
    ) {}

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
