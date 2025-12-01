<?php
	include("_sessionchecker.php");
	include("_config.php");

	// copied and modified from https://www.tutorialspoint.com/php/php_mysql_login.htm
	if($_SERVER["REQUEST_METHOD"] == "POST") {
		// process the added data
		if(isset($_POST['uname']) == true){
		   // data sent from form
		   $myusername = mysqli_real_escape_string($db,$_POST['uname']);
		   $myname= mysqli_real_escape_string($db,$_POST['cname']);
		   $myemail = mysqli_real_escape_string($db,$_POST['email']);
		   $mypass = mysqli_real_escape_string($db,$_POST['pass']);
		   $mypassr = mysqli_real_escape_string($db,$_POST['passr']);

		   // to store the error message
		   $errorMessage = "";

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

		   // if no error message
		   if($errorMessage == ""){
	        // encrypt the password
	        $mypass = password_hash($mypass, PASSWORD_DEFAULT);
	        // add the entry
	        $sql = "INSERT INTO user (username, password, name, email, role)
					 VALUES ('".$myusername."', '".$mypass."', '".$myname."', '".$myemail."', 'student')";
		      if ($db->query($sql) === TRUE) {
		        // if updated well, redirect to dashboard
		        header('Location: admin_students.php');
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
		<title> E-STRANGE: Add student</title>
    <link rel="icon" href="strange_html_layout_additional_files/icon.png">
	<!-- Untuk Icon -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
	
    <script>
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
		.card {
		display: flex;
		justify-content: center;
		align-items: center;
		height: auto;
		width: 100%;
		flex-direction: column;
		border: none;
		color:#396ab3;
		}


		.inputBox,
		.inputBox1 {
		position: relative;
		width: 100%;
		}

		.inputBox input,
		.inputBox1 input {
		width: 100%;
		padding: 10px;
		outline: none;
		border: none;
		color: #396ab3;
		font-size: 1em;
		background: transparent;
		border: 2px solid #000;
		transition: 0.1s;
		border-radius: 8px;
		}

		

		.inputBox input:valid,
		.inputBox input:focus,
		.inputBox1 input:valid,
		.inputBox1 input:focus {
		border: 2px solid #000;
		border-radius: 8px;
		}
	</style>
  </head>
  <body>
  <?php setHeaderAdmin("students", "Add student"); ?>

  <div class="container">
			<div class="row d-flex justify-content-center align-items-center" style="min-height:50vh">
				<div class="col-md-12">
					<div class="card mx-auto w-100">
						<form class="w-50"  action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post">	
							<?php
								// if error message exist, show it
								if(isset($errorMessage) && $errorMessage != ""){
									// show the message
									echo "<div class='warning'>Error(s):<br />".$errorMessage."</div>";
								}
							?>
							<div class="inputBox my-4">
								<input class="w-100" type="text" placeholder="Username" required="required" name="uname" <?php if(isset($myusername) == true){echo "value = \"".$myusername."\"";} ?> />
								
							</div>
							<div class="inputBox my-4">
								<input class="w-100" type="text" placeholder="Name"  required="required" name="cname" <?php if(isset($myname) == true){echo "value = \"".$myname."\"";} ?> />
						
							</div>
							<div class="inputBox my-4">
								<input class="w-100" type="password" placeholder="Password"  required="required" name="pass"/>
			    			
							</div>
							<div class="inputBox1 my-4">
								<input  class="w-100" type="password" placeholder="Retype Password"  required="required" name="passr" />
						
							</div>
							<div class="inputBox my-4">
								<input  class="w-100" type="email" placeholder="Email"  required="required" name="email" <?php if(isset($myemail) == true){echo "value = \"".$myemail."\"";} ?> />
								
							</div>
							<div class="inputBox my-4 text-right">
								<a href="admin_students.php" class="btn btn-danger">Cancel</a>
								<button class="btn btn-primary" type="submit">Add</button>
							</div>
					</form>

					<script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
