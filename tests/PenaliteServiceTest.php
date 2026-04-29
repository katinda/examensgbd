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
    public function testLeverPenaliteRetourneAdminNonGlobal(): void {
        $pr=$this->createStub(PenaliteRepository::class);$pr->method('findById')->willReturn(new Penalite(1,1,null,'2026-05-01','2026-05-15','OTHER',false));$ar=$this->createStub(AdministrateurRepository::class);$ar->method('findById')->willReturn(new Administrateur(2,'a','h','A','B',null,'SITE',1,true));$s=new PenaliteService($pr,$this->createStub(MembreRepository::class),$ar);$this->assertEquals('admin_non_global',$s->leverPenalite(1,['admin_id'=>2,'raison'=>'t']));
    }
}
