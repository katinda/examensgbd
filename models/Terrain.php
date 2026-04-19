<?php

// Un modèle c'est comme une fiche d'identité pour un terrain.
// Quand on récupère un terrain depuis la base de données,
// on range toutes ses infos dans un objet Terrain.
// Cette classe ne fait JAMAIS de SQL. Elle stocke juste des données.

class Terrain {

    // Chaque propriété correspond à une colonne de la table Terrains.
    // Le ? devant le type veut dire que la valeur peut être null (vide).

    private ?int $terrainId;    // numéro unique du terrain (colonne Terrain_ID)
    private int $siteId;        // ID du site auquel appartient ce terrain (colonne Site_ID)
    private int $numTerrain;    // numéro du terrain dans le site ex: 1, 2, 3... (colonne Num_Terrain)
    private ?string $libelle;   // nom optionnel du terrain ex: "Terrain Central" (colonne Libelle)
    private bool $estActif;     // true = terrain disponible, false = terrain fermé (colonne Est_Actif)

    public function __construct(
        ?int $terrainId,
        int $siteId,
        int $numTerrain,
        ?string $libelle,
        bool $estActif = true
    ) {
        $this->terrainId  = $terrainId;
        $this->siteId     = $siteId;
        $this->numTerrain = $numTerrain;
        $this->libelle    = $libelle;
        $this->estActif   = $estActif;
    }

    // --- Getters et Setters ---
    // Un getter permet de LIRE une propriété depuis l'extérieur de la classe.
    // Un setter permet de MODIFIER une propriété depuis l'extérieur de la classe.

    public function getTerrainId(): ?int { return $this->terrainId; }
    public function setTerrainId(?int $terrainId): void { $this->terrainId = $terrainId; }

    public function getSiteId(): int { return $this->siteId; }
    public function setSiteId(int $siteId): void { $this->siteId = $siteId; }

    public function getNumTerrain(): int { return $this->numTerrain; }
    public function setNumTerrain(int $numTerrain): void { $this->numTerrain = $numTerrain; }

    public function getLibelle(): ?string { return $this->libelle; }
    public function setLibelle(?string $libelle): void { $this->libelle = $libelle; }

    public function isEstActif(): bool { return $this->estActif; }
    public function setEstActif(bool $estActif): void { $this->estActif = $estActif; }
}
