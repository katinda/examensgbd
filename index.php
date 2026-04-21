<?php

// GET /api/reservations/{id} → retourne une réservation par son ID
if ($method === 'GET' && preg_match('#^/api/reservations/(\d+)$#', $uri, $matches)) {
    $reservationController->getById((int) $matches[1]);
}
