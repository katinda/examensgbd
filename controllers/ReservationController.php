<?php

require_once __DIR__ . '/../services/ReservationService.php';

// Le controller reçoit les requêtes HTTP et renvoie du JSON.
// Il ne contient aucune logique métier — il appelle le service et formate la réponse.

class ReservationController {

    // Le service est reçu en paramètre (injection de dépendance).
    public function __construct(private ReservationService $reservationService) {}


    // GET /api/reservations/{id} → retourne une réservation précise ou 404
    public function getById(int $id): void {
        $reservation = $this->reservationService->getReservationById($id);

        header('Content-Type: application/json');

        if ($reservation === null) {
            http_response_code(404);
            echo json_encode(['erreur' => "Réservation $id introuvable"]);
            return;
        }

        echo json_encode($this->toArray($reservation), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }


    // GET /api/membres/{id}/reservations → retourne toutes les réservations d'un membre
    public function getByMembre(int $membreId): void {
        $reservations = $this->reservationService->getReservationsByMembre($membreId);

        header('Content-Type: application/json');
        echo json_encode(array_map(fn($r) => $this->toArray($r), $reservations), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }


    // GET /api/terrains/{id}/reservations?date=YYYY-MM-DD → retourne les réservations d'un terrain à une date
    // Le paramètre ?date= est obligatoire — sans lui on ne peut pas lister les créneaux
    public function getByTerrainAndDate(int $terrainId): void {
        $date = $_GET['date'] ?? null;

        header('Content-Type: application/json');

        if ($date === null) {
            http_response_code(400);
            echo json_encode(['erreur' => 'Le paramètre "date" est obligatoire (format: YYYY-MM-DD)']);
            return;
        }

        $reservations = $this->reservationService->getReservationsByTerrainAndDate($terrainId, $date);
        echo json_encode(array_map(fn($r) => $this->toArray($r), $reservations), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }


    // POST /api/reservations → crée une nouvelle réservation
    // Codes possibles : 201 (créé), 400 (champs manquants ou terrain inactif), 404 (terrain/membre introuvable), 409 (créneau pris)
    public function create(): void {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['terrain_id']) || empty($data['organisateur_id']) || empty($data['date_match']) || empty($data['heure_debut'])) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['erreur' => 'Les champs "terrain_id", "organisateur_id", "date_match" et "heure_debut" sont obligatoires']);
            return;
        }

        $result = $this->reservationService->createReservation($data);

        header('Content-Type: application/json');

        match ($result) {
            'terrain_introuvable'      => (function() use ($data) {
                http_response_code(404);
                echo json_encode(['erreur' => "Terrain {$data['terrain_id']} introuvable"]);
            })(),
            'terrain_inactif'          => (function() use ($data) {
                http_response_code(400);
                echo json_encode(['erreur' => "Terrain {$data['terrain_id']} inactif"]);
            })(),
            'organisateur_introuvable' => (function() use ($data) {
                http_response_code(404);
                echo json_encode(['erreur' => "Membre {$data['organisateur_id']} introuvable"]);
            })(),
            'creneau_pris'             => (function() use ($data) {
                http_response_code(409);
                echo json_encode(['erreur' => "Ce créneau est déjà réservé sur ce terrain"]);
            })(),
            default                    => (function() use ($result) {
                http_response_code(201);
                echo json_encode(['message' => 'Réservation créée avec succès', 'id' => $result], JSON_UNESCAPED_UNICODE);
            })(),
        };
    }


    // Transforme un objet Reservation en tableau simple pour le JSON
    private function toArray(Reservation $r): array {
        return [
            'id'             => $r->getReservationId(),
            'terrain_id'     => $r->getTerrainId(),
            'organisateur_id'=> $r->getOrganisateurId(),
            'date_match'     => $r->getDateMatch(),
            'heure_debut'    => $r->getHeureDebut(),
            'heure_fin'      => $r->getHeureFin(),
            'type'           => $r->getType(),
            'etat'           => $r->getEtat(),
            'prix_total'     => $r->getPrixTotal(),
            'date_creation'  => $r->getDateCreation(),
        ];
    }
}
