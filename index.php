<?php
// DELETE /api/membres/{id}
if ($method === 'DELETE' && preg_match('#^/api/membres/(\d+)$#', $uri, $matches)) {
    $membreController->delete((int) $matches[1]);
}
