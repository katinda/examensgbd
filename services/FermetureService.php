<?php
require_once __DIR__ . '/../repositories/FermetureRepository.php';
class FermetureService {
    public function __construct(private FermetureRepository $fermetureRepository) {}
    public function getFermeturesGlobales(): array { return $this->fermetureRepository->findGlobales(); }
}
