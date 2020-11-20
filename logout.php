<?php

    session_start();
     
    unset($_SESSION['user']); 
     
    header("Location: index.html"); 
    die("Redirecting to: index.html");
