<?php
/**
 * Origin Driving School – Front Controller
 * File: public/index.php
 */

// --------------------------------------------------
// 1. Start session ONCE
// --------------------------------------------------
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// --------------------------------------------------
// 2. Error reporting
// --------------------------------------------------
error_reporting(E_ALL);
ini_set('display_errors', 1);

set_error_handler(function ($severity, $message, $file, $line) {
    // Convert warnings/errors into exceptions, ignore notices
    if ($severity & (E_ERROR | E_USER_ERROR | E_WARNING | E_USER_WARNING)) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
    return false;
});

set_exception_handler(function ($e) {
    http_response_code(500);
    echo "<h1>Application Error</h1>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    exit;
});

// --------------------------------------------------
// 3. Load config
// --------------------------------------------------
require_once __DIR__ . '/../config/database.php';

// --------------------------------------------------
// 4. Load helpers (optional, if you’re using them)
// --------------------------------------------------
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../helpers/validation.php';

// --------------------------------------------------
// 5. Load core classes
// --------------------------------------------------
require_once __DIR__ . '/../core/App.php';
require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Model.php';

// --------------------------------------------------
// 6. Dispatch request
// --------------------------------------------------
$app = new App();
