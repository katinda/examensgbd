<?php

require_once __DIR__ . '/../services/PenaliteService.php';

class PenaliteController {

    public function __construct(private PenaliteService $penaliteService) {}

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
}
