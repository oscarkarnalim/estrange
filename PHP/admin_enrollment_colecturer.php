<?php
include("_sessionchecker.php");
include("_config.php");

// copied and modified from https://www.tutorialspoint.com/php/php_mysql_login.htm
if($_SERVER["REQUEST_METHOD"] == "POST") {
	// start to enroll the students based on the text given in the form
	if(isset($_POST['students']) == true && $_POST['students'] != ""){
		 // data sent from form
		 $mycolecturers= mysqli_real_escape_string($db,$_POST['students']);
		 $mycourseid = mysqli_real_escape_string($db,$_POST['course']);

		 // to store the student IDs
		 $colecturerIDs = array();

		 // to store the error message
		 $errorMessage = "";

		 // split based on newline
		 $arr = explode ("\\n",$mycolecturers);
		 $arrLength = count($arr);
		 for($i=0;$i<$arrLength;$i++){
			 // get the username without escape characters
			 $arr[$i] = str_replace("\\r", "", $arr[$i]);

			 // if empty, skip the line but show no error message
			 if($arr[$i] == ""){
				 continue;
			 }

			 // checking the validity of username
		     $sql = "SELECT user_id, role FROM user
			 				 WHERE username = '".$arr[$i]."'";
		     $result = mysqli_query($db,$sql);
		     $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
		     $count = mysqli_num_rows($result);
		     if($count == 0) {
		        // if the username does not exist
		        $errorMessage .= "Line ".($i+1).": The username does not exist. <br />";
		     }else if($row['role'] == 'admin'){
				// if not student
				$errorMessage .= "Line ".($i+1).": The username is not registered as a student or a lecturer. <br />";
			 } else{
				 // check whether the account is associated with students enrolled to the course
				 $sql = "SELECT student_id FROM enrollment
			 				 WHERE student_id = '".$row['user_id']."' 
							 AND course_id = '".$mycourseid."' ";
				 $result = mysqli_query($db,$sql);
				 $count = mysqli_num_rows($result);
				 if($count > 0){
					 // has been assigned to student
					 $errorMessage .= "Line ".($i+1).": The username has been registered as a student for this course. <br />";
				 }else{
					 // check whether it is the creator of the course
					 $sql = "SELECT creator_id FROM course
			 				 WHERE creator_id = '".$row['user_id']."'
							 AND course_id = '".$mycourseid."' ";
					 $result = mysqli_query($db,$sql);
					 $count = mysqli_num_rows($result);
					 if($count > 0){
						 // the creator of the course
						 $errorMessage .= "Line ".($i+1).": The username has been registered as the creator of this course. <br />";
					 }else{
						// get the id to an array
						$colecturerIDs[] = $row['user_id'];
					 }
				 }
			 }
		 }

		 // if no error message
		 if($errorMessage == ""){
			  $IDLength = count($colecturerIDs);
				// for each student id given in the text
				for($i=0;$i<$IDLength;$i++){
				   // check whether the account has been enrolled for given course as a co-lecturer
				   $sql = "SELECT user_id FROM colecturer
						 WHERE user_id = '".$colecturerIDs[$i] ."' AND course_id = '".$mycourseid."'";
		 		   $result = mysqli_query($db,$sql);
		 		   $count = mysqli_num_rows($result);
					 // if it has been enrolled, skip the process
					if($count > 0){
						$errorMessage .= "Line ".($i+1).": The username has been registered as a co-lecturer for this course. <br />";
					 	continue;
					}

					// add the entry
					$sql = "INSERT INTO colecturer (user_id, course_id)
							VALUES ('".$colecturerIDs[$i]."', '".$mycourseid."')";
					

					// if error, print the message and exit
					if ($db->query($sql) != TRUE) {							
						echo "Error adding record: " . $db->error;
						exit;
					}
				}
				
				if($errorMessage == ""){
					// if updated well, redirect to dashboard of colecturers
					header('Location: admin_dashboard.php');
					exit;
				}
		 }
	 }
}
?>
<html>
	<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
		<title> E-STRANGE: Enroll co-lecturer(s)</title>
		<link rel="icon" href="strange_html_layout_additional_files/icon.png">
		<link href="bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">


	  <!-- Untuk Icon -->
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
	
		<style>
		@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300&display=swap');
		body {
		/* font-family: "Times New Roman", Times, serif; */
		font-family: 'Montserrat', sans-serif;
		}
		.btn-primary{
			background: #a8c6e7 !important ;
			color: black  !important ;
		}
		.btn-danger{
			background: #f56976 !important ;
		}
		.form-control {
		border: 2px solid #000;	
	}
		</style>
  </head>
  <body>
		<?php
		  setHeaderAdmin("colecturer enrollment", "Co-lecturer enrollment");
		?>

		<div class="container mt-4">
			<div class="row d-flex justify-content-center align-items-center" style="min-height:50vh">
				<div class="col-md-6">			
					<form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post">
						<div class="formbody">
							<?php
								// if error message exist, show it
								if(isset($errorMessage) && $errorMessage != ""){
									// show the message
									echo "<div class='alert alert-warning'>Error(s):<br />".$errorMessage."</div>";
								}
							?>
							<div class="form-group mt-3">
								<label for="course"><b>Course:</b></label>
							<?php
								// get all courses
								$sql = "SELECT course.course_id, course.name, 
									user.username FROM course 
									INNER JOIN user ON user.user_id = course.creator_id";
								$result = mysqli_query($db,$sql);
								if ($result->num_rows == 0) {
									// if no courses
									$isValidCourse = false;
									echo "<div class='form-control courseselect'>No courses</div>";
								}
								else{
									$isValidCourse = true;
									echo '<select name="course" id="course" class="form-control courseselect">';
									while($row = $result->fetch_assoc()) {
										// echo all of the options
										echo "<option value=\"".$row['course_id']."\" >";
										echo $row['name']." (".$row['username'].") </option>";
									}
									echo "</select>";
								}
							?>	
							</div>
							<div class="form-group mt-3">
								<label for="students"><b>Co-lecturer usernames:</b></label>
								<textarea rows=10 placeholder="Enter co-lecturer usernames separated by line"
								class="form-control" name="students" required><?php if(isset($mycolecturers) == true){echo str_replace("\\r", "\r", str_replace("\\n", "\n", $mycolecturers)); } ?></textarea>
							</div>
						</div>
						<div class="form-group mt-3">
							<?php 
								if($isValidCourse){
									echo '
									<button class="btn btn-primary w-100" type="submit">Enroll</button>';
								}
							?>
						</div>
					</form>
					<script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
