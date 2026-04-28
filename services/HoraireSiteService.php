<?php

require_once __DIR__ . '/../repositories/HoraireSiteRepository.php';

class HoraireSiteService {

    public function __construct(
        private HoraireSiteRepository $horaireRepository
    ) {}

    public function getHorairesBySiteId(int $siteId): array {
        return $this->horaireRepository->findBySiteId($siteId);
    }
}
