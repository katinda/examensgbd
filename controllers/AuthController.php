<?php

require_once __DIR__ . '/../services/AuthService.php';

// Gère l'authentification des administrateurs.
// POST /api/auth → vérifie login + mot_de_passe, retourne les infos admin si valide.

class AuthController {

    public function __construct(private AuthService $authService) {}


    public function login(): void {
        $data      = json_decode(file_get_contents('php://input'), true) ?? [];
        $login     = $data['login']       ?? '';
        $motDePasse = $data['mot_de_passe'] ?? '';

        header('Content-Type: application/json');

        if (empty($login) || empty($motDePasse)) {
            http_response_code(400);
            echo json_encode(['erreur' => 'Les champs "login" et "mot_de_passe" sont obligatoires']);
            return;
        }

        $result = $this->authService->authentifier($login, $motDePasse);

        match ($result) {
            'identifiants_invalides' => (function() {
                http_response_code(401);
                echo json_encode(['erreur' => 'Login ou mot de passe incorrect']);
            })(),
            'compte_inactif' => (function() {
                http_response_code(403);
                echo json_encode(['erreur' => 'Ce compte administrateur est désactivé']);
            })(),
            default => (function() use ($result) {
                echo json_encode([
                    'id'      => $result->getAdminId(),
                    'login'   => $result->getLogin(),
                    'nom'     => $result->getNom(),
                    'prenom'  => $result->getPrenom(),
                    'type'    => $result->getType(),
                    'site_id' => $result->getSiteId(),
                ], JSON_UNESCAPED_UNICODE);
            })(),
        };
    }
}
