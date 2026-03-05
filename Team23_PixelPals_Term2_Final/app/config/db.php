<?php
$host = 'localhost';
$db_name = 'cs2team23_db'; 
$username = 'cs2team23'; 
$password = '5JWJ5aZvA1TzknSYRW8I1niW1';

try {
    $db = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>