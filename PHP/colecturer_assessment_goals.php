<?php
	include("_sessionchecker.php");
	include("_config.php");
	
	$courseID = null;
	$courseName = null;
	
	// if it has a course id attached in the url, set the course id
	if(isset($_GET['id']) == true && $_GET['id'] != ''){
		$courseID = mysqli_real_escape_string($db,$_GET['id']);
		
		$sql = "SELECT course.name FROM course 
				INNER JOIN game_course ON game_course.course_id = course.course_id 
				INNER JOIN colecturer ON colecturer.course_id = course.course_id 
				WHERE course.course_id = '".$courseID."' AND game_course.is_active = 1 AND 
				(course.creator_id = '".$_SESSION['user_id']."' OR colecturer.user_id = '".$_SESSION['user_id']."')";
		$result = mysqli_query($db,$sql);
		if ($result->num_rows > 0) {
			// if the lecturer is in charge of that course and the course offers game feature, get the course name
			$row = $result->fetch_assoc();
			$courseName = $row["name"];
		}else{
			// otherwise, redirect to dashboard
			header('Location: colecturer_courses.php');
			exit;
		}
		
	}else{
		// if there is no courseID, redirect to dashboard
		header('Location: colecturer_courses.php');
		exit;
	}
	
?>
<html>
	<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
		<title> E-STRANGE: Course game</title>
    <link rel="icon" href="strange_html_layout_additional_files/icon.png">
	<link rel="stylesheet" href="w3.css">
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

   
    <script>
			// sort table content. Copied and modified from https://www.w3schools.com/howto/howto_js_sort_table.asp
			function sortTable(n, tableId, isNumber, tableContainerId) {
				var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
				table = document.getElementById(tableId);
				switching = true;
				// Set the sorting direction to ascending:
				dir = "asc";
				/* Make a loop that will continue until
				no switching has been done: */
				while (switching) {
					// Start by saying: no switching is done:
					switching = false;
					rows = table.rows;
					/* Loop through all table rows */
					for (i = 0; i < (rows.length - 1); i++) {
						// Start by saying there should be no switching:
						shouldSwitch = false;
						/* Get the two elements you want to compare,
						one from current row and one from the next: */
						x = rows[i].getElementsByTagName("TD")[n];
						y = rows[i + 1].getElementsByTagName("TD")[n];
						if(n==0){
							/*
							* the column content is encapsulated with a link and can provide confusing result
							* as the <A> tag is considered in comparison
							*/
							x = x.getElementsByTagName("A")[0];
							y = y.getElementsByTagName("A")[0];
						}
						/* Check if the two rows should switch place,
						based on the direction, asc or desc: */
						if (dir == "asc") {
							if(isNumber == true){
								numx = Number(x.innerHTML.split(" ")[0]);
								numy = Number(y.innerHTML.split(" ")[0]);
								if (numx > numy ){
									// If so, mark as a switch and break the loop:
									shouldSwitch = true;
									break;
								}
							}else{
								if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
									// If so, mark as a switch and break the loop:
									shouldSwitch = true;
									break;
								}
							}
						} else if (dir == "desc") {
							if(isNumber == true){
								numx = Number(x.innerHTML.split(" ")[0]);
								numy = Number(y.innerHTML.split(" ")[0]);
								if (numx < numy ){
									// If so, mark as a switch and break the loop:
									shouldSwitch = true;
									break;
								}
							}else{
								if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
									// If so, mark as a switch and break the loop:
									shouldSwitch = true;
									break;
								}
							}
						}
					}
					if (shouldSwitch) {
						/* If a switch has been marked, make the switch
						and mark that a switch has been done: */
						rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
						switching = true;
						// Each time a switch is done, increase this count by 1:
						switchcount ++;
					} else {
						/* If no switching has been done AND the direction is "asc",
						set the direction to "desc" and run the while loop again. */
						if (switchcount == 0 && dir == "asc") {
							dir = "desc";
							switching = true;
						}
					}
				}
				recolorTableContent(tableId);
				recolorCodeFragment(previousRowId,"rgba(60,200,246,1)");
			}

			function recolorTableContent(tableId){
				table = document.getElementById(tableId);
				rows = table.rows;
				/* Loop through all table rows */
				for (i = 0; i < rows.length; i++) {
					if(i%2 == 0){
						rows[i].style.backgroundColor = "rgba(255,255,255,1)";
					}else {
						rows[i].style.backgroundColor = "#eeeeee";
					}
				}
			}

			var previousRowId = null;
			function selectRow(id, tableId){
				if(previousRowId != null){
					// for header table, recolor the contents
					recolorTableContent(tableId);
				}
				// for header table, recolor the row
				recolorCodeFragment(id,"rgba(60,200,246,1)");
				previousRowId= id;
			}

			// recolor a code fragment with its following rows
			function recolorCodeFragment(id, defaultColour){
				document.getElementById(id).style.backgroundColor = defaultColour;
			}
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
		.btn-danger{
			background: #f56976 !important ;
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
		<?php
			if($_SESSION['role'] == 'lecturer')
				setHeaderLecturer("colecturer courses", "Co-lecturer course game: collaborative goals");
			else setHeaderStudent("colecturer courses", "Co-lecturer course game: collaborative goals");
		?>

		<div class="container">
			<div class="infotitle fs-2 my-3"> Collaborative goals for <?php echo $courseName; ?> </div>
				<div class="row d-flex justify-content-center">
					<div class="col-md-8 mb-2">
							<div class="tablecontainer">
								<table id="sumtablecontent" class="table table-bordered table-striped responsive nowrap"  style="width:100%">
									<thead>
										<tr>
											<th>Assessment name</th>
											<th>Expected points</th>
											<th>Due date</th>
											<th>Progress</th>
										</tr>
									</thead>
									<tbody>
										<?php
											// get all assessments regardless whether the collaboration score is fulfilled
											$sql = "SELECT assessment.assessment_id, 
											assessment.public_assessment_id, 
											assessment.name AS assessment_name, course.name AS course_name, assessment.submission_close_time, game_assessment.expected_collaboration_score 
											FROM game_assessment 
												INNER JOIN assessment ON assessment.assessment_id = game_assessment.assessment_id
												INNER JOIN course ON course.course_id = assessment.course_id
												INNER JOIN game_course ON game_course.course_id = course.course_id 
												WHERE course.course_id = '".$courseID."'
												AND course.creator_id = '".$_SESSION['user_id']."' 
												AND game_course.is_active = 1 
												ORDER BY assessment.submission_close_time DESC";
											$result = mysqli_query($db,$sql);
											if ($result->num_rows > 0) {
													while($row = $result->fetch_assoc()) {
														// print a row 
														echo "<tr id=\"".$row['assessment_id']."\" onclick=\"selectRow('".$row['assessment_id']."','sumtablecontent')\">
																<td><a>".$row['assessment_name']."</a></td>
																<td>".$row['expected_collaboration_score']."</td>
																<td>".$row['submission_close_time']."</td>";
																
														// for printing progress bar
														// calculate current collaboration points 
														$totalcontributions = 0;
														$sqlt = "SELECT ROUND(SUM(total_dissim_degree /total_submission)) AS contributions FROM game_student_assessment 
														WHERE assessment_id = '".$row['assessment_id']."'";
														$resultt = mysqli_query($db,$sqlt);
														if ($resultt->num_rows > 0) {
															$rowt = $resultt->fetch_assoc();
															$totalcontributions = $rowt['contributions'];
														}
														
														// calculate the progress
														$progress = $totalcontributions*100/$row['expected_collaboration_score'];
														if($progress >= 100){
															// this should never happen in reality but the condition is put here just in case some bugs occur.
															$progress = 100;
														}
														
														// set the color 
														if($progress < 33){
															// red
															$r = 255; $g = 0; $b = 0;
														}else if($progress < 66){
															// yellow
															$r = 255; $g = 255; $b = 0;
														}else{
															// green 
															$r = 0; $g = 255; $b = 0;
														}
														
														
														
														// print the progress bar
														echo "<td> 
																<div class=\"progressholder\">
																	<div class=\"progressvalue\" style=\"width:".$progress."%; background-color:rgba(".$r.",".$g.",".$b.",1);\"></div>
																	<div class=\"progressvalueoverlay\"";
														if($progress == 0) // to deal with empty progress bar
															echo "style=\"margin-top:2px\" ";
														echo "		><b>".round($progress)."%</b></div>
																</div>
															</td>";
														// echo the closing header
														echo "
															</tr>
														";
													}

											} else {
													// if no submissions
													echo "
														<tr>
															<td  style='text-align:center'>No entries</td>
															<td  style='text-align:center'>No entries</td>
															<td  style='text-align:center'>No entries</td>
															<td  style='text-align:center'>No entries</td>
														</tr>
													";
											}
										?>
										<tbody>
									</table>	
								</div>
							</div>	
						<div class="col-md-4">
							<div class="row d-flex justify-content-center">
								<div class="col-md-12 fs-6" style="text-align:justify;">
								<?php 
									// echo guideline text about how to complete assessment goals
									if($human_language == "en")
										echo "Each student will automatically contribute in completing a collaborative goal if they submit program(s) for that assessment. <br /> The contribution equals to their average originality points. <br />Once the goal is achieved (i.e., the cumulative points are higher than expected points), their contributed originality points will be doubled.</div>";
									else
										echo "Setiap siswa akan otomatis berpartisipasi dalam menyelesaikan collaborative goal pada sebuah tugas jika dia mengumpulkan program untuk tugas terkait. <br /><br />Kontribusi partisipasi tiap siswa akan sejumlah rerata proporsi konten original program mereka. <br /><br />Setelah goal tercapai (poin total kumulatif lebih besar daripada poin yang dibutuhkan), rerata poin originalitas yang siswa kontribusikan akan digandakan.</div>";
								
								?>
								</div>
								<div class="col-md-12">
									<button class="btn btn-primary w-100 mt-4" onclick="window.open('colecturer_game.php?id=<?php echo $courseID; ?>', '_self');">Return to game home</button>
								</div>
							</div>
						</div>
					</div>		
				</div>
			</div>
				</div>
			</div>
									
		</div>
	</div>

	<script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
	<script>

		new DataTable('#sumtablecontent', {
			responsive: true,
			// columnDefs: [
			// 	{ responsivePriority: 1, targets: 0 },
			// ]
		});
	</script>
	</body>
</html>
