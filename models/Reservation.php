<?php

// Un modèle c'est comme une fiche d'identité pour une réservation.
// Quand on récupère une réservation depuis la base de données,
// on range toutes ses infos dans un objet Reservation.
// Cette classe ne fait JAMAIS de SQL. Elle stocke juste des données.

class Reservation {

    // Chaque propriété correspond à une colonne de la table Reservations.

    private ?int $reservationId;    // numéro unique de la réservation (colonne Reservation_ID)
    private int $terrainId;         // quel terrain est réservé (colonne Terrain_ID)
    private int $organisateurId;    // quel membre a créé la réservation (colonne Organisateur_ID)
    private string $dateMatch;      // date du match, format YYYY-MM-DD (colonne Date_Match)
    private string $heureDebut;     // heure de début, format HH:MM:SS (colonne Heure_Debut)
    private string $heureFin;       // heure de fin = heureDebut + 1h30, calculée par le service (colonne Heure_Fin)
    private string $type;           // 'PRIVE' (organisateur invite) ou 'PUBLIC' (ouvert à tous) (colonne Type)
    private string $etat;           // cycle de vie : EN_COURS → COMPLETEE / BASCULE_PUBLIC → TERMINEE (ou ANNULEE / FORFAIT)
    private float $prixTotal;       // prix total du créneau, défaut 60.00 € divisé en 4 parts de 15 € (colonne Prix_Total)
    private ?string $dateCreation;  // date de création automatique (colonne Date_Creation)
    private ?string $lastUpdate;    // date de dernière modification automatique (colonne LastUpdate)

    // Le constructeur oblige à fournir toutes les valeurs importantes dès la création.
    // heureDebut et heureFin sont des strings "HH:MM:SS" — le service calcule heureFin avant d'appeler le constructeur.
    // etat et prixTotal ont des valeurs par défaut car ils sont toujours les mêmes à la création.
    public function __construct(
        ?int $reservationId,
        int $terrainId,
        int $organisateurId,
        string $dateMatch,
        string $heureDebut,
        string $heureFin,
        string $type,
        string $etat = 'EN_COURS',
        float $prixTotal = 60.00,
        ?string $dateCreation = null,
        ?string $lastUpdate = null
    ) {
        $this->reservationId  = $reservationId;
        $this->terrainId      = $terrainId;
        $this->organisateurId = $organisateurId;
        $this->dateMatch      = $dateMatch;
        $this->heureDebut     = $heureDebut;
        $this->heureFin       = $heureFin;
        $this->type           = $type;
        $this->etat           = $etat;
        $this->prixTotal      = $prixTotal;
        $this->dateCreation   = $dateCreation;
        $this->lastUpdate     = $lastUpdate;
    }

    // --- Getters : permettent de LIRE les propriétés depuis l'extérieur ---
    public function getReservationId(): ?int    { return $this->reservationId; }
    public function getTerrainId(): int         { return $this->terrainId; }
    public function getOrganisateurId(): int    { return $this->organisateurId; }
    public function getDateMatch(): string      { return $this->dateMatch; }
    public function getHeureDebut(): string     { return $this->heureDebut; }
    public function getHeureFin(): string       { return $this->heureFin; }
    public function getType(): string           { return $this->type; }
    public function getEtat(): string           { return $this->etat; }
    public function getPrixTotal(): float       { return $this->prixTotal; }
    public function getDateCreation(): ?string  { return $this->dateCreation; }
    public function getLastUpdate(): ?string    { return $this->lastUpdate; }

    // --- Setters : permettent de MODIFIER les propriétés depuis l'extérieur ---
    public function setReservationId(?int $id): void      { $this->reservationId = $id; }
    public function setTerrainId(int $id): void           { $this->terrainId = $id; }
    public function setOrganisateurId(int $id): void      { $this->organisateurId = $id; }
    public function setDateMatch(string $d): void         { $this->dateMatch = $d; }
    public function setHeureDebut(string $h): void        { $this->heureDebut = $h; }
    public function setHeureFin(string $h): void          { $this->heureFin = $h; }
    public function setType(string $type): void           { $this->type = $type; }
    public function setEtat(string $etat): void           { $this->etat = $etat; }
    public function setPrixTotal(float $prix): void       { $this->prixTotal = $prix; }
    public function setDateCreation(?string $d): void     { $this->dateCreation = $d; }
    public function setLastUpdate(?string $d): void       { $this->lastUpdate = $d; }
}
