<?php
require_once __DIR__ . '/../services/FermetureService.php';
class FermetureController {
    public function __construct(private FermetureService $fermetureService) {}
    public function delete(int $id): void { $ok = $this->fermetureService->deleteFermeture($id); header('Content-Type: application/json'); if (!$ok) { http_response_code(404); echo json_encode(['erreur' => "Fermeture $id introuvable"]); return; } echo json_encode(['message' => "Fermeture $id supprimée avec succès"], JSON_UNESCAPED_UNICODE); }
    private function toArray(Fermeture $f): array { return ['id' => $f->getFermetureId(), 'site_id' => $f->getSiteId(), 'date_debut' => $f->getDateDebut(), 'date_fin' => $f->getDateFin(), 'raison' => $f->getRaison(), 'date_creation' => $f->getDateCreation()]; }
}
