<?php

require_once __DIR__ . '/../services/HoraireSiteService.php';

// admin_id est attendu en query param (?admin_id=1) pour toutes les opérations d'écriture.

class HoraireSiteController {

    public function __construct(private HoraireSiteService $horaireService) {}


    // GET /api/horaires
    public function getAll(): void {
        $horaires = $this->horaireService->getAllHoraires();

        header('Content-Type: application/json');
        echo json_encode(array_map(fn($h) => $this->toArray($h), $horaires), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }


    // GET /api/horaires/{id}
    public function getById(int $id): void {
        $horaire = $this->horaireService->getHoraireById($id);

        header('Content-Type: application/json');

        if ($horaire === null) {
            http_response_code(404);
            echo json_encode(['erreur' => "Horaire $id introuvable"]);
            return;
        }

        echo json_encode($this->toArray($horaire), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }


    // GET /api/horaires?site_id={id}
    public function getBySiteId(int $siteId): void {
        $horaires = $this->horaireService->getHorairesBySiteId($siteId);

        header('Content-Type: application/json');
        echo json_encode(array_map(fn($h) => $this->toArray($h), $horaires), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }


    // GET /api/horaires?site_id={id}&annee={annee}
    public function getBySiteAndAnnee(int $siteId, int $annee): void {
        $horaire = $this->horaireService->getHoraireBySiteAndAnnee($siteId, $annee);

        header('Content-Type: application/json');

        if ($horaire === null) {
            http_response_code(404);
            echo json_encode(['erreur' => "Aucun horaire trouvé pour le site $siteId en $annee"]);
            return;
        }

        echo json_encode($this->toArray($horaire), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }


    // POST /api/horaires?admin_id={id}
    public function create(): void {
        $data    = json_decode(file_get_contents('php://input'), true);
        $adminId = isset($_GET['admin_id']) ? (int) $_GET['admin_id'] : null;

        header('Content-Type: application/json');

        if ($adminId === null) {
            http_response_code(400);
            echo json_encode(['erreur' => 'Le paramètre "admin_id" est obligatoire']);
            return;
        }

        if (empty($data['site_id']) || empty($data['annee']) || empty($data['heure_debut']) || empty($data['heure_fin'])) {
            http_response_code(400);
            echo json_encode(['erreur' => 'Les champs "site_id", "annee", "heure_debut" et "heure_fin" sont obligatoires']);
            return;
        }

        $result = $this->horaireService->createHoraire($data, $adminId);

        match ($result) {
            'admin_introuvable' => (function() use ($adminId) {
                http_response_code(404);
                echo json_encode(['erreur' => "Administrateur $adminId introuvable"]);
            })(),
            'acces_interdit' => (function() {
                http_response_code(403);
                echo json_encode(['erreur' => 'Vous ne pouvez créer des horaires que pour votre propre site']);
            })(),
            'annee_invalide' => (function() {
                http_response_code(400);
                echo json_encode(['erreur' => "L'année doit être comprise entre 2000 et 2100"]);
            })(),
            'heures_invalides' => (function() {
                http_response_code(400);
                echo json_encode(['erreur' => "L'heure de début doit être inférieure à l'heure de fin"]);
            })(),
            'doublon' => (function() use ($data) {
                http_response_code(409);
                echo json_encode(['erreur' => "Un horaire existe déjà pour le site {$data['site_id']} en {$data['annee']}"]);
            })(),
            default => (function() use ($result) {
                http_response_code(201);
                echo json_encode(['message' => 'Horaire créé avec succès', 'id' => $result], JSON_UNESCAPED_UNICODE);
            })(),
        };
    }


    // PUT /api/horaires/{id}?admin_id={id}
    public function update(int $id): void {
        $data    = json_decode(file_get_contents('php://input'), true);
        $adminId = isset($_GET['admin_id']) ? (int) $_GET['admin_id'] : null;

        header('Content-Type: application/json');

        if ($adminId === null) {
            http_response_code(400);
            echo json_encode(['erreur' => 'Le paramètre "admin_id" est obligatoire']);
            return;
        }

        $result = $this->horaireService->updateHoraire($id, $data ?? [], $adminId);

        if ($result === 'admin_introuvable') {
            http_response_code(404);
            echo json_encode(['erreur' => "Administrateur $adminId introuvable"]);
            return;
        }

        if ($result === 'acces_interdit') {
            http_response_code(403);
            echo json_encode(['erreur' => 'Vous ne pouvez modifier que les horaires de votre propre site']);
            return;
        }

        if ($result === false) {
            http_response_code(404);
            echo json_encode(['erreur' => "Horaire $id introuvable"]);
            return;
        }

        echo json_encode(['message' => "Horaire $id mis à jour avec succès"], JSON_UNESCAPED_UNICODE);
    }


    // DELETE /api/horaires/{id}?admin_id={id}
    public function delete(int $id): void {
        $adminId = isset($_GET['admin_id']) ? (int) $_GET['admin_id'] : null;

        header('Content-Type: application/json');

        if ($adminId === null) {
            http_response_code(400);
            echo json_encode(['erreur' => 'Le paramètre "admin_id" est obligatoire']);
            return;
        }

        $result = $this->horaireService->deleteHoraire($id, $adminId);

        if ($result === 'admin_introuvable') {
            http_response_code(404);
            echo json_encode(['erreur' => "Administrateur $adminId introuvable"]);
            return;
        }

        if ($result === 'acces_interdit') {
            http_response_code(403);
            echo json_encode(['erreur' => 'Vous ne pouvez supprimer que les horaires de votre propre site']);
            return;
        }

        if ($result === false) {
            http_response_code(404);
            echo json_encode(['erreur' => "Horaire $id introuvable"]);
            return;
        }

        echo json_encode(['message' => "Horaire $id supprimé avec succès"], JSON_UNESCAPED_UNICODE);
    }


    private function toArray(HoraireSite $h): array {
        return [
            'id'          => $h->getHoraireId(),
            'site_id'     => $h->getSiteId(),
            'annee'       => $h->getAnnee(),
            'heure_debut' => $h->getHeureDebut(),
            'heure_fin'   => $h->getHeureFin(),
        ];
    }
}
