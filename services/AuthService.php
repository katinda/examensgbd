<?php

require_once __DIR__ . '/../repositories/AdministrateurRepository.php';

// Vérifie les credentials d'un administrateur.
// Utilise password_verify() pour comparer le mot de passe avec le hash bcrypt stocké en DB.

class AuthService {

    public function __construct(private AdministrateurRepository $adminRepository) {}


    // Authentifie un admin par login + mot de passe.
    // Retourne l'admin si les credentials sont valides, ou une string d'erreur.
    //
    // Erreurs possibles :
    //   'identifiants_invalides' → login inconnu ou mot de passe incorrect → 401
    //   'compte_inactif'         → admin désactivé → 403
    public function authentifier(string $login, string $motDePasse): Administrateur|string {
        $admin = $this->adminRepository->findByLogin($login);

        if ($admin === null) {
            return 'identifiants_invalides';
        }

        if (!$admin->isEstActif()) {
            return 'compte_inactif';
        }

        if (!password_verify($motDePasse, $admin->getMotDePasseHash())) {
            return 'identifiants_invalides';
        }

        return $admin;
    }
}
