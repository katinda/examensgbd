<?php

require_once __DIR__ . '/../services/AdministrateurService.php';

class AdministrateurController {

    public function __construct(private AdministrateurService $adminService) {}

    // PUT /api/administrateurs/{id}
    public function update(int $id): void {
        $data = json_decode(file_get_contents('php://input'), true);
        $ok   = $this->adminService->updateAdministrateur($id, $data);

        header('Content-Type: application/json');

        if (!$ok) {
            http_response_code(404);
            echo json_encode(['erreur' => "Administrateur $id introuvable"]);
            return;
        }

        echo json_encode(['message' => "Administrateur $id mis à jour avec succès"], JSON_UNESCAPED_UNICODE);
    }
}
