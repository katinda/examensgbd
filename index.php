<?php

// Point d'entrée de l'application.
// Toutes les requêtes HTTP arrivent ici en premier.
// Ce fichier lit l'URL et appelle le bon controller.

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/repositories/SiteRepository.php';
require_once __DIR__ . '/services/SiteService.php';
require_once __DIR__ . '/controllers/SiteController.php';

// On récupère l'URL demandée et la méthode HTTP (GET, POST...)
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// On prépare le controller Sites avec toute la chaîne d'injection de dépendance :
// Database → Repository → Service → Controller
$pdo            = Database::getConnection();
$siteRepo       = new SiteRepository($pdo);
$siteService    = new SiteService($siteRepo);
$siteController = new SiteController($siteService);

// --- Routeur ---
// GET /sites → retourne tous les sites
if ($method === 'GET' && $uri === '/sites') {
    $siteController->getAll();

// GET /sites/5 → retourne le site avec l'ID 5
} elseif ($method === 'GET' && preg_match('#^/sites/(\d+)$#', $uri, $matches)) {
    $siteController->getById((int) $matches[1]);

// URL inconnue → erreur 404
} else {
    header('Content-Type: application/json');
    http_response_code(404);
    echo json_encode(['erreur' => 'Route introuvable']);
}
