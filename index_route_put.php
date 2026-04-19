<?php

// PUT /sites/{id} → met à jour un site existant
// Appelle la méthode update() du SiteController
} elseif ($method === 'PUT' && preg_match('#^/sites/(\d+)$#', $uri, $matches)) {
    $siteController->update((int) $matches[1]);
