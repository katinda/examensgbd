<?php
require_once __DIR__ . '/../services/ReservationService.php';
class ReservationController {
    public function __construct(private ReservationService $reservationService) {}
    public function getByTerrainAndDate(int $terrainId): void {
        $date = $_GET['date'] ?? null;
        header('Content-Type: application/json');
        if ($date === null) { http_response_code(400); echo json_encode(['erreur' => 'Le paramètre "date" est obligatoire (format: YYYY-MM-DD)']); return; }
        $reservations = $this->reservationService->getReservationsByTerrainAndDate($terrainId, $date);
        echo json_encode(array_map(fn($r) => $this->toArray($r), $reservations), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    private function toArray(Reservation $r): array {
        return ['id' => $r->getReservationId(), 'terrain_id' => $r->getTerrainId(), 'organisateur_id' => $r->getOrganisateurId(),
            'date_match' => $r->getDateMatch(), 'heure_debut' => $r->getHeureDebut(), 'heure_fin' => $r->getHeureFin(),
            'type' => $r->getType(), 'etat' => $r->getEtat(), 'prix_total' => $r->getPrixTotal(), 'date_creation' => $r->getDateCreation()];
    }
}
