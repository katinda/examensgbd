<?php

// Un modèle c'est comme une fiche d'identité pour un site.
// Quand on récupère un site depuis la base de données,
// on range toutes ses infos dans un objet Site plutôt que dans un tableau brut.
// Cette classe ne fait JAMAIS de SQL. Elle stocke juste des données.

class Site {

    // Chaque propriété correspond à une colonne de la table Sites en base de données.
    // Le ? devant le type (ex: ?string) veut dire que la valeur peut être null (vide).

    private int $siteId;        // numéro unique du site (colonne Site_ID)
    private string $nom;        // nom du site, obligatoire (colonne Nom)
    private ?string $adresse;   // adresse, peut être vide (colonne Adresse)
    private ?string $ville;     // ville, peut être vide (colonne Ville)
    private ?string $codePostal;// code postal, peut être vide (colonne Code_Postal)
    private bool $estActif;     // true = site ouvert, false = site fermé (colonne Est_Actif)
    private string $dateCreation; // date à laquelle le site a été créé (colonne Date_Creation)

    // --- Getters et Setters ---
    // Un getter permet de LIRE une propriété depuis l'extérieur de la classe.
    // Un setter permet de MODIFIER une propriété depuis l'extérieur de la classe.
    // On les utilise car les propriétés sont "private" (on ne peut pas y accéder directement).

    // Exemple d'utilisation :
    // $site->getNom()         → lit le nom du site
    // $site->setNom("Paris")  → change le nom du site

    public function getSiteId(): int { return $this->siteId; }
    public function setSiteId(int $siteId): void { $this->siteId = $siteId; }

    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): void { $this->nom = $nom; }

    public function getAdresse(): ?string { return $this->adresse; }
    public function setAdresse(?string $adresse): void { $this->adresse = $adresse; }

    public function getVille(): ?string { return $this->ville; }
    public function setVille(?string $ville): void { $this->ville = $ville; }

    public function getCodePostal(): ?string { return $this->codePostal; }
    public function setCodePostal(?string $codePostal): void { $this->codePostal = $codePostal; }

    public function isEstActif(): bool { return $this->estActif; }
    public function setEstActif(bool $estActif): void { $this->estActif = $estActif; }

    public function getDateCreation(): string { return $this->dateCreation; }
    public function setDateCreation(string $dateCreation): void { $this->dateCreation = $dateCreation; }
}
