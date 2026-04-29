<?php

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/repositories/AdministrateurRepository.php';
require_once __DIR__ . '/repositories/SiteRepository.php';
require_once __DIR__ . '/services/AdministrateurService.php';
require_once __DIR__ . '/controllers/AdministrateurController.php';

$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$pdo             = Database::getConnection();
$siteRepo        = new SiteRepository($pdo);
$adminRepo       = new AdministrateurRepository($pdo);
$adminService    = new AdministrateurService($adminRepo, $siteRepo);
$adminController = new AdministrateurController($adminService);

// DELETE /api/administrateurs/{id}
if ($method === 'DELETE' && preg_match('#^/api/administrateurs/(\d+)$#', $uri, $matches)) {
    $adminController->delete((int) $matches[1]);
} else {
    header('Content-Type: application/json');
    http_response_code(404);
    echo json_encode(['erreur' => 'Route introuvable']);
}
