<?php
require_once __DIR__ . '/../services/ReservationService.php';
class ReservationController {
    public function __construct(private ReservationService $reservationService) {}
    public function getById(int $id): void {
        $reservation = $this->reservationService->getReservationById($id);
        header('Content-Type: application/json');
        if ($reservation === null) { http_response_code(404); echo json_encode(['erreur' => "Réservation $id introuvable"]); return; }
        echo json_encode($this->toArray($reservation), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    private function toArray(Reservation $r): array {
        return ['id' => $r->getReservationId(), 'terrain_id' => $r->getTerrainId(), 'organisateur_id' => $r->getOrganisateurId(),
            'date_match' => $r->getDateMatch(), 'heure_debut' => $r->getHeureDebut(), 'heure_fin' => $r->getHeureFin(),
            'type' => $r->getType(), 'etat' => $r->getEtat(), 'prix_total' => $r->getPrixTotal(), 'date_creation' => $r->getDateCreation()];
    }
}
