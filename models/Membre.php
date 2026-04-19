<?php

class Membre {
    private ?int $membreId;
    private string $matricule;
    private string $nom;
    private string $prenom;
    private ?string $email;
    private ?string $telephone;
    private string $categorie;   // 'G', 'S' ou 'L'
    private ?int $siteId;        // null sauf si categorie = 'S'
    private bool $estActif;
    private ?string $dateCreation;

    public function __construct(
        ?int $membreId,
        string $matricule,
        string $nom,
        string $prenom,
        ?string $email,
        ?string $telephone,
        string $categorie,
        ?int $siteId,
        bool $estActif = true,
        ?string $dateCreation = null
    ) {
        $this->membreId     = $membreId;
        $this->matricule    = $matricule;
        $this->nom          = $nom;
        $this->prenom       = $prenom;
        $this->email        = $email;
        $this->telephone    = $telephone;
        $this->categorie    = $categorie;
        $this->siteId       = $siteId;
        $this->estActif     = $estActif;
        $this->dateCreation = $dateCreation;
    }

    public function getMembreId(): ?int       { return $this->membreId; }
    public function getMatricule(): string    { return $this->matricule; }
    public function getNom(): string          { return $this->nom; }
    public function getPrenom(): string       { return $this->prenom; }
    public function getEmail(): ?string       { return $this->email; }
    public function getTelephone(): ?string   { return $this->telephone; }
    public function getCategorie(): string    { return $this->categorie; }
    public function getSiteId(): ?int         { return $this->siteId; }
    public function isEstActif(): bool        { return $this->estActif; }
    public function getDateCreation(): ?string { return $this->dateCreation; }

    public function setMembreId(?int $membreId): void       { $this->membreId = $membreId; }
    public function setMatricule(string $matricule): void   { $this->matricule = $matricule; }
    public function setNom(string $nom): void               { $this->nom = $nom; }
    public function setPrenom(string $prenom): void         { $this->prenom = $prenom; }
    public function setEmail(?string $email): void          { $this->email = $email; }
    public function setTelephone(?string $telephone): void  { $this->telephone = $telephone; }
    public function setCategorie(string $categorie): void   { $this->categorie = $categorie; }
    public function setSiteId(?int $siteId): void           { $this->siteId = $siteId; }
    public function setEstActif(bool $estActif): void       { $this->estActif = $estActif; }
    public function setDateCreation(?string $d): void       { $this->dateCreation = $d; }
}
