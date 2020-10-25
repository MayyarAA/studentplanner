<?php

$submitted_tastName = $_POST['taskTitle'];
$submitted_taskDetail = $_POST['tastDecrip'];

echo $submitted_tastName;

$sql = "SELECT attraction FROM ".$tripType." WHERE city='".$city."';";
$result = mysqli_query($conn, $sql_2);
    
$resulCheck = mysqli_num_rows($result);
if ($resulCheck > 0) {
	header("location: $submitted_tastName");
} else {
    echo "no records found";

}



?>
