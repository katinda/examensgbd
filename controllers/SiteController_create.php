<?php

// POST /sites → crée un nouveau site
// Reçoit un JSON dans le body, vérifie que le champ "nom" est présent,
// appelle le service pour créer le site et retourne l'ID avec un code 201.
public function create(): void {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['nom'])) {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['erreur' => 'Le champ "nom" est obligatoire']);
        return;
    }

    $id = $this->siteService->createSite($data);

    header('Content-Type: application/json');
    http_response_code(201);
    echo json_encode(['message' => 'Site créé avec succès', 'id' => $id], JSON_UNESCAPED_UNICODE);
}
