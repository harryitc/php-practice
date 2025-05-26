<?php

// Start session for routing decisions
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$url = $_GET['url'] ?? '';
$url = rtrim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);

// Handle root URL routing based on user role
if (empty($url[0]) || $url[0] == '') {
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        // Redirect admin to dashboard
        header('Location: /Order/dashboard');
        exit();
    } else {
        // Show homepage for customers/guests
        $controllerName = 'HomeController';
        $action = 'index';
    }
} else {
    $controllerName = ucfirst($url[0]) . 'Controller';
    $action = isset($url[1]) && $url[1] != '' ? $url[1] : 'index';
}

if (!file_exists('app/controllers/' . $controllerName . '.php')) {
    die('Controller not found');
}

require_once 'app/controllers/' . $controllerName . '.php';

$controller = new $controllerName();

if (!method_exists($controller, $action)) {
    die('Action not found');
}

call_user_func_array([$controller, $action], array_slice($url, 2));
