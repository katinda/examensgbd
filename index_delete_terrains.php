<?php

// DELETE /terrains/{id} → supprime un terrain
} elseif ($method === 'DELETE' && preg_match('#^/terrains/(\d+)$#', $uri, $matches)) {
    $terrainController->delete((int) $matches[1]);
