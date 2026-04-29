<?php

require_once __DIR__ . '/../services/PenaliteService.php';

class PenaliteController {

    public function __construct(private PenaliteService $penaliteService) {}

    // PATCH /api/penalites/{id}/lever
    public function lever(int $id): void {
        $data   = json_decode(file_get_contents('php://input'), true);
        $result = $this->penaliteService->leverPenalite($id, $data ?? []);

        header('Content-Type: application/json');

        match ($result) {
            'penalite_introuvable' => (function() use ($id) {
                http_response_code(404);
                echo json_encode(['erreur' => "Pénalité $id introuvable"]);
            })(),
            'deja_levee'           => (function() {
                http_response_code(409);
                echo json_encode(['erreur' => 'Cette pénalité est déjà levée']);
            })(),
            'admin_introuvable'    => (function() {
                http_response_code(404);
                echo json_encode(['erreur' => 'Administrateur introuvable']);
            })(),
            'admin_non_global'     => (function() {
                http_response_code(403);
                echo json_encode(['erreur' => 'Seul un administrateur GLOBAL peut lever une pénalité']);
            })(),
            default => (function() use ($id) {
                echo json_encode(['message' => "Pénalité $id levée avec succès"], JSON_UNESCAPED_UNICODE);
            })(),
        };
    }
}
