<?php
require_once __DIR__ . '/../services/MembreService.php';
class MembreController {
    public function __construct(private MembreService $membreService) {}
    public function getByMatricule(string $matricule): void {
        $membre = $this->membreService->getMembreByMatricule($matricule);
        header('Content-Type: application/json');
        if ($membre === null) { http_response_code(404); echo json_encode(['erreur' => "Membre avec matricule $matricule introuvable"]); return; }
        echo json_encode($this->toArray($membre), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    private function toArray(Membre $m): array {
        return ['id' => $m->getMembreId(), 'matricule' => $m->getMatricule(), 'nom' => $m->getNom(),
            'prenom' => $m->getPrenom(), 'email' => $m->getEmail(), 'telephone' => $m->getTelephone(),
            'categorie' => $m->getCategorie(), 'site_id' => $m->getSiteId(),
            'est_actif' => $m->isEstActif(), 'date_creation' => $m->getDateCreation()];
    }
}
