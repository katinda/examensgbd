<?php

class Fermeture {

    private ?int $fermetureId;  // numéro unique (colonne Fermeture_ID)
    private ?int $siteId;       // null = fermeture globale, renseigné = fermeture locale (colonne Site_ID)
    private string $dateDebut;  // début de la fermeture (colonne Date_Debut)
    private string $dateFin;    // fin de la fermeture (colonne Date_Fin)
    private ?string $raison;    // motif, peut être vide (colonne Raison)
    private ?string $dateCreation; // date de création automatique (colonne Date_Creation)

    public function __construct(
        ?int $fermetureId,
        ?int $siteId,
        string $dateDebut,
        string $dateFin,
        ?string $raison = null,
        ?string $dateCreation = null
    ) {
        $this->fermetureId  = $fermetureId;
        $this->siteId       = $siteId;
        $this->dateDebut    = $dateDebut;
        $this->dateFin      = $dateFin;
        $this->raison       = $raison;
        $this->dateCreation = $dateCreation;
    }

    public function getFermetureId(): ?int { return $this->fermetureId; }
    public function setFermetureId(?int $fermetureId): void { $this->fermetureId = $fermetureId; }

    public function getSiteId(): ?int { return $this->siteId; }
    public function setSiteId(?int $siteId): void { $this->siteId = $siteId; }

    public function getDateDebut(): string { return $this->dateDebut; }
    public function setDateDebut(string $dateDebut): void { $this->dateDebut = $dateDebut; }

    public function getDateFin(): string { return $this->dateFin; }
    public function setDateFin(string $dateFin): void { $this->dateFin = $dateFin; }

    public function getRaison(): ?string { return $this->raison; }
    public function setRaison(?string $raison): void { $this->raison = $raison; }

    public function getDateCreation(): ?string { return $this->dateCreation; }
    public function setDateCreation(?string $dateCreation): void { $this->dateCreation = $dateCreation; }
}
