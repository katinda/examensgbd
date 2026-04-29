<?php

require_once __DIR__ . '/../repositories/AdministrateurRepository.php';
require_once __DIR__ . '/../repositories/SiteRepository.php';

class AdministrateurService {

    public function __construct(
        private AdministrateurRepository $adminRepository,
        private SiteRepository           $siteRepository
    ) {}


    // Retourne tous les administrateurs actifs
    public function getAllAdministrateurs(): array {
        $tous = $this->adminRepository->findAll();
        return array_values(array_filter($tous, fn($a) => $a->isEstActif()));
    }


    // Retourne un administrateur actif par son ID, ou null s'il est inactif ou inexistant
    public function getAdministrateurById(int $id): ?Administrateur {
        $admin = $this->adminRepository->findById($id);

        if ($admin === null || !$admin->isEstActif()) {
            return null;
        }

        return $admin;
    }


    // Retourne un administrateur actif par son login, ou null s'il est inactif ou inexistant
    public function getAdministrateurByLogin(string $login): ?Administrateur {
        $admin = $this->adminRepository->findByLogin($login);

        if ($admin === null || !$admin->isEstActif()) {
            return null;
        }

        return $admin;
    }


    // Crée un nouvel administrateur.
    // Retourne l'ID créé, ou une string décrivant l'erreur.
    //
    // Erreurs possibles :
    //   'type_invalide'   → type n'est pas GLOBAL ou SITE → 400
    //   'site_requis'     → type SITE sans site_id → 400
    //   'site_interdit'   → type GLOBAL avec site_id → 400
    //   'site_introuvable'→ site_id fourni mais le site n'existe pas → 404
    //   'doublon_login'   → ce login existe déjà → 409
    public function createAdministrateur(array $data): int|string {
        $type   = $data['type']    ?? '';
        $siteId = isset($data['site_id']) ? (int) $data['site_id'] : null;

        // Règle 1 : le type doit être GLOBAL ou SITE
        if (!in_array($type, ['GLOBAL', 'SITE'], true)) {
            return 'type_invalide';
        }

        // Règle 2 : cohérence site_id / type
        if ($type === 'SITE' && $siteId === null) {
            return 'site_requis';
        }
        if ($type === 'GLOBAL' && $siteId !== null) {
            return 'site_interdit';
        }

        // Règle 3 : si type SITE, vérifier que le site existe
        if ($type === 'SITE') {
            $site = $this->siteRepository->findById($siteId);
            if ($site === null) {
                return 'site_introuvable';
            }
        }

        // Règle 4 : le login doit être unique
        if ($this->adminRepository->findByLogin($data['login'] ?? '') !== null) {
            return 'doublon_login';
        }

        $admin = new Administrateur(
            null,
            $data['login'],
            password_hash($data['mot_de_passe'], PASSWORD_BCRYPT),
            $data['nom']    ?? null,
            $data['prenom'] ?? null,
            $data['email']  ?? null,
            $type,
            $siteId,
            true
        );

        return $this->adminRepository->insert($admin);
    }


    // Met à jour un administrateur existant.
    // Retourne false si l'administrateur n'existe pas.
    public function updateAdministrateur(int $id, array $data): bool {
        $admin = $this->adminRepository->findById($id);

        if ($admin === null) {
            return false;
        }

        if (isset($data['nom']))       $admin->setNom($data['nom']);
        if (isset($data['prenom']))    $admin->setPrenom($data['prenom']);
        if (isset($data['email']))     $admin->setEmail($data['email']);
        if (isset($data['est_actif'])) $admin->setEstActif((bool) $data['est_actif']);

        $this->adminRepository->update($admin);
        return true;
    }


    // Soft-delete : désactive l'administrateur au lieu de le supprimer définitivement.
    // Retourne false si l'administrateur n'existe pas.
    public function deleteAdministrateur(int $id): bool {
        $admin = $this->adminRepository->findById($id);

        if ($admin === null) {
            return false;
        }

        $admin->setEstActif(false);
        $this->adminRepository->update($admin);
        return true;
    }
}
