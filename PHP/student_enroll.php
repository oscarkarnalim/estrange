<?php
	include("_sessionchecker.php");
	include("_config.php");

	// copied and modified from https://www.tutorialspoint.com/php/php_mysql_login.htm
	if($_SERVER["REQUEST_METHOD"] == "POST"){
		if(isset($_POST['enrollid']) == true){
			$courseID = mysqli_real_escape_string($db,$_POST['enrollid']);
			// get the course name and description
			$sql = "SELECT name, description FROM course
			WHERE course_id = '".$courseID."'";
			$result = mysqli_query($db,$sql);
			$row = $result->fetch_assoc();
			$myname = $row['name'];
			$mydesc = $row['description'];
		}else{
			// add new course from the added data given in the form
			if(isset($_POST['cname']) == true){
				// data sent from form
				$myname= mysqli_real_escape_string($db,$_POST['cname']);
				$mydesc = $_POST['desc'];
				$courseID = mysqli_real_escape_string($db,$_POST['course_id']);
				$mycpassword = mysqli_real_escape_string($db,$_POST['cpassword']);
				$errorMessage = "";
				
				// check if the password matches
				$sql = "SELECT course_password FROM course
				WHERE course_id = '".$courseID."' AND course_password = '".$mycpassword."'";
				$result = mysqli_query($db,$sql);
				$count = mysqli_num_rows($result);
				if($count == 0) { 
					$errorMessage .= "The course password is incorrect!<br/>";
				}

				// if no error message
				if($errorMessage == ""){
					 
					// enrol
					$id = mysqli_real_escape_string($db,$_POST['id']);
					$sql = "INSERT INTO enrollment (course_id, student_id) VALUES ('".$courseID."','".$_SESSION['user_id']."')";
					if ($db->query($sql) === TRUE) {
						// if added well
						// add an entry for game_student_course
						$sql = "INSERT INTO game_student_course (student_id, course_id)
					 			VALUES ('".$_SESSION['user_id']."', '".$courseID."')";

						// if works well, redirect to enrollment
						if ($db->query($sql) == TRUE){
							header('Location: student_enrollment.php');
							exit;
						}else{
							echo "<script>alert('The course cannot be enrolled!');</script>";
						}
					} else {
						echo "<script>alert('The course cannot be enrolled!');</script>";
					}
				}
			 }
		}
	}
?>
<html>
	<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">

		<title> E-STRANGE: Add course</title>
		<!-- Untuk Icon -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
	<link href="bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">


    <link rel="icon" href="strange_html_layout_additional_files/icon.png">
    <script>
    </script>
    <style>
			body{
				font-family: "Times New Roman", Times, serif;
				font-size: 12px;
				background-color: rgba(250,250,250,1);
			}
			div{
				float:left;
			}

			/* copied and modified from https://www.w3schools.com/css/css3_buttons.asp */
			button {
				background-color: rgba(0,140,186,1);
				border: none;
				color: white;
				padding: 2px 4px;
				text-align: center;
				text-decoration: none;
				display: inline-block;
				cursor: pointer;
			}

			/* for tabbed view. copied and modified from https://www.w3schools.com/howto/howto_js_tabs.asp */
			.tab {
			  float:left;
				width:100%;
				background-color: rgba(0,140,186,1);
			}
			button.tablinks {
				border:none;
				outline: none;
			  float: left;
			  cursor: pointer;
			  padding: 6px 20px;
				height:30px;
			  transition: 0.3s;
			}
			.tab button:hover {
			  background-color: rgba(20,160,206,1);
			}
			.tab button.active {
			  background-color: rgba(40,180,226,1);
			}
			div.tabcontent {
				float:left;
				width:99%;
				height:80%;
				display: none;
				border-top: none;
			}

			/* for header */
			div.header{
				width:100%;
				height:8%;
				margin-bottom:10px;
			}
			img{
				float:left;
				height:100%;
				margin-right:10px;
			}
			div.headertitle{
				font-weight: bold;
				font-size: 22px;
				height:100%;
				padding-top:20px;
				color: rgba(0,65,111,1);
			}
			div.logintext{
				float:right;
			}

			button.actionbutton, a.actionbutton{
				float:right;
				margin-top:10px;
				margin-left:10px;
				padding: 6px 20px;
				height:30px;
			}
			a.actionbutton{
				height:18px;
				padding-top:8px;
				padding-bottom:4px;
				background-color: rgba(0,140,186,1);
				border: none;
				color: white;
				text-align: center;
				text-decoration: none;
				cursor: pointer;
			}

			form{
				float:left;
				width:100%;
			}
			div.formbody{
				width:58%;
				height:50%;
				padding: 1%;
				margin-top:30px;
				margin-left:20%;
				margin-right:20%;
				border: 1px solid #dddddd;
				overflow-y: scroll;
			}
			div.formrow {
				float:left;
				width:100%;
			}
			div.formrowaction{
				float:left;
				width:60%;
				margin-left:20%;
				margin-right:20%;
			}
			label{
				float:left;
				width:20%;
				font-size: 16px;
				text-align: left;
				padding: 12px 20px;
			  margin: 8px 0;
			}
			input, textarea, select{
				float:right;
			  width: 70%;
			  border: 1px solid #ccc;
				box-sizing: border-box;
				padding: 12px 20px;
			  margin: 8px 0;
			}
			textarea{
				margin-top:15px;
				font-family: inherit;
				font-size: inherit;
				resize: none;
			}

			div.warning{
				float:left;
				width:95%;
				font-size: 16px;
				font-weight:bold;
				text-align:left;
				color:red;
				margin:2%;
			}

		
    </style>
  </head>
  <body>
		<?php
		  setHeaderStudent("enrollment", "Student enrolment to course");
		?>


			<!-- copied and modified from https://www.w3schools.com/howto/howto_css_login_form.asp -->
		<form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post">
			<div class="formbody">
				<?php
					// if error message exist, show it
					if(isset($errorMessage) && $errorMessage != ""){
						// show the message
						echo "<div class='warning'>Error(s):<br />".$errorMessage."</div>";
					}
				 ?>
				<div class="formrow">
					<label for="cname"><b>Course name:</b></label>
					<input type="text" name="cname" 
						<?php echo "value = \"".$myname."\""; ?>
					required readonly style="background-color:rgba(240,240,240,1);"/>
				</div>
				<div class="formrow">
					<label for="desc"><b>Description:</b></label>
					<textarea rows=10 name="desc" readonly style="background-color:rgba(240,240,240,1);"><?php echo $mydesc; ?></textarea>
				</div>
				<div class="formrow">
					<label for="cpassword"><b>Course password:</b><br /></label>
					<input type="text" placeholder="Enter course password" name="cpassword" 
						<?php if(isset($mycpassword) == true){echo "value = \"".$mycpassword."\"";} ?>/>
				</div>	
				<input type="hidden" name="course_id" value="<?php echo $courseID; ?>">
			</div>
			<div class="formrowaction">
				<a href="student_enrollment.php" class="actionbutton">Cancel</a>
				<button class="actionbutton" type="submit">Enrol</button>
			</div>
		</form>

		<script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
