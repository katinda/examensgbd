<?php

// GET /terrains/{id} → retourne un terrain par son ID
} elseif ($method === 'GET' && preg_match('#^/terrains/(\d+)$#', $uri, $matches)) {
    $terrainController->getById((int) $matches[1]);
