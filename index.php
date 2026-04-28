<?php
// GET /api/horaires, GET /api/horaires?site_id={id}, GET /api/horaires?site_id={id}&annee={annee}
if ($method === 'GET' && $uri === '/api/horaires') {
    $siteId = isset($_GET['site_id']) ? (int) $_GET['site_id'] : null;
    $annee  = isset($_GET['annee'])   ? (int) $_GET['annee']   : null;
    if ($siteId !== null && $annee !== null) {
        $horaireController->getBySiteAndAnnee($siteId, $annee);
    } elseif ($siteId !== null) {
        $horaireController->getBySiteId($siteId);
    } else {
        $horaireController->getAll();
    }
}
