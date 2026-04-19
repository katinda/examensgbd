<?php

// DELETE /terrains/{id} → supprime un terrain
public function delete(int $id): void {
    $ok = $this->terrainService->deleteTerrain($id);

    header('Content-Type: application/json');

    if (!$ok) {
        http_response_code(404);
        echo json_encode(['erreur' => "Terrain $id introuvable"]);
        return;
    }

    echo json_encode(['message' => "Terrain $id supprimé avec succès"], JSON_UNESCAPED_UNICODE);
}
