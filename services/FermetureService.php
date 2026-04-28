<?php
require_once __DIR__ . '/../repositories/FermetureRepository.php';
class FermetureService {
    public function __construct(private FermetureRepository $fermetureRepository) {}
    public function createFermeture(array $data): int|string { $dateDebut = $data['date_debut'] ?? ''; $dateFin = $data['date_fin'] ?? ''; if ($dateDebut > $dateFin) { return 'dates_invalides'; } $fermeture = new Fermeture(null, isset($data['site_id']) ? (int) $data['site_id'] : null, $dateDebut, $dateFin, $data['raison'] ?? null); return $this->fermetureRepository->insert($fermeture); }
}
