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
    public function testCreatePenaliteRetourneMembreIntrouvable(): void {
        $mr=$this->createStub(MembreRepository::class);$mr->method('findById')->willReturn(null);$s=new PenaliteService($this->createStub(PenaliteRepository::class),$mr,$this->createStub(AdministrateurRepository::class));$this->assertEquals('membre_introuvable',$s->createPenalite(['membre_id'=>999,'date_debut'=>'2026-05-01','date_fin'=>'2026-05-15','cause'=>'OTHER']));
    }
}
