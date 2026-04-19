<?php

require_once __DIR__ . '/../services/SiteService.php';

// Le controller reçoit les requêtes HTTP et renvoie du JSON.
// Il ne fait jamais de SQL et ne contient pas de logique métier.

class SiteController {

    public function __construct(private SiteService $siteService) {}


    // GET /sites → retourne tous les sites actifs en JSON
    public function getAll(): void {
        $sites = $this->siteService->getAllSites();

        $data = array_map(fn($site) => [
            'id'            => $site->getSiteId(),
            'nom'           => $site->getNom(),
            'adresse'       => $site->getAdresse(),
            'ville'         => $site->getVille(),
            'code_postal'   => $site->getCodePostal(),
            'est_actif'     => $site->isEstActif(),
            'date_creation' => $site->getDateCreation(),
        ], $sites);

        header('Content-Type: application/json');
        echo json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }


    // GET /sites/{id} → retourne un site ou une erreur 404
    public function getById(int $id): void {
        $site = $this->siteService->getSiteById($id);

        if ($site === null) {
            header('Content-Type: application/json');
            http_response_code(404);
            echo json_encode(['erreur' => "Site $id introuvable"]);
            return;
        }

        header('Content-Type: application/json');
        echo json_encode([
            'id'            => $site->getSiteId(),
            'nom'           => $site->getNom(),
            'adresse'       => $site->getAdresse(),
            'ville'         => $site->getVille(),
            'code_postal'   => $site->getCodePostal(),
            'est_actif'     => $site->isEstActif(),
            'date_creation' => $site->getDateCreation(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }


    // POST /sites → crée un nouveau site
    public function create(): void {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['nom'])) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['erreur' => 'Le champ "nom" est obligatoire']);
            return;
        }

        $id = $this->siteService->createSite($data);

        header('Content-Type: application/json');
        http_response_code(201);
        echo json_encode(['message' => 'Site créé avec succès', 'id' => $id], JSON_UNESCAPED_UNICODE);
    }


    // PUT /sites/{id} → met à jour un site existant
    public function update(int $id): void {
        $data = json_decode(file_get_contents('php://input'), true);
        $ok   = $this->siteService->updateSite($id, $data);

        header('Content-Type: application/json');

        if (!$ok) {
            http_response_code(404);
            echo json_encode(['erreur' => "Site $id introuvable"]);
            return;
        }

        echo json_encode(['message' => "Site $id mis à jour avec succès"], JSON_UNESCAPED_UNICODE);
    }


    // DELETE /sites/{id} → supprime un site
    public function delete(int $id): void {
        $ok = $this->siteService->deleteSite($id);

        header('Content-Type: application/json');

        if (!$ok) {
            http_response_code(404);
            echo json_encode(['erreur' => "Site $id introuvable"]);
            return;
        }

        echo json_encode(['message' => "Site $id supprimé avec succès"], JSON_UNESCAPED_UNICODE);
    }
}
