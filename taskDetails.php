<?php
//This file contains the php code for inserting tast detail data from the html form

//declaring local php variables 
$submitted_tastName = $_POST['taskTitle'];
$submitted_taskDetail = $_POST['tastDecrip'];

//binding php var to sql object
echo $submitted_tastName;

//imports the objects and valiues from conn
require("conn.php"); 
$sql = "INSERT INTO `task` (`taskID`, `taskTitle`, `description`, `dueDate`, `taskDateCreated`, `importance`, `typeOfWork`, `c.courseID`, `tl.listID`, `archived`) VALUES (null, '.$submitted_tastName.', '.$submitted_taskDetail.', '10', '10', '2', 'Hard', '111', '1', '1');";
//executes the post call
$result = mysqli_query($conn, $sql);
echo $result;

      



?>


