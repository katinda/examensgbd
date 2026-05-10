<?php

// Calcule les statistiques globales et par site pour l'interface administrateur.
// Toutes les requêtes sont en lecture seule — pas de repository intermédiaire.

class StatsService {

    public function __construct(private PDO $pdo) {}


    // Retourne les statistiques globales : CA, réservations, membres, pénalités.
    public function getStatsGlobales(): array {
        return [
            'chiffre_affaires'      => $this->getChiffreAffaires(),
            'reservations'          => $this->getStatsReservations(),
            'membres'               => $this->getStatsMembres(),
            'penalites_actives'     => $this->getNbPenalitesActives(),
            'taux_remplissage'      => $this->getTauxRemplissage(),
        ];
    }


    // Retourne les statistiques filtrées par site.
    public function getStatsBySite(int $siteId): array {
        return [
            'chiffre_affaires'  => $this->getChiffreAffairesBySite($siteId),
            'reservations'      => $this->getStatsReservationsBySite($siteId),
            'taux_remplissage'  => $this->getTauxRemplissageBySite($siteId),
        ];
    }


    // Chiffre d'affaires total (paiements non annulés).
    private function getChiffreAffaires(): float {
        $stmt = $this->pdo->query("SELECT COALESCE(SUM(Montant), 0) FROM Paiements WHERE Est_Annule = 0");
        return (float) $stmt->fetchColumn();
    }


    // Chiffre d'affaires par site (via terrains → réservations → inscriptions → paiements).
    private function getChiffreAffairesBySite(int $siteId): float {
        $stmt = $this->pdo->prepare("
            SELECT COALESCE(SUM(p.Montant), 0)
            FROM Paiements p
            JOIN Inscriptions i ON i.Inscription_ID = p.Inscription_ID
            JOIN Reservations r ON r.Reservation_ID = i.Reservation_ID
            JOIN Terrains t     ON t.Terrain_ID     = r.Terrain_ID
            WHERE t.Site_ID = :siteId
              AND p.Est_Annule = 0
        ");
        $stmt->execute([':siteId' => $siteId]);
        return (float) $stmt->fetchColumn();
    }


    // Nombre de réservations par type et par état.
    private function getStatsReservations(): array {
        $stmt = $this->pdo->query("
            SELECT
                COUNT(*) AS total,
                SUM(Type = 'PUBLIC')  AS publiques,
                SUM(Type = 'PRIVE')   AS privees
            FROM Reservations
        ");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return [
            'total'     => (int) $row['total'],
            'publiques' => (int) $row['publiques'],
            'privees'   => (int) $row['privees'],
        ];
    }


    // Nombre de réservations pour un site donné.
    private function getStatsReservationsBySite(int $siteId): array {
        $stmt = $this->pdo->prepare("
            SELECT
                COUNT(*) AS total,
                SUM(r.Type = 'PUBLIC') AS publiques,
                SUM(r.Type = 'PRIVE')  AS privees
            FROM Reservations r
            JOIN Terrains t ON t.Terrain_ID = r.Terrain_ID
            WHERE t.Site_ID = :siteId
        ");
        $stmt->execute([':siteId' => $siteId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return [
            'total'     => (int) $row['total'],
            'publiques' => (int) $row['publiques'],
            'privees'   => (int) $row['privees'],
        ];
    }


    // Nombre de membres actifs par catégorie.
    private function getStatsMembres(): array {
        $stmt = $this->pdo->query("
            SELECT Categorie, COUNT(*) AS nb
            FROM Membres
            WHERE Est_Actif = 1
            GROUP BY Categorie
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = ['total' => 0, 'G' => 0, 'S' => 0, 'L' => 0];
        foreach ($rows as $row) {
            $result[$row['Categorie']] = (int) $row['nb'];
            $result['total'] += (int) $row['nb'];
        }
        return $result;
    }


    // Nombre de pénalités actives (non levées).
    private function getNbPenalitesActives(): int {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM Penalites WHERE Levee = 0");
        return (int) $stmt->fetchColumn();
    }


    // Taux de remplissage global : moyenne du ratio joueurs inscrits / 4 par réservation.
    private function getTauxRemplissage(): float {
        $stmt = $this->pdo->query("
            SELECT COALESCE(AVG(nb_inscrits / 4 * 100), 0)
            FROM (
                SELECT COUNT(i.Inscription_ID) AS nb_inscrits
                FROM Reservations r
                LEFT JOIN Inscriptions i ON i.Reservation_ID = r.Reservation_ID
                GROUP BY r.Reservation_ID
            ) AS sous_requete
        ");
        return round((float) $stmt->fetchColumn(), 1);
    }


    // Taux de remplissage pour un site donné.
    private function getTauxRemplissageBySite(int $siteId): float {
        $stmt = $this->pdo->prepare("
            SELECT COALESCE(AVG(nb_inscrits / 4 * 100), 0)
            FROM (
                SELECT COUNT(i.Inscription_ID) AS nb_inscrits
                FROM Reservations r
                JOIN Terrains t ON t.Terrain_ID = r.Terrain_ID
                LEFT JOIN Inscriptions i ON i.Reservation_ID = r.Reservation_ID
                WHERE t.Site_ID = :siteId
                GROUP BY r.Reservation_ID
            ) AS sous_requete
        ");
        $stmt->execute([':siteId' => $siteId]);
        return round((float) $stmt->fetchColumn(), 1);
    }
}
