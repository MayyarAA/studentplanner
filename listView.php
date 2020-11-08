<?php
//This is a workaround, where the page loads too fast before the delete function happen so we need to re-load after the fact.
if ($_GET['r'] == t){
header("Location: listView.php");
die();
}
?>
<!DOCTYPE html>
<html lang="">


<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
    <title>Student Planner</title>
</head>

    <body>
        <h1 class="text-center">Student Planner</h1>
	<br>
        <h2 class="text-center">List View</h2>
	<br>

  <!-- button to create newlist-->
  <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createList">
  Create List
  </button>
  <!-- button to edit title of an existing list -->
  <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#editList">
  Edit List Title
  </button>

<?php
require("conn.php"); 
//If we have a delete function posted lets process it.
if (!empty($_POST['deleteID'])){
$stmt = $db->prepare('DELETE FROM task WHERE `taskID`=?');
            $stmt->execute(array($_POST['deleteID']));       
            $task = $stmt->fetch();
}
if (!empty($_POST['name'])){
	//If the user submitted a new list request we run this code
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
            ':boardID' => 1 //Placeholder until we develop the board view function
        ); 
        $stmt = $db->prepare($query); 
       	$result = $stmt->execute($query_params); 
}

if (!empty($_POST['old_name']) && !empty($_POST['new_name'])){
	//If the user requests to change the name of existing lists, this code is run
	$query = " 
            UPDATE taskList 
            SET listTitle = :new_name 
            WHERE listTitle = :old_name
        "; 
        $query_params = array( 
            ':new_name' => $_POST['new_name'], // set the new name of the list
            ':old_name' => $_POST['old_name'] // query for old name
        ); 
        $stmt = $db->prepare($query); 
       	$result = $stmt->execute($query_params); 
}

// get complete details for a list
$stmt = $db->prepare('SELECT * FROM taskList ORDER BY `listID` ASC');
            $stmt->execute();       
            $lists = $stmt->fetchAll();
echo "<div class='row'>";
foreach ($lists as $list){
	//This code is run FOR EACH list that exists.
	echo "
	<div class='col-xs-3'>
	<div class='card'>
	<div class='card-header'>".$list['listTitle']."</div>
	<ul class='list-group list-group-flush'>
	";
  
    
	$stmt = $db->prepare('SELECT * FROM task WHERE `tl.listID` = ?');
            $stmt->execute(array($list['listID']));       
            $tasks = $stmt->fetchAll();
            foreach ($tasks as $task){
            	//This code is run FOR EACH task in each list. Here we output an html row with some data. It also lets it target a custom modal to pop up each viewTask
            	echo "<li class='list-group-item' onclick='dynamicModal(".$task['taskID'].")' data-toggle='modal' data-target='#viewTask'>".$task['taskTitle'].": ".$task['description']."</li>";
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

<!--Below we have the code for our "modal" for the user to modify the title of a list. The modal outputs a form that takes in the list's original title and new title as input, both in the form of a textbox-->
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

            <label for="inputName" class="sr-only">From</label>
            <input type="text" name="old_name" value="" class="form-control" placeholder="Original List Name" required/>
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


<!--Below we have the code for our "modal" which pops up when the user clicks a task. The modal outputs all the details of the task-->
<div class="modal fade" id="viewTask" tabindex="-1" aria-labelledby="viewTask" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Task view</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <iframe sandbox="allow-top-navigation allow-scripts allow-forms" class="embed-responsive-item" id="viewTaskFrame" style="border:0; width:458px; height:500px;" src="sample.com"></iframe>
      </div>
    </div>
  </div>
</div>
<script>

// function to refresh the modal page for task details
function dynamicModal(str)
{
$("#viewTaskFrame").attr("src", "https://mansci-db.uwaterloo.ca/~wmmeyer/wmmeyer/viewTask.php?id="+str);
}
</script>
<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
</body>
