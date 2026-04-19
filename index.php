<?php

// Point d'entrée de l'application.
// Toutes les requêtes HTTP arrivent ici en premier.
// Ce fichier lit l'URL et appelle le bon controller.

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/repositories/SiteRepository.php';
require_once __DIR__ . '/services/SiteService.php';
require_once __DIR__ . '/controllers/SiteController.php';

$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Chaîne d'injection de dépendance : Database → Repository → Service → Controller
$pdo            = Database::getConnection();
$siteRepo       = new SiteRepository($pdo);
$siteService    = new SiteService($siteRepo);
$siteController = new SiteController($siteService);

// --- Routeur ---

// GET /sites → retourne tous les sites
if ($method === 'GET' && $uri === '/sites') {
    $siteController->getAll();

// GET /sites/{id} → retourne un site par son ID
} elseif ($method === 'GET' && preg_match('#^/sites/(\d+)$#', $uri, $matches)) {
    $siteController->getById((int) $matches[1]);

// POST /sites → crée un nouveau site
} elseif ($method === 'POST' && $uri === '/sites') {
    $siteController->create();

// PUT /sites/{id} → met à jour un site existant
} elseif ($method === 'PUT' && preg_match('#^/sites/(\d+)$#', $uri, $matches)) {
    $siteController->update((int) $matches[1]);

// DELETE /sites/{id} → supprime un site
} elseif ($method === 'DELETE' && preg_match('#^/sites/(\d+)$#', $uri, $matches)) {
    $siteController->delete((int) $matches[1]);

// URL inconnue → erreur 404
} else {
    header('Content-Type: application/json');
    http_response_code(404);
    echo json_encode(['erreur' => 'Route introuvable']);
}
