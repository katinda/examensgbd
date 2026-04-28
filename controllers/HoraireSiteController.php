<?php

require_once __DIR__ . '/../services/HoraireSiteService.php';

class HoraireSiteController {

    public function __construct(private HoraireSiteService $horaireService) {}

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
