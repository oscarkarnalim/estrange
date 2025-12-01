<?php
ob_start();
// should not be logged in
include("_nosessionchecker.php");
include("_config.php");

// function to generate a random pass
function random_str(
    $length,
    // the possible values
    $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') {
    $str = '';
    $max = mb_strlen($keyspace, '8bit') - 1;
    for ($i = 0; $i < $length; ++$i) {
        $str .= $keyspace[rand(0, $max)];
    }
    return $str;
}

function endsWith($haystack, $needle) {
    return substr_compare($haystack, $needle, -strlen($needle)) === 0;
}

// copied and modified from https://www.tutorialspoint.com/php/php_mysql_login.htm
if($_SERVER["REQUEST_METHOD"] == "POST"){
	$myemail = mysqli_real_escape_string($db,$_POST['email']);
	
	$errorMessage = "";
	
	// checking the validity of email
	$sql = "SELECT email FROM user
					 WHERE email = '".$myemail."'";
	$result = mysqli_query($db,$sql);
	$row = mysqli_fetch_array($result,MYSQLI_ASSOC);
	$count = mysqli_num_rows($result);
	if($count != 0) {
	  // if the email already exists on database
	  $errorMessage .= "The email has been registered to another user. <br />";
	}
	if(strlen($myemail) >= 50){
		// if the email is too long
		$errorMessage .=  "The email should be shorter or equal to 50 characters. <br />";
	}
	if(endsWith($myemail,$registered_email_domain) == false){
		// if the email is not from given domain
		$errorMessage .=  "The email should be your student email (ends with '".$registered_email_domain."'). <br />";
	}
	
	 if($errorMessage == ""){

		 // set access key
		 $access_key = '';
		 
		 // check whether the email already registered
		 $registrationId  = "";
		 $sql = "SELECT registration_id, access_key FROM student_registration WHERE email = '".$myemail."'";
		 $result = mysqli_query($db,$sql);
		 $count = mysqli_num_rows($result);
		 if($count == 1) {
			// get registration id and access key
			$row = mysqli_fetch_array($result,MYSQLI_ASSOC);
			$registrationId = $row['registration_id'];
			$access_key = $row['access_key'];
		 }else{
			// generate the access key
			while(true){
			   // generate the key
			   $access_key = intval(microtime(true)).random_str(3);

			   // if such key is nonexistent, escape the loop
			   $sql = "SELECT invitation_id FROM student_registration WHERE access_key = '".$access_key."'";
			   $result = mysqli_query($db,$sql);
			   $count = mysqli_num_rows($result);
			   if($count == 0){
				 break;
			   }
			}
			
			// insert into student registration table with the key
			$sql = "INSERT INTO student_registration (email, access_key)
									VALUES ('".$myemail."','".$access_key."')";
			$db->query($sql);
		 }
		 
		// create and send the link email
		$registerlink = $baseDomainLink.'student_registration_acc.php?key='.$access_key;
		$to = $myemail;
		if($human_language == 'en'){
			$subject = "[E-STRANGE] Account registration request";
			$txt = "Hi!<br /><br />An account registration request for this email has been made. <br />Click <a href='".$registerlink."'>here</a> to register an account. <br /> <br />Thank you <br /><br /> E-STRANGE Team";
		}else{
			$subject = "[E-STRANGE] Permintaan registrasi akun";
			$txt = "Halo!<br /><br />Permintaan registrasi akun untuk email ini sudah berhasil diajukan. Akses <a href='".$registerlink."'>tautan ini</a> untuk meregister akun.<br /> <br />Terima kasih <br /><br /> E-STRANGE Team";
		}

		// send the email
		include("_phpmailerlib.php");
		sendEmail($to,$subject,$txt);

		// redirect page
		header('Location: index.php?update3=true');
		exit;
	 }
}
?>
<html>
	<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">

		<title> E-STRANGE: Student registration </title>
    <link rel="icon" href="strange_html_layout_additional_files/icon.png">
    <!-- Untuk Icon -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
	
    <script>
    </script>

<link href="bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">

</head>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300&display=swap');
    body {
      /* font-family: "Times New Roman", Times, serif; */
      font-family: 'Montserrat', sans-serif;
    }
    .singup {
      color: #000;
      text-transform: uppercase;
      text-decoration: none;
      font-size: 2rem;
    }

    .card {

      height: auto;
      width: 100%;
      flex-direction: column;
      border: none;
      color: #396ab3;
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



    .inputBox input:valid,
    .inputBox input:focus,
    .inputBox1 input:valid,
    .inputBox1 input:focus {
      border: 2px solid #000;
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
      color: #396ab3;
    }

    .btn-primary{
			background: #a8c6e7 !important ;
			color: black  !important ;
		}
    .form-control {
			border: 2px solid #000;	
			border-radius: 8px;
		}
	.pusat{
		min-height: 100vh
	}
	@media only screen 
    and (min-width: 320px) 
    and (max-width: 769px) {
		.pusat{
		min-height: 0vh
	}
	 }
  </style>
  <body>
  <div class="container">
	<div>
      <div class="row d-flex justify-content-center align-items-center pusat">
        <div class="col-md-5">
          <img src="strange_html_layout_additional_files/logo.png" alt="logo" style="width: 100%; height: auto; min-height: 175px; min-width: 300px" />
          <h3><b>Educational Mode</b></h3>
          <h4>Maranatha Christian University</h4>
        </div>
        <div class="col-md-5">
          <?php
          // if error message exist, show it
          if(isset($errorMessage) && $errorMessage != ""){
            // show the message
            echo "<div class='warning'>Error(s):<br />".$errorMessage."</div>";
          }
         ?>
          <!-- copied and modified from https://www.w3schools.com/howto/howto_css_login_form.asp -->
          <form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post">
            <div class="card ">
              <a class="singup mb-3"><b>Register</b></a>

              <div class="inputBox my-3">
                <input type="email" required="required" name="email" placeholder="Your student email"/>
              </div>

              <button class="btn btn-primary mb-3" type="submit">Send registration link</button>
              <span class="return">Return to <a href="index.php">login</a>?</span>
            </div>
          </form>
        </div>
      </div>
    </div>

    <script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
