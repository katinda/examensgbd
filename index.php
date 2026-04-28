<?php
// GET /api/horaires/{id}
if ($method === 'GET' && preg_match('#^/api/horaires/(\d+)$#', $uri, $matches)) {
    $horaireController->getById((int) $matches[1]);
}
