<?php

require_once __DIR__ . '/../services/FermetureService.php';

// admin_id est attendu en query param (?admin_id=1) pour toutes les opérations d'écriture.

class FermetureController {

    public function __construct(private FermetureService $fermetureService) {}


    // GET /api/fermetures, GET /api/fermetures?site_id={id}, GET /api/fermetures?globales=1
    public function getAll(): void {
        $siteId   = isset($_GET['site_id'])  ? (int) $_GET['site_id'] : null;
        $globales = isset($_GET['globales']) && $_GET['globales'] === '1';

        if ($globales) {
            $fermetures = $this->fermetureService->getFermeturesGlobales();
        } elseif ($siteId !== null) {
            $fermetures = $this->fermetureService->getFermeturesBySiteId($siteId);
        } else {
            $fermetures = $this->fermetureService->getAllFermetures();
        }

        header('Content-Type: application/json');
        echo json_encode(array_map(fn($f) => $this->toArray($f), $fermetures), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }


    // GET /api/fermetures/{id}
    public function getById(int $id): void {
        $fermeture = $this->fermetureService->getFermetureById($id);

        header('Content-Type: application/json');

        if ($fermeture === null) {
            http_response_code(404);
            echo json_encode(['erreur' => "Fermeture $id introuvable"]);
            return;
        }

        echo json_encode($this->toArray($fermeture), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }


    // POST /api/fermetures?admin_id={id}
    public function create(): void {
        $data    = json_decode(file_get_contents('php://input'), true);
        $adminId = isset($_GET['admin_id']) ? (int) $_GET['admin_id'] : null;

        header('Content-Type: application/json');

        if ($adminId === null) {
            http_response_code(400);
            echo json_encode(['erreur' => 'Le paramètre "admin_id" est obligatoire']);
            return;
        }

        if (empty($data['date_debut']) || empty($data['date_fin'])) {
            http_response_code(400);
            echo json_encode(['erreur' => 'Les champs "date_debut" et "date_fin" sont obligatoires']);
            return;
        }

        $result = $this->fermetureService->createFermeture($data, $adminId);

        match ($result) {
            'admin_introuvable' => (function() use ($adminId) {
                http_response_code(404);
                echo json_encode(['erreur' => "Administrateur $adminId introuvable"]);
            })(),
            'acces_interdit' => (function() {
                http_response_code(403);
                echo json_encode(['erreur' => 'Droits insuffisants pour créer cette fermeture']);
            })(),
            'dates_invalides' => (function() {
                http_response_code(400);
                echo json_encode(['erreur' => 'La date de début doit être inférieure ou égale à la date de fin']);
            })(),
            default => (function() use ($result) {
                http_response_code(201);
                echo json_encode(['message' => 'Fermeture créée avec succès', 'id' => $result], JSON_UNESCAPED_UNICODE);
            })(),
        };
    }


    // PUT /api/fermetures/{id}?admin_id={id}
    public function update(int $id): void {
        $data    = json_decode(file_get_contents('php://input'), true);
        $adminId = isset($_GET['admin_id']) ? (int) $_GET['admin_id'] : null;

        header('Content-Type: application/json');

        if ($adminId === null) {
            http_response_code(400);
            echo json_encode(['erreur' => 'Le paramètre "admin_id" est obligatoire']);
            return;
        }

        $result = $this->fermetureService->updateFermeture($id, $data ?? [], $adminId);

        if ($result === 'admin_introuvable') {
            http_response_code(404);
            echo json_encode(['erreur' => "Administrateur $adminId introuvable"]);
            return;
        }

        if ($result === 'acces_interdit') {
            http_response_code(403);
            echo json_encode(['erreur' => 'Droits insuffisants pour modifier cette fermeture']);
            return;
        }

        if ($result === false) {
            http_response_code(404);
            echo json_encode(['erreur' => "Fermeture $id introuvable"]);
            return;
        }

        echo json_encode(['message' => "Fermeture $id mise à jour avec succès"], JSON_UNESCAPED_UNICODE);
    }


    // DELETE /api/fermetures/{id}?admin_id={id}
    public function delete(int $id): void {
        $adminId = isset($_GET['admin_id']) ? (int) $_GET['admin_id'] : null;

        header('Content-Type: application/json');

        if ($adminId === null) {
            http_response_code(400);
            echo json_encode(['erreur' => 'Le paramètre "admin_id" est obligatoire']);
            return;
        }

        $result = $this->fermetureService->deleteFermeture($id, $adminId);

        if ($result === 'admin_introuvable') {
            http_response_code(404);
            echo json_encode(['erreur' => "Administrateur $adminId introuvable"]);
            return;
        }

        if ($result === 'acces_interdit') {
            http_response_code(403);
            echo json_encode(['erreur' => 'Droits insuffisants pour supprimer cette fermeture']);
            return;
        }

        if ($result === false) {
            http_response_code(404);
            echo json_encode(['erreur' => "Fermeture $id introuvable"]);
            return;
        }

        echo json_encode(['message' => "Fermeture $id supprimée avec succès"], JSON_UNESCAPED_UNICODE);
    }


    private function toArray(Fermeture $f): array {
        return [
            'id'            => $f->getFermetureId(),
            'site_id'       => $f->getSiteId(),
            'date_debut'    => $f->getDateDebut(),
            'date_fin'      => $f->getDateFin(),
            'raison'        => $f->getRaison(),
            'date_creation' => $f->getDateCreation(),
        ];
    }
}
