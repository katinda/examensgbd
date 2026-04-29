<?php

require_once __DIR__ . '/../models/Penalite.php';

class PenaliteRepository {

    public function __construct(private PDO $pdo) {}

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

    private function hydrateOne(array $row): Penalite {
        return new Penalite(
            (int) $row['Penalite_ID'],
            (int) $row['Membre_ID'],
            $row['Reservation_ID'] !== null ? (int) $row['Reservation_ID'] : null,
            $row['Date_Debut'], $row['Date_Fin'], $row['Cause'],
            (bool) $row['Levee'],
            $row['Levee_Par'] !== null ? (int) $row['Levee_Par'] : null,
            $row['Levee_Le'], $row['Levee_Raison'], $row['Date_Creation']
        );
    }
}
