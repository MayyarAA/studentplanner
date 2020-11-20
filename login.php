<?php 

    require("conn.php"); //Use the conn.php file to connect to the database so we don't have to write this in every file.
     session_start(); //Start the PHP session
    $submitted_username = '';  //Set this blank, we keep this variable for purpose of not making the user retype username if they get password wrong
    if(!empty($_POST))  //This code is only ran if the $_POST variables are filled, meaning the form was submitted.
    { 

        $query = " 
            SELECT 
                WATIAM, 
                passwordHash
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
            die("Failed to run query: Select username"); //We notify that in the "Select username" query it failed, for the purposes of development. This will be removed in production to not give hints to malicious users.
        } 
         
        $login_ok = false; //First say that the login is false so we don't have a fake true login.
        $row = $stmt->fetch(); 
        if($row) 
        { 
            //We compare the sha256 hash of the user input to the one in the database to see if they match. if they do, the user typed the right password, and the website owners still do not know the password.
            $check_password = hash('sha256', $_POST['password']); 
             
            if($check_password === $row['passwordHash']) 
            { 
                $login_ok = true; 
            } 
        } 
         
        if($login_ok) 
        { 

            unset($row['passwordHash']);  //Take out the password hash from the returned row variable, we don't need this to be in the session.
            $_SESSION['user'] = $row['WATIAM']; //Now put all variablse (except our password hash) into the php session so the rest of the website can use it.
            header("Location: selectBoard.php");   //Redirect the user to board view page
        } 
        else 
        { 
            print("Login Failed."); 
            $submitted_username = htmlentities($_POST['WATIAM'], ENT_QUOTES, 'UTF-8');  //Protect against attacks by ensuring the input is outputted cleanly.
        } 
    } 
  
?> 

