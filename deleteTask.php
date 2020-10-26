<?php
//This file contains the php code for deleting a given task ID

require("conn.php"); //Use the conn.php file to connect to the database 
session_start();
$query = " 
            DELETE
            FROM task 
            WHERE 
                taskID = :taskID;
        "; 
        // taskID parameter from user form 
        $query_params = array( 
            ':taskID' => $_POST['taskID'] 
        ); 
         
        try 
        { 
            $stmt = $db->prepare($query); 
            $result = $stmt->execute($query_params);
            header("Location: boardView.html"); 
        } 
        catch(PDOException $ex) 
        { 
            die("Failed to run query: Delete task");
            
        } 
?>