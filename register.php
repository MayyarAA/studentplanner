<?php
function console_log($output, $with_script_tags = true) {
    $js_code = 'console.log(' . json_encode($output, JSON_HEX_TAG) . 
');';
    if ($with_script_tags) {
        $js_code = '<script>' . $js_code . '</script>';
    }
    echo $js_code;
}
?>

<?php 
    require("conn.php"); //Initiate our database connection
         session_start(); //Start the PHP session
    if(!empty($_POST))  //the code is only ran if the $_POST variables are set, meaning the user submitted the form. 
    { 

        if(empty($_POST['WATIAM']))  //Ensure the username is not blank
        { 
            die("Please enter a username."); 
        } 
         
        if(empty($_POST['password']))  //Ensure the password is not blank
        { 
            die("Please enter a password."); 
        } 
    //Here we make sure the username is not already in use
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
       
  //Prepare the variables to be inserted for our new user. 
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
         //Hash the password instead of using plaintext, so that the site owners do not know the user's password.
        $password = hash('sha256', $_POST['password']); 
         
        //Bind the user's inputs to the query that is about to be ran.
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

            //Redirect to index if successful

                header("Location: index.html"); 

        } 
?> 
