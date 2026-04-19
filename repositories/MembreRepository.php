<?php

require_once __DIR__ . '/../models/Membre.php';

class MembreRepository {

    public function __construct(private PDO $pdo) {}

    public function findAll(): array {
        $stmt = $this->pdo->query("SELECT * FROM Membres");
        return $this->hydrate($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function findByCategorie(string $categorie): array {
        $stmt = $this->pdo->prepare("SELECT * FROM Membres WHERE Categorie = :categorie");
        $stmt->execute([':categorie' => $categorie]);
        return $this->hydrate($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function findById(int $id): ?Membre {
        $stmt = $this->pdo->prepare("SELECT * FROM Membres WHERE Membre_ID = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->hydrateOne($row) : null;
    }

    public function findByMatricule(string $matricule): ?Membre {
        $stmt = $this->pdo->prepare("SELECT * FROM Membres WHERE Matricule = :matricule");
        $stmt->execute([':matricule' => $matricule]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->hydrateOne($row) : null;
    }

    public function insert(Membre $membre): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO Membres (Matricule, Nom, Prenom, Email, Telephone, Categorie, Site_ID, Est_Actif)
            VALUES (:matricule, :nom, :prenom, :email, :telephone, :categorie, :siteId, :estActif)
        ");
        $stmt->execute([
            ':matricule'  => $membre->getMatricule(),
            ':nom'        => $membre->getNom(),
            ':prenom'     => $membre->getPrenom(),
            ':email'      => $membre->getEmail(),
            ':telephone'  => $membre->getTelephone(),
            ':categorie'  => $membre->getCategorie(),
            ':siteId'     => $membre->getSiteId(),
            ':estActif'   => $membre->isEstActif() ? 1 : 0,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(Membre $membre): void {
        $stmt = $this->pdo->prepare("
            UPDATE Membres
            SET Matricule = :matricule, Nom = :nom, Prenom = :prenom,
                Email = :email, Telephone = :telephone, Categorie = :categorie,
                Site_ID = :siteId, Est_Actif = :estActif
            WHERE Membre_ID = :id
        ");
        $stmt->execute([
            ':id'         => $membre->getMembreId(),
            ':matricule'  => $membre->getMatricule(),
            ':nom'        => $membre->getNom(),
            ':prenom'     => $membre->getPrenom(),
            ':email'      => $membre->getEmail(),
            ':telephone'  => $membre->getTelephone(),
            ':categorie'  => $membre->getCategorie(),
            ':siteId'     => $membre->getSiteId(),
            ':estActif'   => $membre->isEstActif() ? 1 : 0,
        ]);
    }

    public function delete(int $id): void {
        $stmt = $this->pdo->prepare("DELETE FROM Membres WHERE Membre_ID = :id");
        $stmt->execute([':id' => $id]);
    }

    private function hydrate(array $rows): array {
        return array_map(fn($row) => $this->hydrateOne($row), $rows);
    }

    private function hydrateOne(array $row): Membre {
        return new Membre(
            (int) $row['Membre_ID'],
            $row['Matricule'],
            $row['Nom'],
            $row['Prenom'],
            $row['Email'],
            $row['Telephone'],
            $row['Categorie'],
            $row['Site_ID'] !== null ? (int) $row['Site_ID'] : null,
            (bool) $row['Est_Actif'],
            $row['Date_Creation']
        );
    }
}
