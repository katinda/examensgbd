<?php

// GET /sites/{siteId}/terrains → retourne tous les terrains actifs d'un site
public function getBySite(int $siteId): void {
    $terrains = $this->terrainService->getTerrainsBySite($siteId);

    if ($terrains === null) {
        header('Content-Type: application/json');
        http_response_code(404);
        echo json_encode(['erreur' => "Site $siteId introuvable"]);
        return;
    }

    $data = array_map(fn($t) => $this->toArray($t), $terrains);
    header('Content-Type: application/json');
    echo json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
