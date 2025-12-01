<?php
	include("_nosessionchecker.php");
	include("_config.php");

	if($_SERVER["REQUEST_METHOD"] != "POST") {
		// first time landed to this page via link given from the change password email

		// if the key does not exist, redirect to login
		if(isset($_GET['key']) == false || $_GET['key'] == ''){
			header('Location: index.php');
			exit;
		}

		// get the access key
		$access_key = mysqli_real_escape_string($db,$_GET['key']);

		// get username and user_id
		$sql = "SELECT user.user_id, user.username FROM user
						INNER JOIN password_request ON user.user_id = password_request.user_id
						WHERE password_request.access_key = '$access_key'";
		$result = mysqli_query($db,$sql);
		$count = mysqli_num_rows($result);
		if($count == 0) {
			// if no such key exists, redirect to dashboard
			header('Location: index.php');
		}

		// set user id and username
		$row = mysqli_fetch_array($result,MYSQLI_ASSOC);
		$myusername = $row['username'];
		$myuserid = $row['user_id'];

	}else{
		// if landed from this page's form
		// copied and modified from https://www.tutorialspoint.com/php/php_mysql_login.htm

   // data sent from form
   $mypass = mysqli_real_escape_string($db,$_POST['pass']);
   $mypassr = mysqli_real_escape_string($db,$_POST['passr']);
	 $myusername = mysqli_real_escape_string($db,$_POST['username']);
	 $myuserid = mysqli_real_escape_string($db,$_POST['userid']);

   // to store the error message
   $errorMessage = "";

   // checking the validity of password
   if($mypass != $mypassr){
     // if the retyped pass is not the same as the pass, error.
     $errorMessage .= "The password is not retyped correctly. <br />";
   }

   // if no error message
   if($errorMessage == ""){
		 // encrypt the password
		 $mypass = password_hash($mypass, PASSWORD_DEFAULT);
		 // update the password
		 $sql = "UPDATE user SET password='$mypass' WHERE user_id='$myuserid'";
      if ($db->query($sql) === TRUE) {
				// if updated well, delete the password request
				$sql = "DELETE FROM password_request
	      WHERE user_id = '$myuserid'";
	      mysqli_query($db,$sql);

        // and redirect to login
        header('Location: index.php?update2=true');
				exit;
      } else {
        echo "Error adding record: " . $db->error;
      }
   }
	}
?>
<html>
	<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
		<title> E-STRANGE: Change password</title>
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
	                <div class="headertitle fs-2">Password change for</div>
		            <div class="fs-2"><?php echo $myusername; ?></div>
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
    
    <div class="container  mt-3">
        <div class="row d-flex justify-content-center align-items-center" >
            <div class="col-md-6" >
                <?php if(isset($errorMessage) && $errorMessage != ""): ?>
                    <div class="alert alert-danger" role="alert">
                        <strong>Error(s):</strong><br /><?php echo $errorMessage; ?>
                    </div>
                <?php endif; ?>
                
                <form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post">
                    <div class="mb-3">
                        <label for="pass" class="form-label"><b>Password:</b></label>
                        <input type="password" class="form-control" placeholder="Enter the password" name="pass" required />
                    </div>
                    <div class="mb-3">
                        <label for="passr" class="form-label"><b>Retype password:</b></label>
                        <input type="password" class="form-control" placeholder="Retype the password" name="passr" required />
                    </div>
                    <input type="hidden" name="username" value="<?php echo $myusername; ?>"/>
                    <input type="hidden" name="userid" value="<?php echo $myuserid; ?>"/>
                    
                    <div class="d-flex justify-content-end">
                        <a href="index.php" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    

		<script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
