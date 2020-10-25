<?php

$submitted_tastName = $_POST['taskTitle'];
$submitted_taskDetail = $_POST['tastDecrip'];

//echo $submitted_tastName;


require("conn.php"); 
$sql = "INSERT INTO `task` (`taskID`, `taskTitle`, `description`, `dueDate`, `taskDateCreated`, `importance`, `typeOfWork`, `c.courseID`, `tl.listID`, `archived`) VALUES (null, 'Temp', 'hard', '10', '10', '2', 'Hard', '111', '1', '1');";
$result = mysqli_query($conn, $sql);
echo $result;

      



?>

