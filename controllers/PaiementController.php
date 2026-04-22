<?php

require_once __DIR__ . '/../services/PaiementService.php';

// Le controller reçoit les requêtes HTTP et renvoie du JSON.
// Il ne contient aucune logique métier — il appelle le service et formate la réponse.

class PaiementController {

    public function __construct(private PaiementService $paiementService) {}


    // GET /api/inscriptions/{id}/paiement → consulte le paiement d'une inscription
    public function getByInscription(int $inscriptionId): void {
        $result = $this->paiementService->getPaiementByInscription($inscriptionId);

        header('Content-Type: application/json');

        match ($result) {
            'inscription_introuvable' => (function() use ($inscriptionId) {
                http_response_code(404);
                echo json_encode(['erreur' => "Inscription $inscriptionId introuvable"]);
            })(),
            'paiement_introuvable' => (function() use ($inscriptionId) {
                http_response_code(404);
                echo json_encode(['erreur' => "Aucun paiement trouvé pour l'inscription $inscriptionId"]);
            })(),
            default => (function() use ($result) {
                echo json_encode($this->toArray($result), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            })(),
        };
    }


    // POST /api/inscriptions/{id}/paiement → enregistre le paiement d'une inscription
    // Codes possibles : 201 (créé), 400 (montant/méthode invalide), 404 (inscription introuvable), 409 (déjà payé)
    public function create(int $inscriptionId): void {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        $result = $this->paiementService->createPaiement($inscriptionId, $data);

        header('Content-Type: application/json');

        match ($result) {
            'inscription_introuvable' => (function() use ($inscriptionId) {
                http_response_code(404);
                echo json_encode(['erreur' => "Inscription $inscriptionId introuvable"]);
            })(),
            'paiement_deja_existant' => (function() use ($inscriptionId) {
                http_response_code(409);
                echo json_encode(['erreur' => "L'inscription $inscriptionId a déjà un paiement enregistré"]);
            })(),
            'montant_invalide' => (function() {
                http_response_code(400);
                echo json_encode(['erreur' => 'Le montant doit être exactement 15.00 €']);
            })(),
            'methode_invalide' => (function() {
                http_response_code(400);
                echo json_encode(['erreur' => 'Méthode invalide. Valeurs acceptées : CARTE, VIREMENT, ESPECES, MOBILE']);
            })(),
            default => (function() use ($result) {
                http_response_code(201);
                echo json_encode(['message' => 'Paiement enregistré avec succès', 'id' => $result], JSON_UNESCAPED_UNICODE);
            })(),
        };
    }


    // DELETE /api/paiements/{id} → annule un paiement (soft, ne supprime pas la ligne)
    // Codes possibles : 200 (annulé), 404 (introuvable), 409 (déjà annulé)
    public function annuler(int $paiementId): void {
        $result = $this->paiementService->annulerPaiement($paiementId);

        header('Content-Type: application/json');

        match ($result) {
            'paiement_introuvable' => (function() use ($paiementId) {
                http_response_code(404);
                echo json_encode(['erreur' => "Paiement $paiementId introuvable"]);
            })(),
            'paiement_deja_annule' => (function() use ($paiementId) {
                http_response_code(409);
                echo json_encode(['erreur' => "Le paiement $paiementId est déjà annulé"]);
            })(),
            default => (function() {
                echo json_encode(['message' => 'Paiement annulé avec succès'], JSON_UNESCAPED_UNICODE);
            })(),
        };
    }


    private function toArray(Paiement $p): array {
        return [
            'id'                => $p->getPaiementId(),
            'inscription_id'    => $p->getInscriptionId(),
            'montant'           => $p->getMontant(),
            'date_paiement'     => $p->getDatePaiement(),
            'methode'           => $p->getMethode(),
            'est_annule'        => $p->isEstAnnule(),
            'montant_rembourse' => $p->getMontantRembourse(),
            'date_annulation'   => $p->getDateAnnulation(),
        ];
    }
}
