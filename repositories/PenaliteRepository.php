<?php

require_once __DIR__ . '/../models/Penalite.php';

class PenaliteRepository {

    public function __construct(private PDO $pdo) {}

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
