<?php

require_once __DIR__ . '/../services/PenaliteService.php';

class PenaliteController {

    public function __construct(private PenaliteService $penaliteService) {}

    // DELETE /api/penalites/{id}
    public function delete(int $id): void {
        $ok = $this->penaliteService->deletePenalite($id);

        header('Content-Type: application/json');

        if (!$ok) {
            http_response_code(404);
            echo json_encode(['erreur' => "Pénalité $id introuvable"]);
            return;
        }

        echo json_encode(['message' => "Pénalité $id supprimée avec succès"], JSON_UNESCAPED_UNICODE);
    }
}
