<?php
include("_sessionchecker.php");
include("_config.php");

if($_SERVER["REQUEST_METHOD"] == "POST") {
	if(isset($_POST['id']) == true){ // this is course id
		// if landed from course page
		// set the course data in the session
		$_SESSION['course_id'] = mysqli_real_escape_string($db,$_POST['id']);
		$_SESSION['course_name'] = mysqli_real_escape_string($db,$_POST['name']);
	}else if(isset($_POST['did']) == true){
		// unenroll student from a course
		$id = mysqli_real_escape_string($db,$_POST['did']);
		
		// remove colecturer entry for that person
		$sql = "DELETE FROM colecturer WHERE colecturer_id = '$id'";
		if ($db->query($sql) === TRUE) {
			// if removed well, redirect to dashboard
			header('Location: lecturer_colecturer.php');
			exit;
		}else{
			echo "Error removing record: " . $db->error;
		}
	}
}

// if the session values do not exist
if(isset($_SESSION['course_id']) == false){
	header('Location: lecturer_dashboard.php');
	exit;
}
?>
<html>
	<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
		<title> E-STRANGE: Course co-lecturers</title>
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
	.buttontambah{
		text-align: right;
	}
	.form-control {
			border: 2px solid #000;	
			border-radius: 8px;
		}
	@media only screen and (max-width: 425px) {
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
		  setHeaderLecturer("courses", "Course co-lecturers");
		?>

	<div class="container my-3">
		<div class="bodycontent">
			<div class="row d-flex justify-content-center align-items-center mb-3">
				<div class="col-md-6">
					<div class="infotitle fs-3"> <?php echo $_SESSION['course_name'] ?>'s co-lecturer list: </div>
				</div>
				<div class="col-md-6 buttontambah">
					<!-- <button class="btn btn-primary addcourse mb-2" onclick="window.open('lecturer_dashboard.php', '_self');">Return to courses</button> -->
					<?php
						echo "<button id='return-colecturer'  class='btn btn-primary mb-2 addcourse' onclick=\"window.open('lecturer_dashboard.php', '_self');\">Return to courses</button>";
						echo "<script>
							document.getElementById('return-colecturer').addEventListener('click', function() {
								localStorage.setItem('returning_from_colecturer', 'true'); // Pastikan flag benar-benar tersimpan
							});
						</script>";
					?>
					<button class="btn btn-primary addcourse mb-2" onclick="window.open('lecturer_enrollment_colecturer.php', '_self');">Enroll co-lecturer(s)</button>

				</div>
			<div class="tablecontainer">
				<table id="lecturerColecturerTable" class="table table-bordered table-striped responsive nowrap" style="width:100%" >
					<thead>
						<tr>
							<th>Username</th>
							<th>Name</th>
							<th>Email</th>
							<th>Role</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody>
						<?php
							// get all students enrolled on given course
							$sql = "SELECT colecturer.colecturer_id, user.username, user.name, user.email, user.role 
								FROM colecturer	INNER JOIN user ON user.user_id = colecturer.user_id
								WHERE colecturer.course_id = '".$_SESSION['course_id']."' ORDER BY user.name ASC";
							$result = mysqli_query($db,$sql);
							// adapted from https://www.w3schools.com/php/php_mysql_select.asp
							if ($result->num_rows > 0) {
									while($row = $result->fetch_assoc()) {
											echo "
												<tr id=\"".$row['colecturer_id']."\" onclick=\"selectRow('".$row['colecturer_id']."','sumtablecontent')\">
													<td><a>".$row['username']."</a></td>
													<td>".$row['name']."</td>
													<td>".$row['email']."</td>
													<td>".ucfirst($row['role'])."</td>
													<td class=\"tdactions\">
														<div class=\"invisform\" action=\"". htmlentities($_SERVER['PHP_SELF']). "\" method=\"post\">
															<button class=\"btn btn-danger w-100\"  onclick=\"confirmDelete('".$row['colecturer_id']."');\">Delete</button>
														</div>
													</td>
												</tr>
												<div class=\"modal fade\" id=\"deleteModal".$row['colecturer_id']."\" data-bs-backdrop=\"static\" data-bs-keyboard=\"false\">
													<div class=\"modal-dialog modal-dialog-centered\">
														<div class=\"modal-content\">
															<div class=\"modal-header\">
																<h4 class=\"modal-title\">Delete Confirmation</h4>
																<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>
															</div>
															<div class=\"modal-body\">
																<p>Are you sure you want to delete this entry?</p>
															</div>
															<div class=\"modal-footer\">
																<form action=\"".htmlentities($_SERVER['PHP_SELF'])."\" method=\"post\">
																	<input type=\"hidden\" name=\"did\" value=\"".$row['colecturer_id']."\">
																	<button type=\"button\" class=\"btn btn-secondary\" data-bs-dismiss=\"modal\">Close</button>
																	<button type=\"submit\" class=\"btn btn-danger\">Delete</button>
																</form>
															</div>
														</div>
													</div>
												</div>
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
			</div>
		</div>
	</div>




	<script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
	<script>
    // $(document).ready(function () {
    //     $('#lecturerTable').DataTable({
	// 		responsive: true
	// 	} );
    // });
	new DataTable('#lecturerColecturerTable', {
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
