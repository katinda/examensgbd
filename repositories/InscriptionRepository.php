<?php

require_once __DIR__ . '/../models/Inscription.php';

// Gère tout le SQL de la table Inscriptions.
// Ne contient aucune logique métier — juste des requêtes SQL.

class InscriptionRepository {

    public function __construct(private PDO $pdo) {}


    // Retourne toutes les inscriptions d'une réservation (les 4 joueurs du match)
    public function findByReservation(int $reservationId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM Inscriptions WHERE Reservation_ID = :reservationId");
        $stmt->execute([':reservationId' => $reservationId]);
        return $this->hydrate($stmt->fetchAll(PDO::FETCH_ASSOC));
    }


    // Retourne l'inscription d'un membre précis sur une réservation, ou null s'il n'est pas inscrit.
    // Utilisé par le service pour détecter un doublon avant que MySQL ne lève la contrainte UNIQUE.
    public function findByReservationAndMembre(int $reservationId, int $membreId): ?Inscription {
        $stmt = $this->pdo->prepare("
            SELECT * FROM Inscriptions
            WHERE Reservation_ID = :reservationId
              AND Membre_ID       = :membreId
        ");
        $stmt->execute([':reservationId' => $reservationId, ':membreId' => $membreId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->hydrateOne($row) : null;
    }


    // Compte le nombre de joueurs déjà inscrits à une réservation.
    // Utilisé par le service pour refuser l'inscription si la réservation est déjà complète (4 joueurs).
    public function countByReservation(int $reservationId): int {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM Inscriptions WHERE Reservation_ID = :reservationId");
        $stmt->execute([':reservationId' => $reservationId]);
        return (int) $stmt->fetchColumn();
    }


    // Crée une nouvelle inscription et retourne son ID généré par MySQL
    public function insert(Inscription $inscription): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO Inscriptions (Reservation_ID, Membre_ID, Est_Organisateur)
            VALUES (:reservationId, :membreId, :estOrganisateur)
        ");
        $stmt->execute([
            ':reservationId'   => $inscription->getReservationId(),
            ':membreId'        => $inscription->getMembreId(),
            ':estOrganisateur' => $inscription->isEstOrganisateur() ? 1 : 0,
        ]);
        return (int) $this->pdo->lastInsertId();
    }


    // Supprime l'inscription d'un membre sur une réservation
    public function delete(int $reservationId, int $membreId): void {
        $stmt = $this->pdo->prepare("
            DELETE FROM Inscriptions
            WHERE Reservation_ID = :reservationId
              AND Membre_ID       = :membreId
        ");
        $stmt->execute([':reservationId' => $reservationId, ':membreId' => $membreId]);
    }


    private function hydrate(array $rows): array {
        return array_map(fn($row) => $this->hydrateOne($row), $rows);
    }


    private function hydrateOne(array $row): Inscription {
        return new Inscription(
            (int)  $row['Inscription_ID'],
            (int)  $row['Reservation_ID'],
            (int)  $row['Membre_ID'],
            (bool) $row['Est_Organisateur']
        );
    }
}
