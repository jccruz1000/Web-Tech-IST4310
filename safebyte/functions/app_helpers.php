<?php
session_start();

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function redirect_if_logged_in($location = 'dashboard.php') {
    if (is_logged_in()) {
        header('Location: ' . $location);
        exit();
    }
}