<?php

require_once __DIR__ . '/../repositories/HoraireSiteRepository.php';

class HoraireSiteService {

    public function __construct(
        private HoraireSiteRepository $horaireRepository
    ) {}

    public function getAllHoraires(): array {
        return $this->horaireRepository->findAll();
    }
}
