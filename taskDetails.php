<!DOCTYPE html>
<html lang="">



<!--This file contains the html code for the taskdetail page -->
<head>
<!-- the link allows bootstrap styling to work on the page -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
    <title>Student Planner</title>

    <body>
        <h1 class="text-center">Student Planner</h1>
        <h2>MSCI 342 Project</h2>

    </body>
<!-- form page which will pass the user information -->
    <form action="taskDetails.php" method="POST">
        <div class="form-group">
            <label for="exampleInputEmail1">Task Title</label>
            <input style="height:50px;width:900px;font-size:20pt;" type="text" class="form-control" name="taskTitle" aria-describedby="emailHelp">
            <small id="taskHelp" class="form-text text-muted">Give your task a descriptive title</small>
        </div>
        <div class="form-group">
            <label for="taskDescription">Task Description</label>
            <textarea style="height:150px;width:900px;font-size:14pt;"class="form-control" id="exampleFormControlTextarea1" rows="3" name="tastDecrip"></textarea>
            <!-- <input style="height:150px;width:900px;font-size:14pt;"type="text" class="form-control" name="tastDecrip"> -->
            <small id="" class="form-text text-muted">Add a description with information regarding your task</small>
        </div>
        <div class="container">
        <div class ="row">
            <div class="col">
            <select name = "listID">
                <?php

                    $boardID = $_POST['newTaskBoard'];
                    require("conn.php"); 
                    $stmt = $db->prepare('SELECT listID,listTitle FROM taskList WHERE boardID=?');
                        $stmt->execute(array($boardID));       
                        $taskLists = $stmt->fetchAll();
                        foreach ($taskLists as $taskList){
                             echo "<option value=" . $taskList['listID'] . ">" . $taskList['listTitle'] . "</option>"; 
                        }
                ?> 
            </select>
            </div>
      <input type="hidden" name="newTaskBoard" value="<?php echo $boardID; ?>">


        <div class="col">
            <label for="taskEffort"> Effort</label>
           <input style="height:50px;width:80px;font-size:14pt;"type="number" class="form-control" name="taskEffort">
           <small id="" class="form-text text-muted">Amount of work required</small>
        </div>

        <div class="col">
            <label for="taskTypeWork"> Type of Work</label>
           <input style="height:50px;width:160px;font-size:14pt;"type="text" class="form-control" name="taskTypeWork">
           <small id="" class="form-text text-muted">Detail of work type</small>
        </div>
        <div class="col">
            <label for="taskDateDue"> Due Date</label>
            <input class="form-control" type="datetime-local" value="2021-08-19T13:45:00" name="taskDueDate" id="taskDueDate">
            <small id="" class="form-text text-muted">Due date of task</small>
        </div>
    </div>
    </div>
        <div class="form-group form-check">

        </div>
        <button type="submit" class="btn btn-primary" name="submit" value="Submit">Submit</button>
    </form>



</head>
<?php
//This file contains the php code for inserting tast detail data from the html form
if (!empty($_POST['submit'])){
    //declaring local php variables 
$submitted_taskName = $_POST['taskTitle'];
$submitted_taskDetail = $_POST['tastDecrip'];
$submitted_taskEffort = $_POST['taskEffort'];
$submitted_taskDateDue= $_POST['taskDateDue'];
$submitted_taskTypeWork= $_POST['taskTypeWork'];
$submitted_taskListAssigned = $_POST['listID'];
$submitted_courseID = $_POST['courseID'];

//imports the objects and valiues from conn
    $query = " 
            INSERT INTO task ( 
                taskTitle, 
                description,
                dueDate,
                taskDateCreated,
                importance,
                typeOfWork,
                `c.courseID`,
                `tl.listID`,
                archived
            ) VALUES ( 
                :taskTitle, 
                :description,
                :dueDate,
                :taskDateCreated,
                :importance,
                :typeOfWork,
                :courseID,
                :listID,
                :archived
            ) 
        "; 
        $query_params = array( 
            ':taskTitle' => $submitted_taskName, 
            ':description' => $submitted_taskDetail, 
            ':dueDate' => strtotime($_POST['taskDueDate']),
            ':taskDateCreated' => time(),
            ':importance' => $submitted_taskEffort,
            ':typeOfWork' => $submitted_taskTypeWork,
            ':courseID' => 111,
            ':listID' => $submitted_taskListAssigned,
            ':archived' => 0
        );    
        $stmt = $db->prepare($query); 
        $result = $stmt->execute($query_params); 
if ($result) {
    header("Location: listView.php?board=".$boardID);
}
}

?>


