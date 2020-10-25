<?php
$username = 'wmmeyer'; 
$password = '342Group8!'; 
$host = "192.168.0.32:8080";
$dbname = 'wmmeyer'; 
$conn = new mysqli($host, $username, $password, $dbname);
    try 
    { 
        $db = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8", $username, $password); 
    } 
    catch(PDOException $ex) 
    { 
        die("Failed to connect to the database. Please contact administrator "); 
    } 
    ?>
