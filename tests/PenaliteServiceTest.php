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
    public function testLeverPenaliteRetourneDejaLevee(): void {
        $pr=$this->createStub(PenaliteRepository::class);$pr->method('findById')->willReturn(new Penalite(1,1,null,'2026-05-01','2026-05-15','OTHER',true));$s=new PenaliteService($pr,$this->createStub(MembreRepository::class),$this->createStub(AdministrateurRepository::class));$this->assertEquals('deja_levee',$s->leverPenalite(1,['admin_id'=>1,'raison'=>'t']));
    }
}
