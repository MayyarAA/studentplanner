<?php
//Force the user to login if they aren't
session_start();
if(empty($_SESSION['user'])) 
    { 
        header("Location: index.html"); 
        die("Redirecting to index.html"); 
    } 
//This is a workaround, where the page loads too fast before the delete function happen so we need to re-load after the fact.
if ($_GET['r'] == t){
header("Location: listView.php");
die();
}
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
            <a class="btn btn-primary" href="selectBoard.php">Back</a>
        </div>
    </div>
<?php
$stmt = $db->prepare('SELECT * FROM share WHERE `u.WATIAM` = ? AND `b.boardID` = ?');
            $stmt->execute(array($_SESSION['user'],$_GET['board']));       
            $share = $stmt->fetch();
            $edit = false;
            if ($share['permission'] == "edit"){
              $edit = true;
            }
if ($edit){
?>
  <div class="editfeatures">
    <!-- button to create newlist-->

    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#sharing">
    Sharing
    </button>

    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createList">
    Create List
    </button>

    <!-- button to delete list -->
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#deleteList">
    Delete List
    </button>

    <!-- button to edit title of an existing list -->
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#editList">
    Edit List Title
    </button>

    <form action = "taskDetails.php" method = "Post"> 
      <input type="hidden" name="newTaskBoard" value="<?php echo $_GET['board']; ?>">
      <button type="submit" class="btn btn-primary">
      Add new task
      </button>
    </form>
  </div>
<?php
}

//If we have a delete task function posted lets process it.
if (!empty($_POST['deleteID'])){
$stmt = $db->prepare('DELETE FROM task WHERE `taskID`=?');
            $stmt->execute(array($_POST['deleteID']));       
            $task = $stmt->fetch();
}
//If the user updated a permission
if (!empty($_POST['updateWATIAM'])){
if ($_POST['updateTO'] == "remove"){
  $stmt = $db->prepare('DELETE FROM share WHERE `u.WATIAM`=? AND `b.boardID`=?');
              $stmt->execute(array($_POST['updateWATIAM'],$_POST['updateID']));       
              $task = $stmt->fetch();
  }
  else{
      $query = " 
            UPDATE share 
            SET permission = :permission 
            WHERE `u.WATIAM` = :WATIAM AND `b.boardID` = :boardID
        "; 
        $query_params = array( 
            ':permission' => $_POST['updateTO'],
            ':WATIAM' => $_POST['updateWATIAM'],
            ':boardID' => $_POST['updateID']
        ); 
        $stmt = $db->prepare($query); 
        $result = $stmt->execute($query_params); 
  }
}
//If the user submitted a new list request we run this code
if (!empty($_POST['name'])){
	//If the user submitted a new list request we run this code - the query takes the list title(String) and the board ID (integer) as its input
	$query = " 
            INSERT INTO taskList ( 
                listTitle, 
                boardID
            ) VALUES ( 
                :listTitle, 
                :boardID
            ) 
        "; 
        $query_params = array( 
            ':listTitle' => $_POST['name'], //Insert the user's input into the database
            ':boardID' => $_GET['board'] 
        ); 
        $stmt = $db->prepare($query); 
       	$result = $stmt->execute($query_params); 
}

if (!empty($_POST['old_ID']) && !empty($_POST['new_name'])){
	//If the user requests to change the name of existing lists, this code is run - takes the original list's ID, and new name for the list as its input
	$query = " 
            UPDATE taskList 
            SET listTitle = :new_name 
            WHERE listID = :old_ID
        "; 
        $query_params = array( 
            ':new_name' => $_POST['new_name'], // set the new name of the list
            ':old_ID' => $_POST['old_ID'] // parameter for old/existing list title
        ); 
        $stmt = $db->prepare($query); 
       	$result = $stmt->execute($query_params); 
}

if (!empty($_POST['Delete_listID'])){
	//If the user submitted a new delete list request we run this code - takes the list's id as input
	$query = " 
            DELETE FROM taskList    
            WHERE listID = :Delete_listID "; 
        $query_params = array (
          ':Delete_listID' => $_POST['Delete_listID'] //Delete list
        );
        $stmt = $db->prepare($query); 
         $result = $stmt->execute($query_params); 
}

// get complete details for a list
$stmt = $db->prepare('SELECT * FROM taskList WHERE boardID = ? ORDER BY `listID` ASC');
            $stmt->execute(array($_GET['board']));       
            $lists = $stmt->fetchAll();
echo "<div class='row'>";
foreach ($lists as $list){
  //This code is run FOR EACH list that exists.
	echo "
	<div class='col-xs-3'>
	<div class='card'>
	<div class='card-header'>
    <div class ='card-Title' onclick='dynamicModal(".$list['listID'].")' data-toggle='modal' data-target='#filterList'>
      ".$list['listTitle']."  
    </div>
  </div>


	<ul class='list-group list-group-flush'>
	";
  
	$stmt = $db->prepare('SELECT * FROM task WHERE `tl.listID` = ?');
            $stmt->execute(array($list['listID']));       
            $tasks = $stmt->fetchAll();
            foreach ($tasks as $task){
            	//This code is run FOR EACH task in each list. Here we output an html row with some data. It also lets it target a custom modal to pop up each viewTask
              echo "<li class='list-group-item' onclick='dynamicModal(".$task['taskID'].")' data-toggle='modal' data-target='#viewTask'>
              <div class='taskTitle-formatting'>".$task['taskTitle']."</div>"."\n<div class='taskDesc-formatting'>".$task['description']."</div></li>";
            }
    echo " 
    </ul>
    
	</div>
	</div>";
}
echo "</div>";

//Below we have the code for our "modal" which pops up when the user clicks the add new list button. It is a simple form that submits back here for a "page refresh" with the new list.
?>
<div class="modal fade" id="createList" tabindex="-1" aria-labelledby="createList" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Add New List</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="" class="form-newList" method="POST">
            <label for="inputName" class="sr-only">List Name</label>
            <input type="text" name="name" value="" class="form-control" placeholder="List Name" required/>
            <button class="btn btn-lg btn-primary btn-block" type="submit" value="Register">Create</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!--Below we have the code for our "modal" for the user to delete a list. this takes the list id and deletes that particular list -->
<div class="modal fade" id="deleteList" tabindex="-1" aria-labelledby="deleteList" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Delete List</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="" class="edit-ListName" method="POST">
          <!-- Obtain the existing list titles, and display them in a dropdown list, so the user can choose the list they want to delete -->
            <script>
            $stmt = $db->prepare('SELECT * FROM taskList WHERE boardID=?');
            $stmt->execute(array($_GET['board']));       
            $lists = $stmt->fetchAll();
            </script>

            <select name="Delete_listID" class="form-control" required>
            <option value="" disabled selected>Select List</option>
            <?php foreach($lists as $list): ?>
              <option value = "<?= $list['listID']; ?>"><?= $list['listTitle']; ?></option>
            <?php endforeach; ?>
            </select>
            <br>
            <br>
            <button class="btn btn-lg btn-primary btn-block" type="submit" value="Register">Delete List</Title></button>
        </form>
      </div>
    </div>
  </div>
</div>

<!--Below we have the code for our "modal" for the user to modify the title of a list. The modal outputs a form that takes in the list's original title(from dropdown) and new title(from textbox) as input-->
<div class="modal fade" id="editList" tabindex="-1" aria-labelledby="editList" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Edit List Name</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="" class="edit-ListName" method="POST">
          <!-- Obtain the existing list title, and display them in a dropdown list, so the user can choose whichever one they want to change -->
            <?php
            $stmt = $db->prepare('SELECT * FROM taskList WHERE boardID=?');
            $stmt->execute(array($_GET['board']));       
            $lists = $stmt->fetchAll();
            ?>

            <select name="old_ID" class="form-control" required>
            <option value="" disabled selected>Select List</option>
            <?php foreach($lists as $list): ?>
              <option value = "<?= $list['listID']; ?>"><?= $list['listTitle']; ?></option>
            <?php endforeach; ?>
            </select>
            <br>
            <br>
            <label for="inputName" class="sr-only">To</label>
            <input type="text" name="new_name" value="" class="form-control" placeholder="New List Name" required/>
            <br>
            <button class="btn btn-lg btn-primary btn-block" type="submit" value="Register">Update Title</Title></button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal to filter lists - upon clicking this, the user can filter the tasks in their lists based on properties such as task title, importance, type of work, and due date -->
<!-- The tasks(title and description) will be displayed within the modal itself, this can change in later iterations-->
<div class="modal fade" id="filterList" tabindex="-1" aria-labelledby="filterList" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content" style="width:578px;">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Filter View</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" >
        <iframe sandbox="allow-top-navigation allow-scripts allow-forms" class="embed-responsive-item" id="filterListFrame" style="border:0; width:558px; height:700px;" src="listView.php"></iframe>
      </div>
    </div>
  </div>
</div>

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

<!--Below we have the code for our "sharing" module to modify sharing permissions -->
<div class="modal fade" id="sharing" tabindex="-1" aria-labelledby="sharing" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Sharing Settings</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
          <!-- Obtain the shares tied to this board -->
            <?php
            $stmt = $db->prepare('SELECT * FROM share WHERE `b.boardID`=?');
            $stmt->execute(array($_GET['board']));       
            $shares = $stmt->fetchAll();
            ?>
            <table class="table">
              <tbody>
                <tr>
                  <th>User</th>
                  <td>Current Permission</td>
                  <td>Change Permission</td>
                </tr>
            <?php 
            foreach ($shares as $share){
            ?>
            <tr>
              <th scope="row"><?php echo $share['u.WATIAM']; ?></th>
              <td><?php echo $share['permission']; ?></td>
              <td>
                <form action="" method="POST">
                  <input type="hidden" name="updateWATIAM" value="<?php echo $share['u.WATIAM']; ?>">
                  <input type="hidden" name="updateID" value="<?php echo $_GET['board']; ?>">
                  <select name="updateTO">
                    <option value="view">View</option>
                    <option value="edit">Edit</option>
                    <option value="remove">Remove</option>
                  </select>
                  <button type="submit" class="btn btn-info">Update</button>
                </form>
              </td>
            </tr>

            <?php
            }
            ?>
                
              </tbody>
            </table>
      </div>
    </div>
  </div>
</div>



<script>

// function to refresh the modal page for task details and filtering task
function dynamicModal(str)
{
$("#viewTaskFrame").attr("src", "https://mansci-db.uwaterloo.ca/~wmmeyer/viewTask.php?id="+str);
$("#filterListFrame").attr("src", "https://mansci-db.uwaterloo.ca/~wmmeyer/dev_gaurav/filterList.php?id="+str);
}
</script>

<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
<script>$('#viewTask').on('hidden.bs.modal', function () {
 location.reload();
})</script>
</body>
