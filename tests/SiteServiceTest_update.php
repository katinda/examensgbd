<?php

// Vérifie que updateSite() retourne true quand le site existe
public function testUpdateSiteRetourneTrueSiSiteExiste(): void {
    $mockRepo = $this->createStub(SiteRepository::class);
    $mockRepo->method('findById')->willReturn(
        $this->creerSite(1, 'Club Paris', true)
    );

    $service = new SiteService($mockRepo);
    $result  = $service->updateSite(1, ['nom' => 'Club Paris Modifie']);

    $this->assertTrue($result, "updateSite() doit retourner true si le site existe");
}


// Vérifie que updateSite() retourne false quand le site n'existe pas
public function testUpdateSiteRetourneFalseSiSiteInexistant(): void {
    $mockRepo = $this->createStub(SiteRepository::class);
    $mockRepo->method('findById')->willReturn(null);

    $service = new SiteService($mockRepo);
    $result  = $service->updateSite(999, ['nom' => 'Club Inconnu']);

    $this->assertFalse($result, "updateSite() doit retourner false si le site n'existe pas");
}
