<?php

// Vérifie que createSite() appelle bien insert() du repository et retourne un ID
public function testCreateSiteRetourneUnId(): void {
    $mockRepo = $this->createStub(SiteRepository::class);
    // On simule que insert() retourne l'ID 5
    $mockRepo->method('insert')->willReturn(5);

    $service = new SiteService($mockRepo);
    $result  = $service->createSite([
        'nom'         => 'Club Bordeaux',
        'adresse'     => '1 avenue du Vin',
        'ville'       => 'Bordeaux',
        'code_postal' => '33000',
    ]);

    $this->assertEquals(5, $result, "createSite() doit retourner l'ID du site créé");
}
