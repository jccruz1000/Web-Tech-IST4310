<?php
require_once('../functions/app_helpers.php');

$_SESSION = array();

session_destroy();

header("Location: login.php");
exit();
?>