<?php
	include("_nosessionchecker.php");
	include("_config.php");
	
	if($_SERVER["REQUEST_METHOD"] != "POST") {
		// first time landed to this page via link given from the public registration email

		// if the key does not exist, redirect to login
		if(isset($_GET['key']) == false || $_GET['key'] == ''){
			header('Location: index.php');
			exit;
		}

		// get the access key
		$access_key = mysqli_real_escape_string($db,$_GET['key']);

		// get username and user_id
		$sql = "SELECT email FROM public_registered_student
					WHERE access_key = '$access_key'";
		$result = mysqli_query($db,$sql);
		$count = mysqli_num_rows($result);
		if($count == 0) {
			// if no such key exists, redirect to dashboard
			header('Location: index.php');
		}

		// set the email
		$row = mysqli_fetch_array($result,MYSQLI_ASSOC);
		$myemail = $row['email'];
	
	}else{
		// copied and modified from https://www.tutorialspoint.com/php/php_mysql_login.htm
		// process the added data
		if(isset($_POST['uname']) == true){
		   // data sent from form
		   $myusername = mysqli_real_escape_string($db,$_POST['uname']);
		   $myname= mysqli_real_escape_string($db,$_POST['cname']);
		   $myemail = mysqli_real_escape_string($db,$_POST['email']);
		   $mypass = mysqli_real_escape_string($db,$_POST['pass']);
		   $mypassr = mysqli_real_escape_string($db,$_POST['passr']);
		   $access_key = mysqli_real_escape_string($db,$_POST['access_key']);
		   
		   

		   // to store the error message
		   $errorMessage = "";
		   
		   // checking the validity of email
		   $sql = "SELECT registration_id FROM public_registered_student WHERE email = '".$myemail."' AND access_key = '".$access_key."'";
		   $result = mysqli_query($db,$sql);
		   $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
		   $count = mysqli_num_rows($result);
		   if($count == 0) {
		      // if no rows exist, it is not the invited one
		      $errorMessage .= "The email is not registered. <br />";
		   }else{
				$registration_id = $row['registration_id'];


			   // checking the validity of username
			   $sql = "SELECT user_id FROM user WHERE username = '$myusername'";
			   $result = mysqli_query($db,$sql);
			   $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
			   $count = mysqli_num_rows($result);
			   if($count > 0) {
				  // if at least one entry fetched, the username is not unique
				  $errorMessage .= "The username has been registered for another account. <br />";
				}

			   // checking the validity of password
			   if($mypass != $mypassr){
				 // if the retyped pass is not the same as the pass, error.
				 $errorMessage .= "The password is not retyped correctly. <br />";
			   }

			   // checking the validity of email
			   $sql = "SELECT user_id FROM user WHERE email = '$myemail'";
			   $result = mysqli_query($db,$sql);
			   $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
			   $count = mysqli_num_rows($result);
			   if($count > 0) {
				  // if at least one entry fetched, the email is not unique
				  $errorMessage .= "The email has been registered for another account. <br />";
			   }

			   // validate the length of the inputs
			   if(strlen($myusername) >= 30){
					$errorMessage .= "The username should be shorter or equal to 30 characters. <br />";
			   }
			   if(strlen($myname) >= 50){
					$errorMessage .= "The name should be shorter or equal to 50 characters. <br />";
			   }
			   if(strlen($myemail) >= 50){
					$errorMessage .= "The email should be shorter or equal to 50 characters. <br />";
			   }
				 
			   // validate the content of the username that should be alphanumeric without space
			   if(ctype_alnum($myusername) == false){
					$errorMessage .= "The username should contain only alphabets and/or numbers. <br />";
			   }
				 
			   // validate the content of the name that should be alphanumeric and space
			   if(preg_match('/^[a-z0-9 .\-]+$/i', $myname) == false){
					$errorMessage .= "The name should contain only alphabets, numbers, and/or space. <br />";
			   }
			   
			   // check max password length
			   if(strlen($mypass) >= 50){
				 $errorMessage .= "The password should be shorter or equal to 50 characters. <br />";
			   }
			   
			   // check min password length
			   if(strlen($mypass) < 8 && strlen($mypass) > 0){
				 $errorMessage .= "The password should be longer or equal to 8 characters. <br />";
			   }
			   
			   // if the course var is not set, ask the user to select at least one course
			   if(isset($_POST['courses']) == false){
					$errorMessage .= "Please select at least one course to be enrolled to. <br />";
			   }
			}

			 // if no error message
		     if($errorMessage == ""){
				// encrypt the password
				$mypass = password_hash($mypass, PASSWORD_DEFAULT);
				// remove the registered email from public registered student list, given that an account has been created
				$sql = "DELETE FROM public_registered_student
						WHERE registration_id = '".$registration_id."'";
				mysqli_query($db,$sql);
				
				// add the entry
				$sql = "INSERT INTO user (username, password, name, email, role)
					 VALUES ('".$myusername."', '".$mypass."', '".$myname."', '".$myemail."', 'student')";
				if ($db->query($sql) === TRUE) {
					// get the user ID
					$sql = "SELECT user_id FROM user WHERE email = '".$myemail."'";
				    $result = mysqli_query($db,$sql);
				    $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
					
					// if updated well, enroll the courses
					foreach ($_POST['courses'] as $enrolledCourseId){
						$enrolledCourseId = mysqli_real_escape_string($db,$enrolledCourseId);
						$sql = "INSERT INTO enrollment (student_id, course_id) VALUES ('".$row['user_id']."','".$enrolledCourseId."')";
						mysqli_query($db,$sql);
					}
					
					// redirect to dashboard
					header('Location: index.php?update4=true');
					exit;
				} else {
					echo "Error adding record: " . $db->error;
				}
			 }
		 }
	}
?>
<html>
	<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
		<title> E-STRANGE: Public registration</title>
    <link rel="icon" href="strange_html_layout_additional_files/icon.png">
	<!-- Untuk Icon -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
	<link href="bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">


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

			<!-- copied and modified from https://www.w3schools.com/howto/howto_css_login_form.asp -->
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
				overflow-y: auto;
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
		}
    </style>
  </head>
  <body>
		<div class="header">
			<img src="strange_html_layout_additional_files/logo.png" alt="logo" />
			<div class="headertitle">Public registration</div>
		</div>

		<div class="tab">
			<button class="tablinks" onclick="window.open('index.php', '_self');">Login</button>
		</div>


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
					<label for="email"><b>Email:</b></label>
					<input type="email" placeholder="Enter the invited email" name="email"
						<?php if(isset($myemail) == true){echo "value = \"".$myemail."\""; } ?>
					required readonly style="background-color:rgba(240,240,240,1);"/>
				</div>
				<div class="formrow">
					<label for="uname"><b>Username:</b></label>
					<input type="text" placeholder="Enter the username" name="uname"
						<?php if(isset($myusername) == true){echo "value = \"".$myusername."\"";} ?>
					required />
				</div>
				<div class="formrow">
					<label for="cname"><b>Name:</b></label>
					<input type="text" placeholder="Enter the name" name="cname"
						<?php if(isset($myname) == true){echo "value = \"".$myname."\""; } ?>
					required />
				</div>
				<div class="formrow">
					<label for="pass"><b>Password:</b></label>
					<input type="password" placeholder="Enter the password" name="pass" required />
				</div>
				<div class="formrow">
					<label for="passr"><b>Retype password:</b></label>
					<input type="password" placeholder="Retype the password" name="passr" required />
				</div>
				<div class="formrow">
					<label for="courses"><b>Enrolled course(s): </b> <br /> <i>Please hold down the control button (Windows) or the command button (Mac) to select more than one courses</i></label>
					<select name="courses[]" id="courses" multiple>
						<?php
							// get all courses. Mode 2 refers to voluntary and for public
							$sql = "SELECT name, course_id FROM course
									WHERE enrollment_mode = 2";
							$result = mysqli_query($db,$sql);
							// adapted from https://www.w3schools.com/php/php_mysql_select.asp
							if ($result->num_rows > 0) {
								while($row = $result->fetch_assoc()) {
									echo "<option value=\"".$row['course_id']."\">".$row['name']."</option>";
								}
							}else{
								// if there are no available courses, redirect to login with a message
								header('Location: index.php?nocoursespublic=true');
								exit;
							}
						?>
					</select>
				</div>
				
				<input type="hidden" name="access_key" value="<?php echo $access_key; ?>">
			</div>
			<div class="formrowaction">
				<a href="index.php" class="actionbutton">Cancel</a>
				<button class="actionbutton" type="submit">Register</button>
			</div>
		</form>

		<script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
