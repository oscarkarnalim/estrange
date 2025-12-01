<?php
    date_default_timezone_set("Asia/Jakarta");
    		
	session_start();
	include("_config.php");
	
	if($_SERVER["REQUEST_METHOD"] == "POST"){ 
		 if(isset($_POST['logout']) == true){
			 // for logout in any pages
			// remove all session variables
			session_unset();

			// destroy the session
			session_destroy();

			// redirect to home
			header('Location: index.php');
			exit;
		 }else{
			// for submitting response to a quiz
			$message = "";
			
			// get the data from form
			$response = mysqli_real_escape_string($db,$_POST['response']);
			
			// check whether the response is correct
			$isCorrect = 0;
			foreach ($_SESSION["q_answers"] as $answer){
				if($answer == $response){
					$isCorrect = 1;
				}
			}
			
			if($isCorrect){
				$message .="<div class='information'>";
				$message .=($human_language == 'en'? "Your response is correct!<br /><br/>": "Jawaban kamu benar!<br /><br/>");
			}
			else{
				$message .="<div class='warning'>";
				$message .=($human_language == 'en'? "Your response is incorrect!<br /><br/>": "Jawaban kamu salah!<br /><br/>");
			}
			$message .=($human_language == 'en'? "Question:<br/>".$_SESSION["q_question"]."<br /><br/>Expected response(s):<br/>": "Pertanyaan:<br/>".$_SESSION["q_question"]."<br /><br/>Jawaban:<br/>");
			
			// add the answers
			foreach($_SESSION["q_answers"] as $answer){
				$message .= ($answer . ",");
			}
			// remove the last comma
			$message = substr($message,0,strlen($message) - 1);
			
			$message .="</div>";
			
			// get last attempt
        	$sql = "SELECT response_time FROM instant_quiz_response_history
        		WHERE student_id = '".$_SESSION['user_id']."' ORDER BY response_time DESC LIMIT 1";
        	$result = mysqli_query($db,$sql);
        	
        	
        	$timeDiffLastSunday = 0; $timeDiffLastResponse = -1;
        	if ($result->num_rows > 0) {
        		$row = $result->fetch_assoc();
        
        		// check whether the user has responded to the quiz this week
        		$lastResponseTime = strtotime($row['response_time']);
        		$lastSundayTime = strtotime('last sunday');
        		$nowTime = strtotime('now');
        		
        		
        		$timeDiffLastSunday = ($nowTime - $lastSundayTime) / (60*60*24);
        		$timeDiffLastResponse = ($lastResponseTime - $lastSundayTime) / (60*60*24);
        	}
			
			if($timeDiffLastSunday < 7 && $timeDiffLastResponse < 0){
    			$sql = "INSERT INTO instant_quiz_response_history (student_id, question_id, is_correct)
    				 VALUES ('".$_SESSION['user_id']."', '".$_SESSION["q_id"]."', '".$isCorrect."')";
    			if ($db->query($sql) === TRUE) {
    				// if updated well, do nothing for now
    			} else {
    				echo "Error adding record: " . $db->error;
    			}
			}
		}
	
	}

	// part of sessionchecker pasted here due to unique behaviour of this page	
	if(isset($_SESSION['name']) == false){
		// redirect if it is not logged in
		header('Location: student_dashboard.php');
		exit;
	}else{
	  // check whether the role is similar to the opened pages

	  // get the page role
	  $pagerole = htmlentities($_SERVER['PHP_SELF']);
	  $pagerole = substr($pagerole, strrpos($pagerole,'/')+1);
	  $pagerole = substr($pagerole, 0, strpos($pagerole,'_'));

	  // check whether the page is user specific
	  if($pagerole != 'user'){
		// if it is in different role
		if($pagerole != $_SESSION['role']){
		  // redirect to its dashboard
		  if ($_SESSION['role'] == 'admin'){
			header('Location: admin_dashboard.php');
		  } else if ($_SESSION['role'] == 'lecturer'){
			header('Location: lecturer_dashboard.php');
		  } else if ($_SESSION['role'] == 'student'){
			header('Location: student_dashboard.php');
		  }
		  exit;
		}
	  }
	}

	
	
	$studentId = $_SESSION['user_id'];

	// get number of correct attempts and incorrect ones
	$sql = "SELECT is_correct, COUNT(question_id)  AS tot FROM instant_quiz_response_history
		WHERE student_id = '".$studentId."' AND response_time > DATE_SUB(now(), INTERVAL 6 MONTH) GROUP BY is_correct";
	$result = mysqli_query($db,$sql);
	$correctAttempts = 0;
	$incorrectAttempts = 0;
	while($row = $result->fetch_assoc()) {
		$temp = $row['is_correct'];
		if($temp == 1){
			$correctAttempts = $row['tot'];
		}else{
			$incorrectAttempts = $row['tot'];
		}
	}
	
	// get last attempt
	$sql = "SELECT response_time FROM instant_quiz_response_history
		WHERE student_id = '".$studentId."' ORDER BY response_time DESC LIMIT 1";
	$result = mysqli_query($db,$sql);
	
	
	$timeDiffLastSunday = 0; $timeDiffLastResponse = -1;
	if ($result->num_rows > 0) {
		$row = $result->fetch_assoc();

		// check whether the user has responded to the quiz this week
		$lastResponseTime = strtotime($row['response_time']);
		$lastSundayTime = strtotime('last sunday');
		$nowTime = strtotime('now');
		
		
		$timeDiffLastSunday = ($nowTime - $lastSundayTime) / (60*60*24);
		$timeDiffLastResponse = ($lastResponseTime - $lastSundayTime) / (60*60*24);
	}
	
	$isValid = false;
	$hasQuestion = false;
	if($timeDiffLastSunday < 7 && $timeDiffLastResponse < 0){
		// if more than one week, and last response time is earlier than this week, set valid to take quiz
		$isValid = true;
		
		// get maximum ID
		$sql = "SELECT MAX(question_id) AS maks FROM instant_quiz_bank";
		$result = mysqli_query($db,$sql);	
		$row = $result->fetch_assoc();
		$maks = $row['maks'];
		
		$question = "";
		$answerOptions = array();
		$answers = array();
		
		if($maks != ""){
		    $hasQuestion = true;
    		while(true){
    			$questionID = rand(0,$maks)+1;
    			
    			$sqlt = "SELECT question, answer_options, answers FROM instant_quiz_bank WHERE question_id = " .$questionID;
    			$resultt = mysqli_query($db,$sqlt);	
    			if ($resultt->num_rows > 0) {
    				$rowt = $resultt->fetch_assoc();
    				$question = $rowt['question'];
    				$answerOptions = explode(",",$rowt['answer_options']);
    				$_SESSION["q_question"] = $question; // set in a session var
    				$_SESSION["q_answers"] = explode(",",$rowt['answers']); // set in a session var
    				$_SESSION["q_id"] = $questionID; // set in a session var
    				break;
    			}
    		}	    
		}
	}
?>
<html>
	<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">

		<title> E-STRANGE: Instant quiz</title>
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

		<link rel="icon" href="strange_html_layout_additional_files/icon.png">
		<!-- The use of Notyf library https://github.com/caroso1222/notyf -->
		<link rel="stylesheet" href="strange_html_layout_additional_files/notyf.min.css">
		<script src="strange_html_layout_additional_files/notyf.min.js"></script>
		<script type="text/javascript">
			function loadGameNotif(){
				// Create an instance of Notyf
				var notyf = new Notyf({
				  duration: 5000,
				  position: {
					x: 'center',
					y: 'top',
				  },
				  dismissible: true
				});
				
	<?php
			 if(isset($_GET['submit'])){ // landed from submitting the code without login
				if($isValid == false){
					echo "notyf.success('Code submitted! Check your progress on instant quizzes!');";
				}else{
					echo "notyf.success('Code submitted! If interested, respond to the instant quiz below!');";
				}
			 }
		?>
			}
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
			.form-control {
				border: 2px solid #000;	
				border-radius: 8px;
			}
		</style>
	</head>
  <body onload="loadGameNotif()">
		<?php
		  setHeaderStudent("quiz", "Instant quiz");
		?>
		<div class="container mt-3">
			<div class="row d-flex justify-content-center align-items-center" style="min-height: 60vh">
				<div class="col-md-6">
					<div class="card mx-auto w-100 border-0">	
						<form class="w-100" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data">
							<div class="formbody">
								<?php
									// if error message exist, show it
									if(isset($message) && $message != ""){
										// show the message
										echo $message;
										// unset afterward
										$message = "";
									}
								?>
								<div class="form-group mt-3">
									<div class="infolabel fs-4"><b><?php echo ($human_language == 'en'? "Correct attempts": "Jumlah benar") ?> :</b></div>
									<div class="infovalue"><?php echo $correctAttempts. " / ". ($incorrectAttempts+$correctAttempts);?></div>
								</div>
							<?php 

							if($isValid == true && $hasQuestion == true){
								// show the question
								echo '	<div class="form-group mt-3">
											<label><b>'.($human_language == 'en'? "Question": "Pertanyaan").':</b></label>
											<div class="infovalue">'.$question.'</div>
										</div>
										<div class="form-group mt-3">
											<div class="infolabel fs-4"><b>'.($human_language == 'en'? "Answer options": "Pilihan jawaban").':</b></div>
											<div class="infovalue">';
								foreach ($answerOptions as $option){
									echo "<input type=\"radio\" name=\"response\" value=\"".$option."\" required />".$option."<br /><br />";
								}
									
								echo '		</div>
										</div>';
							}else if($isValid == true && $hasQuestion == false){
								// no questions
								if($human_language == 'en'){
									echo '<div class="form-group mt-3">
											<div class="infolabel fs-4"><b>Information:</b></div>
											<div class="infovalue">No questions are available for this course!</div>
										</div>';
								}else{
									echo '<div class="form-group mt-3">
											<div class="infolabel fs-4"><b>Informasi:</b></div>
											<div class="infovalue">Tidak ada pertanyaan yang tersedia untuk mata pelajaran ini!</div>
										</div>';
								}
								$isValid = false;
							}else{
								// if not valid
								if($human_language == 'en'){
									echo '<div class="form-group mt-3">
											<div class="infolabel fs-4"><b>Information:</b></div>
											<div class="infovalue">You have participated in this week\'s quis. Kindly wait for next week\'s quiz!</div>
										</div>';
								}else{
									echo '<div class="form-group mt-3">
											<div class="infolabel fs-4"><b>Informasi:</b></div>
											<div class="infovalue">Kamu telah berpartisipasi dalam kuis minggu ini. Silakan menunggu kuis baru di minggu depan!</div>
										</div>';
								}
								
							}
							?>
						</div>
						<div class="formrowaction mt-3">
								<?php
									if($isValid == true){
										echo '<a href="student_dashboard.php" class="btn btn-secondary me-2">Skip</a>';
										echo '<button class="btn btn-primary" type="submit">Submit</button>';
									}else{
										echo '<a href="student_dashboard.php" class="btn btn-primary">Go to dashboard</a>';
									}
								?>
								
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>

		<script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
