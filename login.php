<?php 

    require("conn.php"); 
     session_start():
    $submitted_username = ''; 
    if(!empty($_POST)) 
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
            die("Failed to run query: Select username"); 
        } 
         
        $login_ok = false; 
        $row = $stmt->fetch(); 
        if($row) 
        { 
            // Using the password submitted by the user and the salt stored in the database, 
            // we now check to see whether the passwords match by hashing the submitted password 
            // and comparing it to the hashed version already stored in the database. 
            $check_password = hash('sha256', $_POST['password']); 
             
            if($check_password === $row['passwordHash']) 
            { 
                $login_ok = true; 
            } 
        } 
         
        if($login_ok) 
        { 
            unset($row['passwordHash']); 
            $_SESSION['user'] = $row; 
              header("Location: index.php"); 
            die("Redirecting to: index.php"); 
        } 
        else 
        { 
            print("Login Failed."); 
            $submitted_username = htmlentities($_POST['username'], ENT_QUOTES, 'UTF-8'); 
        } 
    } 
  
?> 
<h1>Login</h1> 
<form action="login.php" method="post"> 
    WATIAM:<br /> 
    <input type="text" name="WATIAM" value="<?php echo $submitted_username; ?>" /> 
    <br /><br /> 
    Password:<br /> 
    <input type="password" name="password" value="" /> 
    <br /><br /> 
    <input type="submit" value="Login" /> 
</form> 
<a href="register.php">Register</a>
