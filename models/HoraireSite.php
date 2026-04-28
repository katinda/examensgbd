<?php

// Un modèle c'est comme une fiche d'identité pour un horaire de site.
// Quand on récupère un horaire depuis la base de données,
// on range toutes ses infos dans un objet HoraireSite.
// Cette classe ne fait JAMAIS de SQL. Elle stocke juste des données.

class HoraireSite {

    // Chaque propriété correspond à une colonne de la table Horaires_Sites.
    // Le ? devant le type veut dire que la valeur peut être null (vide).

    private ?int $horaireId;    // numéro unique de l'horaire (colonne Horaire_ID)
    private int $siteId;        // ID du site concerné par cet horaire (colonne Site_ID)
    private int $annee;         // année civile concernée ex: 2026 (colonne Annee)
    private string $heureDebut; // heure d'ouverture format "HH:MM:SS" (colonne Heure_Debut)
    private string $heureFin;   // heure de fermeture format "HH:MM:SS" (colonne Heure_Fin)

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

    // --- Getters et Setters ---
    // Un getter permet de LIRE une propriété depuis l'extérieur de la classe.
    // Un setter permet de MODIFIER une propriété depuis l'extérieur de la classe.

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
