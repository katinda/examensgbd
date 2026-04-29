<?php

require_once __DIR__ . '/../models/Penalite.php';

class PenaliteRepository {

    public function __construct(private PDO $pdo) {}

    public function delete(int $id): void {
        $stmt = $this->pdo->prepare("DELETE FROM Penalites WHERE Penalite_ID = :id");
        $stmt->execute([':id' => $id]);
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
