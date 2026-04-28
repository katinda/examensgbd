<?php
require_once __DIR__ . '/../services/FermetureService.php';
class FermetureController {
    public function __construct(private FermetureService $fermetureService) {}
    public function getAll(): void { $siteId = isset($_GET['site_id']) ? (int) $_GET['site_id'] : null; $globales = isset($_GET['globales']) && $_GET['globales'] === '1'; if ($globales) { $fermetures = $this->fermetureService->getFermeturesGlobales(); } elseif ($siteId !== null) { $fermetures = $this->fermetureService->getFermeturesBySiteId($siteId); } else { $fermetures = $this->fermetureService->getAllFermetures(); } header('Content-Type: application/json'); echo json_encode(array_map(fn($f) => $this->toArray($f), $fermetures), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); }
    private function toArray(Fermeture $f): array { return ['id' => $f->getFermetureId(), 'site_id' => $f->getSiteId(), 'date_debut' => $f->getDateDebut(), 'date_fin' => $f->getDateFin(), 'raison' => $f->getRaison(), 'date_creation' => $f->getDateCreation()]; }
}
