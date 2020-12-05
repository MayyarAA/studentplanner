<?php
session_start();

// if(empty($_SESSION['user'])) 
//     { 
//         header("Location: index.html"); 
//         die("Redirecting to index.html"); 
//     } 

require('conn.php');
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">

<?php

// echo $_GET['id'];
if(!empty($_POST['share'])) {

    // echo $_POST['course'];
    
    $stmt = $db->prepare('SELECT DISTINCT(`u.WATIAM`) from board WHERE boardID IN (SELECT boardID from `taskList` WHERE `listID` IN (SELECT `tl.listID` FROM `task` WHERE `c.courseID` = ?))');     
    $stmt->execute(array($_POST['course']));  
    $users = $stmt->fetchAll();
    ?>

    <form action="selectBoard.php" class="edit-ListName" method="POST">
    <?php
    echo "<select name='user' class='form-control' required>";
    echo "<option value='' disabled selected>Select User</option>";
    foreach($users as $user):
        if($user['u.WATIAM']!==$_SESSION['user'])
        {
            // echo "<br>";
            // echo "working";
            echo "<option value='".$user['u.WATIAM']."'>".$user['u.WATIAM']."</option>";
            // echo "<br>";
        } // include edge case for no one sharing a course
    endforeach;
    echo "</select>";
    ?>
    <br>
    <select name="permission" class="form-control" required>
            <option value="" disabled selected>Permission</option>
            <option value="edit">Edit</option>
            <option value="view">View</option>
    </select>
    <br>
    <input type="hidden" name="boardIDshare" id="boardIDshare" value="<?php echo $_GET['id']; ?>">
    <button class="btn btn-lg btn-primary btn-block" type="submit" name="shareSubmit" value="shareSubmit">Share</Title></button>
    </form>

    <br>
    <br>
<?php } ?>
    
<form action="shareBoardCourse.php?id=<?php echo $_GET['id']; ?>" method="POST" class="edit-ListName" >
        <?php
            $stmt = $db->prepare('SELECT courseID, courseTitle FROM `course` WHERE courseID IN (SELECT DISTINCT(task.`c.courseID`) FROM `task` WHERE task.`tl.listID` IN (SELECT l.listID FROM `board` b NATURAL JOIN `taskList` l WHERE b.boardID = ? ))');     
            $stmt->execute(array($_GET['id']));  
            $courses = $stmt->fetchAll();
        	?>
            <select name="course" class="form-control" required>
            <option value="" disabled selected>Select Course</option>
            <?php foreach($courses as $course): ?>
              <option value = "<?php echo $course['courseID']; ?>"> <?php echo $course['courseTitle']; ?> </option>
            <?php endforeach; ?>
            </select>
            <br>
            <input type="submit" class="btn btn-warning" name="share" value="Find Students">   
</form>