<?php

require_once __DIR__ . '/../models/Membre.php';

class MembreRepository {

    public function __construct(private PDO $pdo) {}

    public function delete(int $id): void {
        $stmt = $this->pdo->prepare("DELETE FROM Membres WHERE Membre_ID = :id");
        $stmt->execute([':id' => $id]);
    }
}
