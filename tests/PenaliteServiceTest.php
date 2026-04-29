<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../models/Penalite.php';
require_once __DIR__ . '/../models/Membre.php';
require_once __DIR__ . '/../models/Administrateur.php';
require_once __DIR__ . '/../repositories/PenaliteRepository.php';
require_once __DIR__ . '/../repositories/MembreRepository.php';
require_once __DIR__ . '/../repositories/AdministrateurRepository.php';
require_once __DIR__ . '/../services/PenaliteService.php';

class PenaliteServiceTest extends TestCase {
    public function testGetAllPenalitesRetourneToutesLesPenalites(): void {
        $m=$this->createStub(PenaliteRepository::class);$m->method('findAll')->willReturn([new Penalite(1,1,null,'2026-05-01','2026-05-15','OTHER'),new Penalite(2,2,null,'2026-06-01','2026-06-07','OTHER')]);$s=new PenaliteService($m,$this->createStub(MembreRepository::class),$this->createStub(AdministrateurRepository::class));$this->assertCount(2,$s->getAllPenalites());
    }
}
