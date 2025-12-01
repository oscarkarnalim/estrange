<?php
	include("_sessionchecker.php");
	include("_config.php");

	// copied and modified from https://www.tutorialspoint.com/php/php_mysql_login.htm
	if($_SERVER["REQUEST_METHOD"] == "POST") {
		// process the updated data
		if(isset($_POST['cname']) == true){
		   // data sent from form
			 $myname= mysqli_real_escape_string($db,$_POST['cname']);
		     $mydesc = mysqli_real_escape_string($db,$_POST['desc']);
			 $myopentime = new DateTime(mysqli_real_escape_string($db,$_POST['open_time']));
			 $myclosetime = new DateTime(mysqli_real_escape_string($db,$_POST['close_time']));
			 $myoldclosetime = new DateTime(mysqli_real_escape_string($db,$_POST['old_close_time']));
			 $myacceptedfileext = mysqli_real_escape_string($db,$_POST['file_ext']);
			 $myallowlatesubmission = mysqli_real_escape_string($db,$_POST['late_submission']);
			 $mycourseid = $_SESSION['course_id'];
			 $assessment_id = mysqli_real_escape_string($db,$_POST['id']);


		   // to store the error message
		   $errorMessage = "";

			 // check whether closing time is later than opening time
			 if($myclosetime < $myopentime){
				 $errorMessage .= "The closing submission time should be later than the opening one. <br />";
			 }

			 if(strlen($myname) >= 50){
					$errorMessage .= "The assessment name should be shorter or equal to 50 characters. <br />";
			 }

		   // if no error message
		   if($errorMessage == ""){
		       // update the entry
		       $sql = "UPDATE assessment SET name = '".$myname."', description = '".$mydesc."',
					 	submission_open_time = '".$myopentime->format('Y-m-d\TH:i')."',
						submission_close_time = '".$myclosetime->format('Y-m-d\TH:i')."',
						course_id = '".$mycourseid."', submission_file_extension = '".$myacceptedfileext."', allow_late_submission = '".$myallowlatesubmission."'";
				
			   // if the new close time is later than the old one, reset similarity report path			
			   if($myclosetime > $myoldclosetime){
				  $sql .= " ,similarity_report_path = '' ";
			   }
						
			  $sql .= "WHERE assessment_id='".$assessment_id."'";
		      if ($db->query($sql) === TRUE) {
				  // if the datetime is later than it should be, 
				  
				  
		        // if updated well, redirect to dashboard
		        header('Location: admin_assessments.php');
				exit;
		      } else {
		        echo "Error updating record: " . $db->error;
				exit;
		      }
		   }
		 }
	}

	// if the post values do not exist
	if(isset($_POST['id']) == false){
		header('Location: admin_dashboard.php');
		exit;
	}
?>
<html>
	<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
		<title> E-STRANGE: Update assessment</title>
    <link rel="icon" href="strange_html_layout_additional_files/icon.png">
    <link href="strange_html_layout_additional_files/quill/quill.snow.css" rel="stylesheet">
	<!-- Untuk Icon -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
	
	<script src="strange_html_layout_additional_files/quill/quill.min.js"></script>
    <script>
		var descricheditor;
		function initialise(){
		  descricheditor = new Quill('#desc_rich_editor', {
			theme: 'snow',
			placeholder: 'Enter description'
		  });
		}
		function prepareform(){
			var dst = document.getElementById("desc");
			dst.value  = descricheditor.root.innerHTML;
		}
    </script>
  <link href="bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">



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
  <body onload="initialise();" >
		<?php
		  setHeaderAdmin("courses", "Update assessments");
		?>
		<div class="container mt-3">
			<div class="row d-flex justify-content-center align-items-center" style="min-height: 60vh">
				<div class="col-md-6">
					<div class="card mx-auto w-100 border-0">

		<!-- copied and modified from https://www.w3schools.com/howto/howto_css_login_form.asp -->
		<?php
			// get the old values
			$sql = "SELECT name, description, submission_open_time, submission_close_time, submission_file_extension, allow_late_submission
							FROM assessment WHERE assessment_id = '".$_POST['id']."'";
			$result = mysqli_query($db,$sql);
			$row = mysqli_fetch_array($result,MYSQLI_ASSOC);
			
			// get old close time from database
			$myOldCloseTime = (new DateTime($row['submission_close_time']))->format('Y-m-d\TH:i');

			echo '
				<form class="w-100" onsubmit="prepareform();" action="'.htmlentities($_SERVER['PHP_SELF']).'" method="post">
					<div class="formbody">
					';
			// if error message exist, show it
			if(isset($errorMessage) == true && $errorMessage != ""){
				// show the message
				echo "<div class='warning'>Error(s):<br />".$errorMessage."</div>";
				// set the values to the changed ones
				$row['name'] = $myname;
				$row['description'] = $mydesc;
				$row['submission_open_time'] = $myopentime->format('Y-m-d\TH:i');
				$row['submission_close_time'] = $myclosetime->format('Y-m-d\TH:i');
				$row['submission_file_extension'] = $myacceptedfileext;
				$row['allow_late_submission'] = $myallowlatesubmission;
			}

			echo '
					<div class="form-group mt-3">
						<label for="cname"><b>Assessment name:</b></label>
						<input class="form-control" type="text" placeholder="Enter assessment name" name="cname" required value="'.$row['name'].'" />
					</div>
					<div class="form-group mt-3">
						<label for="desc"><b>Description:</b></label>
						<div id="desc_rich_editor" contenteditable="true">'.$row['description'].'</div>
						<input class="form-control" type="hidden" id="desc" name="desc" />
					</div>
					<div class="form-group mt-3">
						<label for="open_time"><b>Submission opening time:</b></label>
						<input class="form-control" type="datetime-local" id="open_time" name="open_time" value="'.(new DateTime($row['submission_open_time']))->format('Y-m-d\TH:i').'" required>
					</div>
					<div class="form-group mt-3">
						<label for="close_time"><b>Submission closing time:</b></label>
						<input class="form-control" type="datetime-local" id="close_time" name="close_time" value= "'.(new DateTime($row['submission_close_time']))->format('Y-m-d\TH:i').'" required>
					</div>
					<div class="form-group mt-3">
						<label for="file_ext"><b>Submission file type:</b></label>
						<select class="form-control" id="file_ext" name="file_ext">';
							// for the options
							if($row['submission_file_extension'] == 'java'){
								echo '<option value="java" selected>Java file</option>';
							}else{
								echo '<option value="java">Java file</option>';
							}

							if($row['submission_file_extension'] == 'py'){
								echo '<option value="py" selected>Python file</option>';
							}else{
								echo '<option value="py">Python file</option>';
							}

							if($row['submission_file_extension'] == 'zip_java'){
								echo '<option value="zip_java" selected>Zip file (Java)</option>';
							}else{
								echo '<option value="zip_java">Zip file (Java)</option>';
							}

							if($row['submission_file_extension'] == 'zip_py'){
								echo '<option value="zip_py" selected>Zip file (Python)</option>';
							}else{
								echo '<option value="zip_py">Zip file (Python)</option>';
							}
			echo '		</select>
					</div>
					<div class="form-group mt-3">
						<label for="late_submission"><b>Late submission:</b></label>
						<select class="form-control" id="late_submission" name="late_submission">';
							// for the options
							if($row['allow_late_submission'] == 1){
								echo '<option value="1" selected>Allow</option>';
							}else{
								echo '<option value="1">Allow</option>';
							}

							if($row['allow_late_submission'] == 0){
								echo '<option value="0" selected>Disallow</option>';
							}else{
								echo '<option value="0">Disallow</option>';
							}
			echo '		</select>
					</div>
					<input type="hidden" name="id" value="'.$_POST['id'].'">
					<input type="hidden" name="old_close_time" value="'.$myOldCloseTime.'">
				</div>
				<div class="mt-3">
					<a href="admin_assessments.php" class="btn btn-danger">Cancel</a>
					<button class="btn btn-primary" type="submit">Update</button>
				</div>
			</form>
					';
				?>
				</div>
				</div>
			</div>
		</div>
		<script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
