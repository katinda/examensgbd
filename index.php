<?php
// POST /api/membres
if ($method === 'POST' && $uri === '/api/membres') {
    $membreController->create();
}
