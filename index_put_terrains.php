<?php

// PUT /terrains/{id} → met à jour un terrain existant
} elseif ($method === 'PUT' && preg_match('#^/terrains/(\d+)$#', $uri, $matches)) {
    $terrainController->update((int) $matches[1]);
