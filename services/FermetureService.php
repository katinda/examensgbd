<?php
require_once __DIR__ . '/../repositories/FermetureRepository.php';
class FermetureService {
    public function __construct(private FermetureRepository $fermetureRepository) {}
    public function updateFermeture(int $id, array $data): bool { $fermeture = $this->fermetureRepository->findById($id); if ($fermeture === null) { return false; } if (isset($data['date_debut'])) $fermeture->setDateDebut($data['date_debut']); if (isset($data['date_fin'])) $fermeture->setDateFin($data['date_fin']); if (isset($data['raison'])) $fermeture->setRaison($data['raison']); $this->fermetureRepository->update($fermeture); return true; }
}
