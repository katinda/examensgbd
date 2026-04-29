<?php

require_once __DIR__ . '/../services/AdministrateurService.php';

class AdministrateurController {

    public function __construct(private AdministrateurService $adminService) {}

    // POST /api/administrateurs
    public function create(): void {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['login']) || empty($data['mot_de_passe']) || empty($data['type'])) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['erreur' => 'Les champs "login", "mot_de_passe" et "type" sont obligatoires']);
            return;
        }

        $result = $this->adminService->createAdministrateur($data);

        header('Content-Type: application/json');

        match ($result) {
            'type_invalide'    => (function() {
                http_response_code(400);
                echo json_encode(['erreur' => 'Le type doit être GLOBAL ou SITE']);
            })(),
            'site_requis'      => (function() {
                http_response_code(400);
                echo json_encode(['erreur' => 'Un administrateur de type SITE doit avoir un site_id']);
            })(),
            'site_interdit'    => (function() {
                http_response_code(400);
                echo json_encode(['erreur' => 'Un administrateur de type GLOBAL ne peut pas avoir de site_id']);
            })(),
            'site_introuvable' => (function() {
                http_response_code(404);
                echo json_encode(['erreur' => 'Site introuvable']);
            })(),
            'doublon_login'    => (function() {
                http_response_code(409);
                echo json_encode(['erreur' => 'Ce login est déjà utilisé']);
            })(),
            default => (function() use ($result) {
                http_response_code(201);
                echo json_encode(['message' => 'Administrateur créé avec succès', 'id' => $result], JSON_UNESCAPED_UNICODE);
            })(),
        };
    }
}
