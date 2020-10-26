<!DOCTYPE html>
<html lang="">


<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
    <title>Student Planner</title>

    <body>
        <h1 class="text-center">Student Planner</h1>
	<br>
        <h2 class="text-center">List View</h2>
	<br>

    </body>
<?php
require("conn.php"); 


// get complete details for a list
$sql = "SELECT listID,listTitle,taskTitle, description FROM task NATURAL JOIN taskList ";
$result = mysqli_query($conn, $sql);


$data = array(); // create a variable to hold the information
while ($row = mysqli_fetch_array($result)){
  $data[] = $row; // add the row in to the results (data) array
}

// display list title - initially, there is just one list - this will change when multiple lists are implemented
echo "<h3>" .$data[0][1]. " List</h3>";

// traverse through and display the information of related tasks
for ($x = 0; $x <= count($data); $x+=1) {
    echo "Task ".$x.": " .$data[$x][2]."<br>";
    echo "Description: " .$data[$x][3]."<br><br><br>";

  }

?>
</head>