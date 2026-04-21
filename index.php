<?php

// GET /api/reservations/{id}/inscriptions → retourne les joueurs inscrits à une réservation
if ($method === 'GET' && preg_match('#^/api/reservations/(\d+)/inscriptions$#', $uri, $matches)) {
    $inscriptionController->getByReservation((int) $matches[1]);
}
