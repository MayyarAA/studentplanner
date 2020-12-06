<?php
//Force the user to login if they aren't
session_start();
if(empty($_SESSION['user'])) 
    { 
        header("Location: index.html"); 
        die("Redirecting to index.html"); 
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
            <!-- <h2 class="display-5"><?php echo $board['boardTitle']; ?> Created <?php echo date("Y-m-d H:i:s", $board['boardDateCreated']);?> by <?php echo $board['u.WATIAM']; ?> </h2> -->
            <h2 class="display-5"><?php echo $board['boardTitle']; ?> Created  by <?php echo $board['u.WATIAM']; ?> </h2>
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

    <!-- button to add a task into an existing list -->
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addTask">
    Add new task
    </button>

    <form action="ArchivedPage.php?board=<?php echo $board['boardID']; ?>" method='Post'>
      <button type="submit" class="btn btn-danger" style="display:inline-block;">
		  View Archived
      </button>
    </form>
  </div>
<?php
}

//User has requested to archive a task
if (!empty($_POST['Archive'])){
  $query = " 
    UPDATE task  SET archived= 1 where taskID = :taskID;
        "; 
        // taskID parameter from user form 
        $query_params = array( 
            ':taskID' => $_POST['ArchiveID'] 
        ); 
         
        try 
        { 
            $stmt = $db->prepare($query); 
            $result = $stmt->execute($query_params);
        } 
        catch(PDOException $ex) 
        { 
            die("Failed to run query: Archive task");
        } 
}

//If we have a update task function posted lets process it.
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

// NOTE: No longer require this delete task function. It is moved to ArchivedPage.php        
//If we have a delete task function posted lets process it.
// if (!empty($_POST['deleteID'])){
// $stmt = $db->prepare('DELETE FROM task WHERE `taskID`=?');
//             $stmt->execute(array($_POST['deleteID']));       
//             $task = $stmt->fetch();
// }

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

if (!empty($_POST['addListID']) && !empty($_POST['addTaskTitle'])){
  // If user submits new task parameters - run this query to add a new task
  
  $query = " 
          INSERT INTO task ( 
              TaskTitle, 
              description,
              dueDate,
              taskDateCreated,
              importance,
              typeOfWork,
              `c.courseID`,
              `tl.listID`,
              archived
          ) VALUES ( 
              :TaskTitle, 
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
          ':TaskTitle' => $_POST['addTaskTitle'], 
          ':description' => $_POST['addTaskDetail'], 
          ':dueDate' => strtotime($_POST['addTaskDueDate']),
          ':taskDateCreated' => time(),
          ':importance' => $_POST['addTaskEffort'],
          ':typeOfWork' => $_POST['addTaskTypeWork'],
          ':courseID' => 111,
          ':listID' => $_POST['addListID'],
          ':archived' => 0
      );    
      $stmt = $db->prepare($query); 
      $result = $stmt->execute($query_params);
      if ($result) {
        header("Location: listView.php?board=".$_GET['board']);
      } 
}


// get complete details for a list
$stmt = $db->prepare('SELECT * FROM taskList WHERE boardID = ? ORDER BY `listID` ASC');
            $stmt->execute(array($_GET['board']));       
            $lists = $stmt->fetchAll();
if ($stmt->rowCount() == 0) {
  //There is no lists, show the user an "empty board" message
?>
<div class="row">
	<div class="jumbotron">
		  <h1 class="display-4">Empty Board</h1>
		  <p class="lead">The board you have selected is empty. Click "Create List" to get started</p>
	</div>
</div>
<?php
} else {
   
echo "<div class='row'>";
foreach ($lists as $list){
  //This code is run FOR EACH list that exists.
  //Each list name can be clicked to access the filtering functionality(UX needs to be improved a little)
	echo "
    <div class='col-xs-3'>
    <div class='card'>
    <div class='card-header'>
      <div class ='card-Title' data-toggle='modal' style='display:inline-block;' data-target='#filterList".$list['listID']."'>
        ".$list['listTitle']."  
      </div>
    <form action = 'listView.php?board=".$_GET['board']."' method = 'POST' style='display:inline-block;''> 
		      <input type='hidden' name='Delete_listID' value=".$list['listID'].">
		      <button type='submit' class='btn btn-danger' style='display:inline-block; visibility:visible;'>
		      Delete
		      </button>
	</form>  
    </div>
    
    <div class='scroll'>
      <ul class='list-group list-group-flush'>
  ";
  // Check if filter/sort feature was selected by user and build a filtered query based on user inputs
  if ($list['listID'] == $_POST['filterListID']) {
    if (!empty($_POST["filter"])) {
      // get user inputs for properties
      $by_name = $_POST['filt_name'];
      $by_effort = $_POST['filt_effort'];
      $by_work = $_POST['filt_work'];
      $by_date = strtotime($_POST['filt_date']);
      $sort_cond = $_POST['sortTask'];
  
      // build initial query
      $query = "SELECT * FROM task WHERE task.`tl.listID` = ".$list['listID']."";
      
      // extend query based on user input filters
      if(! empty($by_name)) {
          $query .= " AND `taskTitle`='".$by_name."' ";
      }
      if(! empty($by_effort)) {
        $query .= " AND `importance`='".$by_effort."' ";
      }
      if(! empty($by_work)) {
        $query .= " AND `typeOfWork`='".$by_work."' ";
      }
      if(! empty($by_date)) {
          $query .= " AND `dueDate`='".$by_date."' ";
      }
          
      //extend query based on user sort parameters
      if (! empty($sort_cond)) {
          if ($sort_cond == 'ascTitle') {
              $query .= " ORDER BY taskTitle ASC";
          }
          if ($sort_cond == 'descTitle') {
              $query .= " ORDER BY taskTitle DESC";
          }
          if ($sort_cond == 'ascEffort') {
              $query .= " ORDER BY importance ASC";
          }
          if ($sort_cond == 'descEffort') {
              $query .= " ORDER BY importance DESC";
          }
          if ($sort_cond == 'ascType') {
              $query .= " ORDER BY typeOfWork ASC";
          }
          if ($sort_cond == 'descType') {
              $query .= " ORDER BY typeOfWork DESC";
          }
          if ($sort_cond == 'ascDate') {
              $query .= " ORDER BY dueDate ASC";
          }
          if ($sort_cond == 'descDate') {
              $query .= " ORDER BY dueDate DESC";
          }
      }
  
      $stmt = $db->prepare($query);
      $stmt->execute(array($list['listID']));       
      $tasks = $stmt->fetchAll();
    } else { 
      // if no filter is selected, output all tasks in the task list
      $stmt = $db->prepare('SELECT * FROM task WHERE `tl.listID` = ? and archived = 0');
      $stmt->execute(array($list['listID']));       
      $tasks = $stmt->fetchAll();
    }
  } else { 
    $stmt = $db->prepare('SELECT * FROM task WHERE `tl.listID` = ? and archived = 0');
    $stmt->execute(array($list['listID']));       
    $tasks = $stmt->fetchAll();
  }

  foreach ($tasks as $task){
    //This code is run FOR EACH task in each list. Here we output an html row with some data. It also lets it target a custom modal to pop up each viewTask
    echo "<li class='list-group-item' data-toggle='modal' data-target='#viewTask".$task['taskID']."'>
    <div class='taskTitle-formatting'>".$task['taskTitle']."</div>"."\n<div class='taskDesc-formatting'>".$task['description']."</div>
		<form action = 'listView.php?board=".$_GET['board']."' method = 'POST' style='display:inline-block;'> 
			      <input type='hidden' name='Archive' value=".$task['taskID'].">
			      <input type='hidden' name='ArchiveID' value=".$task['taskID'].">
			      <button type='submit' class='btn btn-danger' style='display:inline-block;''>
			      Archive
			      </button>
		</form>
    </li>";
  }
  echo " 
  </ul> 
  </div>
	</div>
	</div>";
}
echo "</div>";
}
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

<!-- SQL query to fetch listID and run a forloop to generate modals for each task list on a board -->
<?php
$stmt = $db->prepare('SELECT * FROM taskList WHERE boardID = ? ORDER BY `listID` ASC');
            $stmt->execute(array($_GET['board']));       
            $lists = $stmt->fetchAll();
foreach ($lists as $list) {

?>
  <div class="modal fade" id="filterList<?php echo $list['listID']; ?>" tabindex="-1" aria-labelledby="filterList" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content" style="width:578px;">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Filter View</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" >
          <!-- <iframe sandbox="allow-top-navigation allow-scripts allow-forms" class="embed-responsive-item" id="filterListFrame" style="border:0; width:558px; height:700px;" src="listView.php"></iframe> -->
          <form action="listView.php?board=<?php echo $board['boardID']; ?>" method="POST"> 
              <input type="hidden" name="filterListID" value="<?php echo $list['listID']; ?>"> 
              <h5>Filter task options:</h5>             
              <input type="text" name="filt_name" value="" class="form-control" placeholder="Task Title" maxlength="255"/>
              <small id="" class="form-text text-muted">Enter the exact name of a task</small> <br>              
              <input type="number" name="filt_effort" value="" class="form-control" placeholder="Importance" min = "0" max = "10" step="1"/>
              <small id="" class="form-text text-muted">Choose the importance of a task</small> <br>              
              <input type="text" name="filt_work" value="" class="form-control" placeholder="Type of Work" maxlength="255"/>
              <small id="" class="form-text text-muted">Enter the type of work</small> <br>
              <input type="datetime-local" name="filt_date" value="unchanged" class="form-control" placeholder="Due Date"/>
              <small id="" class="form-text text-muted">Choose the due date of task</small> <br>              
              <h5>Sort task options:</h5>
              <select name="sortTask">
                  <option value="" disabled selected>Sort Task By</option>
                  <optgroup label="Sort by Title">
                      <option value="ascTitle">Ascending</option>
                      <option value="descTitle">Descending</option>
                  </optgroup>
                  <optgroup label="Sort by importance">
                      <option value="ascEffort">Ascending</option>
                      <option value="descEffort">Descending</option>
                  </optgroup>
                  <optgroup label="Sort by work type">
                      <option value="ascType">Ascending</option>
                      <option value="descType">Descending</option>
                  </optgroup>
                  <optgroup label="Sort by due-date">
                      <option value="ascDate">Ascending</option>
                      <option value="descDate">Descending</option>
                  </optgroup>
              </select> 
              <br><br>
              <input type="submit" class="btn btn-warning" name="filter" value="Filter List">
          </form>  
        </div>
      </div>
    </div>
  </div>
  <?php
}
?>
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
            $stmt = $db->prepare('SELECT * FROM share WHERE `b.boardID`=? AND `u.WATIAM` != ?');
            $stmt->execute(array($_GET['board'], $_SESSION['user']));       
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

<!--Below we have the code for our "modal" for the user to add a new task. The modal outputs a form that takes in various user parameters to add a task into a task list-->
<div class="modal fade" id="addTask" tabindex="-1" aria-labelledby="addTask" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Add Task</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="" class="addTask" method="POST">
            <div class="form-group">
                <label for="Task Title">Task Title (Required)</label>
                <input style="height:50px;width:400px;font-size:20pt;" type="text" class="form-control" name="addTaskTitle" required aria-required="true" maxlength="255">
                <small id="taskHelp" class="form-text text-muted">Give your task a descriptive title</small>
            </div>
            <div class="form-group">
                <label for="taskDescription">Task Description</label>
                <textarea style="height:150px;width:400px;font-size:14pt;"class="form-control" id="exampleFormControlTextarea1" rows="3" 
                name="addTaskDetail" required aria-required="true" maxlength="255"></textarea>
                <small id="" class="form-text text-muted">Add a description with information regarding your task</small>
            </div>
            <!-- Retrieve existing list of task lists and display in a dropdown list for a user to select to add a task into-->
            <div>
            <label for="addListID"> Choose Task List (Required)</label>
              <br>
              <select name = "addListID">
                  <option value="" disabled selected>Select Task List</option>
                  <?php
                      require("conn.php"); 
                      $stmt = $db->prepare('SELECT listID,listTitle FROM taskList WHERE boardID=?');
                          $stmt->execute(array($_GET['board']));       
                          $taskLists = $stmt->fetchAll();
                          foreach ($taskLists as $taskList){
                              echo "<option value=" . $taskList['listID'] . ">" . $taskList['listTitle'] . "</option>"; 
                          }
                  ?> 
              </select>
            </div>
            <div>
              <label for="addTaskEffort"> Effort</label>
              <input style="height:50px;width:80px;font-size:14pt;"type="number" class="form-control" name="addTaskEffort" min = "0" max = "10" step="1">
              <small id="" class="form-text text-muted">Amount of work required</small>
            </div>
            <div>
              <label for="addTaskTypeWork"> Type of Work</label>
              <input style="height:50px;width:160px;font-size:14pt;"type="text" class="form-control" name="addTaskTypeWork" maxlength="255">
              <small id="" class="form-text text-muted">Detail of work type</small>
            </div>
            <div>
                <label for="addTaskDateDue"> Due Date</label>
                <input class="form-control" type="datetime-local" value="2021-08-19T13:45:00" name="addTaskDueDate" id="addTaskDueDate">
                <small id="" class="form-text text-muted">Due date of task</small>
            </div>
            <br>
            <br>
            <button class="btn btn-lg btn-primary btn-block" type="submit" name="addTaskSubmit" value="">Add task</Title></button>
        </form>
      </div>
    </div>
  </div>
</div>


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
          //This document is the modal popup for viewtask, we needed to have the individual page which uses GET to know which task it needs to retrieve.
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
            <input type="hidden" id="updateID" name="updateID" value="<?php echo $task['taskID']; ?>">
            <?php if ($edit) { ?><input type="submit" class="btn btn-warning" name="update" value="Update Task Details" style="position:absolute; right:10px;"></input><?php } ?>
          </form>

        <form action="listView.php?board=<?php echo $board['boardID']; ?>" class="form-newList" method="POST">
         <input type="hidden" id="ArchiveID" name="ArchiveID" value="<?php echo $task['taskID']; ?>">
        <?php if ($edit) { ?><input type="submit" class="btn btn-danger" name="Archive" value="Archive Task"></input><?php } ?>
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

