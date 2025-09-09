<?php
// middleware/authMiddleware.php
require_once __DIR__ . '/sessionManager.php';

function checkAccess(string $requiredRole) {
    enforceSession($requiredRole);
}
