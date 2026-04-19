<?php

require_once __DIR__ . '/../models/Membre.php';

class MembreRepository {

    public function __construct(private PDO $pdo) {}

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
}
