<?php
	include("_sessionchecker.php");
	include("_config.php");

	if($_SERVER["REQUEST_METHOD"] == "POST") {
		// set current assessment to the session
		if(isset($_POST['id'])){
				// if landed from assessment page
				$_SESSION['assessment_id'] = mysqli_real_escape_string($db,$_POST['id']);
				$_SESSION['assessment_name'] = mysqli_real_escape_string($db,$_POST['name']);
		}
	}

	// redirect if the sessions are not set
	if(isset($_SESSION['assessment_id']) == false){
	  header('Location: admin_dashboard.php');
		exit;
	}
?>
<html>
	<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
		<title> E-STRANGE: Assessment submissions</title>
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
	.buttontambah{
		text-align: right;
	}
	.btn-warning{
			background: #fef2b3  !important ;
		}
	.btn-danger{
			background: #f56976 !important ;
		}
	.form-control {
			border: 2px solid #000;	
			border-radius: 8px;
		}
	.swap-rows {
		display: flex;
		flex-direction: column;
	}

	.swap-rows .row:nth-child(2) {
		order: 3; /* Menukar row 2 ke posisi row 3 */
	}

	.swap-rows .row:nth-child(3) {
		order: 2; /* Menukar row 3 ke posisi row 2 */
	}
	@media only screen and (max-width: 450px) {

		.buttontambah{
			text-align: left;
			margin: 1rem 0 1rem 0;
		}
		tr td{
			font-size: 0.9rem;
		}
		.addcourse{
			width: 100%;
		}
	}
	</style>
  </head>
  <body>
		<?php
		  setHeaderAdmin("courses", "Assessment submissions");
		?>
		<div class="container mt-3">
			<div class="bodycontent swap-rows">

				<div class="row d-flex justify-content-center align-items-center mb-3">
					<div class="col-md-6">
						<div class="infotitle fs-1"> <?php echo $_SESSION['assessment_name']; ?>'s submission list: </div>
					</div>
					<div class="col-md-6 buttontambah">
						<!-- <button class="btn btn-primary mb-2 addcourse" onclick="window.open('admin_assessments.php', '_self');">Return to assessments</button> -->
						<?php
						echo "<button id='return-course'  class='btn btn-primary mb-2 addcourse' onclick=\"window.open('admin_assessments.php', '_self');\">Return to assessments</button>";
						echo "<script>
							document.getElementById('return-course').addEventListener('click', function() {
								localStorage.setItem('returning_from_submission', 'true'); // Pastikan flag benar-benar tersimpan
							});
						</script>";
						?>
					
					</div>
				</div>


				<div class="row d-flex justify-content-center align-items-center tablecontainer">
				<div class="nosubmissionstudents my-3"> <b>Students who have not submitted their work:</b> 
					<?php
						$sqlt = "SELECT user.username 
							FROM user
							INNER JOIN enrollment ON enrollment.student_id = user.user_id
							INNER JOIN course ON course.course_id = enrollment.course_id
							INNER JOIN assessment ON assessment.course_id = course.course_id
							WHERE assessment.assessment_id = '".$_SESSION['assessment_id']."'
							AND user.user_id NOT IN 
								(SELECT submitter_id 
								FROM submission WHERE assessment_id = '".$_SESSION['assessment_id']."')";
						$resultt = mysqli_query($db,$sqlt);
						if ($resultt->num_rows > 0) {
							$studentList = "";
							while($row = $resultt->fetch_assoc()) {
								$studentList = $studentList.', '.$row['username'];
							}
							echo substr($studentList,2);
						}
					?>
					</div>
					<table id="adminAssessmentTable" class="table table-bordered table-striped responsive nowrap" style="width:100%" >
						<thead>		
							<tr>
								<th>Submitter</th>
								<th>File name</th>
								<th>Description</th>
								<th>Attemp</th>
								<th>Submission time</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody>
							<!-- only suspicious ones are featured with 'suspicion alert' -->
							<?php
								// get all submissions for a particular assessment
								$sql = "SELECT submission.submission_id, user.username, submission.filename,
									submission.description, submission.attempt, submission.submission_time,
									submission.submitter_id, assessment.name AS assessment_name, course.name AS course_name, assessment.submission_close_time
									FROM submission	INNER JOIN user ON user.user_id = submission.submitter_id
									INNER JOIN assessment ON submission.assessment_id = assessment.assessment_id
									INNER JOIN course ON assessment.course_id = course.course_id
									WHERE submission.assessment_id = '".$_SESSION['assessment_id']."'
									ORDER BY submission.submission_time DESC";
								$result = mysqli_query($db,$sql);
								// adapted from https://www.w3schools.com/php/php_mysql_select.asp
								if ($result->num_rows > 0) {
										while($row = $result->fetch_assoc()) {
												echo "
													<tr id=\"".$row['submission_id']."\" onclick=\"selectRow('".$row['submission_id']."','sumtablecontent')\">
														<td><a>";
												if($row['submission_time'] > $row['submission_close_time'])
													echo "<b>[LATE]</b> ";
												echo $row['username']."</a></td>
														<td>".$row['filename']."</td>
														<td>".$row['description']."</td>
														<td>".$row['attempt']."</td>
														<td>".$row['submission_time']."</td>
														<td class=\"tdactions\">
															<form class=\"invisform\" action=\"user_download_code.php\" method=\"post\">
																<input type=\"hidden\" name=\"id\" value=\"".$row['submission_id']."\">
																<button class=\"btn btn-primary mb-4 w-100 \"  type=\"submit\">download</button>
															</form>
														";
													// for dealing with suspicion
													$sqlt = "SELECT suspicion_id FROM suspicion
														WHERE submission_id = '".$row['submission_id']."'
														AND suspicion_type = 'real'";
													$resultt = mysqli_query($db,$sqlt);
													if ($resultt->num_rows > 0) {
														$rowt = $resultt->fetch_assoc();
														echo "
																<form class=\"invisform\" action=\"user_suspicion_report.php\" method=\"post\">
																	<input type=\"hidden\" name=\"id\" value=\"".$rowt['suspicion_id']."\">
																	<input type=\"hidden\" name=\"course_name\" value=\"".$row['course_name']."\">
																	<input type=\"hidden\" name=\"assessment_name\" value=\"".$row['assessment_name']."\">
																	<input type=\"hidden\" name=\"submitter_id\" value=\"".$row['submitter_id']."\">
																	<button class=\"btn btn-warning\" type=\"submit\">suspicion alert</button>
																</form>
																";
														}
												echo "
														</td>
													</tr>
												";
										}
								} else {
										echo "
										";
								}
							?>
							</tbody>
						</table>
				</div>
					

				<div class="row d-flex justify-content-center align-items-center mb-3">
				<?php
					// this is based on query above, at the beginning of the table
					if ($result->num_rows > 0) {
						
						// check whether the similarity report has been generated
						$sqlt = "SELECT similarity_report_path FROM assessment WHERE assessment_id = '".$_SESSION['assessment_id']."' AND similarity_report_path != ''";
						$resultt = mysqli_query($db,$sqlt);
						if ($resultt->num_rows > 0) {
							// if exist, check the path
							// 'null' means the report cannot be generated
							$rowt = $resultt->fetch_assoc();
							if($rowt['similarity_report_path'] == 'null'){
								// if none
								echo "<div class=\"col-md-3\">
										<button class=\"btn btn-primary mb-4 w-100 \" style=\"font-size:0.9em\">Too few subs for sim. report</button>
										</div>
									";
							}
							else{
								//if the path actually exists, show download sim report button
								echo "<div class=\"col-md-3\">
										<button class=\"btn btn-primary mb-4 w-100 \" onclick=\"window.open('coadmin_download_sim_report.php?id=".$_SESSION['assessment_id']."', '_self');\">Download sim. report</button>
									</div>
										";
							}
						}else{
							// check whether it is on the queue
							$sqlt = "SELECT queue_id FROM similarity_report_generation_queue WHERE assessment_id = '".$_SESSION['assessment_id']."'";
							$resultt = mysqli_query($db,$sqlt);
							if ($resultt->num_rows > 0) {
								echo "<div class=\"col-md-3\">
										<button class=\"btn btn-primary mb-4 w-100 \" style=\"font-size:0.9em\">Sim. report is being generated</button>
										</div>
									";
							}else{
								echo "<div class=\"col-md-3\">
										<button class=\"btn btn-secondary mb-4 w-100 \" style=\"font-size:0.9em\">Earlier than due date for sim. report</button>
										</div>	
									";
							}
						}
						
						echo "
						<div class=\"col-md-2\">
							<button class=\"btn btn-primary mb-4 w-100 \" onclick=\"window.open('coadmin_download_metadata.php', '_self');\">Download metadata</button>
						</div>	

						<div class=\"col-md-4\">
							<button class=\"btn btn-primary mb-4 w-100 \" onclick=\"window.open('coadmin_download_all_last_code.php', '_self');\">Download only last attempt submissions</button>
						</div>	

						<div class=\"col-md-2\">	
							<button class=\"btn btn-primary mb-4 w-100 \" onclick=\"window.open('coadmin_download_all_code.php', '_self');\">Download all</button>
						</div>	
							";
					}
					?>
				</div>
				</div>
			</div>
	

			<script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
					<script>
    // $(document).ready(function () {
    //     $('#adminTable').DataTable({
	// 		responsive: true
	// 	} );
    // });
	new DataTable('#adminAssessmentTable', {
    	responsive: true,
		// columnDefs: [
        // 	{ responsivePriority: 1, targets: 0 },
    	// ]
	});
	
</script>
<script>
    function confirmDelete(course_id) {
        $('#deleteModal' + course_id).modal('show');
    }
</script>
  </body>
</html>
