<?php

// POST /api/reservations → crée une nouvelle réservation
if ($method === 'POST' && $uri === '/api/reservations') {
    $reservationController->create();
}
