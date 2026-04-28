<?php
// PUT /api/horaires/{id}
if ($method === 'PUT' && preg_match('#^/api/horaires/(\d+)$#', $uri, $matches)) {
    $horaireController->update((int) $matches[1]);
}
