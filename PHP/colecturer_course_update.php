<?php
	include("_sessionchecker.php");
	include("_config.php");

	// copied and modified from https://www.tutorialspoint.com/php/php_mysql_login.htm
	if($_SERVER["REQUEST_METHOD"] == "POST") {
		// update the course based on given data in the form
		if(isset($_POST['cname']) == true){
		   	// data sent from form
		   	$myname= mysqli_real_escape_string($db,$_POST['cname']);
		   	$mydesc = mysqli_real_escape_string($db,$_POST['desc']);
			$myenrollmenttype = mysqli_real_escape_string($db,$_POST['enrollment_type']);
			$mycpassword = mysqli_real_escape_string($db,$_POST['cpassword']);
			$mygamefeature = mysqli_real_escape_string($db,$_POST['game_feature']);
			$myprizetext = mysqli_real_escape_string($db,$_POST['prize_text']);
		   	$id = mysqli_real_escape_string($db,$_POST['id']);

				$errorMessage = "";

				if(strlen($myname) >= 50){
					 $errorMessage .= "The course name should be shorter or equal to 50 characters. <br />";
				}

				// if no error message
				if($errorMessage == ""){
					$sql = "UPDATE course SET name = '".$myname."', course_password = '".$mycpassword."',
						description = '".$mydesc."', enrollment_mode = '".$myenrollmenttype."'
						WHERE course_id='".$id."'";
					if ($db->query($sql) === TRUE) {
						$sql = "UPDATE game_course SET is_active = '".$mygamefeature."', prize_text = '".$myprizetext."' 
						WHERE course_id='".$id."'";
						if ($db->query($sql) === TRUE) {
							// if updated well, redirect to co-lecturer dashboard
							header('Location: colecturer_courses.php');
							exit;
						}else{
							echo "Error updating record: " . $conn->error;
						}
					} else {
						echo "Error updating record: " . $conn->error;
					}
				}
		 }
	}

	// if the posted values do not exist
	if(isset($_POST['id']) == false){
		header('Location: colecturer_courses.php');
		exit;
	}
?>
<html>
	<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
		<title> E-STRANGE: Update course</title>
    <link rel="icon" href="strange_html_layout_additional_files/icon.png">
	<link href="bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Untuk Icon -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
	
 
  	<script>
    </script>
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
			border-radius: 8px;
		}
	</style>
  </head>
  <body>
		<?php
			if($_SESSION['role'] == 'lecturer')
			  setHeaderLecturer("colecturer courses", "Update co-lecturer course");
			else setHeaderStudent("colecturer courses", "Update co-lecturer course");
		?>
		<div class="container mt-3">
			<div class="row d-flex justify-content-center align-items-center" style="min-height: 60vh">
				<div class="col-md-6">
					<div class="card mx-auto w-100 border-0">
						<!-- copied and modified from https://www.w3schools.com/howto/howto_css_login_form.asp -->
						<?php
							// get the old values
							$sql = "SELECT course.name, course.description, course.enrollment_mode, game_course.is_active, game_course.prize_text, course.course_password FROM course 
							INNER JOIN game_course ON game_course.course_id = course.course_id 
							WHERE course.course_id = '".$_POST['id']."'";
							$result = mysqli_query($db,$sql);
							$row = mysqli_fetch_array($result,MYSQLI_ASSOC);
							echo '
								<form class="w-100" action="'.htmlentities($_SERVER['PHP_SELF']).'" method="post">
									<div class="formbody">';
							// if error message exist, show it
							if(isset($errorMessage) && $errorMessage != ""){
								// show the message
								echo "<div class='warning'>Error(s):<br />".$errorMessage."</div>";
							}
							echo '
										<div class="form-group mt-3">
											<label for="cname"><b>Course name:</b></label>
											<input class="form-control" type="text" placeholder="Enter course name" name="cname" value="'.$row['name'].'" required />
										</div>
										<div class="form-group mt-3">
											<label for="desc"><b>Description:</b></label>
											<textarea class="form-control" class="form-control" placeholder="Enter description" name="desc" >'.$row['description'].'</textarea>
										</div>
										<div class="form-group mt-3">
											<label for="enrollment_type"><b>Enrolment type:</b></label>
											<select class="form-control" id="enrollment_type" name="enrollment_type">';
							// set enrollment type
							if($row['enrollment_mode'] == 0){
								echo '<option value="0" selected>Manual: Lecturer enrols students to the course</option>';
							}else{
								echo '<option value="0">Manual: Lecturer enrols students to the course</option>';
							}
							if($row['enrollment_mode'] == 1){
								echo '<option value="1" selected>Public: students enrol themselves to the course</option>';
							}else{
								echo '<option value="1">Public: students enrol themselves to the course</option>';
							}
							echo '</select>
								</div>';
								
							// set enrolment password 
							echo '<div class="form-group mt-3">
											<label for="cpassword"><b>Course password:</b><br /><i>If enrolment is public</i></label>
											<input class="form-control" type="text" placeholder="Enter course password" name="cpassword" value="'.$row['course_password'].'" />
										</div>';
								
							// set game feature
							echo '<div class="form-group mt-3">
								<label for="game_feature"><b>Game feature:</b></label>
								<select class="form-control" id="game_feature" name="game_feature">';
							if($row['is_active'] == 0){
								echo '<option value="0" selected>Off</option>';
							}else{
								echo '<option value="0">Off</option>';
							}
							if($row['is_active'] == 1){
								echo '<option value="1" selected>On</option>';
							}else{
								echo '<option value="1">On</option>';
							}
							echo '</select>
								</div>';
								
							// game prize text
							echo '<div class="form-group mt-3">
								<label for="prize_text"><b>Game prize text:</b><br /><i>If game feature is on</i></label>
								<textarea class="form-control" placeholder="Enter explanation about game prize" name="prize_text" >'.$row['prize_text'].'</textarea>
							</div>';	
								
							echo '		<input class="form-control" type="hidden" name="id" value="'.$_POST['id'].'">
									</div>
									<div class="mt-3">
										<a href="colecturer_courses.php" class="btn btn-danger">Cancel</a>
										<button class="btn btn-primary" type="submit">Update</button>
									</div>
								</form>
									';
								?>

<script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
