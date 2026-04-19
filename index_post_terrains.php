<?php

// POST /terrains → crée un nouveau terrain
} elseif ($method === 'POST' && $uri === '/terrains') {
    $terrainController->create();
