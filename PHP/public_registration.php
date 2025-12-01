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

// copied and modified from https://www.tutorialspoint.com/php/php_mysql_login.htm
if($_SERVER["REQUEST_METHOD"] != "POST"){
	// first time landing
	$sql = "SELECT name, course_id FROM course
			WHERE enrollment_mode = 2";
	$result = mysqli_query($db,$sql);
	if ($result->num_rows == 0) {
		header('Location: index.php?nocoursespublic=true');
		exit;
	}
}else{
	$myemail = mysqli_real_escape_string($db,$_POST['email']);

     // set access key
     $access_key = '';
     while(true){
       // generate the key
       $access_key = random_str(5).microtime(true).random_str(5);

       // if such key is nonexistent, escape the loop
       $sql = "SELECT registration_id FROM public_registered_student WHERE access_key = '$access_key'";
       $result = mysqli_query($db,$sql);
       $count = mysqli_num_rows($result);
       if($count == 0){
         break;
       }
     }

     // update invited student table with the key
	 $sql = "INSERT INTO public_registered_student (email, access_key)
					 VALUES ('".$myemail."', '".$access_key."')";
     if ($db->query($sql) === TRUE) {
        // create and send the link email

        $registerlink = $baseDomainLink.'public_registration_acc.php?key='.$access_key;
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
        //exit;
    } else {
        echo "Error updating record: " . $db->error;
    }
}
?>
<html>
	<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
		<title> E-STRANGE: Public registration </title>
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
			}
			div{
				float:left;
			}
			.container {
				width:40%;
				margin-top:1%;
				margin-left:29%;
				margin-right:29%;
				padding: 1%;
				border: 1px solid #b1b1b1;
			}
			div.image{
				width:100%;
			}
			img{
				width:40%;
				margin-right:30%;
				margin-left:30%;
			}
			div.ver{
				width:100%;
				text-align:center;
				font-size:16px;
				font-weight: bold;
				color: rgba(0,65,111,1);
				margin-bottom:1%;
			}

			<!-- copied from https://www.w3schools.com/howto/howto_css_login_form.asp -->
			form {
				float:left;
				width:100%;
			}
			label{
				font-size: 16px;
			}

			input[type=text], input[type=password], input[type=email] {
			  width: 100%;
			  padding: 12px 20px;
			  margin: 8px 0;
			  display: inline-block;
			  border: 1px solid #ccc;
			  box-sizing: border-box;
			}

			button {
			  background-color: rgba(0,140,186,1);
			  color: white;
			  padding: 14px 20px;
			  margin: 8px 0;
			  border: none;
			  cursor: pointer;
			  width: 100%;
			}

			button:hover {
			  opacity: 0.8;
			}

			span.remember{
				font-size:12px;
			}

			span.return {
			  padding:5px;
			  float: right;
			}

			div.warning{
				float:left;
				width:100%;
				font-size: 16px;
				font-weight:bold;
				text-align:left;
				margin-top:4%;
				margin-bottom:2%;
				color:red;
			}

		}
    </style>
  </head>
  <body>
		<div class="container">
				<div class="image"><img src="strange_html_layout_additional_files/logo.png" alt="logo"></div>
				<div class="ver">Educational Mode</div>
				<!-- copied and modified from https://www.w3schools.com/howto/howto_css_login_form.asp -->
				<form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post">
				    <label for="email"><b>Email:</b></label>
				    <input type="email" placeholder="Enter your email" name="email" required>

				    <button type="submit">Send an account registration link</button>
					<span class="return">Return to <a href="index.php">login?</a></span>
				</form>
		 </div>
		 
<script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
