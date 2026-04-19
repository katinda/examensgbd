<?php

// DELETE /sites/{id} → supprime un site
// Appelle la méthode delete() du SiteController
} elseif ($method === 'DELETE' && preg_match('#^/sites/(\d+)$#', $uri, $matches)) {
    $siteController->delete((int) $matches[1]);
