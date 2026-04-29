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
    public function testCreatePenaliteRetourneUnId(): void {
        $mr=$this->createStub(MembreRepository::class);$mr->method('findById')->willReturn(new Membre(1,'G0001','A','B',null,null,'G',null,true));$pr=$this->createStub(PenaliteRepository::class);$pr->method('insert')->willReturn(3);$s=new PenaliteService($pr,$mr,$this->createStub(AdministrateurRepository::class));$r=$s->createPenalite(['membre_id'=>1,'date_debut'=>'2026-05-01','date_fin'=>'2026-05-15','cause'=>'OTHER']);$this->assertEquals(3,$r);
    }
}
