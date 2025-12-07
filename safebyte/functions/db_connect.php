<?php
$host = 'sql301.infinityfree.com';
$user = 'if0_40549660';
$password = 'tb4fJCGd4P';
$dbname = 'if0_40549660_safebyte_db';

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Database Connection failed: " . $conn->connect_error);
}
?>