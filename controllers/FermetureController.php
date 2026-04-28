<?php
require_once __DIR__ . '/../services/FermetureService.php';
class FermetureController {
    public function __construct(private FermetureService $fermetureService) {}
    public function create(): void { $data = json_decode(file_get_contents('php://input'), true); if (empty($data['date_debut']) || empty($data['date_fin'])) { header('Content-Type: application/json'); http_response_code(400); echo json_encode(['erreur' => 'Les champs date_debut et date_fin sont obligatoires']); return; } $result = $this->fermetureService->createFermeture($data); header('Content-Type: application/json'); if ($result === 'dates_invalides') { http_response_code(400); echo json_encode(['erreur' => 'La date de début doit être inférieure ou égale à la date de fin']); } else { http_response_code(201); echo json_encode(['message' => 'Fermeture créée avec succès', 'id' => $result], JSON_UNESCAPED_UNICODE); } }
    private function toArray(Fermeture $f): array { return ['id' => $f->getFermetureId(), 'site_id' => $f->getSiteId(), 'date_debut' => $f->getDateDebut(), 'date_fin' => $f->getDateFin(), 'raison' => $f->getRaison(), 'date_creation' => $f->getDateCreation()]; }
}
