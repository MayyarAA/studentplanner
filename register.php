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
                1 
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
         
        header("Location: login.php"); 

        die("Redirecting to login.php"); 

        
        
    } 


?> 
<h1>Register</h1> 
<form action="register.php" method="post"> 
    Username:<br /> 
    <input type="text" name="WATIAM" value="" /> 
    <br /><br /> 
    First Name:<br /> 
    <input type="text" name="firstName" value="" /> 
    <br /><br /> 
    Last Name:<br /> 
    <input type="text" name="lastName" value="" /> 
    <br /><br /> 
    Program:<br /> 
    <input type="text" name="program" value="" /> 
    <br /><br /> 
    Password:<br /> 
    <input type="password" name="password" value="" /> 
    <br /><br /> 
    <input type="submit" value="Register" /> 
</form>
