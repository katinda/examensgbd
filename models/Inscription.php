<?php

// Un modèle c'est comme une fiche d'identité pour une inscription.
// Quand on récupère une inscription depuis la base de données,
// on range toutes ses infos dans un objet Inscription.
// Cette classe ne fait JAMAIS de SQL. Elle stocke juste des données.

class Inscription {

    // Chaque propriété correspond à une colonne de la table Inscriptions.

    private ?int $inscriptionId;   // numéro unique de l'inscription (colonne Inscription_ID)
    private int $reservationId;    // à quelle réservation appartient cette inscription (colonne Reservation_ID)
    private int $membreId;         // quel membre est inscrit (colonne Membre_ID)
    private bool $estOrganisateur; // true = c'est lui qui a créé la réservation, false = joueur invité ou inscrit (colonne Est_Organisateur)

    // Le constructeur oblige à fournir toutes les valeurs dès la création.
    // estOrganisateur vaut false par défaut car la majorité des inscriptions sont des joueurs invités.
    // L'organisateur est le seul cas où on passe true — et c'est ReservationService qui le fait automatiquement.
    public function __construct(
        ?int $inscriptionId,
        int $reservationId,
        int $membreId,
        bool $estOrganisateur = false
    ) {
        $this->inscriptionId   = $inscriptionId;
        $this->reservationId   = $reservationId;
        $this->membreId        = $membreId;
        $this->estOrganisateur = $estOrganisateur;
    }

    // --- Getters : permettent de LIRE les propriétés depuis l'extérieur ---
    public function getInscriptionId(): ?int   { return $this->inscriptionId; }
    public function getReservationId(): int    { return $this->reservationId; }
    public function getMembreId(): int         { return $this->membreId; }
    public function isEstOrganisateur(): bool  { return $this->estOrganisateur; }

    // --- Setters : permettent de MODIFIER les propriétés depuis l'extérieur ---
    public function setInscriptionId(?int $id): void        { $this->inscriptionId = $id; }
    public function setReservationId(int $id): void         { $this->reservationId = $id; }
    public function setMembreId(int $id): void              { $this->membreId = $id; }
    public function setEstOrganisateur(bool $val): void     { $this->estOrganisateur = $val; }
}
