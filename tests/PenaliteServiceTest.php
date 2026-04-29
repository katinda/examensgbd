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
    public function testDeletePenaliteRetourneFalseSiInexistant(): void {
        $pr=$this->createStub(PenaliteRepository::class);$pr->method('findById')->willReturn(null);$s=new PenaliteService($pr,$this->createStub(MembreRepository::class),$this->createStub(AdministrateurRepository::class));$this->assertFalse($s->deletePenalite(999));
    }
}
