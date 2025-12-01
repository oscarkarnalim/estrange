<?php
	include("_sessionchecker.php");
	include("_config.php");
	
	// check if the lecturer is in charge in at least one course with game feature
	$sql = "SELECT course.course_id, course.name, 
			game_course.prize_text FROM course 
			INNER JOIN game_course ON game_course.course_id = course.course_id 
			INNER JOIN colecturer ON colecturer.course_id = course.course_id 
			WHERE game_course.is_active = 1 
			AND (course.creator_id = '".$_SESSION['user_id']."'
			OR colecturer.user_id = '".$_SESSION['user_id']."') ";
	$result = mysqli_query($db,$sql);
	if ($result->num_rows > 0) {
		// if the lecturer is in charge in at least one gamified course, redirect to lecturer_game
		header('Location: colecturer_game.php');
		exit;
	}
?>
<html>
	<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
		<title> E-STRANGE: Course game</title>
    <link rel="icon" href="strange_html_layout_additional_files/icon.png">
	<link href="bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Untuk Icon -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
	
<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.slim.min.js" integrity="sha256-kmHvs0B+OpCW5GVHUNjv9rOmY0IvSIRcf7zGUDTDQM8=" crossorigin="anonymous"></script>

<!-- DataTables JS -->
<script type="text/javascript" src="//cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
  

    <script>
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
			
			// to update the whole page when the course is changed
			function updateDisplayedGameDataBasedOnCourse(){
				var selectedValue = document.getElementById("course").value;
				window.location.href = window.location.href.substring(0, window.location.href.indexOf("?")) + "?id=" + selectedValue;
			}
			
			// for sorting table 
			// sort table content. Copied and modified from https://www.w3schools.com/howto/howto_js_sort_table.asp
			function sortTable(n, tableId, isNumber) {
				var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
				table = document.getElementById(tableId);
				
				switching = true;
				// Set the sorting direction to ascending:
				dir = "desc";
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
				
				// set the ranks for the first top N
				rows = table.rows;
				for (i = 0; i < <?php echo $num_students_shown_leaderboard;?>; i++) {
					if(rows[i].getElementsByTagName("TD").length == 1)
						// if it is only one <td>, it means the table has no entries, skip the process
						break;
					else
						// set the first <td>
						rows[i].getElementsByTagName("TD")[0].innerHTML = (i+1);
				}
				
				// remove remaining elements
				while(rows.length > <?php echo $num_students_shown_leaderboard;?>){
					rows[rows.length-1].remove();
				}
				
			}
		
			
			window.addEventListener('load', function () {
			  sortTable(3,'sumtablecontent',true);
			});
			
			
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
	@media only screen and (max-width: 425px) {
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
				setHeaderLecturer("colecturer courses", "Co-lecturer course game");
			else
				setHeaderStudent("colecturer courses", "Co-lecturer course game");
		?>

		<div class="bodycontent">
			<div class="infotitle"> Co-lecturer course game </div>
			<div class="content">
			<?php 
				if($human_language == "en")
					echo "You are not in charge of any gamified courses. Please create a course with such feature or enable the feature on existing courses in the course tab";
				else
					echo "Kamu tidak memiliki kelas dengan gamifikasi. Silahkan membuat kelas dengan fitur tersebut atau mengijinkan gamifikasi di kelas-kelas yang sudah ada pada tab course";
			?>
			</div>
			<button class="btn btn-primary w-50" onclick="window.open('colecturer_courses.php', '_self');">Return to co-lecturer courses</button>
		</div>

		<script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
