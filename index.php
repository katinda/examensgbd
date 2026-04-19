<?php
// GET /api/membres/matricule/{matricule} — placée avant /{id} pour éviter les conflits
if ($method === 'GET' && preg_match('#^/api/membres/matricule/([A-Z0-9]+)$#i', $uri, $matches)) {
    $membreController->getByMatricule($matches[1]);
}
