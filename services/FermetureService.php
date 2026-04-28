<?php

require_once __DIR__ . '/../repositories/FermetureRepository.php';

class FermetureService {

    public function __construct(
        private FermetureRepository $fermetureRepository
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

    // Erreurs possibles :
    //   'dates_invalides' → Date_Debut > Date_Fin → 400
    public function createFermeture(array $data): int|string {
        $dateDebut = $data['date_debut'] ?? '';
        $dateFin   = $data['date_fin']   ?? '';

        if ($dateDebut > $dateFin) {
            return 'dates_invalides';
        }

        $fermeture = new Fermeture(
            null,
            isset($data['site_id']) ? (int) $data['site_id'] : null,
            $dateDebut,
            $dateFin,
            $data['raison'] ?? null
        );

        return $this->fermetureRepository->insert($fermeture);
    }

    // Retourne false si la fermeture n'existe pas.
    public function updateFermeture(int $id, array $data): bool {
        $fermeture = $this->fermetureRepository->findById($id);

        if ($fermeture === null) {
            return false;
        }

        if (isset($data['date_debut'])) $fermeture->setDateDebut($data['date_debut']);
        if (isset($data['date_fin']))   $fermeture->setDateFin($data['date_fin']);
        if (isset($data['raison']))     $fermeture->setRaison($data['raison']);

        $this->fermetureRepository->update($fermeture);
        return true;
    }

    // Retourne false si la fermeture n'existe pas.
    public function deleteFermeture(int $id): bool {
        $fermeture = $this->fermetureRepository->findById($id);

        if ($fermeture === null) {
            return false;
        }

        $this->fermetureRepository->delete($id);
        return true;
    }
}
