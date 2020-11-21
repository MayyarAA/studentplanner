<?php
//Force user to login if they aren't
session_start();
if(empty($_SESSION['user'])) 
    { 
        header("Location: index.html"); 
        die("Redirecting to index.html"); 
    } 
require("conn.php");
//If we have a deleteID request we must delete the board by ID
if (!empty($_POST['deleteID'])){
$stmt = $db->prepare('DELETE FROM board WHERE boardID=?');
            $stmt->execute(array($_POST['deleteID']));       
            $deleteboard = $stmt->fetch();

}
if (!empty($_POST['boardIDshare'])){
$stmt = $db->prepare('DELETE FROM board WHERE boardID=?');
            $stmt->execute(array($_POST['deleteID']));       
            $deleteboard = $stmt->fetch();
	$query = " 
            INSERT INTO share ( 
                `b.boardID`, 
                `u.WATIAM`,
                `permission`
            ) VALUES ( 
                :boardID, 
                :WATIAM,
                :permission
            ) 
        "; 
        $query_params = array( 
            ':boardID' => $_POST['boardIDshare'],
            ':WATIAM' => $_POST['user'],
            ':permission' => $_POST['permission']
        ); 
        $stmt = $db->prepare($query); 
       	$result = $stmt->execute($query_params); 
}
if (!empty($_POST['boardName'])){
$query = " 
            INSERT INTO board ( 
                `boardTitle`,
                `u.WATIAM`,
                `boardDateCreated`
            ) VALUES ( 
                :boardTitle,
                :WATIAM,
                :boardDateCreated
            ) 
        "; 
        $query_params = array( 
            ':boardTitle' => $_POST['boardName'],
            ':WATIAM' => $_SESSION['user'],
            ':boardDateCreated' => time()
        ); 
        $stmt = $db->prepare($query); 
       	$result = $stmt->execute($query_params);
       	$id = $db->lastInsertId();

$query = " 
            INSERT INTO share ( 
                `b.boardID`,
                `u.WATIAM`,
                `permission`
            ) VALUES ( 
                :boardID,
                :WATIAM,
                :permission
            ) 
        "; 
        $query_params = array( 
            ':boardID' => $id,
            ':WATIAM' => $_SESSION['user'],
            ':permission' => "edit"
        ); 
        $stmt = $db->prepare($query); 
       	$result = $stmt->execute($query_params);   	

}
?>
<!DOCTYPE html>
<html lang="">

<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
    <link href="CSS/jumbotron" rel="stylesheet">
    <link href="CSS/BoardView-Body.css" rel="stylesheet">
    <title>Select Board</title>

</head>
<div class="jumbotron">
        <div class="container">
            <h2 class="display-5">Welcome <?php echo $_SESSION['user']; ?></h2>
            <a href="logout.php" class="btn btn-warning">Logout</a>
        </div>
</div>


    <!-- button to create board -->
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createBoard">
    Create Board
    </button>


    

<div class="row">
<?php
$stmt = $db->prepare('SELECT * FROM share WHERE `u.WATIAM` = ? ORDER BY `b.boardID` DESC');
            $stmt->execute(array($_SESSION['user']));       
            $shares = $stmt->fetchAll();
foreach ($shares as $share){
	$stmt = $db->prepare('SELECT * FROM board WHERE `boardID` = ? ORDER BY `boardID` DESC');
            $stmt->execute(array($share['b.boardID']));       
            $board = $stmt->fetch();
?>
<div class='col-xs-2'>
	<div class="card">
	  <div class="card-body">
	    <h5 class="card-title">Board: <?php echo $board['boardTitle']; ?></h5>
	    <h6 class="card-subtitle mb-2 text-muted"><?php echo $share['permission']; ?> access</h6>
	    <p class="card-text">Created <?php echo date("Y-m-d H:i:s", $board['boardDateCreated']); ?> by <?php echo $board['u.WATIAM']; ?></p>
		<div>   
		    <a href="listView.php?board=<?php echo $board['boardID']; ?>" class="btn btn-primary" style="display:inline-block;">View Board</a>
        
        <?php if ($board['boardTitle']=="Archived") {
		   	?>
		   	<form action = "selectBoard.php" method = "POST" style="display:inline-block;"> 
		      <input type="hidden" name="deleteID" value="<?php echo $board['boardID']; ?>">
		    </form>
		    
		   	<?php
		    }
		    ?>
        
        <?php if ($share['permission'] == "edit" && $board['boardTitle']!="Archived") {
		   	?>
		   	<button type="button" class="btn btn-info" data-toggle="modal" data-target="#shareBoard">
		    Share
		    </button>
		   	<form action = "selectBoard.php" method = "POST" style="display:inline-block;"> 
		      <input type="hidden" name="deleteID" value="<?php echo $board['boardID']; ?>">
		      <button type="submit" class="btn btn-danger" style="display:inline-block;">
		      Delete Board
		      </button>
		    </form>
		    
		   	<?php
		    }
		    ?>
		    
		</div> 
	  </div>
	</div>
</div>
<div class="modal fade" id="shareBoard" tabindex="-1" aria-labelledby="shareBoard" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Share Board</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="" class="edit-ListName" method="POST">
        	<?php
        	$stmt = $db->prepare('SELECT * FROM user ORDER BY `WATIAM` ASC');
            $stmt->execute();       
            $users = $stmt->fetchAll();
        	?>
            <select name="user" class="form-control" required>
            <option value="" disabled selected>Select User</option>
            <?php foreach($users as $user): ?>
              <option value = "<?php echo $user['WATIAM']; ?>"><?php echo $user['WATIAM']; ?></option>
            <?php endforeach; ?>
            </select>
            <br>
            <select name="permission" class="form-control" required>
            <option value="edit">Edit</option>
            <option value="view">View</option>
            </select>
            <br>
            <br>
            <input type="hidden" name="boardIDshare" id="boardIDshare" value="<?php echo $board['boardID']; ?>">
            <button class="btn btn-lg btn-primary btn-block" type="submit" name="shareSubmit" value="shareSubmit">Share</Title></button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php           
}
?>
</div>

<div class="modal fade" id="createBoard" tabindex="-1" aria-labelledby="createBoard" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Add New Board</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="" class="form-newList" method="POST">
            <label for="inputName" class="sr-only">Board Name</label>
            <input type="text" name="boardName" value="" class="form-control" placeholder="Board Name" required/>
            <button class="btn btn-lg btn-primary btn-block" type="submit" value="createBoard">Create</button>
        </form>
      </div>
    </div>
  </div>
</div>



<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
