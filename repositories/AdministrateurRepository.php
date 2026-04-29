<?php

require_once __DIR__ . '/../models/Administrateur.php';

class AdministrateurRepository {

    public function __construct(private PDO $pdo) {}

    public function insert(Administrateur $admin): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO Administrateurs (Login, Mot_De_Passe_Hash, Nom, Prenom, Email, Type, Site_ID, Est_Actif)
            VALUES (:login, :motDePasseHash, :nom, :prenom, :email, :type, :siteId, :estActif)
        ");
        $stmt->execute([
            ':login'          => $admin->getLogin(),
            ':motDePasseHash' => $admin->getMotDePasseHash(),
            ':nom'            => $admin->getNom(),
            ':prenom'         => $admin->getPrenom(),
            ':email'          => $admin->getEmail(),
            ':type'           => $admin->getType(),
            ':siteId'         => $admin->getSiteId(),
            ':estActif'       => $admin->isEstActif() ? 1 : 0,
        ]);
        return (int) $this->pdo->lastInsertId();
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
