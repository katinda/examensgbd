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
    public function testGetPenaliteByIdRetourneNullSiInexistant(): void {
        $m=$this->createStub(PenaliteRepository::class);$m->method('findById')->willReturn(null);$s=new PenaliteService($m,$this->createStub(MembreRepository::class),$this->createStub(AdministrateurRepository::class));$this->assertNull($s->getPenaliteById(999));
    }
}
