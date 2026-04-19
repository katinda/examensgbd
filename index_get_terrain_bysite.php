<?php

// GET /sites/{siteId}/terrains → retourne les terrains d'un site précis
} elseif ($method === 'GET' && preg_match('#^/sites/(\d+)/terrains$#', $uri, $matches)) {
    $terrainController->getBySite((int) $matches[1]);
