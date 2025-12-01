<?php
	// default template
	include("_sessionchecker.php");
	include("_config.php");
	
	if($_SERVER["REQUEST_METHOD"] == "POST") {
		// unenrol
		$id = mysqli_real_escape_string($db,$_POST['id']);
		
		// check whether the user has submitted any assessments in the course
		$sql = "SELECT submission.submission_id FROM submission
		INNER JOIN assessment ON assessment.assessment_id = submission.assessment_id
		WHERE submission.submitter_id = '".$_SESSION['user_id']."' AND assessment.course_id = '".$id."'";
		$result = mysqli_query($db,$sql);
		$count = mysqli_num_rows($result);
		if($count > 0) {
		  // if at least one entry fetched, the student cannot unenrol from the course
		  echo "<script>alert('You cannot unenrol from this course since you have made submissions to at least one of the assessments!');</script>";
		}else{			
			// unenrol the course
			$sql = "DELETE FROM enrollment WHERE course_id = '".$id."' AND student_id = '".$_SESSION['user_id']."'";
			if ($db->query($sql) === TRUE) {
				// if updated well, delete an entry from game_student_course 
				$sql = "DELETE FROM game_student_course WHERE course_id = '".$id."' AND student_id = '".$_SESSION['user_id']."'";
				if ($db->query($sql) === TRUE) {
					// refresh the page
					header('Location: student_enrollment.php');
					exit;
				}else{
					echo "<script>alert('You cannot unenrol from this course!');</script>";
				}
			} else {
				echo "<script>alert('You cannot unenrol from this course!');</script>";
			}
		}
	}
?>
<html>
	<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">

		<title> E-STRANGE: Student enrolment</title>
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
	.btn-danger{
			background: #f56976 !important ;
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
	}
	</style>
    
  </head>
  <body>
		<?php
		  setHeaderStudent("enrollment", "Student enrolment");
		?>

		<div class="container my-3">

			<div class="bodycontent">
			<div class="row d-flex justify-content-center align-items-center">
				<div class="col-md-12">
					<div class="infotitle fs-1">Courses:</div>
				</div>
			</div>

			<!-- table -->
			<div class="tablecontainer">
				<table id="studentEnrollment" class="table table-bordered table-striped responsive nowrap" style="width:100%">
					<thead>
						<tr>
							<th>Course name</th>
							<th>Description</th>
							<th>Lecturer</th>
							<th>Status</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody>

					
			</div>

				 <?php
						// to count the number of eligible courses shown (i.e., those that are public or manually enrolled)
						$eligibleCourses = 0;
						
				 		// get all of the courses
						$sql = "SELECT course.course_id, course.name AS course_name, course.description AS course_description, course.enrollment_mode, user.username AS lecturer_id, user.name AS lecturer_name 
							 FROM course INNER JOIN user ON user.user_id = course.creator_id";
						$result = mysqli_query($db,$sql);
						// adapted from https://www.w3schools.com/php/php_mysql_select.asp
						if ($result->num_rows > 0) {
								while($row = $result->fetch_assoc()) {
										$isEnrolled = false;
									
										// check whether the student enrols to the course
										$sqlt = "SELECT course_id FROM enrollment
											 WHERE course_id = '".$row['course_id']."' AND student_id = '".$_SESSION['user_id']."'";
										$resultt = mysqli_query($db,$sqlt);
										if ($resultt->num_rows > 0) 
											$isEnrolled = true;
										else{
											// if the user is not enrolled to the course and the enrollment mode is not public, skip to the next entry
											if($row['enrollment_mode'] != 1)
												continue;
										}
										
										// increment the number of eligible courses
										$eligibleCourses = $eligibleCourses + 1;

										echo "
											<tr id=\"".$row['course_id']."\" onclick=\"selectRow('".$row['course_id']."','sumtablecontent')\">
												<td><a>".$row['course_name']."</a></td>
												<td>".$row['course_description']."</td>
												<td>".$row['lecturer_id']." / ".$row['lecturer_name']."</td>";
												
										if($isEnrolled){
											if($row['enrollment_mode'] == 0)
												echo "<td>Enrolled by the lecturer</td>";
											else
												echo "<td>Enrolled</td>";
										}else{
											echo "<td>Not enrolled</td>";
										}
										
										if($isEnrolled && $row['enrollment_mode'] == 0){
											echo "<td class=\"tdactions\"></td>";
										}else if($isEnrolled && $row['enrollment_mode'] == 1){
											echo "<td class=\"tdactions\">
											<div class=\"invisform\" action=\"". htmlentities($_SERVER['PHP_SELF']). "\" method=\"post\">
												<button class=\"btn btn-danger w-100\"  onclick=\"confirmDelete('".$row['course_id']."');\">Delete</button>
											</div>
												</td>";
										}else if(!$isEnrolled && $row['enrollment_mode'] == 1){
											echo "<td class=\"tdactions\">
													<form class=\"invisform\" action=\"student_enroll.php\" method=\"post\">
														<input type=\"hidden\" name=\"enrollid\" value=\"".$row['course_id']."\">
														<button class=\"btn btn-primary\" type=\"submit\">Enrol</button>
													</form>
												</td>";
										}

										// just for HTML closing tags
										echo"</tr>
										
										<div class=\"modal fade\" id=\"deleteModal".$row['course_id']."\" data-bs-backdrop=\"static\" data-bs-keyboard=\"false\">
													<div class=\"modal-dialog modal-dialog-centered\">
														<div class=\"modal-content\">
															<div class=\"modal-header\">
																<h4 class=\"modal-title\">Delete Confirmation</h4>
																<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>
															</div>
															<div class=\"modal-body\">
																<p>Are you sure you want to unenroll this entry?</p>
															</div>
															<div class=\"modal-footer\">
															<form class=\"invisform\" action=\"". htmlentities($_SERVER['PHP_SELF']). "\" method=\"post\">
																<input type=\"hidden\" name=\"id\" value=\"".$row['course_id']."\">
																<button class=\"btn btn-primary\" type=\"submit\" onclick=\"return confirm('Unenroll from \'".$row['course_name']."\' course?');\">Unenrol</button>
															</form>
															</div>
														</div>
													</div>
												</div>
												";
								}

						}
						
						if($eligibleCourses == 0){
							// if no eligible courses
								echo "
								";
						}
					?>
					
				

					</tbody>
				</table>
				</div>
			</div>
		</div>
		<!-- link boothstrap -->

		<script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
				<script>

		new DataTable('#studentEnrollment', {
			responsive: true,
			// columnDefs: [
			// 	{ responsivePriority: 1, targets: 0 },
			// ]
		});
		</script>
		
		<script>
			function confirmDelete(userId) {
				$('#deleteModal' + userId).modal('show');
			}
		</script>
  </body>
</html>