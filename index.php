<?php
// PUT /api/fermetures/{id}
if ($method === 'PUT' && preg_match('#^/api/fermetures/(\d+)$#', $uri, $matches)) { $fermetureController->update((int) $matches[1]); }
