<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

function redirect_if_logged_in($location = 'dashboard.php') {
    if (is_logged_in()) {
        header('Location: ' . $location);
        exit();
    }
}

function redirect_if_not_admin($location = '../index.php') {
    if (!is_admin()) {
        header('Location: ' . $location);
        exit();
    }
}

function require_login($redirect = 'index.php?page=login') {
    if (!is_logged_in()) {
        header('Location: ' . $redirect);
        exit();
    }
}
?>