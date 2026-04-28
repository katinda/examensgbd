<?php

require_once __DIR__ . '/../repositories/HoraireSiteRepository.php';

class HoraireSiteService {

    public function __construct(
        private HoraireSiteRepository $horaireRepository
    ) {}

    public function getHoraireBySiteAndAnnee(int $siteId, int $annee): ?HoraireSite {
        return $this->horaireRepository->findBySiteAndAnnee($siteId, $annee);
    }
}
