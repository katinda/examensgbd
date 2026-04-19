<?php
// PUT /api/membres/{id}
if ($method === 'PUT' && preg_match('#^/api/membres/(\d+)$#', $uri, $matches)) {
    $membreController->update((int) $matches[1]);
}
