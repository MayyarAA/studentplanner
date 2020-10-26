<?php 
    require("conn.php"); 
         session_start(); 
    if(!empty($_POST)) 
    { 

        if(empty($_POST['WATIAM'])) 
        { 
            die("Please enter a username."); 
        } 
         
        if(empty($_POST['password'])) 
        { 
            die("Please enter a password."); 
        } 
     
        $query = " 
            SELECT 
            FROM user 
            WHERE 
                WATIAM = :WATIAM 
        "; 
         
        $query_params = array( 
            ':WATIAM' => $_POST['WATIAM'] 
        ); 
         
        try 
        { 
            $stmt = $db->prepare($query); 
            $result = $stmt->execute($query_params); 
        } 
        catch(PDOException $ex) 
        { 
            die("Failed to run query: Check user"); 
        } 
         
        $row = $stmt->fetch(); 
        if($row) 
        { 
            die("This username is already in use"); 
        } 
       
        $query = " 
            INSERT INTO user ( 
                WATIAM, 
                firstName, 
                lastName, 
                program,
                passwordHash
            ) VALUES ( 
                :WATIAM, 
                :firstName, 
                :lastName, 
                :program,
                :passwordHash
            ) 
        "; 
         
        $password = hash('sha256', $_POST['password']); 
         
        $query_params = array( 
            ':WATIAM' => $_POST['WATIAM'], 
            ':firstName' => $_POST['firstName'], 
            ':lastName' => $_POST['lastName'], 
            ':program' => $_POST['program'],
            ':passwordHash' => $password
        ); 
         
        try 
        { 
            $stmt = $db->prepare($query); 
            $result = $stmt->execute($query_params); 
        } 
        catch(PDOException $ex) 
        { 
            die("Failed to run query. Insert user" ); 
        } 
        
	        header("Location: index.html"); 

    } 

?> 
