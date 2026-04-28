<?php
// POST /api/horaires
if ($method === 'POST' && $uri === '/api/horaires') {
    $horaireController->create();
}
