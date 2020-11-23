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

$stmt = $db->prepare('SELECT * FROM board WHERE boardID = ?');
            $stmt->execute(array($_GET['board']));       
            $board = $stmt->fetch();
?>

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
            <a class="btn btn-primary" href="listView.php?board=<?php echo $board['boardID']; ?>">Back</a>
        </div>
    </div>
    
<?php

if (!empty($_POST['Delete'])){
$stmt = $db->prepare('DELETE FROM task WHERE `taskID`=?');
            $stmt->execute(array($_POST['DeleteID']));       
            $task = $stmt->fetch();
}

if (!empty($_POST['UnArchiveID'])){
  $query = " 
  UPDATE task  SET archived=0 where taskID = :taskID;
        "; 
        // taskID parameter from user form 
        $query_params = array( 
            ':taskID' => $_POST['UnArchiveID'] 
        ); 
         
        try 
        { 
            $stmt = $db->prepare($query); 
            $result = $stmt->execute($query_params);
        } 
        catch(PDOException $ex) 
        { 
            die("Failed to run query: UnArchive task");
            
        } 
}

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
        echo "<li class='list-group-item' data-toggle='modal' data-target='#viewTask".$task['taskID']."'>
        <div class='taskTitle-formatting'>".$task['taskTitle']."</div>"."\n<div class='taskDesc-formatting'>".$task['description']."</div></li>";
    }
        echo " 
        </ul>
        </div>
        </div>";
    echo "</div>";
?>

<!--Below we have the code for our "modal" which pops up when the user clicks a task. The modal outputs all the details of the task-->
<?php
$stmt = $db->prepare('SELECT * FROM taskList WHERE boardID = ? ORDER BY `listID` ASC');
            $stmt->execute(array($_GET['board']));       
            $lists = $stmt->fetchAll();
foreach ($lists as $list){
    $stmt = $db->prepare('SELECT * FROM task WHERE `tl.listID` = ?');
            $stmt->execute(array($list['listID']));       
            $tasks = $stmt->fetchAll();
            foreach ($tasks as $task){
?>


<div class="modal fade" id="viewTask<?php echo $task['taskID']; ?>" tabindex="-1" aria-labelledby="viewTask" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content" style="width:578px;">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Task view</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" >
        <table class="table">
          <tbody>
          <?php
          //This document is the modal popup for viewtask, we needed to have the individual page which uses GET to know which task it needs to retrieve. It is viewed in an iframe in listView
         $stmt = $db->prepare('
          SELECT tl.boardID FROM task t
        INNER JOIN taskList tl
        ON t.`tl.listID` = tl.listID
        WHERE `taskID`=?
          ');
                    $stmt->execute(array($task['taskID']));       
                    $board = $stmt->fetch();
         $stmt = $db->prepare('SELECT * FROM share WHERE `u.WATIAM` = ? AND `b.boardID` = ?');
                    $stmt->execute(array($_SESSION['user'],$board['boardID']));       
                    $share = $stmt->fetch();
                    $edit = false;
                    if ($share['permission'] == "edit"){
                      $edit = true;
                    }
        
         $stmt = $db->prepare('SELECT *, t.description AS taskDescription FROM task t
        INNER JOIN course c
        ON t.`c.courseID` = c.courseID 
        INNER JOIN taskList tl
        ON t.`tl.listID` = tl.listID WHERE `taskID`=?');
                    $stmt->execute(array($task['taskID']));       
                    $task = $stmt->fetch();
                    //Now we have our task info in the $task variable, lets output the information for the user.
                    ?>
            <form action="listView.php?board=<?php echo $board['boardID']; ?>" class="form-newList" method="POST">
            <tr>
              <th>ID</th>
              <td><?php echo $task['taskID'];?></td>
              <?php if ($edit) { ?><td>Modify Values and Submit Below</td><?php } ?>
            </tr>
            <tr>
              <th scope="row">Title</th>
              <td><?php echo $task['taskTitle'];?></td>
              <?php if ($edit) { ?><td><input type="text" class="form-control" name="taskTitleUpdate" value="<?php echo $task['taskTitle']; ?>"></td><?php } ?>
            </tr>
            <tr>
              <th scope="row">Description</th>
              <td><?php echo $task['taskDescription'];?></td>
              <?php if ($edit) { ?><td><input type="text" class="form-control" name="descriptionUpdate" value="<?php echo $task['taskDescription']; ?>"></td><?php } ?>
            </tr>
            <tr>
              <th scope="row">Due Date</th>
              <td><?php echo date("Y-m-d H:i:s", $task['dueDate']);?></td>
              <?php if ($edit) { ?><td><input class="form-control" type="datetime-local" value="unchanged" name="updateDueDate" id="updateDueDate"></td><?php } ?>
            
            </tr>
            <tr>
              <th scope="row">Date Created</th>
              <td><?php echo date("Y-m-d H:i:s", $task['taskDateCreated']);?></td>
              <?php if ($edit) { ?><td>Cannot Modify Date Created</td><?php } ?>
            </tr>
            <tr>
              <th scope="row">Importance</th>
              <td><?php echo $task['importance'];?></td>
              <?php if ($edit) { ?><td><input type="text" class="form-control" name="importanceUpdate" value="<?php echo $task['importance']; ?>"></td><?php } ?>
            </tr>
            <tr>
              <th scope="row">Type Of Work</th>
              <td><?php echo $task['typeOfWork'];?></td>
              <?php if ($edit) { ?><td><input type="text" class="form-control" name="typeOfWorkUpdate" value="<?php echo $task['typeOfWork']; ?>"></td><?php } ?>
            </tr>
            <tr>
              <th scope="row">Course</th>
              <td><?php echo $task['courseTitle'];?></td>
              <?php if ($edit) { ?><td><select name = "updateCourseID">
                    <option value="<?php echo $task['courseID']; ?>"><?php echo $task['courseTitle']; ?></option>
                        <?php                    
                            $query = "SELECT courseID,courseTitle FROM course";
                            $courseTitles = mysqli_query($conn, $query);
                            while ($row = mysqli_fetch_array($courseTitles)) {
                                echo "<option value=" . $row['courseID'] . ">" . $row['courseTitle'] . "</option>"; 
                            }
                        ?> 
              </select></td><?php } ?>
            </tr>
            <tr>
              <th scope="row">List</th>
              <td><?php echo $task['listTitle'];?></td>
              <?php if ($edit) { ?><td><select name = "updateListID">
                    <option value="<?php echo $task['listID']; ?>"><?php echo $task['listTitle']; ?></option>
                        <?php                    
                            $query = "SELECT listID,listTitle FROM taskList";
                            $taskLists = mysqli_query($conn, $query);
                            while ($row = mysqli_fetch_array($taskLists)) {
                                echo "<option value=" . $row['listID'] . ">" . $row['listTitle'] . "</option>"; 
                            }
                        ?> 
              </select></td><?php } ?>
            </tr>

          </tbody>
        </table>
            </form>

        <form action="ArchivedPage.php?board=<?php echo $board['boardID']; ?>" class="form-newList" method="POST">
         <input type="hidden" id="DeleteID" name="DeleteID" value="<?php echo $task['taskID']; ?>">
         <input type="hidden" id="UnArchiveID" name="UnArchiveID" value="<?php echo $task['taskID']; ?>">
        <?php if ($edit) { ?><input type="submit" class="btn btn-danger" name="Delete" value="Delete Task"></input>
            <input type="submit" class="btn btn-danger" name="UnArchive" value="UnArchive Task"></input><?php } ?>
        </form>
      </div>
    </div>
  </div>
</div>

<?php
  }
}
?>

<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
</body>
