<?php

require_once __DIR__ . '/../services/HoraireSiteService.php';

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


    // POST /api/horaires
    public function create(): void {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['site_id']) || empty($data['annee']) || empty($data['heure_debut']) || empty($data['heure_fin'])) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['erreur' => 'Les champs "site_id", "annee", "heure_debut" et "heure_fin" sont obligatoires']);
            return;
        }

        $result = $this->horaireService->createHoraire($data);

        header('Content-Type: application/json');

        match ($result) {
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


    // PUT /api/horaires/{id}
    public function update(int $id): void {
        $data = json_decode(file_get_contents('php://input'), true);
        $ok   = $this->horaireService->updateHoraire($id, $data);

        header('Content-Type: application/json');

        if (!$ok) {
            http_response_code(404);
            echo json_encode(['erreur' => "Horaire $id introuvable"]);
            return;
        }

        echo json_encode(['message' => "Horaire $id mis à jour avec succès"], JSON_UNESCAPED_UNICODE);
    }


    // DELETE /api/horaires/{id}
    public function delete(int $id): void {
        $ok = $this->horaireService->deleteHoraire($id);

        header('Content-Type: application/json');

        if (!$ok) {
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
