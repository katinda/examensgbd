<?php
// DELETE /api/horaires/{id}
if ($method === 'DELETE' && preg_match('#^/api/horaires/(\d+)$#', $uri, $matches)) {
    $horaireController->delete((int) $matches[1]);
}
