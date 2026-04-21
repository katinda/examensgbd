<?php
require_once __DIR__ . '/../models/Inscription.php';

class InscriptionRepository {
    public function __construct(private PDO $pdo) {}

    // Retourne l'inscription d'un membre précis sur une réservation, ou null s'il n'est pas inscrit.
    // Utilisé par le service pour détecter un doublon avant que MySQL ne lève la contrainte UNIQUE.
    public function findByReservationAndMembre(int $reservationId, int $membreId): ?Inscription {
        $stmt = $this->pdo->prepare("SELECT * FROM Inscriptions WHERE Reservation_ID = :reservationId AND Membre_ID = :membreId");
        $stmt->execute([':reservationId' => $reservationId, ':membreId' => $membreId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new Inscription((int) $row['Inscription_ID'], (int) $row['Reservation_ID'], (int) $row['Membre_ID'], (bool) $row['Est_Organisateur']) : null;
    }
}
