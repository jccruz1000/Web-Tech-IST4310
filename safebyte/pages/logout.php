<?php
require_once(__DIR__ . '/../functions/app_helpers.php');

$_SESSION = array();

session_destroy();

header("Location: ../index.php?page=login");
exit();
?>