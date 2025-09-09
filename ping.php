<?php
require_once __DIR__ . '/middleware/sessionManager.php'; // unifica config y session_start
$_SESSION['LAST_ACTIVITY'] = time();
refreshSessionCookie();
http_response_code(204);
