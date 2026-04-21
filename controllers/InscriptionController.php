<?php

require_once __DIR__ . '/../services/InscriptionService.php';

// Le controller reçoit les requêtes HTTP et renvoie du JSON.
// Il ne contient aucune logique métier — il appelle le service et formate la réponse.

class InscriptionController {

    // Le service est reçu en paramètre (injection de dépendance).
    public function __construct(private InscriptionService $inscriptionService) {}


    // GET /api/reservations/{id}/inscriptions → retourne la liste des joueurs inscrits
    public function getByReservation(int $reservationId): void {
        $inscriptions = $this->inscriptionService->getInscriptionsByReservation($reservationId);

        header('Content-Type: application/json');
        echo json_encode(array_map(fn($i) => $this->toArray($i), $inscriptions), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }


    // POST /api/reservations/{id}/inscriptions → ajoute un joueur à la réservation
    // Codes possibles : 201 (inscrit), 400 (champ manquant ou réservation complète), 404 (réservation/membre introuvable), 409 (déjà inscrit)
    public function addJoueur(int $reservationId): void {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['membre_id'])) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['erreur' => 'Le champ "membre_id" est obligatoire']);
            return;
        }

        $result = $this->inscriptionService->addJoueur($reservationId, (int) $data['membre_id']);

        header('Content-Type: application/json');

        match ($result) {
            'reservation_introuvable' => (function() use ($reservationId) {
                http_response_code(404);
                echo json_encode(['erreur' => "Réservation $reservationId introuvable"]);
            })(),
            'membre_introuvable'      => (function() use ($data) {
                http_response_code(404);
                echo json_encode(['erreur' => "Membre {$data['membre_id']} introuvable"]);
            })(),
            'reservation_complete'    => (function() {
                http_response_code(409);
                echo json_encode(['erreur' => 'Cette réservation est déjà complète (4 joueurs maximum)']);
            })(),
            'deja_inscrit'            => (function() use ($data, $reservationId) {
                http_response_code(409);
                echo json_encode(['erreur' => "Le membre {$data['membre_id']} est déjà inscrit à cette réservation"]);
            })(),
            default                   => (function() use ($result) {
                http_response_code(201);
                echo json_encode(['message' => 'Joueur inscrit avec succès', 'id' => $result], JSON_UNESCAPED_UNICODE);
            })(),
        };
    }


    // DELETE /api/reservations/{id}/inscriptions/{membreId} → retire un joueur de la réservation
    public function removeJoueur(int $reservationId, int $membreId): void {
        $supprime = $this->inscriptionService->removeJoueur($reservationId, $membreId);

        header('Content-Type: application/json');

        if (!$supprime) {
            http_response_code(404);
            echo json_encode(['erreur' => "Le membre $membreId n'est pas inscrit à la réservation $reservationId"]);
            return;
        }

        echo json_encode(['message' => 'Joueur retiré avec succès'], JSON_UNESCAPED_UNICODE);
    }


    // Transforme un objet Inscription en tableau simple pour le JSON
    private function toArray(Inscription $i): array {
        return [
            'id'               => $i->getInscriptionId(),
            'reservation_id'   => $i->getReservationId(),
            'membre_id'        => $i->getMembreId(),
            'est_organisateur' => $i->isEstOrganisateur(),
        ];
    }
}
