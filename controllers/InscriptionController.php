<?php
require_once __DIR__ . '/../services/InscriptionService.php';

class InscriptionController {
    public function __construct(private InscriptionService $inscriptionService) {}

    // GET /api/reservations/{id}/inscriptions → retourne la liste des joueurs inscrits
    public function getByReservation(int $reservationId): void {
        $inscriptions = $this->inscriptionService->getInscriptionsByReservation($reservationId);
        header('Content-Type: application/json');
        echo json_encode(array_map(fn($i) => ['id' => $i->getInscriptionId(), 'reservation_id' => $i->getReservationId(), 'membre_id' => $i->getMembreId(), 'est_organisateur' => $i->isEstOrganisateur()], $inscriptions), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
