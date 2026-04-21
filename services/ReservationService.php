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
    public function getReservationsByTerrainAndDate(int $terrainId, string $date): array {
        return $this->reservationRepository->findByTerrainAndDate($terrainId, $date);
    }
}
