<?php

require_once __DIR__ . '/../services/TerrainService.php';

// Le controller reçoit les requêtes HTTP et renvoie du JSON.
// Il ne fait jamais de SQL et ne contient pas de logique métier.
// Son seul rôle : appeler le bon service et formater la réponse.

class TerrainController {

    // Le service est reçu en paramètre (injection de dépendance).
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


    // POST /terrains → crée un nouveau terrain
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

        // Le service retourne une string si erreur, un int si succès
        if ($result === 'site_introuvable') {
            http_response_code(404);
            echo json_encode(['erreur' => "Site {$data['site_id']} introuvable"]);
            return;
        }

        if ($result === 'doublon') {
            http_response_code(409); // 409 Conflict
            echo json_encode(['erreur' => "Le terrain {$data['num_terrain']} existe déjà pour ce site"]);
            return;
        }

        http_response_code(201);
        echo json_encode(['message' => 'Terrain créé avec succès', 'id' => $result], JSON_UNESCAPED_UNICODE);
    }


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


    // Transforme un objet Terrain en tableau simple pour le JSON
    // Méthode privée réutilisée par getAll(), getById() et getBySite()
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
