<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
<table class="table">
  <tbody>
  <?php
  //This document is the modal popup for viewtask, we needed to have the individual page which uses GET to know which task it needs to retrieve. It is viewed in an iframe in listView
 require('conn.php');

 $stmt = $db->prepare('SELECT * FROM task t
INNER JOIN course c
ON t.`c.courseID` = c.courseID 
INNER JOIN taskList tl
ON t.`tl.listID` = tl.listID WHERE `taskID`=?');
            $stmt->execute(array($_GET['id']));       
            $task = $stmt->fetch();
            //Now we have our task info in the $task variable, lets output the information for the user.
            ?>
    <tr>
      <th>ID</th>
      <td><?php echo $task['taskID'];?></td>
    </tr>
    <tr>
      <th scope="row">Title</th>
      <td><?php echo $task['taskTitle'];?></td>
    </tr>
    <tr>
      <th scope="row">Description</th>
      <td><?php echo $task['description'];?></td>
    </tr>
    <tr>
      <th scope="row">Due Date</th>
      <td><?php echo date("Y D M j G:i:s", $task['dueDate']);?></td>
    </tr>
    <tr>
      <th scope="row">Date Created</th>
      <td><?php echo date("Y D M j G:i:s", $task['taskDateCreated']);?></td>
    </tr>
    <tr>
      <th scope="row">Importance</th>
      <td><?php echo $task['importance'];?></td>
    </tr>
    <tr>
      <th scope="row">Type Of Work</th>
      <td><?php echo $task['typeOfWork'];?></td>
    </tr>
    <tr>
      <th scope="row">Course</th>
      <td><?php echo $task['courseTitle'];?></td>
    </tr>
    <tr>
      <th scope="row">List</th>
      <td><?php echo $task['listTitle'];?></td>
    </tr>
  </tbody>
</table>
<form action="listView.php" onclick="window.top.location = 'https://mansci-db.uwaterloo.ca/~wmmeyer/r2/studentplanner/listView.php?r=t'" class="form-newList" method="POST">
 <input type="hidden" id="deleteID" name="deleteID" value="<?php echo $_GET['id']; ?>">
<input type="submit" class="btn btn-danger" name="delete" value="Delete Task"></input>
</form>
