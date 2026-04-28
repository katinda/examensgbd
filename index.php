<?php
// DELETE /api/fermetures/{id}
if ($method === 'DELETE' && preg_match('#^/api/fermetures/(\d+)$#', $uri, $matches)) { $fermetureController->delete((int) $matches[1]); }
