<?php
require_once __DIR__ . '/../services/ReservationService.php';
class ReservationController {
    public function __construct(private ReservationService $reservationService) {}
    public function create(): void {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['terrain_id']) || empty($data['organisateur_id']) || empty($data['date_match']) || empty($data['heure_debut'])) {
            header('Content-Type: application/json'); http_response_code(400);
            echo json_encode(['erreur' => 'Les champs "terrain_id", "organisateur_id", "date_match" et "heure_debut" sont obligatoires']); return;
        }
        $result = $this->reservationService->createReservation($data);
        header('Content-Type: application/json');
        match ($result) {
            'terrain_introuvable'      => (function() use ($data) { http_response_code(404); echo json_encode(['erreur' => "Terrain {$data['terrain_id']} introuvable"]); })(),
            'terrain_inactif'          => (function() use ($data) { http_response_code(400); echo json_encode(['erreur' => "Terrain {$data['terrain_id']} inactif"]); })(),
            'organisateur_introuvable' => (function() use ($data) { http_response_code(404); echo json_encode(['erreur' => "Membre {$data['organisateur_id']} introuvable"]); })(),
            'creneau_pris'             => (function() { http_response_code(409); echo json_encode(['erreur' => "Ce créneau est déjà réservé sur ce terrain"]); })(),
            default                    => (function() use ($result) { http_response_code(201); echo json_encode(['message' => 'Réservation créée avec succès', 'id' => $result], JSON_UNESCAPED_UNICODE); })(),
        };
    }
}
