<?php

require_once __DIR__ . '/../repositories/AdministrateurRepository.php';
require_once __DIR__ . '/../repositories/SiteRepository.php';

class AdministrateurService {

    public function __construct(
        private AdministrateurRepository $adminRepository,
        private SiteRepository           $siteRepository
    ) {}

    // Crée un nouvel administrateur.
    // Erreurs : 'type_invalide', 'site_requis', 'site_interdit', 'site_introuvable', 'doublon_login'
    public function createAdministrateur(array $data): int|string {
        $type   = $data['type']    ?? '';
        $siteId = isset($data['site_id']) ? (int) $data['site_id'] : null;

        if (!in_array($type, ['GLOBAL', 'SITE'], true)) {
            return 'type_invalide';
        }

        if ($type === 'SITE' && $siteId === null) {
            return 'site_requis';
        }
        if ($type === 'GLOBAL' && $siteId !== null) {
            return 'site_interdit';
        }

        if ($type === 'SITE') {
            $site = $this->siteRepository->findById($siteId);
            if ($site === null) {
                return 'site_introuvable';
            }
        }

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
}
