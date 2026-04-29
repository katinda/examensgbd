<?php

require_once __DIR__ . '/../models/Administrateur.php';

class AdministrateurRepository {

    public function __construct(private PDO $pdo) {}

    public function findAll(): array {
        $stmt = $this->pdo->query("SELECT * FROM Administrateurs");
        return array_map(fn($row) => $this->hydrateOne($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function findById(int $id): ?Administrateur {
        $stmt = $this->pdo->prepare("SELECT * FROM Administrateurs WHERE Admin_ID = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->hydrateOne($row) : null;
    }

    public function findByLogin(string $login): ?Administrateur {
        $stmt = $this->pdo->prepare("SELECT * FROM Administrateurs WHERE Login = :login");
        $stmt->execute([':login' => $login]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->hydrateOne($row) : null;
    }

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

    public function delete(int $id): void {
        $stmt = $this->pdo->prepare("DELETE FROM Administrateurs WHERE Admin_ID = :id");
        $stmt->execute([':id' => $id]);
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
