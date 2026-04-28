<?php

require_once __DIR__ . '/../repositories/HoraireSiteRepository.php';

class HoraireSiteService {

    public function __construct(
        private HoraireSiteRepository $horaireRepository
    ) {}

    public function getHoraireById(int $id): ?HoraireSite {
        return $this->horaireRepository->findById($id);
    }
}
