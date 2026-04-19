<?php

// Vérifie que deleteSite() retourne true quand le site existe
public function testDeleteSiteRetourneTrueSiSiteExiste(): void {
    $mockRepo = $this->createStub(SiteRepository::class);
    $mockRepo->method('findById')->willReturn(
        $this->creerSite(1, 'Club Paris', true)
    );

    $service = new SiteService($mockRepo);
    $result  = $service->deleteSite(1);

    $this->assertTrue($result, "deleteSite() doit retourner true si le site existe");
}


// Vérifie que deleteSite() retourne false quand le site n'existe pas
public function testDeleteSiteRetourneFalseSiSiteInexistant(): void {
    $mockRepo = $this->createStub(SiteRepository::class);
    $mockRepo->method('findById')->willReturn(null);

    $service = new SiteService($mockRepo);
    $result  = $service->deleteSite(999);

    $this->assertFalse($result, "deleteSite() doit retourner false si le site n'existe pas");
}
