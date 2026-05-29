<?php

require_once __DIR__ . '/../repositories/ReservationRepository.php';
require_once __DIR__ . '/../repositories/TerrainRepository.php';
require_once __DIR__ . '/../repositories/MembreRepository.php';
require_once __DIR__ . '/../repositories/InscriptionRepository.php';
require_once __DIR__ . '/../repositories/AdministrateurRepository.php';

// Le service contient la logique métier des réservations.
// Version minimale : vérifie terrain + organisateur, calcule Heure_Fin, insère.
// Les règles avancées (horaires, fermetures, délais, pénalités) seront ajoutées après.

class ReservationService {

    public function __construct(
        private ReservationRepository    $reservationRepository,
        private TerrainRepository        $terrainRepository,
        private MembreRepository         $membreRepository,
        private InscriptionRepository    $inscriptionRepository,
        private AdministrateurRepository $adminRepository,
        private PDO                      $pdo
    ) {}


    // Retourne une réservation par son ID, ou null si elle n'existe pas
    public function getReservationById(int $id): ?Reservation {
        return $this->reservationRepository->findById($id);
    }


    // Retourne les réservations d'un membre.
    // Admin SITE → uniquement les réservations sur les terrains de son site.
    public function getReservationsByMembre(int $membreId, ?int $adminId = null): array {
        $reservations = $this->reservationRepository->findByOrganisateur($membreId);

        if ($adminId === null) return $reservations;

        $admin = $this->adminRepository->findById($adminId);
        if ($admin === null || $admin->getType() === 'GLOBAL') return $reservations;

        return array_values(array_filter($reservations, function($r) use ($admin) {
            $terrain = $this->terrainRepository->findById($r->getTerrainId());
            return $terrain !== null && $terrain->getSiteId() === $admin->getSiteId();
        }));
    }


    // Retourne toutes les réservations d'un terrain à une date précise
    public function getReservationsByTerrainAndDate(int $terrainId, string $date): array {
        return $this->reservationRepository->findByTerrainAndDate($terrainId, $date);
    }


    // Retourne les matches publics à venir avec places restantes (< 4 joueurs inscrits).
    // Filtre par site_id pour les membres de site (catégorie S).
    public function getMatchesPublics(?int $siteId = null): array {
        $rows = $this->reservationRepository->findPubliques($siteId);
        return array_map(fn($row) => [
            'id'               => (int) $row['Reservation_ID'],
            'terrain_id'       => (int) $row['Terrain_ID'],
            'organisateur_id'  => (int) $row['Organisateur_ID'],
            'date_match'       => $row['Date_Match'],
            'heure_debut'      => $row['Heure_Debut'],
            'heure_fin'        => $row['Heure_Fin'],
            'places_restantes' => 4 - (int) $row['nb_inscrits'],
        ], $rows);
    }


    // Crée une nouvelle réservation — version minimale.
    //
    // Validations effectuées :
    //   'terrain_introuvable'    → le terrain n'existe pas → 404
    //   'terrain_inactif'        → le terrain est fermé → 400
    //   'organisateur_introuvable' → le membre n'existe pas → 404
    //   'creneau_pris'           → ce créneau est déjà réservé → 409
    //
    // Calcul automatique : Heure_Fin = Heure_Debut + 1h30
    public function createReservation(array $data): int|string {
        // Règle 1 : le terrain doit exister
        $terrain = $this->terrainRepository->findById((int) $data['terrain_id']);
        if ($terrain === null) {
            return 'terrain_introuvable';
        }

        // Règle 2 : le terrain doit être actif
        if (!$terrain->isEstActif()) {
            return 'terrain_inactif';
        }

        // Règle 3 : l'organisateur doit exister
        $membre = $this->membreRepository->findById((int) $data['organisateur_id']);
        if ($membre === null || !$membre->isEstActif()) {
            return 'organisateur_introuvable';
        }

        // Règle 4 : le créneau ne doit pas être déjà pris
        $dejaReserve = $this->reservationRepository->findByTerrainDateHeure(
            (int) $data['terrain_id'],
            $data['date_match'],
            $data['heure_debut']
        );
        if ($dejaReserve !== null) {
            return 'creneau_pris';
        }

        // Calcul de Heure_Fin = Heure_Debut + 1h30
        $heureFin = $this->calculerHeureFin($data['heure_debut']);

        $reservation = new Reservation(
            null,
            (int) $data['terrain_id'],
            (int) $data['organisateur_id'],
            $data['date_match'],
            $data['heure_debut'],
            $heureFin,
            strtoupper($data['type'] ?? 'PRIVE')
        );

        // Transaction : si l'inscription de l'organisateur échoue, on annule aussi la réservation
        $this->pdo->beginTransaction();
        try {
            $reservationId = $this->reservationRepository->insert($reservation);

            // Inscription automatique de l'organisateur (joueur 1 sur 4, Est_Organisateur = true)
            $inscription = new Inscription(null, $reservationId, (int) $data['organisateur_id'], true);
            $this->inscriptionRepository->insert($inscription);

            $this->pdo->commit();
            return $reservationId;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }


    // Calcule l'heure de fin en ajoutant 1h30 à l'heure de début.
    // Exemple : "09:00:00" → "10:30:00"
    private function calculerHeureFin(string $heureDebut): string {
        $dt = new DateTime($heureDebut);
        $dt->modify('+1 hour +30 minutes');
        return $dt->format('H:i:s');
    }
}
