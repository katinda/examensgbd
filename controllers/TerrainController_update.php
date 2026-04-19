<?php

// PUT /terrains/{id} → met à jour un terrain existant
public function update(int $id): void {
    $data = json_decode(file_get_contents('php://input'), true);
    $ok   = $this->terrainService->updateTerrain($id, $data);

    header('Content-Type: application/json');

    if (!$ok) {
        http_response_code(404);
        echo json_encode(['erreur' => "Terrain $id introuvable"]);
        return;
    }

    echo json_encode(['message' => "Terrain $id mis à jour avec succès"], JSON_UNESCAPED_UNICODE);
}
