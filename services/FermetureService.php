<?php
require_once __DIR__ . '/../repositories/FermetureRepository.php';
class FermetureService {
    public function __construct(private FermetureRepository $fermetureRepository) {}
    public function getFermeturesBySiteId(int $siteId): array { return $this->fermetureRepository->findBySiteId($siteId); }
}
