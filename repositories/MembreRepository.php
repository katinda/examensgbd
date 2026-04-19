<?php

require_once __DIR__ . '/../models/Membre.php';

class MembreRepository {

    public function __construct(private PDO $pdo) {}

    public function findByCategorie(string $categorie): array {
        $stmt = $this->pdo->prepare("SELECT * FROM Membres WHERE Categorie = :categorie");
        $stmt->execute([':categorie' => $categorie]);
        return $this->hydrate($stmt->fetchAll(PDO::FETCH_ASSOC));
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
