<?php

$submitted_tastName = $_POST['taskTitle'];
$submitted_taskDetail = $_POST['tastDecrip'];

echo $submitted_tastName;
require("conn.php"); 
$sql = "INSERT INTO `task` (`taskID`, `taskTitle`, `description`, `dueDate`, `taskDateCreated`, `importance`, `typeOfWork`, `c.courseID`, `tl.listID`, `archived`) VALUES ('2', 'Temp', 'hard', '10', '10', '2', 'Hard', '111', '1', '1');";
$result = mysqli_query($conn, $sql);
    
$resulCheck = mysqli_num_rows($result);
if ($resulCheck > 0) {
	echo "working sql";
} else {
    echo "no records found";

}



?>
