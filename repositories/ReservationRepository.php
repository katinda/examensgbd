<?php
require_once __DIR__ . '/../models/Reservation.php';
class ReservationRepository {
    public function __construct(private PDO $pdo) {}
    public function update(Reservation $reservation): void {
        $stmt = $this->pdo->prepare("UPDATE Reservations SET Etat = :etat, Prix_Total = :prixTotal WHERE Reservation_ID = :id");
        $stmt->execute([':id' => $reservation->getReservationId(), ':etat' => $reservation->getEtat(), ':prixTotal' => $reservation->getPrixTotal()]);
    }
}
