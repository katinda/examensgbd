<?php
require_once __DIR__ . '/../services/MembreService.php';
class MembreController {
    public function __construct(private MembreService $membreService) {}
    public function getAll(): void {
        $categorie = $_GET['categorie'] ?? null;
        $membres = $categorie !== null
            ? $this->membreService->getMembresByCategorie(strtoupper($categorie))
            : $this->membreService->getAllMembres();
        header('Content-Type: application/json');
        echo json_encode(array_map(fn($m) => $this->toArray($m), $membres), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    private function toArray(Membre $m): array {
        return ['id' => $m->getMembreId(), 'matricule' => $m->getMatricule(), 'nom' => $m->getNom(),
            'prenom' => $m->getPrenom(), 'email' => $m->getEmail(), 'telephone' => $m->getTelephone(),
            'categorie' => $m->getCategorie(), 'site_id' => $m->getSiteId(),
            'est_actif' => $m->isEstActif(), 'date_creation' => $m->getDateCreation()];
    }
}
