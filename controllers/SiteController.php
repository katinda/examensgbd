<?php

require_once __DIR__ . '/../services/SiteService.php';

// Le controller reçoit les requêtes HTTP et renvoie du JSON.
// Il ne fait jamais de SQL et ne contient pas de logique métier.
// Son seul rôle : appeler le bon service et formater la réponse.

class SiteController {

    // Le service est reçu en paramètre (injection de dépendance).
    public function __construct(private SiteService $siteService) {}


    // Gère la requête GET /sites
    // Retourne la liste de tous les sites actifs en JSON
    public function getAll(): void {
        $sites = $this->siteService->getAllSites();

        // On transforme les objets Site en tableaux simples pour pouvoir les convertir en JSON
        $data = array_map(fn($site) => [
            'id'           => $site->getSiteId(),
            'nom'          => $site->getNom(),
            'adresse'      => $site->getAdresse(),
            'ville'        => $site->getVille(),
            'code_postal'  => $site->getCodePostal(),
            'est_actif'    => $site->isEstActif(),
            'date_creation'=> $site->getDateCreation(),
        ], $sites);

        // On envoie la réponse en JSON avec le bon header HTTP
        header('Content-Type: application/json');
        echo json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }


    // Gère la requête GET /sites/{id}
    // Retourne un seul site en JSON, ou une erreur 404 s'il n'existe pas
    public function getById(int $id): void {
        $site = $this->siteService->getSiteById($id);

        if ($site === null) {
            // Le site n'existe pas : on renvoie une erreur 404
            header('Content-Type: application/json');
            http_response_code(404);
            echo json_encode(['erreur' => "Site $id introuvable"]);
            return;
        }

        header('Content-Type: application/json');
        echo json_encode([
            'id'           => $site->getSiteId(),
            'nom'          => $site->getNom(),
            'adresse'      => $site->getAdresse(),
            'ville'        => $site->getVille(),
            'code_postal'  => $site->getCodePostal(),
            'est_actif'    => $site->isEstActif(),
            'date_creation'=> $site->getDateCreation(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
