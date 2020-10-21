<?php
$username = 'wmmeyer'; 
$password = '342Group8!'; 
$host = "localhost";
$dbname = 'wmmeyer'; 
    try 
    { 
        $db = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8", $username, $password); 
    } 
    catch(PDOException $ex) 
    { 
        die("Failed to connect to the database. Please contact administrator "); 
    } 
    ?>
