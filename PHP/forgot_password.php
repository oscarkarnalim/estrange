<?php
ob_start();
// should not be logged in
include("_nosessionchecker.php");

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
if($_SERVER["REQUEST_METHOD"] == "POST") {
	 include("_config.php");

   // username and password sent from form
   $myusername = mysqli_real_escape_string($db,$_POST['uname']);
   $myemail = mysqli_real_escape_string($db,$_POST['email']);

   // check whether the combination is valid
   $sql = "SELECT user_id, name FROM user WHERE username = '$myusername' and email = '$myemail'";
   $result = mysqli_query($db,$sql);
   $count = mysqli_num_rows($result);
   if($count == 1) {

     // get user id and name
     $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
     $userid = $row['user_id'];
     $name = $row['name'];

     // set access key
     $access_key = '';
     while(true){
       // generate the key
       $access_key = intval(microtime(true)).random_str(3);

       // if such key is nonexistent, escape the loop
       $sql = "SELECT user_id FROM password_request WHERE access_key = '$access_key'";
       $result = mysqli_query($db,$sql);
       $count = mysqli_num_rows($result);
       if($count == 0){
         break;
       }
     }

     // remove all password requests from given user id or any requests with more than one day period
     $sql = "DELETE FROM password_request
     WHERE user_id = '".$userid."' OR time_created + INTERVAL 1 DAY < CURRENT_TIMESTAMP";
     mysqli_query($db,$sql);

     // add to password request table
     $sql = "INSERT INTO password_request (user_id, access_key)
      VALUES ('".$userid."', '".$access_key."')";
     if ($db->query($sql) === TRUE) {
        // create and send the link email

        $changepasslink = $baseDomainLink.'change_password.php?key='.$access_key;
        $to = $myemail;
		if($human_language == 'en'){
			$subject = "[E-STRANGE] Password change request";
			$txt = "Hi ".$name."!<br /><br />A password change request for ".$myusername." has been made. <br />Click <a href='".$changepasslink."'>here</a> to change the password. <br />The link is only valid for 24 hours.<br /> <br />Thank you <br /><br /> E-STRANGE Team";
		}else{
			$subject = "[E-STRANGE] Permintaan penggantian kata sandi";
			$txt = "Halo ".$name."!<br /><br />Permintaan penggantian kata sandi untuk akun ".$myusername." sudah berhasil diajukan. <br />Akses <a href='".$changepasslink."'>tautan ini</a> untuk mengganti kata sandi. <br />Tautan hanya berlaku selama 24 jam saja.<br /> <br />Terima kasih <br /><br /> E-STRANGE Team";
		}

        // send the email
        include("_phpmailerlib.php");
        sendEmail($to,$subject,$txt);

        // redirect page
        header('Location: index.php?update=true');
        //exit;
    } else {
        echo "Error updating record: " . $db->error;
    }
   }else {
      // if no such username and email combination exists
      header('Location: forgot_password.php?err=true');
      exit;
   }
}
?>
<html>
	<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
		<title> E-STRANGE: Forgot password </title>
	
    <link rel="icon" href="strange_html_layout_additional_files/icon.png">

    <link href="bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Untuk Icon -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
	

    <style>
    @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300&display=swap');
    body {
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

    .enter:hover {
      background-color: #396ab3;
      color: white;
    }
    .btn-primary{
			background: #a8c6e7 !important ;
			color: black  !important ;
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
  </head>
  <body>
  <div class="container">
    <div class="row d-flex justify-content-center align-items-center pusat">
        <div class="col-md-5">
            <img style="width: 100%; height: auto; min-height: 175px; min-width: 300px" src="strange_html_layout_additional_files/logo.png" alt="logo">
            <h3><b>Educational Mode</b></h3>
        <h4>Maranatha Christian University</h4>
            <div class="instruction">Please write your username and email address </div>
        </div>
        <div class="col-md-4">
            <?php
            if (isset($_GET['err'])) {
                echo "<div class='alert alert-danger'>Incorrect username and/or email!</div>";
            }
            ?>
            <form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post">
            <a class="singup mb-3"><b>Forgot Password</b></a>

              <div class="inputBox my-3">
                <input type="text" required="required" name="uname" placeholder="Username" />
     
              </div>

              <div class="inputBox my-3">
                <input type="email" required="required"  name="email" placeholder="Email" />
         
              </div>
                <button type="submit" class="btn btn-primary w-100">Send a password request link</button>
                <p class="return mt-5">Return to <a href="index.php">login?</a></p>
            </form>
        </div>
    </div>
</div>


				

<script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
