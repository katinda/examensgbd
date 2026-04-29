<?php

require_once __DIR__ . '/../models/Penalite.php';

class PenaliteRepository {

    public function __construct(private PDO $pdo) {}

    public function findByMembreId(int $membreId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM Penalites WHERE Membre_ID = :membreId");
        $stmt->execute([':membreId' => $membreId]);
        return array_map(fn($row) => $this->hydrateOne($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
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
