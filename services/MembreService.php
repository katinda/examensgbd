<?php

require_once __DIR__ . '/../repositories/MembreRepository.php';
require_once __DIR__ . '/../repositories/SiteRepository.php';

// Le service contient toute la logique métier des membres.
// Il utilise deux repositories :
// - MembreRepository pour gérer les membres
// - SiteRepository pour vérifier qu'un site existe (obligatoire pour catégorie S)

class MembreService {

    public function __construct(
        private MembreRepository $membreRepository,
        private SiteRepository   $siteRepository
    ) {}


    // Retourne tous les membres actifs
    public function getAllMembres(): array {
        $tous = $this->membreRepository->findAll();
        return array_values(array_filter($tous, fn($m) => $m->isEstActif()));
    }


    // Retourne les membres actifs d'une catégorie (G, S ou L)
    public function getMembresByCategorie(string $categorie): array {
        $tous = $this->membreRepository->findByCategorie($categorie);
        return array_values(array_filter($tous, fn($m) => $m->isEstActif()));
    }


    // Retourne un membre actif par son ID, ou null s'il est inactif ou inexistant
    public function getMembreById(int $id): ?Membre {
        $membre = $this->membreRepository->findById($id);

        if ($membre === null || !$membre->isEstActif()) {
            return null;
        }

        return $membre;
    }


    // Retourne un membre actif par son matricule, ou null s'il est inactif ou inexistant
    public function getMembreByMatricule(string $matricule): ?Membre {
        $membre = $this->membreRepository->findByMatricule($matricule);

        if ($membre === null || !$membre->isEstActif()) {
            return null;
        }

        return $membre;
    }


    // Crée un nouveau membre.
    // Retourne l'ID créé, ou une string décrivant l'erreur.
    //
    // Erreurs possibles :
    //   'matricule_invalide' → format incorrect (ex: S123 pour catégorie G) → 400
    //   'site_requis'        → catégorie S sans Site_ID → 400
    //   'site_interdit'      → catégorie G ou L avec un Site_ID → 400
    //   'site_introuvable'   → Site_ID fourni mais le site n'existe pas → 404
    //   'doublon_matricule'  → ce matricule existe déjà → 409
    public function createMembre(array $data): int|string {
        $categorie = $data['categorie'] ?? '';
        $matricule = $data['matricule'] ?? '';
        $siteId    = isset($data['site_id']) ? (int) $data['site_id'] : null;

        // Règle 1 : le format du matricule doit correspondre à la catégorie
        // G → commence par G suivi de chiffres : G0001
        // S → commence par S suivi de chiffres : S00001
        // L → commence par L suivi de chiffres : L00001
        $prefixes = ['G' => '/^G\d+$/', 'S' => '/^S\d+$/', 'L' => '/^L\d+$/'];
        if (!isset($prefixes[$categorie]) || !preg_match($prefixes[$categorie], $matricule)) {
            return 'matricule_invalide';
        }

        // Règle 2 : cohérence Site_ID / catégorie
        if ($categorie === 'S' && $siteId === null) {
            return 'site_requis'; // un membre S doit être rattaché à un site
        }
        if (in_array($categorie, ['G', 'L']) && $siteId !== null) {
            return 'site_interdit'; // un membre G ou L ne peut pas avoir de site
        }

        // Règle 3 : si catégorie S, vérifier que le site existe
        if ($categorie === 'S') {
            $site = $this->siteRepository->findById($siteId);
            if ($site === null) {
                return 'site_introuvable';
            }
        }

        // Règle 4 : le matricule doit être unique
        if ($this->membreRepository->findByMatricule($matricule) !== null) {
            return 'doublon_matricule';
        }

        $membre = new Membre(
            null,
            $matricule,
            $data['nom'],
            $data['prenom'],
            $data['email'] ?? null,
            $data['telephone'] ?? null,
            $categorie,
            $siteId,
            true
        );

        return $this->membreRepository->insert($membre);
    }


    // Met à jour un membre existant.
    // Retourne false si le membre n'existe pas.
    public function updateMembre(int $id, array $data): bool {
        $membre = $this->membreRepository->findById($id);

        if ($membre === null) {
            return false;
        }

        if (isset($data['nom']))       $membre->setNom($data['nom']);
        if (isset($data['prenom']))    $membre->setPrenom($data['prenom']);
        if (isset($data['email']))     $membre->setEmail($data['email']);
        if (isset($data['telephone'])) $membre->setTelephone($data['telephone']);
        if (isset($data['est_actif'])) $membre->setEstActif((bool) $data['est_actif']);

        $this->membreRepository->update($membre);
        return true;
    }


    // Soft-delete : désactive le membre au lieu de le supprimer définitivement.
    // Retourne false si le membre n'existe pas.
    public function deleteMembre(int $id): bool {
        $membre = $this->membreRepository->findById($id);

        if ($membre === null) {
            return false;
        }

        $membre->setEstActif(false);
        $this->membreRepository->update($membre);
        return true;
    }
}
