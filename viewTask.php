<?php
session_start();
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
<table class="table">
  <tbody>
  <?php
  //This document is the modal popup for viewtask, we needed to have the individual page which uses GET to know which task it needs to retrieve. It is viewed in an iframe in listView
 require('conn.php');
 $stmt = $db->prepare('
  SELECT tl.boardID FROM task t
INNER JOIN taskList tl
ON t.`tl.listID` = tl.listID
WHERE `taskID`=?
  ');
            $stmt->execute(array($_GET['id']));       
            $board = $stmt->fetch();
 $stmt = $db->prepare('SELECT * FROM share WHERE `u.WATIAM` = ? AND `b.boardID` = ?');
            $stmt->execute(array($_SESSION['user'],$board['boardID']));       
            $share = $stmt->fetch();
            $edit = false;
            if ($share['permission'] == "edit"){
              $edit = true;
            }
if (!empty($_POST['update'])) {
//User has submitted an update to task details
  $stmt = $db->prepare('UPDATE task SET taskTitle=?, description=?, importance=?,typeOfWork=?,`c.courseID`=?, `tl.listID`=? WHERE taskID=?');
            $result = $stmt->execute(array(
              $_POST['taskTitleUpdate'],
              $_POST['descriptionUpdate'],
              $_POST['importanceUpdate'],
              $_POST['typeOfWorkUpdate'],
              $_POST['updateCourseID'],
              $_POST['updateListID'],
              $_POST['updateID']
            ));
            if ($_POST['updateDueDate'] != "unchanged"){
              //Special update that deals with date cases which was more complicated
            $stmt = $db->prepare('UPDATE task SET dueDate=? WHERE taskID=?');
            $result = $stmt->execute(array(
              strtotime($_POST['updateDueDate']),
              $_POST['updateID']
            ));
            }
}
 $stmt = $db->prepare('SELECT *, t.description AS taskDescription FROM task t
INNER JOIN course c
ON t.`c.courseID` = c.courseID 
INNER JOIN taskList tl
ON t.`tl.listID` = tl.listID WHERE `taskID`=?');
            $stmt->execute(array($_GET['id']));       
            $task = $stmt->fetch();
            //Now we have our task info in the $task variable, lets output the information for the user.
            ?>
    <form action="viewTask.php?id=<?php echo $_GET['id']; ?>" class="form-newList" method="POST">
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
    <input type="hidden" id="updateID" name="updateID" value="<?php echo $_GET['id']; ?>">
    <?php if ($edit) { ?><input type="submit" class="btn btn-warning" name="update" value="Update Task Details" style="position:absolute; right:10px;"></input><?php } ?>
  </form>

<form action="listView.php?board=<?php echo $board['boardID']; ?>" class="form-newList" method="POST">
 <input type="hidden" id="deleteID" name="ArchiveID" value="<?php echo $board['boardID']; ?>">
<?php if ($edit) { ?><input type="submit" class="btn btn-danger" name="Archive" value="Archive Task"></input><?php } ?>
</form>



