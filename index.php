<?php
// GET /api/fermetures/{id}
if ($method === 'GET' && preg_match('#^/api/fermetures/(\d+)$#', $uri, $matches)) { $fermetureController->getById((int) $matches[1]); }
