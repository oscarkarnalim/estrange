<?php
	include("_sessionchecker.php");
	include("_config.php");

	// copied and modified from https://www.tutorialspoint.com/php/php_mysql_login.htm
	if($_SERVER["REQUEST_METHOD"] == "POST") {
		// for processing the updated data entry
		if(isset($_POST['uname']) == true){
		   // data sent from form
		   $myusername = mysqli_real_escape_string($db,$_POST['uname']);
		   $myname= mysqli_real_escape_string($db,$_POST['cname']);
		   $myemail = mysqli_real_escape_string($db,$_POST['email']);
		   $mypass = mysqli_real_escape_string($db,$_POST['pass']);
		   $mypassr = mysqli_real_escape_string($db,$_POST['passr']);
		   $id = mysqli_real_escape_string($db,$_POST['id']);


		   // to store the error message
		   $errorMessage = "";

		   // checking the validity of username
		   $sql = "SELECT user_id FROM user WHERE username = '$myusername'";
		   $result = mysqli_query($db,$sql);
		   $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
		   $count = mysqli_num_rows($result);
		   if($count == 1 && $row['user_id'] != $id){
		       // if not the updated one, the username is not unique
		       $errorMessage .= "The username has been registered for another account. <br />";
		   }else if($count > 1) {
		      // if more than one entry fetched, the username is not unique
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
		   if($count == 1 && $row['user_id'] != $id){
		       // if not the updated one, the email is not unique
		       $errorMessage .= "The email has been registered for another account. <br />";
		   }else if($count > 1) {
		      // if more than one entry fetched, the email is not unique
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
		      $sql = "";
		       if($mypass != ''){
		         // encrypt the password
		         $mypass = password_hash($mypass, PASSWORD_DEFAULT);
		         // if new pass is set, change the pass also
		         $sql = "UPDATE user SET username = '$myusername', name = '$myname', email = '$myemail', password='$mypass' WHERE user_id='$id'";
		       }else{
		         // otherwise, exclude it
		         $sql = "UPDATE user SET username = '$myusername', name = '$myname', email = '$myemail' WHERE user_id='$id'";
		       }
		      if ($db->query($sql) === TRUE) {
		        // if updated well, redirect to dashboard
		        header('Location: admin_dashboard.php');
						exit;
		      } else {
		        echo "Error updating record: " . $db->error;
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
		<title> E-STRANGE: Update lecturer</title>
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
	.btn-danger{
			background: #f56976 !important ;
		}
    .singup {
      color: #000;
      text-transform: uppercase;
      text-decoration: none;
      font-size: 2rem;
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

    .inputBox span,
    .inputBox1 span {
      margin-top: 5px;
      position: absolute;
      left: 0;
      transform: translateY(-4px);
      margin-left: 10px;
      padding: 10px;
      pointer-events: none;
      font-size: 12px;
      color: #396ab3;
      text-transform: uppercase;
      transition: 0.5s;
      letter-spacing: 3px;
      border-radius: 8px;
    }

    
    .enter {
      height: 45px;
      width: 100%;
      border-radius: 5px;
      border: 2px solid #000;
      cursor: pointer;
      background-color: transparent;
      transition: 0.5s;
      text-transform: uppercase;
      font-size: 16px;
      letter-spacing: 2px;
      margin-bottom: 3em;
	  color:#396ab3;
    }

    .enter:hover {
      background-color: #396ab3;
      color: white;
    }

	.youtube-video {
	 aspect-ratio: 16 / 9;
	 width: 80%;
	}
	.btn-primary{
			background: #a8c6e7 !important ;
			color: black  !important ;
		}
  	</style>
  </head>
  <body>
  <?php setHeaderAdmin("dashboard", "Update lecturer"); ?>


			<!-- copied and modified from https://www.w3schools.com/howto/howto_css_login_form.asp -->
		<?php
			// get the old values from database
			$sql = "SELECT username, name, email FROM user WHERE user_id = '".$_POST['id']."'";
			$result = mysqli_query($db,$sql);
			$row = mysqli_fetch_array($result,MYSQLI_ASSOC);

			// show the header of the form
			echo '
			<div class="container">
			<div class="row d-flex justify-content-center align-items-center" style="min-height:70vh">
				<div class="col-md-12">
					<div class="card mx-auto">
					<form class="w-50" action="'.htmlentities($_SERVER['PHP_SELF']).'" method="post">
						<div class="formbody">
					';

			// if error message exist, show it
			if(isset($errorMessage) == true && $errorMessage != ""){
				// show the message
				echo "<div class='warning'>Error(s):<br />".$errorMessage."</div>";
				// set the values to the changed ones
				$row['username'] = $myusername;
				$row['name'] = $myname;
				$row['email'] = $myemail;
			}

			// show the remaining form
			echo '
						 <div class="inputBox my-4">
						 <input type="text" placeholder="Enter the username" name="uname" value="'.$row['username'].'" required />
						 
						</div>
						 <div class="inputBox my-4">
						 <input type="text" placeholder="Enter the name" name="cname" value="'.$row['name'].'" required />
					
						</div>
						 <div class="inputBox my-4">
						 <input type="password" placeholder="Password"  name="pass" />
						 
						 <p style="font-size:0.75em">(leave empty if unchanged)</p>
						</div>
						 <div class="inputBox1 my-4">
						 <input type="password" placeholder="Retype Password"  name="passr" />
						
						</div>
						 <div class="inputBox my-4">
						 <input type="email" placeholder="Enter the email" name="email" value="'.$row['email'].'" required />
						 
						</div>
						 <input type="hidden" name="id" value="'.$_POST['id'].'">
					</div>

					<div class="inputBox my-4 text-right">
						<a href="admin_dashboard.php" class="btn btn-danger">Cancel</a>
						<button class="btn btn-primary" type="submit">Update</button>
					</div>
				</form>
				</div>
    			</div>
			</div>
		</div>
					';
				?>

			
<script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
