<?php

// GET /terrains/{id} → retourne un terrain ou une erreur 404
public function getById(int $id): void {
    $terrain = $this->terrainService->getTerrainById($id);

    if ($terrain === null) {
        header('Content-Type: application/json');
        http_response_code(404);
        echo json_encode(['erreur' => "Terrain $id introuvable"]);
        return;
    }

    header('Content-Type: application/json');
    echo json_encode($this->toArray($terrain), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
