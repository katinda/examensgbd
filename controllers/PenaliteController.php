<?php

require_once __DIR__ . '/../services/PenaliteService.php';

class PenaliteController {

    public function __construct(private PenaliteService $penaliteService) {}

    // GET /api/penalites/{id}
    public function getById(int $id): void {
        $penalite = $this->penaliteService->getPenaliteById($id);

        header('Content-Type: application/json');

        if ($penalite === null) {
            http_response_code(404);
            echo json_encode(['erreur' => "Pénalité $id introuvable"]);
            return;
        }

        echo json_encode($this->toArray($penalite), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    private function toArray(Penalite $p): array {
        return [
            'id' => $p->getPenaliteId(), 'membre_id' => $p->getMembreId(),
            'reservation_id' => $p->getReservationId(), 'date_debut' => $p->getDateDebut(),
            'date_fin' => $p->getDateFin(), 'cause' => $p->getCause(),
            'levee' => $p->isLevee(), 'levee_par' => $p->getLeveePar(),
            'levee_le' => $p->getLeveeLe(), 'levee_raison' => $p->getLeveeRaison(),
            'date_creation' => $p->getDateCreation(),
        ];
    }
}
