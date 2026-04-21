<?php

// POST /api/reservations/{id}/inscriptions → ajoute un joueur à la réservation
if ($method === 'POST' && preg_match('#^/api/reservations/(\d+)/inscriptions$#', $uri, $matches)) {
    $inscriptionController->addJoueur((int) $matches[1]);
}
