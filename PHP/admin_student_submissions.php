<?php
	include("_sessionchecker.php");
	include("_config.php");

?>
<html>
	<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
		<title> E-STRANGE: Student submissions</title>
    <link rel="icon" href="strange_html_layout_additional_files/icon.png">
	<link href="bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">


  <<!-- Untuk Icon -->
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

			// adapted and modified from https://www.w3schools.com/howto/howto_js_copy_clipboard.asp
			function copy_assessment_link(input_id) {
				/* Get the text field */
			  var copyText = document.getElementById(input_id);
			  /* Select the text field */
			  copyText.select();
			  copyText.setSelectionRange(0, 99999); /*For mobile devices*/
			  /* Copy the text inside the text field */
			  document.execCommand("copy");
			  /* Alert the copied submission link */
			  alert("Copied the submission link: " + copyText.value);
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
	.contfluid{
		padding-left: 3rem;
		padding-right: 3rem;
	}
	.menuatas{
		width: 30%;
	}
	td:nth-child(1) {
        width: 10%;
    }
	td:nth-child(6) {
        width: 30%;
    }
	@media only screen and (max-width: 425px) {
		*{
			font-size: 0.98em;
		}
		.buttontambah{
			text-align: left;
			margin: 1rem 0 1rem 0;
		}
		.contfluid{
			padding-left: 0.5rem;
			padding-right: 0.5rem;
		}
		.menuatas{
		width: 100%;
		margin-top: .5rem;
		margin-bottom: .5rem;
	}
	}
	</style>
  </head>
  <body>
  <?php setHeaderAdmin("submissions", "Student submissions"); ?>
  <div class="container-fluid contfluid my-3">
		<div class="bodycontent">
		
			<div class="row d-flex justify-content-center align-items-center kanandikit">
				<div class="col-md-6">
					<div class="infotitle fs-1"> Student submissions: </div>
				</div>
				<div class="col-md-6 text-end">
					<button class="btn btn-primary menuatas" onclick="window.open('lecturer_assessment_add.php', '_self');">Add assessment</button>
				</div>
			</div>
	

			<div class="tablecontainer">
				<table id="submissionTable"  class="table table-bordered table-striped responsive" style="width:100%">
				<thead>
					<tr>
						<th>Lecturer</th>
						<th>Course</th>
						<th>Assessment</th>
						<th>Submission opening time</th>
						<th>Submission closing time</th>
						<th> Download </th>
					</tr>
				</thead>
				<tbody>
						<?php
							// get the assessments for a particular course
							$sql = "SELECT user.name AS user_name, course.name AS course_name, assessment.name AS assessment_name, assessment.submission_open_time, assessment.submission_close_time, assessment.assessment_id 
							FROM assessment 
							INNER JOIN course ON course.course_id = assessment.course_id
							INNER JOIN user ON user.user_id = course.creator_id
							ORDER BY submission_close_time DESC";
							$result = mysqli_query($db,$sql);
							// adapted from https://www.w3schools.com/php/php_mysql_select.asp
							if ($result->num_rows > 0) {
									while($row = $result->fetch_assoc()) {
											echo "
												<tr id=\"".$row['assessment_id']."\" onclick=\"selectRow('".$row['assessment_id']."','sumtablecontent')\">
													<td ><a>".$row['user_name']."</a></td>
													<td>".$row['course_name']."</td>
													<td>".$row['assessment_name']."</td>
													<td>".$row['submission_open_time']."</td>
													<td>".$row['submission_close_time']."</td>";
											echo "
													<td>
													<div class=\"container\">
													<div class=\"row d-flex text-center justify-content-center\">
										
											
													<form class=\"invisform w-auto\" action=\"admin_download_metadata.php\" method=\"post\">
															<input type=\"hidden\" name=\"assessment_id\" value=\"".$row['assessment_id']."\">
															<input type=\"hidden\" name=\"assessment_name\" value=\"".$row['assessment_name']."\">
															<input type=\"hidden\" name=\"course_name\" value=\"".$row['course_name']."\">
															<button class=\"actions btn btn-primary w-auto\" type=\"submit\">Metadata</button>
														</form>
														<form class=\"invisform w-auto \" action=\"admin_download_all_last_code.php\" method=\"post\">
															<input type=\"hidden\" name=\"assessment_id\" value=\"".$row['assessment_id']."\">
															<input type=\"hidden\" name=\"assessment_name\" value=\"".$row['assessment_name']."\">
															<input type=\"hidden\" name=\"course_name\" value=\"".$row['course_name']."\">
															<button class=\"actions btn btn-primary w-auto\" type=\"submit\">Last subs</button>
														</form>
										
														
														<form class=\"invisform w-auto \" action=\"admin_download_all_code.php\" method=\"post\">
															<input type=\"hidden\" name=\"assessment_id\" value=\"".$row['assessment_id']."\">
															<input type=\"hidden\" name=\"assessment_name\" value=\"".$row['assessment_name']."\">
															<input type=\"hidden\" name=\"course_name\" value=\"".$row['course_name']."\">
															<button class=\"actions btn btn-primary w-auto\" type=\"submit\">All subs</button>
														</form>";
											// check whether the similarity report has been generated
											$sqlt = "SELECT similarity_report_path FROM assessment WHERE assessment_id = '".$row['assessment_id']."' AND similarity_report_path != ''";
											$resultt = mysqli_query($db,$sqlt);
											if ($resultt->num_rows > 0) {
											    // if exist, check the path
                    							// 'null' means the report cannot be generated
                    							$rowt = $resultt->fetch_assoc();
                    							if($rowt['similarity_report_path'] == 'null'){
                    							    // if null
                    							    echo "
                        								<button class=\"actions btn btn-danger w-100\">Too few subs for sim. report</button>
                        								";
                    							}else {
												//if the path actually exists, show download sim report button
												echo "
													<form class=\"invisform \" action=\"admin_download_sim_report.php\" method=\"post\">
															<input type=\"hidden\" name=\"assessment_id\" value=\"".$row['assessment_id']."\">
															<input type=\"hidden\" name=\"assessment_name\" value=\"".$row['assessment_name']."\">
															<input type=\"hidden\" name=\"course_name\" value=\"".$row['course_name']."\">
															<button class=\"actions btn btn-danger w-100\" type=\"submit\">Sim report</button>
														</form>";
                    							}
											}else{
											     // check whether it is on the queue
                    						    $sqlt = "SELECT queue_id FROM similarity_report_generation_queue WHERE assessment_id = '".$row['assessment_id']."'";
                    						    $resultt = mysqli_query($db,$sqlt);
                    						    if ($resultt->num_rows > 0) {
                        						    echo "
														<div class=\"invisform  \">
                        									<button class=\"actions btn btn-danger w-100\">Sim. report is being generated</button>
														</div>
														";
                    						    }else{
                    						        echo "
														<div class=\"invisform  \">
															<button class=\"actions btn btn-danger w-100\">Earlier than due date for sim. report</button>
														</div>
														";
                    						    }
											}
											echo "
											</div></div>
											</td>
												</tr>";
									}
							} else {
									echo "
									
									";
							}
							?>
				</tbody>
				</table>
				</div>
				
				<!-- <button class="btn btn-primary" onclick="window.open('lecturer_dashboard.php', '_self');">Return to courses</button>
				<button class="btn btn-primary" onclick="window.open('lecturer_assessment_add.php', '_self');">Add assessment</button> -->
			</div>
		</div>
	
		<script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
				<script>
	new DataTable('#submissionTable', {
    	responsive: true,
		// columnDefs: [
        // 	{ responsivePriority: 1, targets: 0 },
    	// ]
	});
	
</script>
  </body>
</html>
