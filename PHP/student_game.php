<?php
	include("_sessionchecker.php");
	include("_config.php");
	
	$courseID = null;
	$prizeText = null;
	
	// if it has a course id attached in the url, set the course id
	if(isset($_GET['id']) == true && $_GET['id'] != ''){
		$courseID = mysqli_real_escape_string($db,$_GET['id']);
		
		// check if the student is enrolled to that course and game feature is on for that course
		$sql = "SELECT enrollment.course_id FROM enrollment
			INNER JOIN game_course ON game_course.course_id = enrollment.course_id 
			WHERE game_course.is_active = 1 
			AND enrollment.student_id = '".$_SESSION['user_id']."' 
			AND enrollment.course_id = '".$courseID."'";
		$result = mysqli_query($db,$sql);
		if ($result->num_rows == 0) {
			// if the student is not enrolled to given course, redirect to dashboard
			header('Location: student_dashboard.php');
			exit;
		}
	}
	
	// check if the student is enrolled in at least one course with game feature
	$sql = "SELECT course.course_id, course.name, 
			game_course.prize_text FROM course 
			INNER JOIN game_course ON game_course.course_id = course.course_id 
			INNER JOIN game_student_course ON game_student_course.course_id = course.course_id
			WHERE game_course.is_active = 1 
			AND game_student_course.student_id = '".$_SESSION['user_id']."' ";
	$result = mysqli_query($db,$sql);
	if ($result->num_rows == 0) {
		// if the student is not enrolled to at least one gamified course, redirect to student_nogame
		header('Location: student_no_game.php');
		exit;
	}
	
	// for access statistics of game page
	$sql = "INSERT INTO game_access (student_id, type) VALUES ('".$_SESSION['user_id']."','main_page_visit')";
	$db->query($sql);
?>
<html>
	<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">

		<title> E-STRANGE: Student game</title>
    <link rel="icon" href="strange_html_layout_additional_files/icon.png">
	<link href="bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Untuk Icon -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
	

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.slim.min.js" integrity="sha256-kmHvs0B+OpCW5GVHUNjv9rOmY0IvSIRcf7zGUDTDQM8=" crossorigin="anonymous"></script>

<!-- DataTables JS -->
<link rel="stylesheet" type="text/css" href="datatables/jquery.dataTables.min.css">
<script type="text/javascript" src="datatables/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="datatables/responsive.bootstrap5.min.css">
<script type="text/javascript" src="datatables/dataTables.responsive.min.js"></script>
<script type="text/javascript" src="datatables/responsive.bootstrap5.min.js"></script>

	
        <script src=
"https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js">
    </script>
    <script src="./chartjs/chart.js"></script>
    
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
	.buttontambah{
		text-align: right;
	}
	@media (max-width: 425px) {
		.buttontambah{
			text-align: left;
			margin: 1rem 0 1rem 0;
		}
		tr td{
			font-size: 0.88em;
		}
	}
	</style>
  </head>
  <body>
		<?php
		  setHeaderStudent("game", "Student game");
		?>

		<div class="container bodycontent mt-4">
			<div class="coursetitle">
				<?php 
					// this section uses result from the top of this code
					// it lists all gamified courses where the student is enrolled to
					echo 'Course: <select name="course" id="course" class="form-control courseselect" onchange="updateDisplayedGameDataBasedOnCourse()">';
					while($row = $result->fetch_assoc()) {
						// set the courseID if it has not been set
						if($courseID == null)
							$courseID = $row['course_id'];
						
						// echo all of the options
						echo "<option value=\"".$row['course_id']."\" ";
						// for selected entry, get also the prize text data
						if($courseID == $row['course_id']){
							echo "selected ";
							$prizeText = $row['prize_text'];
						}
						echo ">";
						echo $row['name']." </option>";
						
						
						
					}
					echo "</select>";
				?>
			</div>
			
			<?php 
    	    					// to know whether the student is participating in the game for this course
    		$isParticipating = false;
    		$gsID = -1;
    		
    		// check participation
    		$sql = "SELECT gs_id, is_participating
    				FROM game_student_course 
    				WHERE student_id = '".$_SESSION['user_id']."' 
    				AND course_id = '".$courseID."'";
    				
            //DEBUG
            // echo "<pre>SQL Query: $sql</pre>";
            
    		$result = mysqli_query($db,$sql);


    		if ($result->num_rows > 0) {
    			$row = $result->fetch_assoc();
    			// set whether the student is participating
    			if($row['is_participating'] == 1)
    				$isParticipating = true;
    			$gsID = $row['gs_id'];
    		}
    		
    		
    		?>
			
			
            
		    <?php if ($isParticipating == true): ?>
		       
		    <div class="row d-flex justify-content-center mt-4" style="min-height:10vh">
		        <div class="col-md-12 mb-3">
		        <div class="row d-flex justify-content-center">
			        <div class="col-md-12 fs-2 fw-bold text-center" id ="leaderboard-title"> 
				Leaderboard 
			</div>
			            <div class="col-md-12">
				<div class="tablecontainer">
					<table id="leaderboard" class="table table-bordered table-striped responsive nowrap"  style="width:100%">
						<thead>
							<tr>
								<th style='width:5%'>Rank</th>
								<th>Student</th>
								<th>General</th>
								<th>Originality</th>
								<th>Quality</th>
								<th>Efficiency</th>
								<th>Quiz</th>
							</tr>
						</thead>
						<tbody>
                            <?php
                                $counter = 1;
                                $students = array(); // Menyimpan semua siswa
                        
                                // Variabel untuk user tertentu
                                $uSubmissionPoints = 0;
                                $uQualityPoints = 0;
                                $uEfficiencyPoints = 0;
                                $uQuizPoints = 0;
                                $uArrAssessmentNames = "";
                                $uArrSubmissionPoints = "";
                                $uArrQualityPoints = "";
                                $uArrEfficiencyPoints = "";
                                $allSubmissionPoints = 0;
                                $allQualityPoints = 0;
                                $allEfficiencyPoints = 0;
                                $allQuizPoints = 0;
                        
                                // Ambil semua siswa yang berpartisipasi
                                $sql = "SELECT user.username, user.name, game_student_course.gs_id, game_student_course.student_id 
                                        FROM game_student_course 
                                        INNER JOIN user ON user.user_id = game_student_course.student_id 
                                        WHERE game_student_course.course_id = '".$courseID."' 
                                        AND game_student_course.is_participating = 1";
                        
                                $result = mysqli_query($db, $sql);
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        $mySubmissionPoints = 0;
                                        $myEfficiencyPoints = 0;
                                        $myQualityPoints = 0;
                                        $myQuizPoints = 0;
                        
                                        // Ambil nilai submission
                                        $sqlt = "SELECT user.user_id AS id, ROUND(AVG(suspicion.originality_point),0) as orig, 
                                                ROUND(AVG(suspicion.efficiency_point),0) as eff, ROUND(AVG(code_clarity_suggestion.quality_point),0) as qual, 
                                                assessment.assessment_id as asmt_id, assessment.name as asmt_name 
                                                FROM suspicion  
                                                INNER JOIN submission ON submission.submission_id = suspicion.submission_id 
                                                INNER JOIN user ON user.user_id = submission.submitter_id 
                                                INNER JOIN assessment ON assessment.assessment_id = submission.assessment_id 
                                                INNER JOIN course ON course.course_id = assessment.course_id 
                                                LEFT JOIN code_clarity_suggestion ON code_clarity_suggestion.submission_id = submission.submission_id 
                                                WHERE user.user_id = '".$row['student_id']."' 
                                                AND course.course_id = '".$courseID."' 
                                                GROUP BY assessment.assessment_id";
                        
                                        $resultt = mysqli_query($db, $sqlt);
                                        if ($resultt->num_rows > 0) {
                                            while ($rowt = $resultt->fetch_assoc()) {
                                                if($rowt['qual'] == NULL)
                                                    $rowt['qual'] = 100;
                                                $mySubmissionPoints += $rowt['orig'];
                                                $myEfficiencyPoints += $rowt['eff'];
                                                $myQualityPoints += $rowt['qual'];
                                                    
                                                // Total semua pengguna
                                                $allSubmissionPoints += $rowt['orig'];
                                                $allEfficiencyPoints += $rowt['eff'];
                                                $allQualityPoints += $rowt['qual'];
                        
                                                // Jika user saat ini, hitung poinnya
                                                if ($rowt['id'] == $_SESSION['user_id']) {
                                                    $uSubmissionPoints += $rowt['orig'];
                                                    $uEfficiencyPoints += $rowt['eff'];
                                                    $uQualityPoints += $rowt['qual'];
                                                    $uArrAssessmentNames .= ",'".$rowt['asmt_name']."'";
                                                    $uArrSubmissionPoints .= ",".$rowt['orig'];
                                                    $uArrEfficiencyPoints .= ",".$rowt['eff'];
                                                    $uArrQualityPoints .= ",".$rowt['qual'];
                                                }
                                            }
                                        }
                        
                                        // Ambil nilai quiz
                                        $sqlt = "SELECT COUNT(question_id) AS tot FROM instant_quiz_response_history
                                                 WHERE student_id = '".$row['student_id']."' AND is_correct = 1 
                                                 AND response_time > DATE_SUB(now(), INTERVAL 6 MONTH)";
                                        $resultt = mysqli_query($db, $sqlt);
                                        $rowt = $resultt->fetch_assoc();
                                        $myQuizPoints = $rowt['tot'] * 100;
                                        $allQuizPoints += $rowt['tot'] * 100;
                                        if ($row['student_id'] == $_SESSION['user_id']) {
                                            $uQuizPoints = $rowt['tot'] * 100;
                                        }
                        
                                        $totalPoints = $mySubmissionPoints + $myQualityPoints + $myEfficiencyPoints + $myQuizPoints;
                        
                                        // Simpan semua siswa ke dalam array
                                        $students[] = array(
                                            'student_id' => $row['student_id'],
                                            'username' => $row['username'],
                                            'name' => $row['name'],
                                            'totalPoints' => $totalPoints,
                                            'mySubmissionPoints' => $mySubmissionPoints,
                                            'myQualityPoints' => $myQualityPoints,
                                            'myEfficiencyPoints' => $myEfficiencyPoints,
                                            'myQuizPoints' => $myQuizPoints
                                        );
                                    }
                        
                                    // Urutkan berdasarkan totalPoints (descending)
                                    usort($students, function ($a, $b) {
                                        return $b['totalPoints'] - $a['totalPoints'];
                                    });
                        
                                    // Ambil hanya 10 siswa teratas untuk ditampilkan
                                    foreach (array_slice($students, 0, 10) as $student) {
                                        echo "<tr class='content' id='".$student['student_id']."' onclick=\"selectRow('".$student['student_id']."','sumtablecontent')\">
                                            <td style='width:5%'>$counter</td>
                                            <td>".$student['username']." / ".$student['name']." / Lv. ".(1+intval($student['totalPoints']/500))."</td>
                                            <td>".$student['totalPoints']."</td>
                                            <td>".$student['mySubmissionPoints']."</td>
                                            <td>".$student['myQualityPoints']."</td>
                                            <td>".$student['myEfficiencyPoints']."</td>
                                            <td>".$student['myQuizPoints']."</td>
                                        </tr>";
                                        $counter++;
                                    }
                        
                                    // Rata-rata nilai untuk semua user
                                    if ($result->num_rows > 0) {
                                        $allSubmissionPoints = round($allSubmissionPoints / $result->num_rows);
                                        $allEfficiencyPoints = round($allEfficiencyPoints / $result->num_rows);
                                        $allQualityPoints = round($allQualityPoints / $result->num_rows);
                                        $allQuizPoints = round($allQuizPoints / $result->num_rows);
                                    }
                                } else {
                                    echo "<tr><td colspan='7'>No data available</td></tr>";
                                }
                            ?>
                        </tbody>

					</table>
				</div>
			</div>
					</div>
	        </div>
			</div>
			<?php else: ?>
            <style>
                #leaderboard-title,
                #leaderboard {
                    display: none;
                }
                .gdescr{
                    margin-top:1rem;
                }
                
            </style>
			<?php endif; ?>

			<div class="row d-flex justify-content-center  ">
				<div class="col-md-12 fs-2 mb-4 fw-bold text-center gdescr"> 
					Game Description 
				</div>
				<div class="col-md-4"> 
					<?php 
										// check participation
						$sql = "SELECT gs_id, collaboration_score, is_participating
								FROM game_student_course 
								WHERE student_id = '".$_SESSION['user_id']."' 
								AND course_id = '".$courseID."'";
						$result = mysqli_query($db,$sql);
						if ($result->num_rows > 0) {
							$row = $result->fetch_assoc();
							// set whether the student is participating
							if($row['is_participating'] == 1)
								$isParticipating = true;
							$gsID = $row['gs_id'];
						}
					    // get overall points
						$totalPoints = $uSubmissionPoints +$uQualityPoints + $uEfficiencyPoints + $uQuizPoints;
						// check if the student is participating in the game for this course
						if($isParticipating == false){
							// echo the text based on selected language
							if($human_language == 'en'){
								echo "<p><b>You are not participating in the game for this course.</b> If you are interested to join, please click \"Turn on game feature\" button on the right.</p>";
							}else{
								echo "<p><b>Kamu belum berpartisipasi dalam game di mata kuliah ini.</b> Jika kamu tertarik untuk berpartisipasi, silakan klik tombol \"Turn on game feature\" di sebelah kanan.</p>";
							}
						}else{
						    // show general statistic summary in selected language
    						if($human_language == 'en'){
    							echo "<p><b>Student level:</b> ".(1+intval($totalPoints/500))."<br />".(500-intval($totalPoints%500))." points more to go to the next level!<br/></p>";
    							
    							echo "<p>For this course, you have <b>".$totalPoints." total points</b>!";
    							if($uSubmissionPoints > 0)
    								echo "<br/>&nbsp;&nbsp;&nbsp;&nbsp; <b>".$uSubmissionPoints." points</b> for the originality aspect (uniqueness of the work)";
    							if($uQualityPoints > 0)
    								echo "<br/>&nbsp;&nbsp;&nbsp;&nbsp; <b>".$uQualityPoints." points</b> for the quality aspect (easiness to further expand the work)";
    							if($uSubmissionPoints > 0)
    								echo "<br/>&nbsp;&nbsp;&nbsp;&nbsp; <b>".$uEfficiencyPoints." points</b> for the efficiency aspect (environment friendly)";
    							if($uQuizPoints > 0){
    								echo "<br/>&nbsp;&nbsp;&nbsp;&nbsp; <b>".($uQuizPoints)." points</b> for responding to ";
    								if($uQuizPoints/100 <= 1)
    									echo ($uQuizPoints/100)." instant quiz correctly";
    								else 
    									echo ($uQuizPoints/100)." instant quizzes correctly";
    							}
    							echo "</p>";
    						}else{
    							echo "<p><b>Level siswa:</b> ".(1+intval($totalPoints/500))."<br />".(500-intval($totalPoints%500))." poin lagi untuk ke level berikutnya!<br/></p>";
    							
    							echo "<p>Untuk mata kuliah ini, kamu memiliki <b>".$totalPoints." total poin</b>!";
    							if($uSubmissionPoints > 0)
    								echo "<br/>&nbsp;&nbsp;&nbsp;&nbsp; <b>".$uSubmissionPoints." poin</b> untuk aspek originalitas (keunikan pekerjaan)";
    							if($uQualityPoints > 0)
    								echo "<br/>&nbsp;&nbsp;&nbsp;&nbsp; <b>".$uQualityPoints." poin</b> untuk aspek kualitas (kemudahan pekerjaan untuk dikembangkan lebih lanjut)";
    							if($uEfficiencyPoints > 0)
    								echo "<br/>&nbsp;&nbsp;&nbsp;&nbsp; <b>".$uEfficiencyPoints." poin</b> untuk aspek efisiensi (ramah lingkungan)";
    							if($uQuizPoints > 0)
    								echo "<br/>&nbsp;&nbsp;&nbsp;&nbsp; <b>".$uQuizPoints." poin</b> untuk menjawab ".($uQuizPoints/100) ." kuis instan dengan benar";
    							echo "</p>";
    
    						}
								echo "
								<button class='btn btn-primary w-100' type='button' data-bs-toggle='offcanvas' data-bs-target='#offcanvasBottom' aria-controls='offcanvasBottom'>Show Statistics</button>

								<div class='offcanvas offcanvas-bottom h-100' tabindex='-1' id='offcanvasBottom' aria-labelledby='offcanvasBottomLabel'>
								<div class='offcanvas-header'>
									<h5 class='offcanvas-title' id='offcanvasBottomLabel'>User Statistics</h5>
									<button type='button' class='btn-close' data-bs-dismiss='offcanvas' aria-label='Close'></button>
								</div>
								<div class='offcanvas-body small'>
									<div class='row d-flex justify-content-center align-items-center'>
										<div class='col-md-9'>
											<div class='row d-flex justify-content-center align-items-center'>
												<div class='col-md-6'>
													<div class='canvradar'>
														<canvas id='radarChart'></canvas>
													</div>
												</div>
												<div class='col-md-6'>
													<div class='canvline'>
														<canvas id='lineChart'></canvas>
													</div>
												</div>
											</div>
										</div>
									</div >
									<script>
										let ctx = 
											document.getElementById('radarChart').getContext('2d');
										let myRadarChart = new Chart(ctx, {
											type: 'radar',
											data: {
												labels:
													['Originality', 'Quality', 'Efficiency',
													'Quiz'],
												datasets: [{
													label: 'Yours',
													data: [".$uSubmissionPoints.", ".$uQualityPoints.", ".$uEfficiencyPoints.", ".$uQuizPoints."],
													backgroundColor: 'rgba(75, 192, 192, 0.2)',
													borderColor: 'rgba(75, 192, 0, 1)',
													borderWidth: 2,
												},
												{
													label: 'Average users',
													data: [".$allSubmissionPoints.", ".$allQualityPoints.", ".$allEfficiencyPoints.", ".$allQuizPoints."],
													backgroundColor: 'rgba(255, 99, 132, 0.2)',
													borderColor: 'rgba(255, 99, 132, 1)',
													borderWidth: 2,
												}]
											},
											options: {
												plugins: {
													title: {
														display: true,
														text: 'Point Statistics'
													}
												}
											}
										});
										
										const xValues = [".substr($uArrAssessmentNames,1)."];
										new Chart('lineChart', {
										type: 'line',
										data: {
											labels: xValues,
											datasets: [{ 
											label: 'Originality',
											data: [".substr($uArrSubmissionPoints,1)."],
											borderColor: 'blue',
											fill: false
											}, { 
											label: 'Quality',
											data: [".substr($uArrQualityPoints,1)."],
											borderColor: 'green',
											fill: false
											}, { 
											label: 'Efficiency',
											data: [".substr($uArrEfficiencyPoints,1)."],
											borderColor: 'red',
											fill: false
											}]
										},
										options: {
											plugins: {
												title: {
													display: true,
													text: 'Progress'
												}
											},
											scales: {
											x: {
												ticks: {
												autoSkip: false,
												maxRotation: 90,
												minRotation: 90
												}
											}
											}
										}
										});
									</script>
								</div>
								</div>
								
								";
							
						}
						
						
					?>
				</div>
				<div class="col-md-8">
			
					<div class="col-md-12">
						<div class="gameexplanation" style="font-size:0.9em"> 
							<?php 
								
								echo "<p><b>Prize</b>: ".$prizeText."</p>";
								
								// showing general rules how to obtain points in preferred language
								if($human_language == 'en'){
									echo "<p>Students will obtain more game points by submitting original, high-quality, and efficient programs. Having original programs means the students are less likely to be involved in plagiarism or collusion.
									Having high-quality programs means the students know how to write maintainable programs. Having efficient programs means students know how to write environment-friendly programs. 
									The points will be averaged if students do multiple submissions for a particular assessment. </p>
									<p>Students can also get 100 more points for each correct response to instant quizzes relevant to the three aspects (originality, quality, and efficiency).
									It is worth noting that only quiz responses recorded no later than six months ago are considered.</p>";
									echo '<p>Students can turn off the game feature. Their points will be hidden from anyone (but still recorded so the students can rejoin at any time without losing any points). </p>';
								}else{
									echo "<p>Siswa akan mendapatkan poin permainan lebih dengan mengumpulkan program yang orisinil, berkualitas tinggi, dan efisien. Memiliki program orisinil berarti siswa terkait kurang mungkin terlibat dalam plagiarisme atau kolusi. 
									Memiliki program berkualitas tinggi berarti siswa terkait mengerti cara menulis program yang dapat dipelihara. Memiliki program efisien berarti siswa terkait mengerti cara menulis program yang ramah lingkungan.
									Poin-poin tersebut akan direrata jika siswanya memiliki beberapa program untuk sebuah tugas. </p>
									<p>Siswa juga dapat memperoleh 100 poin lagi untuk setiap jawaban benar terhadap kuis instan yang relevan terhadap ketiga aspek tersebut (originalitas, kualitas, dan efisiensi).
									Perlu diketahui bahwa respon kuis yang diambil dalam enam bulan terakhir saja yang diperhitungkan.</p>";
									echo '<p>Siswa dapat mematikan fitur permainan. Poin nya akan disembunyikan dari siswa lain (namun tetap disimpan sehingga siswa dapat ikut kembali tanpa kehilangan poin). </p>';
									
								}
							?>
						</div>
					</div>
					<?php
					// generating button to toggle game feature
					$text = "Turn off game feature";
					if($isParticipating == false)
						$text = "Turn on game feature";
					echo '<form class="invisform "  action="student_game_toggle.php" method="post">
							<input type="hidden" name="id" value="'.$gsID.'">
							<input type="hidden" name="is_participating" value="'.$isParticipating.'">
							<input type="hidden" name="course_id" value="'.$courseID.'">
							<button class="btn btn-primary " type="submit">'.$text.'</button>
						</form>';
				?>
				</div>
			
			</div>
		</div>

		<script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
		<script>
        	document.addEventListener("DOMContentLoaded", function () {
        		let table = new DataTable('#leaderboard', {
        			responsive: true,
        			order: [], 
        			paging: false, 
        			searching: false 
        		});
        	});
        </script>
  </body>
</html>
