<?php

require_once __DIR__ . '/../services/MembreService.php';

class MembreController {

    public function __construct(private MembreService $membreService) {}


    // GET /api/membres ou GET /api/membres?categorie=G
    public function getAll(): void {
        $categorie = $_GET['categorie'] ?? null;

        if ($categorie !== null) {
            $membres = $this->membreService->getMembresByCategorie(strtoupper($categorie));
        } else {
            $membres = $this->membreService->getAllMembres();
        }

        header('Content-Type: application/json');
        echo json_encode(array_map(fn($m) => $this->toArray($m), $membres), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }


    // GET /api/membres/{id}
    public function getById(int $id): void {
        $membre = $this->membreService->getMembreById($id);

        header('Content-Type: application/json');

        if ($membre === null) {
            http_response_code(404);
            echo json_encode(['erreur' => "Membre $id introuvable"]);
            return;
        }

        echo json_encode($this->toArray($membre), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }


    // GET /api/membres/matricule/{matricule}
    public function getByMatricule(string $matricule): void {
        $membre = $this->membreService->getMembreByMatricule($matricule);

        header('Content-Type: application/json');

        if ($membre === null) {
            http_response_code(404);
            echo json_encode(['erreur' => "Membre avec matricule $matricule introuvable"]);
            return;
        }

        echo json_encode($this->toArray($membre), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }


    // POST /api/membres
    public function create(): void {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['matricule']) || empty($data['nom']) || empty($data['prenom']) || empty($data['categorie'])) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['erreur' => 'Les champs "matricule", "nom", "prenom" et "categorie" sont obligatoires']);
            return;
        }

        $result = $this->membreService->createMembre($data);

        header('Content-Type: application/json');

        match ($result) {
            'matricule_invalide' => (function() use ($data) {
                http_response_code(400);
                echo json_encode(['erreur' => "Format de matricule invalide : {$data['matricule']}"]);
            })(),
            'site_requis' => (function() {
                http_response_code(400);
                echo json_encode(['erreur' => 'Un membre de catégorie S doit avoir un site_id']);
            })(),
            'site_interdit' => (function() {
                http_response_code(400);
                echo json_encode(['erreur' => 'Un membre de catégorie G ou L ne peut pas avoir de site_id']);
            })(),
            'site_introuvable' => (function() use ($data) {
                http_response_code(404);
                echo json_encode(['erreur' => "Site {$data['site_id']} introuvable"]);
            })(),
            'doublon_matricule' => (function() use ($data) {
                http_response_code(409);
                echo json_encode(['erreur' => "Le matricule {$data['matricule']} existe déjà"]);
            })(),
            default => (function() use ($result) {
                http_response_code(201);
                echo json_encode(['message' => 'Membre créé avec succès', 'id' => $result], JSON_UNESCAPED_UNICODE);
            })(),
        };
    }


    // PUT /api/membres/{id}
    public function update(int $id): void {
        $data = json_decode(file_get_contents('php://input'), true);
        $ok   = $this->membreService->updateMembre($id, $data);

        header('Content-Type: application/json');

        if (!$ok) {
            http_response_code(404);
            echo json_encode(['erreur' => "Membre $id introuvable"]);
            return;
        }

        echo json_encode(['message' => "Membre $id mis à jour avec succès"], JSON_UNESCAPED_UNICODE);
    }


    // DELETE /api/membres/{id} — soft-delete via Est_Actif = 0
    public function delete(int $id): void {
        $ok = $this->membreService->deleteMembre($id);

        header('Content-Type: application/json');

        if (!$ok) {
            http_response_code(404);
            echo json_encode(['erreur' => "Membre $id introuvable"]);
            return;
        }

        echo json_encode(['message' => "Membre $id désactivé avec succès"], JSON_UNESCAPED_UNICODE);
    }


    private function toArray(Membre $m): array {
        return [
            'id'           => $m->getMembreId(),
            'matricule'    => $m->getMatricule(),
            'nom'          => $m->getNom(),
            'prenom'       => $m->getPrenom(),
            'email'        => $m->getEmail(),
            'telephone'    => $m->getTelephone(),
            'categorie'    => $m->getCategorie(),
            'site_id'      => $m->getSiteId(),
            'est_actif'    => $m->isEstActif(),
            'date_creation' => $m->getDateCreation(),
        ];
    }
}
