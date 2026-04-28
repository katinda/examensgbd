<?php

require_once __DIR__ . '/../services/HoraireSiteService.php';

class HoraireSiteController {

    public function __construct(private HoraireSiteService $horaireService) {}

    // GET /api/horaires?site_id={id}
    public function getBySiteId(int $siteId): void {
        $horaires = $this->horaireService->getHorairesBySiteId($siteId);

        header('Content-Type: application/json');
        echo json_encode(array_map(fn($h) => $this->toArray($h), $horaires), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    private function toArray(HoraireSite $h): array {
        return [
            'id'          => $h->getHoraireId(),
            'site_id'     => $h->getSiteId(),
            'annee'       => $h->getAnnee(),
            'heure_debut' => $h->getHeureDebut(),
            'heure_fin'   => $h->getHeureFin(),
        ];
    }
}
