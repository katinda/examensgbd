<?php

require_once __DIR__ . '/../services/AdministrateurService.php';

class AdministrateurController {

    public function __construct(private AdministrateurService $adminService) {}

    // GET /api/administrateurs/{id}
    public function getById(int $id): void {
        $admin = $this->adminService->getAdministrateurById($id);

        header('Content-Type: application/json');

        if ($admin === null) {
            http_response_code(404);
            echo json_encode(['erreur' => "Administrateur $id introuvable"]);
            return;
        }

        echo json_encode($this->toArray($admin), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    private function toArray(Administrateur $a): array {
        return [
            'id'            => $a->getAdminId(),
            'login'         => $a->getLogin(),
            'nom'           => $a->getNom(),
            'prenom'        => $a->getPrenom(),
            'email'         => $a->getEmail(),
            'type'          => $a->getType(),
            'site_id'       => $a->getSiteId(),
            'est_actif'     => $a->isEstActif(),
            'date_creation' => $a->getDateCreation(),
        ];
    }
}
