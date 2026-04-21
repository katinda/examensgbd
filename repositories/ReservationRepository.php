<?php
require_once __DIR__ . '/../models/Reservation.php';
class ReservationRepository {
    public function __construct(private PDO $pdo) {}
    public function insert(Reservation $reservation): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO Reservations (Terrain_ID, Organisateur_ID, Date_Match, Heure_Debut, Heure_Fin, Type, Etat, Prix_Total)
            VALUES (:terrainId, :organisateurId, :dateMatch, :heureDebut, :heureFin, :type, :etat, :prixTotal)
        ");
        $stmt->execute([
            ':terrainId'      => $reservation->getTerrainId(),
            ':organisateurId' => $reservation->getOrganisateurId(),
            ':dateMatch'      => $reservation->getDateMatch(),
            ':heureDebut'     => $reservation->getHeureDebut(),
            ':heureFin'       => $reservation->getHeureFin(),
            ':type'           => $reservation->getType(),
            ':etat'           => $reservation->getEtat(),
            ':prixTotal'      => $reservation->getPrixTotal(),
        ]);
        return (int) $this->pdo->lastInsertId();
    }
}
