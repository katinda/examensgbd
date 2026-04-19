<?php

// POST /sites → crée un nouveau site
// Appelle la méthode create() du SiteController
} elseif ($method === 'POST' && $uri === '/sites') {
    $siteController->create();
