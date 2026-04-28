<?php
require_once __DIR__ . '/../repositories/FermetureRepository.php';
class FermetureService {
    public function __construct(private FermetureRepository $fermetureRepository) {}
    public function getFermetureById(int $id): ?Fermeture { return $this->fermetureRepository->findById($id); }
}
