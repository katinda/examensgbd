<?php
require_once __DIR__ . '/../models/Inscription.php';

class InscriptionRepository {
    public function __construct(private PDO $pdo) {}

    // Retourne toutes les inscriptions d'une réservation (les 4 joueurs du match)
    public function findByReservation(int $reservationId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM Inscriptions WHERE Reservation_ID = :reservationId");
        $stmt->execute([':reservationId' => $reservationId]);
        return array_map(fn($row) => new Inscription((int) $row['Inscription_ID'], (int) $row['Reservation_ID'], (int) $row['Membre_ID'], (bool) $row['Est_Organisateur']), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}
