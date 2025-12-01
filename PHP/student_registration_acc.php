<?php
	include("_nosessionchecker.php");
	include("_config.php");
	
	if($_SERVER["REQUEST_METHOD"] != "POST") {
		// first time landed to this page via link given from the student registration email

		// if the key does not exist, redirect to login
		if(isset($_GET['key']) == false || $_GET['key'] == ''){
			header('Location: index.php');
			exit;
		}

		// get the access key
		$access_key = mysqli_real_escape_string($db,$_GET['key']);

		// get username and user_id
		$sql = "SELECT email FROM student_registration
					WHERE access_key = '".$access_key."'";
		$result = mysqli_query($db,$sql);
		$count = mysqli_num_rows($result);
		if($count == 0) {
			// if no such key exists, redirect to dashboard
			header('Location: index.php?errregis=true');
			exit;
		}

		// set the email
		$row = mysqli_fetch_array($result,MYSQLI_ASSOC);
		$myemail = $row['email'];
		$myusername = substr($myemail,0,strrpos($myemail,$registered_email_domain));
// 		echo $myusername;
	
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
		   $sql = "SELECT registration_id FROM student_registration WHERE email = '".$myemail."' AND access_key = '".$access_key."'";
		   $result = mysqli_query($db,$sql);
		   $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
		   $count = mysqli_num_rows($result);
		   if($count == 0) {
		      // if no rows exist, it is not the invited one
		      $errorMessage .= "The email is not the registered one! <br />";
		   }else{
				$registration_id = $row['registration_id'];

			   // checking the validity of username
			   $sql = "SELECT user_id FROM user WHERE username = '".$myusername."'";
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
			   $sql = "SELECT user_id FROM user WHERE email = '".$myemail."'";
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
			}

			 // if no error message
		     if($errorMessage == ""){
				// encrypt the password
				$mypass = password_hash($mypass, PASSWORD_DEFAULT);
				// remove the invited email from invited student list, given that an account has been created
				$sql = "DELETE FROM student_registration
						WHERE registration_id = '".$registration_id."'";
				mysqli_query($db,$sql);
				
				// add the entry
				$sql = "INSERT INTO user (username, password, name, email, role)
					 VALUES ('".$myusername."', '".$mypass."', '".$myname."', '".$myemail."', 'student')";
				if ($db->query($sql) === TRUE) {
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

		<title> E-STRANGE: Student registration</title>
    <link rel="icon" href="strange_html_layout_additional_files/icon.png">
	<!-- Untuk Icon -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
	<link href="bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">


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
    	.buttontambah{
    		text-align: right;
    	}
    	.form-control {
    			border: 2px solid #000;	
    			border-radius: 8px;
    		}
    	@media (max-width: 425px) {
    		.buttontambah{
    			text-align: left;
    			margin: 1rem 0 1rem 0;
    		}
    	}
		
    </style>
  </head>
  <body>
		<div class="container-fluid">
	        <div class="row d-flex justify-content-center align-items-center  mx-3">
	            <div class="col-md-6 layoutmobilestart">
	<img src="strange_html_layout_additional_files/logo.png" alt="logo" class="mobile" />
	<style>
		.layoutmobilestart{
			text-align:left;
		}
		.layoutmobileend{
			text-align:right;
		}
		.logout{
			margin-right:1rem;
		}
		.mobile {
			margin: 0;
			width: 100%;
			height: auto;
			max-height: 200px;
			max-width: 200px;
		}
		.navbarAdmin{
			background-color: #51adba;height:auto;padding-bottom:1rem;
		}
		.colNav{
			margin-bottom:-1.25rem;
		}
		.logoutli{
			margin-left:auto;
		}
		@media only screen and (max-width: 425px) {
			.mobile {
				margin: 1rem;
				width: 100%;
				height: auto;
				max-height: 150px;
				max-width: 150px;
			}
			.layoutmobilestart{
				text-align:center;
			}
			.layoutmobileend{
				text-align:center;
			}
			.logout{
				margin:0;
			}
			.navbarAdmin{
				background-color: #51adba;height:auto;padding-bottom:0rem;
			}
			.colNav{
				margin-bottom:1rem;
				text-align:left;
			}
			a{
				text-align:left;			
			}
			.logoutli{
				margin-left:0;
			}
			.logouttext{
				margin-left:-15px;
			}
		}
	</style>
	 
	  </div>
	            <div class="col-md-6 layoutmobileend">
		<div class="headertitle fs-2">Student registration</div>
	  </div>
	        </div>
        </div>
        
        <nav class="navbar navbar-expand-lg fw-bold navbarAdmin" >
        <!-- <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
             <span class="navbar-toggler-icon"></span>
         </button> -->
		<button class="navbar-toggler text-center w-100 h-100 border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
			<div class=" me-2 pb-2"><h3 class="fw-bolder">Menu</h3></div>
	  	</button>
        <div class="collapse navbar-collapse mx-3 colNav" id="navbarNav">
            <ul class="navbar-nav " style="display: flex;  ;width: 100%;">
                <li class="nav-item ' . ($selectedMenu == 'courses' ? 'active' : '') . '">
                    <button class="tablinks nav-link text-white  me-3 pb-3 fw-medium" onclick="window.open('index.php', '_self');">Login</button>
                </li>
				
            </ul>
        </div>
    </nav>



			<!-- copied and modified from https://www.w3schools.com/howto/howto_css_login_form.asp -->
		<div class="container mt-3">
            <div class="row d-flex justify-content-center">
                <div class="col-md-6">
                <div class="card shadow-lg p-4">
                    <h3 class="text-center mb-4">Register</h3>
                    <?php if(isset($errorMessage) && $errorMessage != ""): ?>
                        <div class="alert alert-danger">Error(s):<br><?php echo $errorMessage; ?></div>
                    <?php endif; ?>
                    <form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email:</label>
                            <input type="email" class="form-control" name="email" 
                                value="<?php echo isset($myemail) ? $myemail : ''; ?>" 
                                required readonly style="background-color:#f0f0f0;">
                        </div>
                        <div class="mb-3">
                            <label for="uname" class="form-label">Username:</label>
                            <input type="text" class="form-control" name="uname" 
                                value="<?php echo isset($myusername) ? $myusername : ''; ?>" 
                                required readonly style="background-color:#f0f0f0;">
                        </div>
                        <div class="mb-3">
                            <label for="cname" class="form-label">Name:</label>
                            <input type="text" class="form-control" name="cname" 
                                value="<?php echo isset($myname) ? $myname : ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="pass" class="form-label">Password:</label>
                            <input type="password" class="form-control" name="pass" required>
                        </div>
                        <div class="mb-3">
                            <label for="passr" class="form-label">Retype Password:</label>
                            <input type="password" class="form-control" name="passr" required>
                        </div>
                        <input type="hidden" name="access_key" value="<?php echo $access_key; ?>">
                        <div class="d-flex justify-content-end">
                            <a href="index.php" class="btn btn-secondary text-dark me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Register</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        </div>

		<script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
