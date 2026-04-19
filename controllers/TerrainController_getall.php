<?php

// GET /terrains → retourne tous les terrains actifs en JSON
public function getAll(): void {
    $terrains = $this->terrainService->getAllTerrains();
    $data = array_map(fn($t) => $this->toArray($t), $terrains);
    header('Content-Type: application/json');
    echo json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
