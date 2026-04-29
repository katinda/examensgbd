<?php

require_once __DIR__ . '/../models/Administrateur.php';

class AdministrateurRepository {

    public function __construct(private PDO $pdo) {}

    public function findById(int $id): ?Administrateur {
        $stmt = $this->pdo->prepare("SELECT * FROM Administrateurs WHERE Admin_ID = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->hydrateOne($row) : null;
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
