<?php

require_once __DIR__ . '/../models/Reservation.php';

// Gère tout le SQL de la table Reservations.
// Ne contient aucune logique métier — juste des requêtes SQL.

class ReservationRepository {

    public function __construct(private PDO $pdo) {}


    // Retourne une réservation par son ID, ou null si elle n'existe pas
    public function findById(int $id): ?Reservation {
        $stmt = $this->pdo->prepare("SELECT * FROM Reservations WHERE Reservation_ID = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->hydrateOne($row) : null;
    }


    // Retourne toutes les réservations d'un membre (en tant qu'organisateur)
    public function findByOrganisateur(int $membreId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM Reservations WHERE Organisateur_ID = :id ORDER BY Date_Match DESC, Heure_Debut DESC");
        $stmt->execute([':id' => $membreId]);
        return $this->hydrate($stmt->fetchAll(PDO::FETCH_ASSOC));
    }


    // Retourne toutes les réservations d'un terrain à une date précise
    public function findByTerrainAndDate(int $terrainId, string $date): array {
        $stmt = $this->pdo->prepare("SELECT * FROM Reservations WHERE Terrain_ID = :terrainId AND Date_Match = :date ORDER BY Heure_Debut");
        $stmt->execute([':terrainId' => $terrainId, ':date' => $date]);
        return $this->hydrate($stmt->fetchAll(PDO::FETCH_ASSOC));
    }


    // Vérifie si un créneau est déjà pris sur un terrain à une date et heure précises.
    // Utilisé par le service pour retourner une erreur propre avant que MySQL ne lève la contrainte UNIQUE.
    public function findByTerrainDateHeure(int $terrainId, string $date, string $heureDebut): ?Reservation {
        $stmt = $this->pdo->prepare("
            SELECT * FROM Reservations
            WHERE Terrain_ID = :terrainId
              AND Date_Match  = :date
              AND Heure_Debut = :heureDebut
        ");
        $stmt->execute([':terrainId' => $terrainId, ':date' => $date, ':heureDebut' => $heureDebut]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->hydrateOne($row) : null;
    }


    // Crée une nouvelle réservation et retourne son ID généré par MySQL
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


    // Met à jour une réservation existante (etat, prixTotal, etc.)
    public function update(Reservation $reservation): void {
        $stmt = $this->pdo->prepare("
            UPDATE Reservations
            SET Etat = :etat, Prix_Total = :prixTotal
            WHERE Reservation_ID = :id
        ");
        $stmt->execute([
            ':id'        => $reservation->getReservationId(),
            ':etat'      => $reservation->getEtat(),
            ':prixTotal' => $reservation->getPrixTotal(),
        ]);
    }


    private function hydrate(array $rows): array {
        return array_map(fn($row) => $this->hydrateOne($row), $rows);
    }


    private function hydrateOne(array $row): Reservation {
        return new Reservation(
            (int) $row['Reservation_ID'],
            (int) $row['Terrain_ID'],
            (int) $row['Organisateur_ID'],
            $row['Date_Match'],
            $row['Heure_Debut'],
            $row['Heure_Fin'],
            $row['Type'],
            $row['Etat'],
            (float) $row['Prix_Total'],
            $row['Date_Creation'],
            $row['LastUpdate']
        );
    }
}
