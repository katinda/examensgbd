<?php

require_once __DIR__ . '/../services/StatsService.php';

// Expose les statistiques via l'API REST.
// GET /api/stats          → stats globales
// GET /api/stats?site_id= → stats d'un site précis

class StatsController {

    public function __construct(private StatsService $statsService) {}


    public function getStats(): void {
        $siteId = isset($_GET['site_id']) ? (int) $_GET['site_id'] : null;

        $stats = $siteId !== null
            ? $this->statsService->getStatsBySite($siteId)
            : $this->statsService->getStatsGlobales();

        header('Content-Type: application/json');
        echo json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
