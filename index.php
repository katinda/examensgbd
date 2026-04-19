<?php
// GET /api/membres/{id}
if ($method === 'GET' && preg_match('#^/api/membres/(\d+)$#', $uri, $matches)) {
    $membreController->getById((int) $matches[1]);
}
