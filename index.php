<?php

// GET /api/membres/{id}/reservations → retourne les réservations d'un membre
if ($method === 'GET' && preg_match('#^/api/membres/(\d+)/reservations$#', $uri, $matches)) {
    $reservationController->getByMembre((int) $matches[1]);
}
