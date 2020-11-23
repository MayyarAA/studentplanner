<?php
session_start();
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">

<!-- This is the code for the page to filter tasks within a list -->

<?php
require('conn.php');
$stmt = $db->prepare('SELECT listTitle FROM taskList WHERE `listID`= ? ');
$stmt->execute(array($_GET['id']));       
$list = $stmt->fetch();
// display list name so user can confirm the list they're editing
echo "Apply filters for ".$list['listTitle']." list <br>";

if(!empty($_POST['filter'])) {

    // get user inputs for properties
    $by_name = $_POST['filt_name'];
    $by_effort = $_POST['filt_effort'];
    $by_work = $_POST['filt_work'];
    $by_date = strtotime($_POST['filt_date']);
    $id = $_GET['id'];

    // build initial query
    $query = "SELECT * FROM task WHERE `tl.listID` = ".$id."";

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

    $stmt = $db->prepare($query);
    $stmt->execute();  
        
    if($stmt->rowCount() > 0) {
        $filtered_tasks = $stmt->fetchAll();
        foreach($filtered_tasks as $task){
            echo "<br>Title:".$task['taskTitle']."<br>";
            echo "Description: ".$task['description']."<br>";
    
        }
    } //dealing with no result outputs(edge cases tested)
    else {
    echo "<br> No results found - please try again. <br>";
    }

}
?>

<!-- Form to take in user inputs -->
<form action="filterList.php?id=<?php echo $_GET['id']; ?>" method="POST"> 

    <br>
    <input type="text" name="filt_name" value="" class="form-control" placeholder="Task Title"/>
    <br>
    <input type="number" name="filt_effort" value="" class="form-control" placeholder="Importance"/>
    <br>
    <input type="text" name="filt_work" value="" class="form-control" placeholder="Type of Work"/>
    <br>
    <input type="datetime-local" name="filt_date" value="unchanged" class="form-control" placeholder="Due Date"/>
    <br>
    <input type="submit" class="btn btn-warning" name="filter" value="Filter List">

</form>