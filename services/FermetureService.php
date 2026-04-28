<?php
require_once __DIR__ . '/../repositories/FermetureRepository.php';
class FermetureService {
    public function __construct(private FermetureRepository $fermetureRepository) {}
    public function deleteFermeture(int $id): bool { $fermeture = $this->fermetureRepository->findById($id); if ($fermeture === null) { return false; } $this->fermetureRepository->delete($id); return true; }
}
