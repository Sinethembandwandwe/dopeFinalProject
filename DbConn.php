<?php
define("DATABASE_HOST", 'redacted');
define("DATABASE_NAME", 'name');
define("DATABASE_USER", 'user');
define("DATABASE_PASSWD", 'Redacted');

$conn = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWD, DATABASE_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

?>