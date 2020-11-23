<?php
//Force the user to login if they aren't
session_start();
if(empty($_SESSION['user'])) 
    { 
        header("Location: index.html"); 
        die("Redirecting to index.html"); 
    } 
//This is a workaround, where the page loads too fast before the delete function happen so we need to re-load after the fact.
require("conn.php"); 

// $stmt = $db->prepare('SELECT * FROM board WHERE boardID = ?');
//             $stmt->execute(array($_GET['board']));       
//             $board = $stmt->fetch();
// ?>

<!DOCTYPE html>
<html lang="">

<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
    <link href="CSS/jumbotron" rel="stylesheet">
    <link href="CSS/BoardView-Body.css" rel="stylesheet">
    <title>Student Planner</title>

</head>

    <body>
    <div class="jumbotron">
        <div class="container">
            <h2 class="display-5"><?php echo $board['boardTitle']; ?> Created <?php echo date("Y-m-d H:i:s", $board['boardDateCreated']);?> by <?php echo $board['u.WATIAM']; ?> </h2>
            <a class="btn btn-primary" href="listView.php">Back</a>
        </div>
    </div>
<?php

// get complete details for a list
$query = " 
    SELECT 
        DISTINCT(t.taskID),
        t.taskTitle,
        t.description,
        t.dueDate,
        t.taskDateCreated,
        t.importance,
        t.typeOfWork,
        c.courseTitle 
    FROM task t INNER JOIN course c INNER JOIN taskList l INNER JOIN board b 
    WHERE t.archived = 1 AND b.`u.WATIAM` =:WATIAM
    "; 
    $query_params = array( 
        ':WATIAM' => $_SESSION['user'],
    ); 
    $stmt = $db->prepare($query); 
    $result = $stmt->execute($query_params);       
    $tasks = $stmt->fetchAll();

    echo "<div class='row'>";
        echo "
        <div class='col-xs-3'>
        <div class='card'>
        <div class='card-header'>
        <div class ='card-Title'>Archived List</div>
        </div>
        <ul class='list-group list-group-flush'>
        ";
    foreach ($tasks as $task){
        //This code is run FOR EACH task in each list. Here we output an html row with some data. It also lets it target a custom modal to pop up each viewTask
        echo "<li class='list-group-item' onclick='dynamicModal(".$task['taskID'].")' data-toggle='modal' data-target='#viewTask'>
        <div class='taskTitle-formatting'>".$task['taskTitle']."</div>"."\n<div class='taskDesc-formatting'>".$task['description']."</div></li>";
    }
        echo " 
        </ul>
        </div>
        </div>";
    echo "</div>";
?>

<!--Below we have the code for our "modal" which pops up when the user clicks a task. The modal outputs all the details of the task-->
<div class="modal fade" id="viewTask" tabindex="-1" aria-labelledby="viewTask" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content" style="width:578px;">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Task view</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" >
        <iframe sandbox="allow-top-navigation allow-scripts allow-forms" class="embed-responsive-item" id="viewTaskFrame" style="border:0; width:558px; height:700px;" src="listView.php"></iframe>
      </div>
    </div>
  </div>
</div>

<script>
// function to refresh the modal page for task details
function dynamicModal(str)
{
$("#viewTaskFrame").attr("src", "https://mansci-db.uwaterloo.ca/~wmmeyer/viewTask.php?id="+str);
}
</script>

<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
<script>$('#viewTask').on('hidden.bs.modal', function () {
 location.reload();
})</script>
</body>
