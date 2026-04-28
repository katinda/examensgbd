<?php

// Point d'entrée de l'application.
// Toutes les requêtes HTTP arrivent ici en premier.
// Ce fichier lit l'URL et appelle le bon controller.

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/repositories/SiteRepository.php';
require_once __DIR__ . '/repositories/TerrainRepository.php';
require_once __DIR__ . '/repositories/MembreRepository.php';
require_once __DIR__ . '/repositories/ReservationRepository.php';
require_once __DIR__ . '/repositories/InscriptionRepository.php';
require_once __DIR__ . '/repositories/HoraireSiteRepository.php';
require_once __DIR__ . '/repositories/FermetureRepository.php';
require_once __DIR__ . '/services/SiteService.php';
require_once __DIR__ . '/services/TerrainService.php';
require_once __DIR__ . '/services/MembreService.php';
require_once __DIR__ . '/services/ReservationService.php';
require_once __DIR__ . '/services/InscriptionService.php';
require_once __DIR__ . '/controllers/SiteController.php';
require_once __DIR__ . '/controllers/TerrainController.php';
require_once __DIR__ . '/controllers/MembreController.php';
require_once __DIR__ . '/controllers/ReservationController.php';
require_once __DIR__ . '/controllers/InscriptionController.php';
require_once __DIR__ . '/services/HoraireSiteService.php';
require_once __DIR__ . '/controllers/HoraireSiteController.php';
require_once __DIR__ . '/services/FermetureService.php';
require_once __DIR__ . '/controllers/FermetureController.php';

$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Chaîne d'injection de dépendance : Database → Repository → Service → Controller
$pdo               = Database::getConnection();
$siteRepo          = new SiteRepository($pdo);
$terrainRepo       = new TerrainRepository($pdo);
$membreRepo        = new MembreRepository($pdo);
$siteService       = new SiteService($siteRepo);
$terrainService    = new TerrainService($terrainRepo, $siteRepo);
$membreService     = new MembreService($membreRepo, $siteRepo);
$reservationRepo       = new ReservationRepository($pdo);
$inscriptionRepo       = new InscriptionRepository($pdo);
$reservationService    = new ReservationService($reservationRepo, $terrainRepo, $membreRepo, $inscriptionRepo, $pdo);
$inscriptionService    = new InscriptionService($inscriptionRepo, $reservationRepo, $membreRepo);
$reservationController = new ReservationController($reservationService);
$inscriptionController = new InscriptionController($inscriptionService);
$siteController        = new SiteController($siteService);
$terrainController     = new TerrainController($terrainService);
$membreController      = new MembreController($membreService);
$horaireRepo           = new HoraireSiteRepository($pdo);
$horaireService        = new HoraireSiteService($horaireRepo);
$horaireController     = new HoraireSiteController($horaireService);
$fermetureRepo         = new FermetureRepository($pdo);
$fermetureService      = new FermetureService($fermetureRepo);
$fermetureController   = new FermetureController($fermetureService);

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

// GET /api/reservations/{id}/inscriptions → retourne les joueurs inscrits à une réservation
} elseif ($method === 'GET' && preg_match('#^/api/reservations/(\d+)/inscriptions$#', $uri, $matches)) {
    $inscriptionController->getByReservation((int) $matches[1]);

// POST /api/reservations/{id}/inscriptions → ajoute un joueur à la réservation
} elseif ($method === 'POST' && preg_match('#^/api/reservations/(\d+)/inscriptions$#', $uri, $matches)) {
    $inscriptionController->addJoueur((int) $matches[1]);

// DELETE /api/reservations/{id}/inscriptions/{membreId} → retire un joueur de la réservation
} elseif ($method === 'DELETE' && preg_match('#^/api/reservations/(\d+)/inscriptions/(\d+)$#', $uri, $matches)) {
    $inscriptionController->removeJoueur((int) $matches[1], (int) $matches[2]);

// GET /api/reservations/{id} → retourne une réservation par son ID
} elseif ($method === 'GET' && preg_match('#^/api/reservations/(\d+)$#', $uri, $matches)) {
    $reservationController->getById((int) $matches[1]);

// GET /api/membres/{id}/reservations → retourne les réservations d'un membre
} elseif ($method === 'GET' && preg_match('#^/api/membres/(\d+)/reservations$#', $uri, $matches)) {
    $reservationController->getByMembre((int) $matches[1]);

// GET /api/terrains/{id}/reservations?date=YYYY-MM-DD → retourne les réservations d'un terrain pour une date
} elseif ($method === 'GET' && preg_match('#^/api/terrains/(\d+)/reservations$#', $uri, $matches)) {
    $reservationController->getByTerrainAndDate((int) $matches[1]);

// POST /api/reservations → crée une nouvelle réservation
} elseif ($method === 'POST' && $uri === '/api/reservations') {
    $reservationController->create();

// GET /api/membres ou /api/membres?categorie=G
} elseif ($method === 'GET' && $uri === '/api/membres') {
    $membreController->getAll();

// GET /api/membres/matricule/{matricule} — AVANT /api/membres/{id} pour éviter le conflit
} elseif ($method === 'GET' && preg_match('#^/api/membres/matricule/([A-Z0-9]+)$#i', $uri, $matches)) {
    $membreController->getByMatricule($matches[1]);

// GET /api/membres/{id}
} elseif ($method === 'GET' && preg_match('#^/api/membres/(\d+)$#', $uri, $matches)) {
    $membreController->getById((int) $matches[1]);

// POST /api/membres
} elseif ($method === 'POST' && $uri === '/api/membres') {
    $membreController->create();

// PUT /api/membres/{id}
} elseif ($method === 'PUT' && preg_match('#^/api/membres/(\d+)$#', $uri, $matches)) {
    $membreController->update((int) $matches[1]);

// DELETE /api/membres/{id}
} elseif ($method === 'DELETE' && preg_match('#^/api/membres/(\d+)$#', $uri, $matches)) {
    $membreController->delete((int) $matches[1]);

// GET /api/horaires, GET /api/horaires?site_id={id}, GET /api/horaires?site_id={id}&annee={annee}
} elseif ($method === 'GET' && $uri === '/api/horaires') {
    $siteId = isset($_GET['site_id']) ? (int) $_GET['site_id'] : null;
    $annee  = isset($_GET['annee'])   ? (int) $_GET['annee']   : null;
    if ($siteId !== null && $annee !== null) {
        $horaireController->getBySiteAndAnnee($siteId, $annee);
    } elseif ($siteId !== null) {
        $horaireController->getBySiteId($siteId);
    } else {
        $horaireController->getAll();
    }

// GET /api/horaires/{id}
} elseif ($method === 'GET' && preg_match('#^/api/horaires/(\d+)$#', $uri, $matches)) {
    $horaireController->getById((int) $matches[1]);

// POST /api/horaires
} elseif ($method === 'POST' && $uri === '/api/horaires') {
    $horaireController->create();

// PUT /api/horaires/{id}
} elseif ($method === 'PUT' && preg_match('#^/api/horaires/(\d+)$#', $uri, $matches)) {
    $horaireController->update((int) $matches[1]);

// DELETE /api/horaires/{id}
} elseif ($method === 'DELETE' && preg_match('#^/api/horaires/(\d+)$#', $uri, $matches)) {
    $horaireController->delete((int) $matches[1]);

// GET /api/fermetures, GET /api/fermetures?site_id={id}, GET /api/fermetures?globales=1
} elseif ($method === 'GET' && $uri === '/api/fermetures') {
    $fermetureController->getAll();

// GET /api/fermetures/{id}
} elseif ($method === 'GET' && preg_match('#^/api/fermetures/(\d+)$#', $uri, $matches)) {
    $fermetureController->getById((int) $matches[1]);

// POST /api/fermetures
} elseif ($method === 'POST' && $uri === '/api/fermetures') {
    $fermetureController->create();

// PUT /api/fermetures/{id}
} elseif ($method === 'PUT' && preg_match('#^/api/fermetures/(\d+)$#', $uri, $matches)) {
    $fermetureController->update((int) $matches[1]);

// DELETE /api/fermetures/{id}
} elseif ($method === 'DELETE' && preg_match('#^/api/fermetures/(\d+)$#', $uri, $matches)) {
    $fermetureController->delete((int) $matches[1]);

// URL inconnue → erreur 404
} else {
    header('Content-Type: application/json');
    http_response_code(404);
    echo json_encode(['erreur' => 'Route introuvable']);
}
