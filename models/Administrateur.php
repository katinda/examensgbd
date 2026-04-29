<?php

class Administrateur {

    private ?int $adminId;           // numéro unique (colonne Admin_ID)
    private string $login;           // identifiant de connexion unique (colonne Login)
    private string $motDePasseHash;  // mot de passe hashé bcrypt/argon2 (colonne Mot_De_Passe_Hash)
    private ?string $nom;            // nom de famille (colonne Nom)
    private ?string $prenom;         // prénom (colonne Prenom)
    private ?string $email;          // adresse email (colonne Email)
    private string $type;            // 'GLOBAL' ou 'SITE' (colonne Type)
    private ?int $siteId;            // null si GLOBAL, renseigné si SITE (colonne Site_ID)
    private bool $estActif;          // true = actif, false = désactivé (colonne Est_Actif)
    private ?string $dateCreation;   // date de création automatique (colonne Date_Creation)

    public function __construct(
        ?int $adminId,
        string $login,
        string $motDePasseHash,
        ?string $nom,
        ?string $prenom,
        ?string $email,
        string $type,
        ?int $siteId,
        bool $estActif = true,
        ?string $dateCreation = null
    ) {
        $this->adminId        = $adminId;
        $this->login          = $login;
        $this->motDePasseHash = $motDePasseHash;
        $this->nom            = $nom;
        $this->prenom         = $prenom;
        $this->email          = $email;
        $this->type           = $type;
        $this->siteId         = $siteId;
        $this->estActif       = $estActif;
        $this->dateCreation   = $dateCreation;
    }

    public function getAdminId(): ?int { return $this->adminId; }
    public function setAdminId(?int $adminId): void { $this->adminId = $adminId; }

    public function getLogin(): string { return $this->login; }
    public function setLogin(string $login): void { $this->login = $login; }

    public function getMotDePasseHash(): string { return $this->motDePasseHash; }
    public function setMotDePasseHash(string $motDePasseHash): void { $this->motDePasseHash = $motDePasseHash; }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(?string $nom): void { $this->nom = $nom; }

    public function getPrenom(): ?string { return $this->prenom; }
    public function setPrenom(?string $prenom): void { $this->prenom = $prenom; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $email): void { $this->email = $email; }

    public function getType(): string { return $this->type; }
    public function setType(string $type): void { $this->type = $type; }

    public function getSiteId(): ?int { return $this->siteId; }
    public function setSiteId(?int $siteId): void { $this->siteId = $siteId; }

    public function isEstActif(): bool { return $this->estActif; }
    public function setEstActif(bool $estActif): void { $this->estActif = $estActif; }

    public function getDateCreation(): ?string { return $this->dateCreation; }
    public function setDateCreation(?string $dateCreation): void { $this->dateCreation = $dateCreation; }
}
