<?php

// DELETE /api/reservations/{id}/inscriptions/{membreId} → retire un joueur de la réservation
if ($method === 'DELETE' && preg_match('#^/api/reservations/(\d+)/inscriptions/(\d+)$#', $uri, $matches)) {
    $inscriptionController->removeJoueur((int) $matches[1], (int) $matches[2]);
}
