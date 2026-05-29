<?php

require_once __DIR__ . '/../services/SiteService.php';

// Le controller reçoit les requêtes HTTP et renvoie du JSON.
// Il ne fait jamais de SQL et ne contient pas de logique métier.
// admin_id est attendu en query param (?admin_id=1) pour toutes les opérations d'écriture.

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


    // POST /sites?admin_id={id} → crée un nouveau site (GLOBAL uniquement)
    public function create(): void {
        $data    = json_decode(file_get_contents('php://input'), true);
        $adminId = isset($_GET['admin_id']) ? (int) $_GET['admin_id'] : null;

        header('Content-Type: application/json');

        if ($adminId === null) {
            http_response_code(400);
            echo json_encode(['erreur' => 'Le paramètre "admin_id" est obligatoire']);
            return;
        }

        if (empty($data['nom'])) {
            http_response_code(400);
            echo json_encode(['erreur' => 'Le champ "nom" est obligatoire']);
            return;
        }

        $result = $this->siteService->createSite($data, $adminId);

        if ($result === 'admin_introuvable') {
            http_response_code(404);
            echo json_encode(['erreur' => "Administrateur $adminId introuvable"]);
            return;
        }

        if ($result === 'acces_interdit') {
            http_response_code(403);
            echo json_encode(['erreur' => 'Seul un administrateur GLOBAL peut créer un site']);
            return;
        }

        http_response_code(201);
        echo json_encode(['message' => 'Site créé avec succès', 'id' => $result], JSON_UNESCAPED_UNICODE);
    }


    // PUT /sites/{id}?admin_id={id} → met à jour un site existant
    public function update(int $id): void {
        $data    = json_decode(file_get_contents('php://input'), true);
        $adminId = isset($_GET['admin_id']) ? (int) $_GET['admin_id'] : null;

        header('Content-Type: application/json');

        if ($adminId === null) {
            http_response_code(400);
            echo json_encode(['erreur' => 'Le paramètre "admin_id" est obligatoire']);
            return;
        }

        $result = $this->siteService->updateSite($id, $data ?? [], $adminId);

        if ($result === 'admin_introuvable') {
            http_response_code(404);
            echo json_encode(['erreur' => "Administrateur $adminId introuvable"]);
            return;
        }

        if ($result === 'acces_interdit') {
            http_response_code(403);
            echo json_encode(['erreur' => 'Vous ne pouvez modifier que votre propre site']);
            return;
        }

        if ($result === false) {
            http_response_code(404);
            echo json_encode(['erreur' => "Site $id introuvable"]);
            return;
        }

        echo json_encode(['message' => "Site $id mis à jour avec succès"], JSON_UNESCAPED_UNICODE);
    }


    // DELETE /sites/{id}?admin_id={id} → supprime un site (GLOBAL uniquement)
    public function delete(int $id): void {
        $adminId = isset($_GET['admin_id']) ? (int) $_GET['admin_id'] : null;

        header('Content-Type: application/json');

        if ($adminId === null) {
            http_response_code(400);
            echo json_encode(['erreur' => 'Le paramètre "admin_id" est obligatoire']);
            return;
        }

        $result = $this->siteService->deleteSite($id, $adminId);

        if ($result === 'admin_introuvable') {
            http_response_code(404);
            echo json_encode(['erreur' => "Administrateur $adminId introuvable"]);
            return;
        }

        if ($result === 'acces_interdit') {
            http_response_code(403);
            echo json_encode(['erreur' => 'Seul un administrateur GLOBAL peut supprimer un site']);
            return;
        }

        if ($result === false) {
            http_response_code(404);
            echo json_encode(['erreur' => "Site $id introuvable"]);
            return;
        }

        echo json_encode(['message' => "Site $id supprimé avec succès"], JSON_UNESCAPED_UNICODE);
    }
}
