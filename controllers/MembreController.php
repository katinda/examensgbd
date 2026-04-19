<?php
require_once __DIR__ . '/../services/MembreService.php';
class MembreController {
    public function __construct(private MembreService $membreService) {}
    public function delete(int $id): void {
        $ok = $this->membreService->deleteMembre($id);
        header('Content-Type: application/json');
        if (!$ok) { http_response_code(404); echo json_encode(['erreur' => "Membre $id introuvable"]); return; }
        echo json_encode(['message' => "Membre $id désactivé avec succès"], JSON_UNESCAPED_UNICODE);
    }
}
