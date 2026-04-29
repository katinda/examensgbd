<?php

require_once __DIR__ . '/../services/PenaliteService.php';

class PenaliteController {

    public function __construct(private PenaliteService $penaliteService) {}


    // GET /api/penalites, GET /api/penalites?membre_id={id}, GET /api/penalites?actives=1
    public function getAll(): void {
        $membreId = isset($_GET['membre_id']) ? (int) $_GET['membre_id'] : null;
        $actives  = isset($_GET['actives']) && $_GET['actives'] === '1';

        if ($actives) {
            $penalites = $this->penaliteService->getPenalitesActives();
        } elseif ($membreId !== null) {
            $penalites = $this->penaliteService->getPenalitesByMembreId($membreId);
        } else {
            $penalites = $this->penaliteService->getAllPenalites();
        }

        header('Content-Type: application/json');
        echo json_encode(array_map(fn($p) => $this->toArray($p), $penalites), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }


    // GET /api/penalites/{id}
    public function getById(int $id): void {
        $penalite = $this->penaliteService->getPenaliteById($id);

        header('Content-Type: application/json');

        if ($penalite === null) {
            http_response_code(404);
            echo json_encode(['erreur' => "Pénalité $id introuvable"]);
            return;
        }

        echo json_encode($this->toArray($penalite), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }


    // POST /api/penalites
    public function create(): void {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['membre_id']) || empty($data['date_debut']) || empty($data['date_fin']) || empty($data['cause'])) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['erreur' => 'Les champs "membre_id", "date_debut", "date_fin" et "cause" sont obligatoires']);
            return;
        }

        $result = $this->penaliteService->createPenalite($data);

        header('Content-Type: application/json');

        match ($result) {
            'cause_invalide'     => (function() {
                http_response_code(400);
                echo json_encode(['erreur' => 'La cause doit être PRIVATE_INCOMPLETE, PAYMENT_MISSING ou OTHER']);
            })(),
            'dates_invalides'    => (function() {
                http_response_code(400);
                echo json_encode(['erreur' => 'La date de début doit être inférieure ou égale à la date de fin']);
            })(),
            'membre_introuvable' => (function() {
                http_response_code(404);
                echo json_encode(['erreur' => 'Membre introuvable']);
            })(),
            default => (function() use ($result) {
                http_response_code(201);
                echo json_encode(['message' => 'Pénalité créée avec succès', 'id' => $result], JSON_UNESCAPED_UNICODE);
            })(),
        };
    }


    // PATCH /api/penalites/{id}/lever
    public function lever(int $id): void {
        $data   = json_decode(file_get_contents('php://input'), true);
        $result = $this->penaliteService->leverPenalite($id, $data ?? []);

        header('Content-Type: application/json');

        match ($result) {
            'penalite_introuvable' => (function() use ($id) {
                http_response_code(404);
                echo json_encode(['erreur' => "Pénalité $id introuvable"]);
            })(),
            'deja_levee'           => (function() {
                http_response_code(409);
                echo json_encode(['erreur' => 'Cette pénalité est déjà levée']);
            })(),
            'admin_introuvable'    => (function() {
                http_response_code(404);
                echo json_encode(['erreur' => 'Administrateur introuvable']);
            })(),
            'admin_non_global'     => (function() {
                http_response_code(403);
                echo json_encode(['erreur' => 'Seul un administrateur GLOBAL peut lever une pénalité']);
            })(),
            default => (function() use ($id) {
                echo json_encode(['message' => "Pénalité $id levée avec succès"], JSON_UNESCAPED_UNICODE);
            })(),
        };
    }


    // DELETE /api/penalites/{id}
    public function delete(int $id): void {
        $ok = $this->penaliteService->deletePenalite($id);

        header('Content-Type: application/json');

        if (!$ok) {
            http_response_code(404);
            echo json_encode(['erreur' => "Pénalité $id introuvable"]);
            return;
        }

        echo json_encode(['message' => "Pénalité $id supprimée avec succès"], JSON_UNESCAPED_UNICODE);
    }


    private function toArray(Penalite $p): array {
        return [
            'id'             => $p->getPenaliteId(),
            'membre_id'      => $p->getMembreId(),
            'reservation_id' => $p->getReservationId(),
            'date_debut'     => $p->getDateDebut(),
            'date_fin'       => $p->getDateFin(),
            'cause'          => $p->getCause(),
            'levee'          => $p->isLevee(),
            'levee_par'      => $p->getLeveePar(),
            'levee_le'       => $p->getLeveeLe(),
            'levee_raison'   => $p->getLeveeRaison(),
            'date_creation'  => $p->getDateCreation(),
        ];
    }
}
