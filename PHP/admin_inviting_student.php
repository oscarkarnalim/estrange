<?php
include("_sessionchecker.php");
include("_config.php");

// for generating random string
function random_str(
	$length,
	$keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') {
	$str = '';
	$max = mb_strlen($keyspace, '8bit') - 1;
	for ($i = 0; $i < $length; ++$i) {
		$str .= $keyspace[rand(0, $max)];
	}
	return $str;
}

// copied and modified from https://www.tutorialspoint.com/php/php_mysql_login.htm
if($_SERVER["REQUEST_METHOD"] == "POST") {
	// start to enroll the students based on the text given in the form
	if(isset($_POST['students']) == true && $_POST['students'] != ""){
		 // data sent from form
		 $mystudents= mysqli_real_escape_string($db,$_POST['students']);

		 // to store the student emails
		 $studentEmails = array();

		 // to store the error message
		 $errorMessage = "";

		 // split based on newline
		 $arr = explode ("\\n",$mystudents);
		 $arrLength = count($arr);
		 for($i=0;$i<$arrLength;$i++){
			// get the email without escape characters
			$arr[$i] = str_replace("\\r", "", $arr[$i]);

			// if empty, skip the line but show no error message
			if($arr[$i] == ""){
				continue;
			}

			// checking the validity of email
			$sql = "SELECT email FROM user
			 				 WHERE email = '".$arr[$i]."'";
			$result = mysqli_query($db,$sql);
			$row = mysqli_fetch_array($result,MYSQLI_ASSOC);
			$count = mysqli_num_rows($result);
			if($count != 0) {
		      // if the email does not exist
		      $errorMessage .= "Line ".($i+1).": The email has been registered to another user. <br />";
			}else{
				$sql = "SELECT email FROM invited_student
			 				 WHERE email = '".$arr[$i]."'";
				$result = mysqli_query($db,$sql);
				$count = mysqli_num_rows($result);
				if($count != 0) {
					// if the email has been invited
					$errorMessage .= "Line ".($i+1).": The email has been invited. <br />";
				}else{
					if(strlen($arr[$i] >= 50)){
						$errorMessage .=  "Line ".($i+1).": The email should be shorter or equal to 50 characters. <br />";
					}else{
						if (!filter_var($arr[$i], FILTER_VALIDATE_EMAIL)) {
							$errorMessage .=  "Line ".($i+1).": The email is ill-formed. <br />";
						}else{
							// get the id to an array
							$studentEmails[] = $arr[$i];
						}
					}
				}
			}
		 }

		 // if no error message
		 if($errorMessage == ""){
			$emailLength = count($studentEmails);
			// for each student email given in the text
			for($i=0;$i<$emailLength;$i++){
				// add the entry
				$sql = "INSERT INTO invited_student (email)
							VALUES ('".$studentEmails[$i]."')";

				// if error, print the message and exit
				if ($db->query($sql) != TRUE) {
					echo "Error adding record: " . $db->error;
					exit;
				}
			}
			// if updated well, redirect to dashboard
			header('Location: admin_invited_students.php');
			exit;
		 }
	 }
}
?>
<html>
	<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
		<title> E-STRANGE: Enroll student(s)</title>
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
			input, textarea{
				float:right;
			  width: 70%;
			  border: 1px solid #ccc;
				box-sizing: border-box;
				padding: 12px 20px;
			  margin: 8px 0;
			}
			textarea{
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
		<div class="header">
			<img src="strange_html_layout_additional_files/logo.png" alt="logo" />
			<div class="headertitle">Student enrollment</div>
			<?php
			  echo '<div class="logintext">Hello '.$_SESSION['name'].' You logged in as '.$_SESSION['role'].'!</div>';
			?>
		</div>

		<div class="tab">
			<button class="tablinks" onclick="window.open('admin_dashboard.php', '_self');">Lecturers</button>
			<button class="tablinks" onclick="window.open('admin_students.php', '_self');">Students</button>
			<button class="tablinks" onclick="window.open('admin_student_submissions.php', '_self');">Student submissions</button>
			<button class="tablinks" onclick="window.open('user_info_self_update.php', '_self');">Update personal information</button>
			<button class="tablinks" onclick="window.open('user_about.php', '_self');">About</button>
			<form action=" <?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post">
			  <input type="hidden" name="logout" value="logout">
			  <button class="tablinks" type="submit">Logout</button>
			</form>
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
						<label for="students"><b>Student emails:</b></label>
						<textarea rows=18 placeholder="Enter student emails separated by line"
						name="students" required><?php if(isset($mystudents) == true){echo str_replace("\\r", "\r", str_replace("\\n", "\n", $mystudents)); } ?></textarea>
					</div>
				</div>
				<div class="formrowaction">
					<a href="admin_invited_students.php" class="actionbutton">Cancel</a>
					<button class="actionbutton" type="submit">Invite</button>
				</div>
		</form>
		<script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>

  </body>
</html>
