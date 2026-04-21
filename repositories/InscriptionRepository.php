<?php
require_once __DIR__ . '/../models/Inscription.php';

class InscriptionRepository {
    public function __construct(private PDO $pdo) {}

    // Crée une nouvelle inscription et retourne son ID généré par MySQL
    public function insert(Inscription $inscription): int {
        $stmt = $this->pdo->prepare("INSERT INTO Inscriptions (Reservation_ID, Membre_ID, Est_Organisateur) VALUES (:reservationId, :membreId, :estOrganisateur)");
        $stmt->execute([
            ':reservationId'   => $inscription->getReservationId(),
            ':membreId'        => $inscription->getMembreId(),
            ':estOrganisateur' => $inscription->isEstOrganisateur() ? 1 : 0,
        ]);
        return (int) $this->pdo->lastInsertId();
    }
}
