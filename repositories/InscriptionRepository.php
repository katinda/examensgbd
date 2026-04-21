<?php
require_once __DIR__ . '/../models/Inscription.php';

class InscriptionRepository {
    public function __construct(private PDO $pdo) {}

    // Compte le nombre de joueurs déjà inscrits à une réservation.
    // Utilisé par le service pour refuser l'inscription si la réservation est déjà complète (4 joueurs).
    public function countByReservation(int $reservationId): int {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM Inscriptions WHERE Reservation_ID = :reservationId");
        $stmt->execute([':reservationId' => $reservationId]);
        return (int) $stmt->fetchColumn();
    }
}
