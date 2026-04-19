<?php

// DELETE /sites/{id} → supprime un site
// Appelle le service pour supprimer le site,
// retourne une erreur 404 si le site n'existe pas.
public function delete(int $id): void {
    $ok = $this->siteService->deleteSite($id);

    header('Content-Type: application/json');

    if (!$ok) {
        http_response_code(404);
        echo json_encode(['erreur' => "Site $id introuvable"]);
        return;
    }

    echo json_encode(['message' => "Site $id supprimé avec succès"], JSON_UNESCAPED_UNICODE);
}
