<?php

require_once __DIR__ . '/../models/Membre.php';

class MembreRepository {

    public function __construct(private PDO $pdo) {}

    public function findById(int $id): ?Membre {
        $stmt = $this->pdo->prepare("SELECT * FROM Membres WHERE Membre_ID = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->hydrateOne($row) : null;
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
