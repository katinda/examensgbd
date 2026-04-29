<?php

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/repositories/PenaliteRepository.php';
require_once __DIR__ . '/repositories/MembreRepository.php';
require_once __DIR__ . '/repositories/AdministrateurRepository.php';
require_once __DIR__ . '/repositories/SiteRepository.php';
require_once __DIR__ . '/services/PenaliteService.php';
require_once __DIR__ . '/controllers/PenaliteController.php';

$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$pdo                = Database::getConnection();
$membreRepo         = new MembreRepository($pdo);
$adminRepo          = new AdministrateurRepository($pdo);
$penaliteRepo       = new PenaliteRepository($pdo);
$penaliteService    = new PenaliteService($penaliteRepo, $membreRepo, $adminRepo);
$penaliteController = new PenaliteController($penaliteService);

// Route
if ($method === 'GET' && preg_match('#^/api/penalites/(\d+)$#', $uri, $matches)) {
    $penaliteController->getById((int) $matches[1]);
} else {
    header('Content-Type: application/json');
    http_response_code(404);
    echo json_encode(['erreur' => 'Route introuvable']);
}
