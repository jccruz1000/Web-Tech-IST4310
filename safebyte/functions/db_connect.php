<?php
$host = 'localhost';
$user = 'root'; 
$password = 'root'; 
$dbname = 'safebyte_db'; 

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Database Connection failed: " . $conn->connect_error);
}

echo "Congrats, you actually succeeded lol!";
?>