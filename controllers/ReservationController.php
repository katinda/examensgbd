<?php
require_once __DIR__ . '/../services/ReservationService.php';
class ReservationController {
    public function __construct(private ReservationService $reservationService) {}
    public function getByMembre(int $membreId): void {
        $reservations = $this->reservationService->getReservationsByMembre($membreId);
        header('Content-Type: application/json');
        echo json_encode(array_map(fn($r) => $this->toArray($r), $reservations), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    private function toArray(Reservation $r): array {
        return ['id' => $r->getReservationId(), 'terrain_id' => $r->getTerrainId(), 'organisateur_id' => $r->getOrganisateurId(),
            'date_match' => $r->getDateMatch(), 'heure_debut' => $r->getHeureDebut(), 'heure_fin' => $r->getHeureFin(),
            'type' => $r->getType(), 'etat' => $r->getEtat(), 'prix_total' => $r->getPrixTotal(), 'date_creation' => $r->getDateCreation()];
    }
}
