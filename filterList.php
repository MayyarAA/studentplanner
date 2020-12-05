<?php
session_start();
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">

<!-- This is the code for the page to filter and sort tasks within a list -->

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
    $sort_cond = $_POST['sortTask'];

    // build initial query
    $query = "SELECT * FROM task WHERE task.`tl.listID` = ".$id."";
     
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
    $stmt->execute();  
        
    if($stmt->rowCount() > 0) {
        $filtered_tasks = $stmt->fetchAll();
        foreach($filtered_tasks as $task){
            echo "<br>Title:".$task['taskTitle']."<br>";
            echo "Description: ".$task['description']."<br>";
            echo "Importance: ".$task['importance']."<br>";
            echo "Type of Work: ".$task['typeOfWork']."<br>";
            echo "Due Date: ".gmdate("Y-m-d", $task['dueDate'])."<br>";
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
    <input type="text" name="filt_name" value="" class="form-control" placeholder="Task Title" maxlength="255"/>
    <br>
    <input type="number" name="filt_effort" value="" class="form-control" placeholder="Importance" min = "0" max = "10" step="1"/>
    <br>
    <input type="text" name="filt_work" value="" class="form-control" placeholder="Type of Work" maxlength="255"/>
    <br>
    <input type="datetime-local" name="filt_date" value="unchanged" class="form-control" placeholder="Due Date"/>
    <br>
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