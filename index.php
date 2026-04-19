<?php

// Point d'entrée de l'application.
// Toutes les requêtes HTTP arrivent ici en premier.
// Ce fichier lit l'URL et appelle le bon controller.

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/repositories/SiteRepository.php';
require_once __DIR__ . '/repositories/TerrainRepository.php';
require_once __DIR__ . '/services/SiteService.php';
require_once __DIR__ . '/services/TerrainService.php';
require_once __DIR__ . '/controllers/SiteController.php';
require_once __DIR__ . '/controllers/TerrainController.php';

$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Chaîne d'injection de dépendance : Database → Repository → Service → Controller
$pdo               = Database::getConnection();
$siteRepo          = new SiteRepository($pdo);
$terrainRepo       = new TerrainRepository($pdo);
$siteService       = new SiteService($siteRepo);
$terrainService    = new TerrainService($terrainRepo, $siteRepo);
$siteController    = new SiteController($siteService);
$terrainController = new TerrainController($terrainService);

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

// GET /terrains → retourne tous les terrains
} elseif ($method === 'GET' && $uri === '/terrains') {
    $terrainController->getAll();

// GET /terrains/{id} → retourne un terrain par son ID
} elseif ($method === 'GET' && preg_match('#^/terrains/(\d+)$#', $uri, $matches)) {
    $terrainController->getById((int) $matches[1]);

// GET /sites/{siteId}/terrains → retourne les terrains d'un site
} elseif ($method === 'GET' && preg_match('#^/sites/(\d+)/terrains$#', $uri, $matches)) {
    $terrainController->getBySite((int) $matches[1]);

// POST /terrains → crée un nouveau terrain
} elseif ($method === 'POST' && $uri === '/terrains') {
    $terrainController->create();

// PUT /terrains/{id} → met à jour un terrain existant
} elseif ($method === 'PUT' && preg_match('#^/terrains/(\d+)$#', $uri, $matches)) {
    $terrainController->update((int) $matches[1]);

// DELETE /terrains/{id} → supprime un terrain
} elseif ($method === 'DELETE' && preg_match('#^/terrains/(\d+)$#', $uri, $matches)) {
    $terrainController->delete((int) $matches[1]);

// URL inconnue → erreur 404
} else {
    header('Content-Type: application/json');
    http_response_code(404);
    echo json_encode(['erreur' => 'Route introuvable']);
}
