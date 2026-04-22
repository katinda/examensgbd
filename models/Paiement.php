<?php

// Représente un paiement lié à une inscription (relation 1-1).
// Un paiement absent = joueur pas encore payé (pas de ligne en base).
// Une annulation ne supprime pas la ligne : Est_Annule passe à 1,
// Date_Annulation et Montant_Rembourse sont renseignés pour garder l'historique.

class Paiement {

    public function __construct(
        private ?int    $paiementId,
        private int     $inscriptionId,
        private float   $montant,
        private ?string $datePaiement     = null,
        private ?string $methode          = null,
        private bool    $estAnnule        = false,
        private ?float  $montantRembourse = null,
        private ?string $dateAnnulation   = null
    ) {}


    public function getPaiementId(): ?int    { return $this->paiementId; }
    public function getInscriptionId(): int  { return $this->inscriptionId; }
    public function getMontant(): float      { return $this->montant; }
    public function getDatePaiement(): ?string { return $this->datePaiement; }
    public function getMethode(): ?string    { return $this->methode; }
    public function isEstAnnule(): bool      { return $this->estAnnule; }
    public function getMontantRembourse(): ?float  { return $this->montantRembourse; }
    public function getDateAnnulation(): ?string   { return $this->dateAnnulation; }


    public function annuler(float $montantRembourse, string $dateAnnulation): void {
        $this->estAnnule        = true;
        $this->montantRembourse = $montantRembourse;
        $this->dateAnnulation   = $dateAnnulation;
    }
}
