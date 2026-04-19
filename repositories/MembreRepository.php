<?php

require_once __DIR__ . '/../models/Membre.php';

class MembreRepository {

    public function __construct(private PDO $pdo) {}

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
}
