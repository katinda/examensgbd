<?php

// On a besoin de la classe Site pour pouvoir créer des objets Site
require_once __DIR__ . '/../models/Site.php';

// Un repository c'est comme un traducteur entre PHP et MySQL.
// Toutes les questions qu'on pose à la base de données sur les sites passent par ici.
// Cette classe ne contient QUE du SQL. Pas de logique métier.

class SiteRepository {

    // Le constructeur reçoit la connexion PDO en paramètre.
    // C'est ce qu'on appelle "injection de dépendance" :
    // on ne crée pas la connexion ici, on la reçoit de l'extérieur.
    // Comme ça, toute l'application partage la même connexion.
    public function __construct(private PDO $pdo) {}


    // Retourne TOUS les sites de la base de données sous forme de tableau d'objets Site
    public function findAll(): array {
        $stmt = $this->pdo->query("SELECT * FROM Sites");
        return $this->hydrate($stmt->fetchAll(PDO::FETCH_ASSOC));
    }


    // Retourne UN site à partir de son ID.
    // Si le site n'existe pas, retourne null (rien).
    // On utilise "prepare" pour éviter les injections SQL (c'est plus sécurisé que d'écrire l'ID directement dans la requête).
    public function findById(int $id): ?Site {
        $stmt = $this->pdo->prepare("SELECT * FROM Sites WHERE Site_ID = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->hydrateOne($row) : null;
    }


    // Crée un nouveau site dans la base de données.
    // Retourne l'ID que MySQL a attribué automatiquement au nouveau site.
    public function insert(Site $site): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO Sites (Nom, Adresse, Ville, Code_Postal, Est_Actif)
            VALUES (:nom, :adresse, :ville, :codePostal, :estActif)
        ");
        $stmt->execute([
            ':nom'        => $site->getNom(),
            ':adresse'    => $site->getAdresse(),
            ':ville'      => $site->getVille(),
            ':codePostal' => $site->getCodePostal(),
            ':estActif'   => $site->isEstActif() ? 1 : 0, // true devient 1, false devient 0 en base
        ]);
        return (int) $this->pdo->lastInsertId(); // récupère l'ID généré par MySQL
    }


    // Met à jour un site existant dans la base de données.
    // On identifie le site à modifier grâce à son ID.
    public function update(Site $site): void {
        $stmt = $this->pdo->prepare("
            UPDATE Sites
            SET Nom = :nom, Adresse = :adresse, Ville = :ville,
                Code_Postal = :codePostal, Est_Actif = :estActif
            WHERE Site_ID = :id
        ");
        $stmt->execute([
            ':id'         => $site->getSiteId(),
            ':nom'        => $site->getNom(),
            ':adresse'    => $site->getAdresse(),
            ':ville'      => $site->getVille(),
            ':codePostal' => $site->getCodePostal(),
            ':estActif'   => $site->isEstActif() ? 1 : 0,
        ]);
    }


    // Supprime un site de la base de données à partir de son ID.
    public function delete(int $id): void {
        $stmt = $this->pdo->prepare("DELETE FROM Sites WHERE Site_ID = :id");
        $stmt->execute([':id' => $id]);
    }


    // Transforme un tableau de plusieurs lignes SQL en tableau d'objets Site.
    // Utilisée par findAll() qui ramène plusieurs lignes.
    private function hydrate(array $rows): array {
        return array_map(fn($row) => $this->hydrateOne($row), $rows);
    }


    // Transforme UNE ligne SQL (tableau associatif) en objet Site.
    // C'est comme remplir une fiche d'identité avec les données venant de MySQL.
    // Exemple : $row['Nom'] = "Club Paris" → $site->setNom("Club Paris")
    private function hydrateOne(array $row): Site {
        $site = new Site();
        $site->setSiteId((int) $row['Site_ID']);
        $site->setNom($row['Nom']);
        $site->setAdresse($row['Adresse']);
        $site->setVille($row['Ville']);
        $site->setCodePostal($row['Code_Postal']);
        $site->setEstActif((bool) $row['Est_Actif']);
        $site->setDateCreation($row['Date_Creation']);
        return $site;
    }
}
