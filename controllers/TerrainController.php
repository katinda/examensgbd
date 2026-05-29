<?php

require_once __DIR__ . '/../services/TerrainService.php';

// Le controller reçoit les requêtes HTTP et renvoie du JSON.
// Il ne fait jamais de SQL et ne contient pas de logique métier.
// admin_id est attendu en query param (?admin_id=1) pour toutes les opérations d'écriture.

class TerrainController {

    public function __construct(private TerrainService $terrainService) {}


    // GET /terrains → retourne tous les terrains actifs en JSON
    public function getAll(): void {
        $terrains = $this->terrainService->getAllTerrains();

        $data = array_map(fn($t) => $this->toArray($t), $terrains);

        header('Content-Type: application/json');
        echo json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }


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


    // POST /terrains?admin_id={id} → crée un nouveau terrain
    public function create(): void {
        $data    = json_decode(file_get_contents('php://input'), true);
        $adminId = isset($_GET['admin_id']) ? (int) $_GET['admin_id'] : null;

        header('Content-Type: application/json');

        if ($adminId === null) {
            http_response_code(400);
            echo json_encode(['erreur' => 'Le paramètre "admin_id" est obligatoire']);
            return;
        }

        if (empty($data['site_id']) || empty($data['num_terrain'])) {
            http_response_code(400);
            echo json_encode(['erreur' => 'Les champs "site_id" et "num_terrain" sont obligatoires']);
            return;
        }

        $result = $this->terrainService->createTerrain($data, $adminId);

        if ($result === 'admin_introuvable') {
            http_response_code(404);
            echo json_encode(['erreur' => "Administrateur $adminId introuvable"]);
            return;
        }

        if ($result === 'acces_interdit') {
            http_response_code(403);
            echo json_encode(['erreur' => 'Vous ne pouvez créer des terrains que sur votre propre site']);
            return;
        }

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


    // PUT /terrains/{id}?admin_id={id} → met à jour un terrain existant
    public function update(int $id): void {
        $data    = json_decode(file_get_contents('php://input'), true);
        $adminId = isset($_GET['admin_id']) ? (int) $_GET['admin_id'] : null;

        header('Content-Type: application/json');

        if ($adminId === null) {
            http_response_code(400);
            echo json_encode(['erreur' => 'Le paramètre "admin_id" est obligatoire']);
            return;
        }

        $result = $this->terrainService->updateTerrain($id, $data ?? [], $adminId);

        if ($result === 'admin_introuvable') {
            http_response_code(404);
            echo json_encode(['erreur' => "Administrateur $adminId introuvable"]);
            return;
        }

        if ($result === 'acces_interdit') {
            http_response_code(403);
            echo json_encode(['erreur' => 'Vous ne pouvez modifier que les terrains de votre propre site']);
            return;
        }

        if ($result === false) {
            http_response_code(404);
            echo json_encode(['erreur' => "Terrain $id introuvable"]);
            return;
        }

        echo json_encode(['message' => "Terrain $id mis à jour avec succès"], JSON_UNESCAPED_UNICODE);
    }


    // DELETE /terrains/{id}?admin_id={id} → supprime un terrain
    public function delete(int $id): void {
        $adminId = isset($_GET['admin_id']) ? (int) $_GET['admin_id'] : null;

        header('Content-Type: application/json');

        if ($adminId === null) {
            http_response_code(400);
            echo json_encode(['erreur' => 'Le paramètre "admin_id" est obligatoire']);
            return;
        }

        $result = $this->terrainService->deleteTerrain($id, $adminId);

        if ($result === 'admin_introuvable') {
            http_response_code(404);
            echo json_encode(['erreur' => "Administrateur $adminId introuvable"]);
            return;
        }

        if ($result === 'acces_interdit') {
            http_response_code(403);
            echo json_encode(['erreur' => 'Vous ne pouvez supprimer que les terrains de votre propre site']);
            return;
        }

        if ($result === false) {
            http_response_code(404);
            echo json_encode(['erreur' => "Terrain $id introuvable"]);
            return;
        }

        echo json_encode(['message' => "Terrain $id supprimé avec succès"], JSON_UNESCAPED_UNICODE);
    }


    private function toArray(Terrain $t): array {
        return [
            'id'          => $t->getTerrainId(),
            'site_id'     => $t->getSiteId(),
            'num_terrain' => $t->getNumTerrain(),
            'libelle'     => $t->getLibelle(),
            'est_actif'   => $t->isEstActif(),
        ];
    }
}
