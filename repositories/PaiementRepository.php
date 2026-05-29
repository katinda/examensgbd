<?php

require_once __DIR__ . '/../models/Paiement.php';

// Gère tout le SQL de la table Paiements.
// Ne contient aucune logique métier — juste des requêtes SQL.

class PaiementRepository {

    public function __construct(private PDO $pdo) {}


    // Retourne tous les paiements, avec filtre optionnel par site.
    public function findAll(?int $siteId = null): array {
        if ($siteId !== null) {
            $stmt = $this->pdo->prepare("
                SELECT p.* FROM Paiements p
                JOIN Inscriptions i ON i.Inscription_ID = p.Inscription_ID
                JOIN Reservations r ON r.Reservation_ID = i.Reservation_ID
                JOIN Terrains    t ON t.Terrain_ID      = r.Terrain_ID
                WHERE t.Site_ID = :siteId
                ORDER BY p.Date_Paiement DESC
            ");
            $stmt->execute([':siteId' => $siteId]);
        } else {
            $stmt = $this->pdo->query("SELECT * FROM Paiements ORDER BY Date_Paiement DESC");
        }
        return array_map(fn($row) => $this->hydrateOne($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }


    // Retourne le paiement d'une inscription, ou null si le joueur n'a pas encore payé
    public function findByInscription(int $inscriptionId): ?Paiement {
        $stmt = $this->pdo->prepare("
            SELECT * FROM Paiements WHERE Inscription_ID = :inscriptionId
        ");
        $stmt->execute([':inscriptionId' => $inscriptionId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->hydrateOne($row) : null;
    }


    // Retourne un paiement par son ID, ou null s'il n'existe pas
    public function findById(int $paiementId): ?Paiement {
        $stmt = $this->pdo->prepare("
            SELECT * FROM Paiements WHERE Paiement_ID = :paiementId
        ");
        $stmt->execute([':paiementId' => $paiementId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->hydrateOne($row) : null;
    }


    // Insère un nouveau paiement et retourne son ID généré
    public function insert(Paiement $paiement): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO Paiements (Inscription_ID, Montant, Methode)
            VALUES (:inscriptionId, :montant, :methode)
        ");
        $stmt->execute([
            ':inscriptionId' => $paiement->getInscriptionId(),
            ':montant'       => $paiement->getMontant(),
            ':methode'       => $paiement->getMethode(),
        ]);
        return (int) $this->pdo->lastInsertId();
    }


    // Met à jour un paiement existant (utilisé pour l'annulation)
    public function update(Paiement $paiement): void {
        $stmt = $this->pdo->prepare("
            UPDATE Paiements
            SET Est_Annule        = :estAnnule,
                Montant_Rembourse = :montantRembourse,
                Date_Annulation   = :dateAnnulation
            WHERE Paiement_ID = :paiementId
        ");
        $stmt->execute([
            ':estAnnule'        => $paiement->isEstAnnule() ? 1 : 0,
            ':montantRembourse' => $paiement->getMontantRembourse(),
            ':dateAnnulation'   => $paiement->getDateAnnulation(),
            ':paiementId'       => $paiement->getPaiementId(),
        ]);
    }


    private function hydrateOne(array $row): Paiement {
        return new Paiement(
            (int)   $row['Paiement_ID'],
            (int)   $row['Inscription_ID'],
            (float) $row['Montant'],
            (string) $row['Date_Paiement'],
            $row['Methode'] ?? null,
            (bool)  $row['Est_Annule'],
            isset($row['Montant_Rembourse']) ? (float) $row['Montant_Rembourse'] : null,
            $row['Date_Annulation'] ?? null
        );
    }
}
