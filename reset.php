<?php
/**
 * Reset Session Script
 *
 * This script clears the user's session data and redirects to the product list page.
 * It's useful for testing and debugging purposes.
 */

// Reset the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
session_destroy();

// Clear any cookies
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Redirect to product list
header('Location: /Product/list');
exit();
