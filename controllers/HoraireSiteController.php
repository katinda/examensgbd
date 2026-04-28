<?php

require_once __DIR__ . '/../services/HoraireSiteService.php';

class HoraireSiteController {

    public function __construct(private HoraireSiteService $horaireService) {}

    // GET /api/horaires?site_id={id}&annee={annee}
    public function getBySiteAndAnnee(int $siteId, int $annee): void {
        $horaire = $this->horaireService->getHoraireBySiteAndAnnee($siteId, $annee);

        header('Content-Type: application/json');

        if ($horaire === null) {
            http_response_code(404);
            echo json_encode(['erreur' => "Aucun horaire trouvé pour le site $siteId en $annee"]);
            return;
        }

        echo json_encode($this->toArray($horaire), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
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
