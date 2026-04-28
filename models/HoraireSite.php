<?php

class HoraireSite {

    private ?int $horaireId;  // numéro unique (colonne Horaire_ID)
    private int $siteId;      // site concerné (colonne Site_ID)
    private int $annee;       // année civile (colonne Annee)
    private string $heureDebut; // début de la première réservation possible (colonne Heure_Debut)
    private string $heureFin;   // fin de la dernière réservation possible (colonne Heure_Fin)

    public function __construct(
        ?int $horaireId,
        int $siteId,
        int $annee,
        string $heureDebut,
        string $heureFin
    ) {
        $this->horaireId  = $horaireId;
        $this->siteId     = $siteId;
        $this->annee      = $annee;
        $this->heureDebut = $heureDebut;
        $this->heureFin   = $heureFin;
    }

    public function getHoraireId(): ?int { return $this->horaireId; }
    public function setHoraireId(?int $horaireId): void { $this->horaireId = $horaireId; }

    public function getSiteId(): int { return $this->siteId; }
    public function setSiteId(int $siteId): void { $this->siteId = $siteId; }

    public function getAnnee(): int { return $this->annee; }
    public function setAnnee(int $annee): void { $this->annee = $annee; }

    public function getHeureDebut(): string { return $this->heureDebut; }
    public function setHeureDebut(string $heureDebut): void { $this->heureDebut = $heureDebut; }

    public function getHeureFin(): string { return $this->heureFin; }
    public function setHeureFin(string $heureFin): void { $this->heureFin = $heureFin; }
}
