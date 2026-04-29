<?php

require_once __DIR__ . '/../models/Penalite.php';

class PenaliteRepository {

    public function __construct(private PDO $pdo) {}

    public function findAll(): array {
        $stmt = $this->pdo->query("SELECT * FROM Penalites");
        return array_map(fn($row) => $this->hydrateOne($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function findById(int $id): ?Penalite {
        $stmt = $this->pdo->prepare("SELECT * FROM Penalites WHERE Penalite_ID = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->hydrateOne($row) : null;
    }

    public function findByMembreId(int $membreId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM Penalites WHERE Membre_ID = :membreId");
        $stmt->execute([':membreId' => $membreId]);
        return array_map(fn($row) => $this->hydrateOne($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function findActives(): array {
        $stmt = $this->pdo->query("SELECT * FROM Penalites WHERE Levee = 0");
        return array_map(fn($row) => $this->hydrateOne($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function insert(Penalite $penalite): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO Penalites (Membre_ID, Reservation_ID, Date_Debut, Date_Fin, Cause, Levee, Levee_Par, Levee_Le, Levee_Raison)
            VALUES (:membreId, :reservationId, :dateDebut, :dateFin, :cause, :levee, :leveePar, :leveeLe, :leveeRaison)
        ");
        $stmt->execute([
            ':membreId'      => $penalite->getMembreId(),
            ':reservationId' => $penalite->getReservationId(),
            ':dateDebut'     => $penalite->getDateDebut(),
            ':dateFin'       => $penalite->getDateFin(),
            ':cause'         => $penalite->getCause(),
            ':levee'         => $penalite->isLevee() ? 1 : 0,
            ':leveePar'      => $penalite->getLeveePar(),
            ':leveeLe'       => $penalite->getLeveeLe(),
            ':leveeRaison'   => $penalite->getLeveeRaison(),
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(Penalite $penalite): void {
        $stmt = $this->pdo->prepare("
            UPDATE Penalites
            SET Membre_ID = :membreId, Reservation_ID = :reservationId,
                Date_Debut = :dateDebut, Date_Fin = :dateFin, Cause = :cause,
                Levee = :levee, Levee_Par = :leveePar, Levee_Le = :leveeLe, Levee_Raison = :leveeRaison
            WHERE Penalite_ID = :id
        ");
        $stmt->execute([
            ':id'            => $penalite->getPenaliteId(),
            ':membreId'      => $penalite->getMembreId(),
            ':reservationId' => $penalite->getReservationId(),
            ':dateDebut'     => $penalite->getDateDebut(),
            ':dateFin'       => $penalite->getDateFin(),
            ':cause'         => $penalite->getCause(),
            ':levee'         => $penalite->isLevee() ? 1 : 0,
            ':leveePar'      => $penalite->getLeveePar(),
            ':leveeLe'       => $penalite->getLeveeLe(),
            ':leveeRaison'   => $penalite->getLeveeRaison(),
        ]);
    }

    public function delete(int $id): void {
        $stmt = $this->pdo->prepare("DELETE FROM Penalites WHERE Penalite_ID = :id");
        $stmt->execute([':id' => $id]);
    }

    private function hydrateOne(array $row): Penalite {
        return new Penalite(
            (int) $row['Penalite_ID'],
            (int) $row['Membre_ID'],
            $row['Reservation_ID'] !== null ? (int) $row['Reservation_ID'] : null,
            $row['Date_Debut'],
            $row['Date_Fin'],
            $row['Cause'],
            (bool) $row['Levee'],
            $row['Levee_Par'] !== null ? (int) $row['Levee_Par'] : null,
            $row['Levee_Le'],
            $row['Levee_Raison'],
            $row['Date_Creation']
        );
    }
}
