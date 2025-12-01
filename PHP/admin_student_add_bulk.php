<?php
	include("_sessionchecker.php");
	include("_config.php");

	// file handling copied and modified from https://stackoverflow.com/questions/5593473/how-to-upload-and-parse-a-csv-file-in-php
	if($_SERVER["REQUEST_METHOD"] == "POST") {
		if ( isset($_FILES["ufile"])) {
			 // if there was an error uploading the file
      if ($_FILES["ufile"]["error"] > 0) {
        echo "Return Code: " . $_FILES["ufile"]["error"] . "<br />";
      }
      else {
				// set error message
				$errorMessage = "";

				// open the file
				$file = fopen($_FILES["ufile"]["tmp_name"],"r");

				// exclude the first line as it contains only column headers
				if(!feof($file)){
					fgetcsv($file);
				}

				// to store the user data from uploaded csv
				$dataList = [];
				$lineCounter = 0;

				// check each remaining line
				while(!feof($file)){
  				$line = fgetcsv($file);

					// the last line read is always null and should be excluded
					if($line == null){
						break;
					}

					// store the values to temporary variables
					$username = trim($line[0]);
					$name = trim($line[1]);
					$password = trim($line[2]);
					$email = trim($line[3]);

					// checking username
					// empty check
					if($username == ""){
						$errorMessage .= ("Entry ". ($lineCounter+2) . ": the username should not be empty.<br />");
					}
					// uniqueness check
					$sql = "SELECT user_id FROM user WHERE username = '".$username."'";
					$result = mysqli_query($db,$sql);
	 		   	$row = mysqli_fetch_array($result,MYSQLI_ASSOC);
	 		   	$count = mysqli_num_rows($result);
	 		   	if($count > 0) {
	 		      // if at least one entry fetched, the username is not unique
	 		     	$errorMessage .= ("Entry ". ($lineCounter+2) . ": the username ". $username ." has been used by another user.<br />");
	 		    }else{
 					 // check from the added entries
 					 $isExist = false;
 					 for($i=0;$i<$lineCounter;$i++){
 						 if($username == $dataList[$i][0]){
 							 $isExist = true;
 							 break;
 						 }
 					 }
					 // if unique
 					 if($isExist){
 						 $errorMessage .= ("Entry ". ($lineCounter+2) . ": the username ".$username. " has been used in an earlier entry.<br />");
 					 }
 				 }

				 // checking name
				 // empty check
				 if($name == ""){
					 $errorMessage .= ("Entry ". ($lineCounter+2) . ": the name should not be empty.<br />");
				 }

				 // checking password
				 // empty check
				 if($password == ""){
					 $errorMessage .= ("Entry ". ($lineCounter+2) . ": the password should not be empty.<br />");
				 }


					// checking email
					// empty check
					if($email == ""){
						$errorMessage .= ("Entry ". ($lineCounter+2) . ": the email should not be empty.<br />");
					}
					// uniqueness check
					$sql = "SELECT user_id FROM user WHERE email = '".$email."'";
					$result = mysqli_query($db,$sql);
	 		   	$row = mysqli_fetch_array($result,MYSQLI_ASSOC);
	 		   	$count = mysqli_num_rows($result);
	 		   	if($count > 0) {
	 		      // if at least one entry fetched, the email is not unique
	 		     $errorMessage .= ("Entry ". ($lineCounter+2) . ": the email ".$email." has been used by another user.<br />");
				 }else{
					 // check from the added entries
					 $isExist = false;
					 for($i=0;$i<$lineCounter;$i++){
						 if($email == $dataList[$i][3]){
							 $isExist = true;
							 break;
						 }
					 }
					 // if unique
					 if($isExist){
						 $errorMessage .= ("Entry ". ($lineCounter+2) . ": the email ".$email." has been used in an earlier entry.<br />");
					 }
				 }
					// validate format
					if (filter_var($email, FILTER_VALIDATE_EMAIL) == false) {
					  $errorMessage .= ("Entry ". ($lineCounter+2). ": the email ".$email." is not correctly written.<br />");
					}

					// check whether the length violates the max length
					if(strlen($username) >= 30){
	 					$errorMessage .= "Entry ". ($lineCounter+2). ": the username ".$username." should be shorter or equal to 30 characters. <br />";
		 			 }
					 if(strlen($name) >= 50){
						 $errorMessage .= "Entry ". ($lineCounter+2). ": the name ".$name." should be shorter or equal to 50 characters. <br />";
					 }
					 if(strlen($email) >= 50){
						 $errorMessage .= "Entry ". ($lineCounter+2). ": the email ".$email." should be shorter or equal to 50 characters. <br />";
					 }
					 
					 // validate the content of the username that should be alphanumeric without space
					 if(ctype_alnum($username) == false){
							$errorMessage .= "Entry ". ($lineCounter+2). ": the username ".$username." should contain only alphabets and/or numbers. <br />";
					 }
					 
					 // validate the content of the name that should be alphanumeric and space
					 if(preg_match('/^[a-z0-9 .\-]+$/i', $name) == false){
							$errorMessage .= "Entry ". ($lineCounter+2). ": the name ".$name." should contain only alphabets, numbers, and/or space. <br />";
					 }

					// create an array entry for this
					$dataList[$lineCounter] = $line;

					$lineCounter++;
  			}

				// close the file
				fclose($file);

				if($errorMessage == ""){
					// no error, proceed to storing the data
					for($i=0;$i<$lineCounter;$i++){
						// encrypt the password
		        $mypass = password_hash($dataList[$i][2], PASSWORD_DEFAULT);
		        // add the entry
		        $sql = "INSERT INTO user (username, password, name, email, role)
						 VALUES ('".$dataList[$i][0]."', '".$mypass."', '".$dataList[$i][1]."', '".$dataList[$i][3]."', 'student')";
			      if ($db->query($sql) === FALSE) {
			        echo "Error adding record: " . $db->error;
			      }
					}

					// redirect to dashboard
					header('Location: admin_students.php');
					exit;
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
		.btn-danger{
			background: #f56976 !important ;
		}
		.btn-primary{
			background: #a8c6e7 !important ;
			color: black  !important ;
		}
	</style>
  </head>
  <body>
<?php setHeaderAdmin("students", "Add student"); ?>

<div class="container ">
			<div class="row d-flex justify-content-center align-items-center" style="min-height:50vh">
				<div class="col-md-6">
		<form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data">
			<div class="form-group">
				<?php
					// if error message exist, show it
					if(isset($errorMessage) && $errorMessage != ""){
						// show the message
						echo "<div class='alert alert-danger'>Error(s):<br />".$errorMessage."</div>";
						// unset afterward
						$errorMessage = "";
					}
				 ?>
				<div class="form-group mb-4">
					<label for="uname"><b>Download the template <a href="bulk_template.csv">here</a>, fill the data, and upload it.</b></label>
				</div>
				<div class="form-group my-3">
					<label for="uname"><b>CSV file:</b></label>
					<input type="file" class="form-control" placeholder="Select the CSV file" name="ufile" required>
				</div>
			</div>

			<div class="form-group">
				<a href="admin_students.php" class="btn w-25  btn-danger">Cancel</a>
				<button class="btn w-25 btn-primary" type="submit">Upload</button>
			</div>
		</form>
		<script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
