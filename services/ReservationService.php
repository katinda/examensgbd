<?php

require_once __DIR__ . '/../repositories/ReservationRepository.php';
require_once __DIR__ . '/../repositories/TerrainRepository.php';
require_once __DIR__ . '/../repositories/MembreRepository.php';
require_once __DIR__ . '/../repositories/InscriptionRepository.php';
require_once __DIR__ . '/../repositories/AdministrateurRepository.php';
require_once __DIR__ . '/../repositories/HoraireSiteRepository.php';
require_once __DIR__ . '/../repositories/FermetureRepository.php';

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
        private HoraireSiteRepository   $horaireRepository,
        private FermetureRepository     $fermetureRepository,
        private PDO                      $pdo
    ) {}


    // Retourne toutes les réservations.
    // Admin SITE → uniquement les réservations sur les terrains de son site.
    public function getAllReservations(?int $adminId = null): array {
        if ($adminId === null) return $this->reservationRepository->findAll();

        $admin = $this->adminRepository->findById($adminId);
        if ($admin === null || $admin->getType() === 'GLOBAL') {
            return $this->reservationRepository->findAll();
        }

        return $this->reservationRepository->findAll($admin->getSiteId());
    }


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


    // Crée une nouvelle réservation.
    //
    // Validations effectuées :
    //   'terrain_introuvable'      → le terrain n'existe pas → 404
    //   'terrain_inactif'          → le terrain est fermé → 400
    //   'organisateur_introuvable' → le membre n'existe pas → 404
    //   'date_passee'              → la date du match est dans le passé → 400
    //   'trop_tot'                 → trop tôt pour réserver (G:21j, S:14j, L:5j) → 400
    //   'site_non_autorise'        → membre S essaie de réserver sur un autre site → 403
    //   'creneau_pris'             → ce créneau est déjà réservé → 409
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

        // Règle 4 : la date du match ne doit pas être dans le passé
        $aujourdhui = new DateTime('today');
        $dateMatch  = new DateTime($data['date_match']);
        if ($dateMatch <= $aujourdhui) {
            return 'date_passee';
        }

        // Règle 5 : vérifier le délai de réservation selon la catégorie du membre
        $delaiMax = match($membre->getCategorie()) {
            'G' => 21,
            'S' => 14,
            'L' => 5,
            default => 0,
        };
        $joursAvant = (int) $aujourdhui->diff($dateMatch)->days;
        if ($joursAvant > $delaiMax) {
            return 'trop_tot';
        }

        // Règle 6 : un membre S ne peut réserver que sur son propre site
        if ($membre->getCategorie() === 'S' && $membre->getSiteId() !== $terrain->getSiteId()) {
            return 'site_non_autorise';
        }

        // Règle 7 : un horaire doit exister pour ce site et cette année
        $annee   = (int) date('Y', strtotime($data['date_match']));
        $horaire = $this->horaireRepository->findBySiteAndAnnee($terrain->getSiteId(), $annee);
        if ($horaire === null) {
            return 'horaire_introuvable';
        }

        // Règle 8 : l'heure de début et de fin doivent être dans les horaires du site
        $heureDebutMatch = $data['heure_debut'];
        $heureFinMatch   = $this->calculerHeureFin($heureDebutMatch);
        if ($heureDebutMatch < $horaire->getHeureDebut() || $heureFinMatch > $horaire->getHeureFin()) {
            return 'hors_horaires';
        }

        // Règle 9 : l'heure de début doit être un créneau valide (Heure_Debut + n * 1h45)
        $minutesDebut     = $this->heureEnMinutes($heureDebutMatch);
        $minutesSiteDebut = $this->heureEnMinutes($horaire->getHeureDebut());
        if (($minutesDebut - $minutesSiteDebut) % 105 !== 0) {
            return 'creneau_invalide';
        }

        // Règle 10 : aucune fermeture (site ou globale) ne doit couvrir la date du match
        $fermetures = array_merge(
            $this->fermetureRepository->findBySiteId($terrain->getSiteId()),
            $this->fermetureRepository->findGlobales()
        );
        foreach ($fermetures as $fermeture) {
            if ($data['date_match'] >= $fermeture->getDateDebut() && $data['date_match'] <= $fermeture->getDateFin()) {
                return 'site_ferme';
            }
        }

        // Règle 11 : le créneau ne doit pas être déjà pris
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
    private function calculerHeureFin(string $heureDebut): string {
        $dt = new DateTime($heureDebut);
        $dt->modify('+1 hour +30 minutes');
        return $dt->format('H:i:s');
    }

    // Convertit "HH:MM:SS" en nombre de minutes depuis minuit.
    private function heureEnMinutes(string $heure): int {
        [$h, $m] = explode(':', $heure);
        return (int) $h * 60 + (int) $m;
    }
}
