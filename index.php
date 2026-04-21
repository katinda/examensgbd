<?php

// GET /api/terrains/{id}/reservations?date=YYYY-MM-DD → retourne les réservations d'un terrain pour une date
if ($method === 'GET' && preg_match('#^/api/terrains/(\d+)/reservations$#', $uri, $matches)) {
    $reservationController->getByTerrainAndDate((int) $matches[1]);
}
