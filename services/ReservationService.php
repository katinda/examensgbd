<?php
require_once __DIR__ . '/../repositories/ReservationRepository.php';
require_once __DIR__ . '/../repositories/TerrainRepository.php';
require_once __DIR__ . '/../repositories/MembreRepository.php';
class ReservationService {
    public function __construct(
        private ReservationRepository $reservationRepository,
        private TerrainRepository $terrainRepository,
        private MembreRepository $membreRepository
    ) {}
    public function createReservation(array $data): int|string {
        $terrain = $this->terrainRepository->findById((int) $data['terrain_id']);
        if ($terrain === null) return 'terrain_introuvable';
        if (!$terrain->isEstActif()) return 'terrain_inactif';
        $membre = $this->membreRepository->findById((int) $data['organisateur_id']);
        if ($membre === null || !$membre->isEstActif()) return 'organisateur_introuvable';
        $dejaReserve = $this->reservationRepository->findByTerrainDateHeure(
            (int) $data['terrain_id'], $data['date_match'], $data['heure_debut']
        );
        if ($dejaReserve !== null) return 'creneau_pris';
        $dt = new DateTime($data['heure_debut']);
        $dt->modify('+1 hour +30 minutes');
        $heureFin = $dt->format('H:i:s');
        $reservation = new Reservation(null, (int)$data['terrain_id'], (int)$data['organisateur_id'],
            $data['date_match'], $data['heure_debut'], $heureFin, strtoupper($data['type'] ?? 'PRIVE'));
        return $this->reservationRepository->insert($reservation);
    }
}
