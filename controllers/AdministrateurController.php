<?php

require_once __DIR__ . '/../services/AdministrateurService.php';

class AdministrateurController {

    public function __construct(private AdministrateurService $adminService) {}

    // GET /api/administrateurs
    public function getAll(): void {
        $admins = $this->adminService->getAllAdministrateurs();

        header('Content-Type: application/json');
        echo json_encode(array_map(fn($a) => $this->toArray($a), $admins), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
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
