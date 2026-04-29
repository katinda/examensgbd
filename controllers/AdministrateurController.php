<?php

require_once __DIR__ . '/../services/AdministrateurService.php';

class AdministrateurController {

    public function __construct(private AdministrateurService $adminService) {}

    // DELETE /api/administrateurs/{id}
    public function delete(int $id): void {
        $ok = $this->adminService->deleteAdministrateur($id);

        header('Content-Type: application/json');

        if (!$ok) {
            http_response_code(404);
            echo json_encode(['erreur' => "Administrateur $id introuvable"]);
            return;
        }

        echo json_encode(['message' => "Administrateur $id désactivé avec succès"], JSON_UNESCAPED_UNICODE);
    }
}
