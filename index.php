<?php
// GET /api/membres ou /api/membres?categorie=G
if ($method === 'GET' && $uri === '/api/membres') {
    $membreController->getAll();
}
