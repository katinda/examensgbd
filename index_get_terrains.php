<?php

// GET /terrains → retourne tous les terrains actifs
} elseif ($method === 'GET' && $uri === '/terrains') {
    $terrainController->getAll();
