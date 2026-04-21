<?php
require_once __DIR__ . '/../services/InscriptionService.php';

class InscriptionController {
    public function __construct(private InscriptionService $inscriptionService) {}

    // DELETE /api/reservations/{id}/inscriptions/{membreId} → retire un joueur de la réservation
    public function removeJoueur(int $reservationId, int $membreId): void {
        $supprime = $this->inscriptionService->removeJoueur($reservationId, $membreId);
        header('Content-Type: application/json');
        if (!$supprime) {
            http_response_code(404);
            echo json_encode(['erreur' => "Le membre $membreId n'est pas inscrit à la réservation $reservationId"]);
            return;
        }
        echo json_encode(['message' => 'Joueur retiré avec succès'], JSON_UNESCAPED_UNICODE);
    }
}
