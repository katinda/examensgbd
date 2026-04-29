<?php

require_once __DIR__ . '/../models/Administrateur.php';

class AdministrateurRepository {

    public function __construct(private PDO $pdo) {}

    public function update(Administrateur $admin): void {
        $stmt = $this->pdo->prepare("
            UPDATE Administrateurs
            SET Login = :login, Mot_De_Passe_Hash = :motDePasseHash, Nom = :nom, Prenom = :prenom,
                Email = :email, Type = :type, Site_ID = :siteId, Est_Actif = :estActif
            WHERE Admin_ID = :id
        ");
        $stmt->execute([
            ':id'             => $admin->getAdminId(),
            ':login'          => $admin->getLogin(),
            ':motDePasseHash' => $admin->getMotDePasseHash(),
            ':nom'            => $admin->getNom(),
            ':prenom'         => $admin->getPrenom(),
            ':email'          => $admin->getEmail(),
            ':type'           => $admin->getType(),
            ':siteId'         => $admin->getSiteId(),
            ':estActif'       => $admin->isEstActif() ? 1 : 0,
        ]);
    }

    private function hydrateOne(array $row): Administrateur {
        return new Administrateur(
            (int) $row['Admin_ID'],
            $row['Login'],
            $row['Mot_De_Passe_Hash'],
            $row['Nom'],
            $row['Prenom'],
            $row['Email'],
            $row['Type'],
            $row['Site_ID'] !== null ? (int) $row['Site_ID'] : null,
            (bool) $row['Est_Actif'],
            $row['Date_Creation']
        );
    }
}
