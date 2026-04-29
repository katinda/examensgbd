<?php

require_once __DIR__ . '/../services/PenaliteService.php';

class PenaliteController {

    public function __construct(private PenaliteService $penaliteService) {}

    // GET /api/penalites, GET /api/penalites?membre_id={id}, GET /api/penalites?actives=1
    public function getAll(): void {
        $membreId = isset($_GET['membre_id']) ? (int) $_GET['membre_id'] : null;
        $actives  = isset($_GET['actives']) && $_GET['actives'] === '1';

        if ($actives) {
            $penalites = $this->penaliteService->getPenalitesActives();
        } elseif ($membreId !== null) {
            $penalites = $this->penaliteService->getPenalitesByMembreId($membreId);
        } else {
            $penalites = $this->penaliteService->getAllPenalites();
        }

        header('Content-Type: application/json');
        echo json_encode(array_map(fn($p) => $this->toArray($p), $penalites), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
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
