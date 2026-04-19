<?php

// POST /terrains → crée un nouveau terrain
// Gère 3 cas d'erreur : champs manquants (400), site inexistant (404), doublon (409)
public function create(): void {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['site_id']) || empty($data['num_terrain'])) {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['erreur' => 'Les champs "site_id" et "num_terrain" sont obligatoires']);
        return;
    }

    $result = $this->terrainService->createTerrain($data);
    header('Content-Type: application/json');

    if ($result === 'site_introuvable') {
        http_response_code(404);
        echo json_encode(['erreur' => "Site {$data['site_id']} introuvable"]);
        return;
    }

    if ($result === 'doublon') {
        http_response_code(409);
        echo json_encode(['erreur' => "Le terrain {$data['num_terrain']} existe déjà pour ce site"]);
        return;
    }

    http_response_code(201);
    echo json_encode(['message' => 'Terrain créé avec succès', 'id' => $result], JSON_UNESCAPED_UNICODE);
}
