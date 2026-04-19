<?php

// PUT /sites/{id} → met à jour un site existant
// Reçoit un JSON dans le body avec les champs à modifier,
// appelle le service et retourne une erreur 404 si le site n'existe pas.
public function update(int $id): void {
    $data = json_decode(file_get_contents('php://input'), true);
    $ok   = $this->siteService->updateSite($id, $data);

    header('Content-Type: application/json');

    if (!$ok) {
        http_response_code(404);
        echo json_encode(['erreur' => "Site $id introuvable"]);
        return;
    }

    echo json_encode(['message' => "Site $id mis à jour avec succès"], JSON_UNESCAPED_UNICODE);
}
