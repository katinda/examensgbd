<?php
require_once __DIR__ . '/../services/MembreService.php';
class MembreController {
    public function __construct(private MembreService $membreService) {}
    public function create(): void {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['matricule']) || empty($data['nom']) || empty($data['prenom']) || empty($data['categorie'])) {
            header('Content-Type: application/json'); http_response_code(400);
            echo json_encode(['erreur' => 'Les champs "matricule", "nom", "prenom" et "categorie" sont obligatoires']); return;
        }
        $result = $this->membreService->createMembre($data);
        header('Content-Type: application/json');
        match ($result) {
            'matricule_invalide' => (function() use ($data) { http_response_code(400); echo json_encode(['erreur' => "Format de matricule invalide : {$data['matricule']}"]); })(),
            'site_requis'        => (function() { http_response_code(400); echo json_encode(['erreur' => 'Un membre de catégorie S doit avoir un site_id']); })(),
            'site_interdit'      => (function() { http_response_code(400); echo json_encode(['erreur' => 'Un membre de catégorie G ou L ne peut pas avoir de site_id']); })(),
            'site_introuvable'   => (function() use ($data) { http_response_code(404); echo json_encode(['erreur' => "Site {$data['site_id']} introuvable"]); })(),
            'doublon_matricule'  => (function() use ($data) { http_response_code(409); echo json_encode(['erreur' => "Le matricule {$data['matricule']} existe déjà"]); })(),
            default              => (function() use ($result) { http_response_code(201); echo json_encode(['message' => 'Membre créé avec succès', 'id' => $result], JSON_UNESCAPED_UNICODE); })(),
        };
    }
}
