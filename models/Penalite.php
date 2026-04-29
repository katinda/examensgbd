<?php

class Penalite {

    private ?int $penaliteId;       // numéro unique (colonne Penalite_ID)
    private int $membreId;          // membre pénalisé (colonne Membre_ID)
    private ?int $reservationId;    // réservation à l'origine de la pénalité, peut être null (colonne Reservation_ID)
    private string $dateDebut;      // début de la période de pénalité (colonne Date_Debut)
    private string $dateFin;        // fin de la période de pénalité (colonne Date_Fin)
    private string $cause;          // 'PRIVATE_INCOMPLETE', 'PAYMENT_MISSING' ou 'OTHER' (colonne Cause)
    private bool $levee;            // false = active, true = levée manuellement (colonne Levee)
    private ?int $leveePar;         // Admin_ID de l'admin qui a levé la pénalité (colonne Levee_Par)
    private ?string $leveeLe;       // date/heure de la levée (colonne Levee_Le)
    private ?string $leveeRaison;   // motif de la levée (colonne Levee_Raison)
    private ?string $dateCreation;  // date de création automatique (colonne Date_Creation)

    public function __construct(
        ?int $penaliteId,
        int $membreId,
        ?int $reservationId,
        string $dateDebut,
        string $dateFin,
        string $cause,
        bool $levee = false,
        ?int $leveePar = null,
        ?string $leveeLe = null,
        ?string $leveeRaison = null,
        ?string $dateCreation = null
    ) {
        $this->penaliteId    = $penaliteId;
        $this->membreId      = $membreId;
        $this->reservationId = $reservationId;
        $this->dateDebut     = $dateDebut;
        $this->dateFin       = $dateFin;
        $this->cause         = $cause;
        $this->levee         = $levee;
        $this->leveePar      = $leveePar;
        $this->leveeLe       = $leveeLe;
        $this->leveeRaison   = $leveeRaison;
        $this->dateCreation  = $dateCreation;
    }

    public function getPenaliteId(): ?int { return $this->penaliteId; }
    public function setPenaliteId(?int $penaliteId): void { $this->penaliteId = $penaliteId; }

    public function getMembreId(): int { return $this->membreId; }
    public function setMembreId(int $membreId): void { $this->membreId = $membreId; }

    public function getReservationId(): ?int { return $this->reservationId; }
    public function setReservationId(?int $reservationId): void { $this->reservationId = $reservationId; }

    public function getDateDebut(): string { return $this->dateDebut; }
    public function setDateDebut(string $dateDebut): void { $this->dateDebut = $dateDebut; }

    public function getDateFin(): string { return $this->dateFin; }
    public function setDateFin(string $dateFin): void { $this->dateFin = $dateFin; }

    public function getCause(): string { return $this->cause; }
    public function setCause(string $cause): void { $this->cause = $cause; }

    public function isLevee(): bool { return $this->levee; }
    public function setLevee(bool $levee): void { $this->levee = $levee; }

    public function getLeveePar(): ?int { return $this->leveePar; }
    public function setLeveePar(?int $leveePar): void { $this->leveePar = $leveePar; }

    public function getLeveeLe(): ?string { return $this->leveeLe; }
    public function setLeveeLe(?string $leveeLe): void { $this->leveeLe = $leveeLe; }

    public function getLeveeRaison(): ?string { return $this->leveeRaison; }
    public function setLeveeRaison(?string $leveeRaison): void { $this->leveeRaison = $leveeRaison; }

    public function getDateCreation(): ?string { return $this->dateCreation; }
    public function setDateCreation(?string $dateCreation): void { $this->dateCreation = $dateCreation; }
}
