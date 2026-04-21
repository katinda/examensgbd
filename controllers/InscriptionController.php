<?php
require_once __DIR__ . '/../services/InscriptionService.php';

class InscriptionController {
    public function __construct(private InscriptionService $inscriptionService) {}

    // POST /api/reservations/{id}/inscriptions → ajoute un joueur à la réservation
    public function addJoueur(int $reservationId): void {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['membre_id'])) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['erreur' => 'Le champ "membre_id" est obligatoire']);
            return;
        }
        $result = $this->inscriptionService->addJoueur($reservationId, (int) $data['membre_id']);
        header('Content-Type: application/json');
        match ($result) {
            'reservation_introuvable' => (function() use ($reservationId) { http_response_code(404); echo json_encode(['erreur' => "Réservation $reservationId introuvable"]); })(),
            'membre_introuvable'      => (function() use ($data) { http_response_code(404); echo json_encode(['erreur' => "Membre {$data['membre_id']} introuvable"]); })(),
            'reservation_complete'    => (function() { http_response_code(409); echo json_encode(['erreur' => 'Cette réservation est déjà complète (4 joueurs maximum)']); })(),
            'deja_inscrit'            => (function() use ($data, $reservationId) { http_response_code(409); echo json_encode(['erreur' => "Le membre {$data['membre_id']} est déjà inscrit à cette réservation"]); })(),
            default                   => (function() use ($result) { http_response_code(201); echo json_encode(['message' => 'Joueur inscrit avec succès', 'id' => $result], JSON_UNESCAPED_UNICODE); })(),
        };
    }
}
