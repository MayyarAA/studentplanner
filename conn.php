<?php
$username = 'wmmeyer'; 
$password = '342Group8!?'; 
$host = "mansci-db.uwaterloo.ca";
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
