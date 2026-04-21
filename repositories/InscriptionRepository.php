<?php
require_once __DIR__ . '/../models/Inscription.php';

class InscriptionRepository {
    public function __construct(private PDO $pdo) {}

    // Supprime l'inscription d'un membre sur une réservation
    public function delete(int $reservationId, int $membreId): void {
        $stmt = $this->pdo->prepare("DELETE FROM Inscriptions WHERE Reservation_ID = :reservationId AND Membre_ID = :membreId");
        $stmt->execute([':reservationId' => $reservationId, ':membreId' => $membreId]);
    }
}
