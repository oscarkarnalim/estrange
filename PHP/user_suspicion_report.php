<?php
	include("_sessionchecker.php");
	include("_config.php");

	if(isset($_POST['id']) == false){
		if(isset($_SESSION['user_suspicion_report_id']) == false){
			// does not have id? move to login (that will redirect to their respective dashboard)
			header('Location: index.php');
			exit;
		}else{
			// if there is a session var for this, set the id with that value
			$_POST['id'] = $_SESSION['user_suspicion_report_id'];
			$_POST['course_name'] = $_SESSION['user_suspicion_report_course_name'];
			$_POST['assessment_name'] = $_SESSION['user_suspicion_report_assessment_name'];
			if(isset($_SESSION['user_suspicion_report_assessment_mode']))
				$_POST['mode'] = $_SESSION['user_suspicion_report_assessment_mode'];
		}
	}else{
		// set the session value for id
		$_SESSION['user_suspicion_report_id'] = $_POST['id'];
		$_SESSION['user_suspicion_report_course_name'] = $_POST['course_name'];
		$_SESSION['user_suspicion_report_assessment_name'] = $_POST['assessment_name'];
		if(isset($_POST['mode']))
			$_SESSION['user_suspicion_report_assessment_mode'] = $_POST['mode'];
	}

	// redirect if not eligible
	if($_SESSION['role'] == 'student'){
		if(isset($_POST['mode']) == false){
			// student without mode? move to dashboard
			header('Location: student_dashboard.php');
			exit;
		}
	}else if($_SESSION['role'] == 'admin'){
		// or admin
		header('Location: admin_dashboard.php');
		exit;
	}

	// get all data required for this page
	$sqlt = "SELECT suspicion.suspicion_type, suspicion.marked_code, suspicion.artificial_code, suspicion.table_info, suspicion.explanation_info, suspicion.is_overly_unique, 
	    assessment.course_id, submission.submission_id,  submission.submitter_id, suspicion.efficiency_point      
		FROM suspicion
		INNER JOIN submission ON submission.submission_id = suspicion.submission_id
		INNER JOIN assessment ON assessment.assessment_id = submission.assessment_id
		WHERE suspicion_id = '".$_POST['id']."'";
	$resultt = mysqli_query($db,$sqlt);
	$rowt = $resultt->fetch_assoc();

	$markedCode = $rowt['marked_code'];
	$artificialCode = $rowt['artificial_code'];
	$tableInfo = $rowt['table_info'];
	$explanationInfo = $rowt['explanation_info'];
	$suspicion_type = $rowt['suspicion_type'];
	$courseId = $rowt['course_id'];
	$submission_id = $rowt['submission_id'];
	$submitter_id = $rowt['submitter_id'];
	$isOverlyUnique = $rowt['is_overly_unique'];
	$efficiencyPoint = $rowt['efficiency_point'];
	
	// record access only if done by student
	if($_SESSION['role'] == 'student')
		recordAccess($db, $_POST['id'], $_SESSION['user_id']);
?>
<html>
	<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php
			if($suspicion_type == 'real'){
				echo ($human_language == 'en'? "<title>Similarity alert</title>": "<title>Laporan kesamaan</title>");
			}else{
			    if($isOverlyUnique == false){
				    echo ($human_language == 'en'? "<title>Similarity simulation</title>": "<title>Simulasi kesamaan</title>");
			    }else{
			        echo ($human_language == 'en'? "<title>Similarity simulation: overly unique submission</title>": "<title>Simulasi kesamaan: pekerjaan terlalu unik</title>");
			    }
			}
		?>
		<!-- DataTables CSS -->
		<link rel="stylesheet" type="text/css" href="cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">

		<!-- jQuery -->
		<script src="https://code.jquery.com/jquery-3.7.1.slim.min.js" integrity="sha256-kmHvs0B+OpCW5GVHUNjv9rOmY0IvSIRcf7zGUDTDQM8=" crossorigin="anonymous"></script>

		<!-- DataTables JS -->
		<link rel="stylesheet" type="text/css" href="datatables/jquery.dataTables.min.css">
		<script type="text/javascript" src="datatables/jquery.dataTables.min.js"></script>
		<link rel="stylesheet" type="text/css" href="datatables/responsive.bootstrap5.min.css">
		<script type="text/javascript" src="datatables/dataTables.responsive.min.js"></script>
		<script type="text/javascript" src="datatables/responsive.bootstrap5.min.js"></script>

		<!-- Untuk Icon -->
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
		<link href="bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="icon" href="strange_html_layout_additional_files/icon.png">
    <!-- Google Prettify to generate highlight https://github.com/google/code-prettify -->
	<script src="strange_html_layout_additional_files/run_prettify.js"></script>
	<!-- The use of Notyf library https://github.com/caroso1222/notyf -->
	<link rel="stylesheet" href="strange_html_layout_additional_files/notyf.min.css">
	<script src="strange_html_layout_additional_files/notyf.min.js"></script>
	<script type="text/javascript">
			function loadGameNotif(){
				// Create an instance of Notyf
				var notyf = new Notyf({
				  duration: 0,
				  position: {
					x: 'right',
					y: 'top',
				  },
				  dismissible: true
				});
				
	<?php
			 // only show the notification for students
			 if($_SESSION['role'] == 'student'){
				 // get three earliest notification for courses in which game feature is active
				 // and student participation in the game is also active
				 $sqlt = "SELECT game_unobserved_notif.notification_id, game_unobserved_notif.message 
						FROM game_unobserved_notif 
						INNER JOIN game_student_course ON game_student_course.gs_id = game_unobserved_notif.gs_id 
						INNER JOIN game_course ON game_course.course_id = game_student_course.course_id 
						WHERE game_student_course.student_id = '".$_SESSION['user_id']."' 
						AND game_student_course.course_id = '".$courseId."' 
						AND game_course.is_active = '1' 
						AND game_student_course.is_participating = '1' 
						ORDER BY game_unobserved_notif.time_created ASC
						LIMIT 3";
				 $rt = mysqli_query($db,$sqlt);
				 
				 // to make each notification has its own JavaScript variable
				 $i =0;
				 while($row = $rt->fetch_assoc()) {
					 // print the notification
					 echo "const notification".$i." = notyf.success(\"".$row['message']."<br />Click me for details!\");
						   notification".$i.".on('click', ({target, event}) => {window.location.href = 'student_game.php?id=".$courseId."';});";
						   
						   
					 // remove the notification
					 $sql = "DELETE FROM game_unobserved_notif WHERE notification_id = '".$row['notification_id']."'";
					 $db->query($sql);
					 
					 // increment the i
					 $i = $i+1;
				 }
			 }
		?>
			}
			function construct(){
				loadGameNotif();
			}

			// function to toggle general info given at top left of the page.
			function toggleCollapsible(targetDiv){
				var content = document.getElementById(targetDiv);
				if (content.style.display == "block") {
					content.style.display = "none";
				} else {
					content.style.display = "block";
				}
			}

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
				// set to the same selection as prior sorted
				if(selectedCodeFragmentId != null)
					markSelected(selectedCodeFragmentId,tableId);
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

            function markSelected(id, tableId){
				// for header table, redirect to the row's link
				console.log(id);
				window.location.hash = '#' + id + "hl";
				// mark all related components on both code views and the table.
				markSelectedWithoutChangingTableFocus(id, tableId);
			}
            


			// to highlight code
			var selectedCodeFragmentId = null;
            function markSelectedWithoutChangingTableFocus(id, tableId) {
                if (selectedCodeFragmentId === id) {
                    return;
                }
            
                if (selectedCodeFragmentId !== null) {
                    resetCurrentFocus(); 
                }
            
                // Warna default berdasarkan ID
                let defaultColour = id.startsWith("c") ? "rgba(244,161,164,1)" : "rgba(101,244,104,1)";
            
                // Highlight kode baru di panel kiri & kanan
                recolorCodeFragment(id + "a", defaultColour);
                recolorCodeFragment(id + "b", defaultColour);
            
                // Tampilkan natural language explanation dan code counterpart
                document.getElementById(id + "he").style.display = "block";
                document.getElementById(id + "g").style.display = "block";
            
                // Simpan ID yang dipilih
                selectedCodeFragmentId = id;
            }


			function resetCurrentFocus(){
				// do nothing if selectedCodeFragmentId is null
				if(selectedCodeFragmentId == null)
					return;


				// reset the CSS of previously selected fragment
				var defaultColour = "";
				if(selectedCodeFragmentId.startsWith("c")){
					defaultColour = "rgba(244,211,214,1)";
				}else if(selectedCodeFragmentId.startsWith("s")){
					defaultColour = "rgba(171,244,174,1)";
				}

				// for natural language explanation
				document.getElementById(selectedCodeFragmentId +"he").style.display = "none";
				// for code fragment counterpart
				document.getElementById(selectedCodeFragmentId +"g").style.display = "none";
				// for left panel
				recolorCodeFragment(selectedCodeFragmentId +"a", defaultColour);
				// for right panel
				recolorCodeFragment(selectedCodeFragmentId +"b", defaultColour);

				selectedCodeFragmentId = null;
			}

			// recolor a code fragment with its following rows
			function recolorCodeFragment(id, defaultColour){
				document.getElementById(id).style.backgroundColor = defaultColour;
				// check the following rows which id is the same as the given parameter except concatenated with a positive number.
				for(var i=1;;i++){
					var childId = id + i;
					var child = document.getElementById(childId);
					if(child == null){
						break;
					}else{
						child.style.backgroundColor = defaultColour;
					}
				}
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
    .selected-row td {
        background-color: #a8c6e7 !important;
        transition: background-color 0.3s ease;
    }
	a {
         cursor: pointer;
         color:blue !important;
    }	
		
	.khususoriginal{
			background: #a8c6e7 !important ;
			color: black  !important ;
		}
	.btn-outline-primary:hover{
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
		  /* Menyesuaikan lebar modal dan posisi */
	.custom-modal {
		width: 50%; /* Setengah dari lebar layar */
		
		left: 0; /* Memposisikan di sebelah kiri */
		margin: 0; /* Menghilangkan margin agar modal penuh lebar */
	}
	.kesamaan {
        height: 100vh;
        margin: 0;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
	.kesamaanmodalbody {
        overflow-y: auto;
    }
	.custom-modal.right {
		left: auto; /* Mengembalikan ke posisi awal (kanan) */
		right: 0; /* Memposisikan di sebelah kanan */
	}
	.prettyprint ol.linenums {
        list-style-type: decimal; /* Pastikan pakai angka biasa */
    }
    
    .prettyprint ol.linenums li {
        counter-increment: list-number 1; /* Naik 1 per baris */
        list-style: none;
        position: relative;
    }
    
    .prettyprint ol.linenums li:before {
        content: counter(list-number);
        position: absolute;
        left: -2em; /* Geser agar sejajar */
    }
	@media only screen and (max-width: 425px) {
		.buttontambah{
			text-align: left;
			margin: 1rem 0 1rem 0;
		}
		tr td{
			font-size: 0.9rem;
		}
		.custom-modal {
          width: 100%; /* Setengah dari lebar layar */
          left: 0; /* Memposisikan di sebelah kiri */
          margin: 0; /* Menghilangkan margin agar modal penuh lebar */
        }

        .custom-modal.right {
          left: 0; /* Mengembalikan ke posisi awal (kanan) */
          right: 0; /* Memposisikan di sebelah kanan */
        }
		.kecilin{
			font-size: 0.9em;
		}
		.buttonmobile{
			width: 100%;
		}
	}
    	a{
    			text-decoration: none;
    			color: black;
    	}
		.commentsim{
			background-color:rgba(244,211,214,1);
		}
		.syntaxsim{
			background-color:rgba(171,244,174,1);
		}

	</style>

  </head>
  <body onload="construct()">
 <div class="container-fluid">
      <div class="titlepanel">
			<div class="row d-flex justify-content-center align-items-center  mx-3">
			<div class="col-md-6 layoutmobilestart">
			<img src="strange_html_layout_additional_files/logo.png" alt="logo" class="mobile" />
			<style>
				.layoutmobilestart{
					text-align:left;
				}
				.layoutmobileend{
					text-align:right;
				}
				.logout{
					margin-right:1rem;
				}
				.mobile {
					margin: 0;
					width: 100%;
					height: auto;
					max-height: 200px;
					max-width: 200px;
				}
				.navbarAdmin{
					background-color: #51adba;height:auto;padding-bottom:1rem;
				}
				.colNav{
					margin-bottom:-1.25rem;
				}
				.logoutli{
					margin-left:auto;
				}
				@media only screen and (max-width: 425px) {
					.mobile {
						margin: 1rem;
						width: 100%;
						height: auto;
						max-height: 150px;
						max-width: 150px;
					}
					.layoutmobilestart{
						text-align:center;
					}
					.layoutmobileend{
						text-align:center;
					}
					.logout{
						margin:0;
					}
					.navbarAdmin{
						background-color: #51adba;height:auto;padding-bottom:0rem;
					}
					.colNav{
						margin-bottom:1rem;
						text-align:left;
					}
					a{
						text-align:left;			
					}
					.logoutli{
						margin-left:0;
					}
					.khususoriginal{
						margin-bottom: .5rem;
					}
				}
			</style>
			
			</div>
			<div class="col-md-6 layoutmobileend">
			<?php
					if($human_language == 'en'){
						if($suspicion_type == 'real')
							echo '<div class="titlewrapper fs-1">Similarity report</div>';
						else{
						    if($isOverlyUnique == false){
							    echo '<div class="titlewrapper fs-1">Similarity simulation</div>';
						    } else{
						        echo '<div class="titlewrapper fs-2">Similarity simulation: overly unique submission</div>';
						    }
						}
					}else{
						if($suspicion_type == 'real')
							echo '<div class="titlewrapper fs-1">Laporan kesamaan</div>';
						else{
						    if($isOverlyUnique == false){
							    echo '<div class="titlewrapper fs-1">Simulasi kesamaan</div>';
						    }else{
						         echo '<div class="titlewrapper fs-2">Simulasi kesamaan: pekerjaan terlalu unik</div>';
						    }
						}
					}
					setHeaderReport("originality", $submission_id, $db);
				?>
			</div>
			</div>
			<div class="row d-flex justify-content-center  mx-1 mt-3 ">
				<?php
					// get the username and name of the victim
					$sqlt = "SELECT username, name FROM user
						WHERE user_id = '".$submitter_id."'";
					$resultt = mysqli_query($db,$sqlt);
					$rowt = $resultt->fetch_assoc();
				?>
				<div class="col-md-6" >
				
					<div class="subcontentwrapper my-1 fs-4"><?php echo ($human_language == 'en'? "Student ID": "ID mahasiswa"); ?><b>:</b> <?php echo $rowt['username'].' / ' . $rowt['name']; ?></div>
					
					<div class="subcontentwrapper my-1 fs-5"><?php echo ($human_language == 'en'? "Course": "Mata kuliah"); ?><b>:</b> <?php echo $_POST['course_name']; ?> </div>
			
					<div class="subcontentwrapper my-1 fs-5"><?php echo ($human_language == 'en'? "Assessment": "Tugas"); ?><b>:</b> <?php echo $_POST['assessment_name']; ?></div>
				</div>
				<?php
				echo '<div class="col-md-6">';
					if($human_language == 'en'){
					    echo '<div class="subcontentwrapper my-1 fs-5">Efficiency&nbsp;:'.$efficiencyPoint.'
							  <!-- Button trigger modal -->
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#efisiensi">
                                  Details
                                </button>
							  </div>
                                
                                <!-- Modal -->
                                <div class="modal fade" id="efisiensi" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                  <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                      <div class="modal-header">
                                        <h1 class="modal-title fs-5" id="exampleModalLabel">Efficiency</h1>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                      </div>
                                      <div class="modal-body">
                                          Efficiency is calculated based on the submission size. If yours is larger than the average size of already submitted works for that assessment, the score will be below 100. While this is not the best way to measure efficiency, it is the most efficient.
                                      </div>
                                    </div>
                                  </div>
							  </div>';
						if($suspicion_type == 'real'){
							echo '
							
								
								<div class="longsubtitlewrapper my-2 kecilin">Why the code is alerted? 
								
								<!-- Button trigger modal -->
								<button type="button" class="btn btn-primary buttonmobile " data-bs-toggle="modal" data-bs-target="#staticBackdropOverlyUnik2">
								Details
								</button>
	
								<!-- Modal -->
								<div class="modal fade" id="staticBackdropOverlyUnik2" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
								<div class="modal-dialog modal-dialog-centered ">
									<div class="modal-content">
									<div class="modal-header">
										<h1 class="modal-title fs-5" id="staticBackdropLabel">Why the code is alerted?</h1>
										<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
									</div>
									<div class="modal-body">
								    <div class="longsubcontentwrapper mb-2" id="message3">
                                        It is the proportion of code found different to those previously submitted by other students. High originality degree does not guarantee the submission to be not suspected for misconduct; the system\'s detection is not comprehensive.									</div>
									<div class="longsubcontentwrapper" id="message1">
                                        The alert is raised since the code shares obvious similarity to other students\' code that has been previously submitted.									</div>
								    </div>
									<div class="modal-footer">
										<button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal" aria-label="Understood">Understood</button>
									</div>
									</div>
								</div>
								</div>
								
								</div>
							';
						    echo '<div class="longsubtitlewrapper my-2 kecilin">What actions did the student possibly do that lead to this alerted similarity?
							
							<!-- Button trigger modal -->
							<button type="button" class="btn btn-primary buttonmobile " data-bs-toggle="modal" data-bs-target="#staticBackdropOverlyUnique3">
							Details
							</button>

							<!-- Modal -->
							<div class="modal fade" id="staticBackdropOverlyUnique3" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
							<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
								<div class="modal-content">
								<div class="modal-header">
									<h1 class="modal-title fs-5" id="staticBackdropLabel">What actions did the student possibly do that lead to this alerted similarity?</h1>
									<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
								</div>
								<div class="modal-body">
									<ol>
										<!-- sorted from positive to negative accusation -->
										<li>Discussing with another student how to approach a task and what resources to use, then developing the solution independently.</li>
										<li>Discussing the detail of your code with another student while working on it.</li>
										<li>Showing troublesome code to another student and asking them for advice on how to fix it.</li>
										<li><u>[Might be inappropriate]</u> Directly copy and paste code from artificial intelligence. </li>
										<li><u>[Might be inappropriate]</u> Asking another student to take troublesome code and get it working. </li>
										<li><u>[Might be inappropriate]</u> Copying an early draft of another student\'s work and developing it into your own.</li>
										<li><u>[Might be inappropriate]</u> Copying another student\'s code and changing it so that it looks quite different.</li>
										<li>After completing an assessment, adding features that you noticed when looking at another student\'s work. </li>
										<li><u>[Might be inappropriate]</u> Incorporating the work of another student without their permission.</li>
										<li><u>[Might be inappropriate]</u> Incorporating purchased code written by other students into your own work.</li>
										<li><u>[Might be inappropriate]</u> Submitting purchased code written by another student as your own work.</li>
										<li>Writing the code by yourself but this unexpectedly happens.</li>
									</ol>
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal" aria-label="Understood">Understood</button>
								</div>
								</div>
							</div>
							</div>
							';
						}
						else{
							echo '<div class="longsubtitlewrapper my-2 kecilin">How the originality degree is calculated? 
														
							<!-- Button trigger modal -->
							<button type="button" class="btn btn-primary buttonmobile " data-bs-toggle="modal" data-bs-target="#staticBackdropOverlyUnique1">
							Details
							</button>

							<!-- Modal -->
							<div class="modal fade" id="staticBackdropOverlyUnique1" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
							<div class="modal-dialog modal-dialog-centered ">
								<div class="modal-content">
								<div class="modal-header">
									<h1 class="modal-title fs-5" id="staticBackdropLabel">How the originality degree is calculated?</h1>
									<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
								</div>
								<div class="modal-body">
									It is the proportion of code found different to those previously submitted by other students. High originality degree does not guarantee the submission to be not suspected for misconduct; the system\'s detection is not comprehensive. 
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal" aria-label="Understood">Understood</button>
								</div>
								</div>
							</div>
							</div>
							
							</div>';
							
							if($isOverlyUnique){
								echo '
								<div class="longsubtitlewrapper my-2 kecilin">The code is overly unique. What are possible reasons? 
								<button type="button" class="btn btn-primary buttonmobile " data-bs-toggle="modal" data-bs-target="#staticBackdropOverlyUnique2">
    							Details
    							</button>
    							</div>
    
    							<!-- Modal -->
    							<div class="modal fade" id="staticBackdropOverlyUnique2" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    							<div class="modal-dialog modal-dialog-centered ">
    								<div class="modal-content">
    								<div class="modal-header">
    									<h1 class="modal-title fs-5" id="staticBackdropLabel">The code is overly unique. What are possible reasons?</h1>
    									<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    								</div>
    								<div class="modal-body">
    									<ol>
    										<!-- sorted from positive to negative accusation -->
    										<li>The submission is submitted early and has limited submissions to be compared.</li>
    										<li>The submitter has more experience in programming.</li>
    										<li><u>[Might be inappropriate]</u> Incorporating artificial intelligence assisted code into your own work.</li>
    										<li><u>[Might be inappropriate]</u> Asking someone to do your work.</li>
    										<li><u>[Might be inappropriate]</u> Incorporating purchased code written by other students into your own work.</li>
    										<li><u>[Might be inappropriate]</u> Submitting purchased code written by another student as your own work.</li>
    										<li><u>[Might be inappropriate]</u> Basing an assessment largely on work that you wrote and submitted for another assessment.</li>
    									</ol>

    								</div>
    								<div class="modal-footer">
    									<button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal" aria-label="Understood">Understood</button>
    								</div>
    								</div>
    							</div>
    							</div>';
							}
							echo '<div class="longsubtitlewrapper my-2 kecilin">What actions that may lead similarity?
								<!-- Button trigger modal -->
    							<button type="button" class="btn btn-primary buttonmobile " data-bs-toggle="modal" data-bs-target="#staticBackdropOverlyUnique3">
    							Details
    							</button></div>
    
    							<!-- Modal -->
    							<div class="modal fade" id="staticBackdropOverlyUnique3" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    							<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    								<div class="modal-content">
    								<div class="modal-header">
    									<h1 class="modal-title fs-5" id="staticBackdropLabel">What actions that may lead similarity?</h1>
    									<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    								</div>
    								<div class="modal-body">
    									<ol>
    										<!-- sorted from positive to negative accusation -->
    										<li>Discussing with another student how to approach a task and what resources to use, then developing the solution independently.</li>
    										<li>Discussing the detail of your code with another student while working on it.</li>
    										<li>Showing troublesome code to another student and asking them for advice on how to fix it.</li>
    										<li><u>[Might be inappropriate]</u> Directly copy and paste code from artificial intelligence. </li>
    										<li><u>[Might be inappropriate]</u> Asking another student to take troublesome code and get it working. </li>
    										<li><u>[Might be inappropriate]</u> Copying an early draft of another student\'s work and developing it into your own.</li>
    										<li><u>[Might be inappropriate]</u> Copying another student\'s code and changing it so that it looks quite different.</li>
    										<li> After completing an assessment, adding features that you noticed when looking at another student\'s work. </li>
    										<li><u>[Might be inappropriate]</u> Incorporating the work of another student without their permission.</li>
    										<li><u>[Might be inappropriate]</u> Incorporating purchased code written by other students into your own work.</li>
    										<li><u>[Might be inappropriate]</u> Submitting purchased code written by another student as your own work.</li>
    										<li>Writing the code by yourself but this unexpectedly happens.</li>
    									</ol>

    								</div>
    								<div class="modal-footer">
    									<button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal" aria-label="Understood">Understood</button>
    								</div>
    								</div>
    							</div>
    							</div>
							';
						}
					}else{
					    echo '<div class="subcontentwrapper my-1 fs-5">
					               Efisiensi:&nbsp;'.$efficiencyPoint.'
							  <!-- Button trigger modal -->
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#efisiensi">
                                  Details
                                </button>
							  </div>
                                
                                <!-- Modal -->
                                <div class="modal fade" id="efisiensi" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                  <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                      <div class="modal-header">
                                        <h1 class="modal-title fs-5" id="exampleModalLabel">Efisiensi</h1>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                      </div>
                                      <div class="modal-body">
                                           Efisiensi dihitung berdasarkan ukuran karya. Jika ukurannya lebih besar daripada ukuran rata-rata karya yang sudah dikumpulkan sebelumnya untuk tugas ini, nilainya akan di bawah 100. Ini bukan cara terbaik menghitung efisiensi namun ini yang paling efisien.
                                      </div>
                                    </div>
                                  </div>
                                </div>
                                

								';
						if($suspicion_type == 'real'){
							echo '
								<div class="longsubtitlewrapper my-2 kecilin">Mengapa kode ini ditandai? 
								
								<!-- Button trigger modal -->
							<button type="button" class="btn btn-primary buttonmobile " data-bs-toggle="modal" data-bs-target="#staticBackdropreal">
							Details
							</button>

							<!-- Modal -->
							<div class="modal fade" id="staticBackdropreal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
							<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
								<div class="modal-content">
								<div class="modal-header">
									<h1 class="modal-title fs-5" id="staticBackdropLabel">Mengapa kode ini ditandai?</h1>
									<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
								</div>
								<div class="modal-body">
								    <div class="longsubcontentwrapper mb-2" id="message3">
										Ini adalah proporsi kode yang ditemukan berbeda dengan kode-kode yang sudah dikumpulkan oleh mahasiswa-mahasiswa lain sebelumnya. Originality degree yang tinggi tidak menjamin tugas ini tidak dicurigai mencontek; deteksi sistem tidak komprehensif.
									</div>
									<div class="longsubcontentwrapper" id="message1">
										Alert didasarkan dari kesamaan kentara dengan sebagian kode program dari mahasiswa-mahasiswa lain yang telah dikumpulkan sebelumnya.
									</div>
									
									
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal" aria-label="Understood">Understood</button>
								</div>
								</div>
							</div>
							</div>
							</div>
							';
							echo '<div class="longsubtitlewrapper my-2 kecilin">Tindakan-tindakan apa saja yang dapat menghasilkan kesamaan?
							
							<!-- Button trigger modal -->
							<button type="button" class="btn btn-primary buttonmobile " data-bs-toggle="modal" data-bs-target="#staticBackdropOverlyUnique3">
							Details
							</button>

							<!-- Modal -->
							<div class="modal fade" id="staticBackdropOverlyUnique3" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
							<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
								<div class="modal-content">
								<div class="modal-header">
									<h1 class="modal-title fs-5" id="staticBackdropLabel">Tindakan-tindakan apa saja yang dapat menghasilkan kesamaan?</h1>
									<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
								</div>
								<div class="modal-body">
									<ol>
										<!-- sorted from positive to negative accusation -->
										<li>Berdiskusi dengan siswa lain tentang cara mengerjakan tugas dan sumber-sumber apa yang sebaiknya digunakan, kemudian mengembangkan solusinya secara mandiri.</li>
										<li>Mendiskusikan kode anda secara detil dengan siswa lain pada saat mengerjakannya.</li>
										<li>Memperlihatkan kode yang bermasalah kepada siswa lain dan meminta saran tentang cara memperbaikinya.</li>
										<li><u>[Mungkin tidak pantas]</u> Menyalin langsung karya kecerdasan buatan.</li>
										<li><u>[Mungkin tidak pantas]</u> Meminta siswa lain untuk memperbaiki kode yang bermasalah.</li>
										<li><u>[Mungkin tidak pantas]</u> Menyalin draf awal hasil karya siswa lain dan mengembangkannya menjadi milik anda.</li>
										<li><u>[Mungkin tidak pantas]</u> Menyalin kode hasil karya siswa lain dan mengubahnya sehingga terlihat agak berbeda.</li>
										<li> Setelah menyelesaikan suatu tugas, anda menambahkan fitur-fitur yang terinspirasi setelah anda  melihat hasil karya siswa lain. </li>
										<li><u>[Mungkin tidak pantas]</u> Memasukkan pekerjaan siswa lain tanpa meminta izin yang kepada bersangkutan.</li>
										<li><u>[Mungkin tidak pantas]</u> Membeli kode yang ditulis oleh siswa lain untuk dimasukkan ke dalam pekerjaan anda sendiri.</li>
										<li><u>[Mungkin tidak pantas]</u> Membayar siswa lain untuk menulis kode dan mengirimkan sebagai karya anda sendiri.</li>
										<li>Menulis kode secara individu namun kecurigaan ini secara tidak diduga muncul.</li>
									</ol>
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal" aria-label="Understood">Understood</button>
								</div>
								</div>
							</div>
							</div>
							';
						}else{
							echo '<div class="longsubtitlewrapper my-2 kecilin">Bagaimana originality degree dihitung? 
														
							<!-- Button trigger modal -->
							<button type="button" class="btn btn-primary buttonmobile " data-bs-toggle="modal" data-bs-target="#staticBackdropOverlyUnique1">
							Details
							</button>

							<!-- Modal -->
							<div class="modal fade" id="staticBackdropOverlyUnique1" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
							<div class="modal-dialog modal-dialog-centered ">
								<div class="modal-content">
								<div class="modal-header">
									<h1 class="modal-title fs-5" id="staticBackdropLabel">Bagaimana originality degree dihitung?</h1>
									<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
								</div>
								<div class="modal-body">
									Ini adalah proporsi kode yang ditemukan berbeda dengan kode-kode yang sudah dikumpulkan oleh mahasiswa-mahasiswa lain sebelumnya. Originality degree yang tinggi tidak menjamin tugas ini tidak dicurigai mencontek; deteksi sistem tidak komprehensif.

								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal" aria-label="Understood">Understood</button>
								</div>
								</div>
							</div>
							</div>
							
							</div>';
								
							if($isOverlyUnique){
								echo '
								<div class="longsubtitlewrapper my-2 kecilin">Kode ini terlalu berbeda dengan yang lain. Apa saja penyebabnya? 
								<!-- Button trigger modal -->
							<button type="button" class="btn btn-primary buttonmobile " data-bs-toggle="modal" data-bs-target="#staticBackdropOverlyUnique2">
							Details
							</button>

							<!-- Modal -->
							<div class="modal fade" id="staticBackdropOverlyUnique2" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
							<div class="modal-dialog modal-dialog-centered ">
								<div class="modal-content">
								<div class="modal-header">
									<h1 class="modal-title fs-5" id="staticBackdropLabel">Kode ini terlalu berbeda dengan yang lainnya. Apa saja kemungkinan penyebabnya?</h1>
									<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
								</div>
								<div class="modal-body">
									<ol>
										<!-- sorted from positive to negative accusation -->
										<li>Kode ini dikumpulkan awal dan belum banyak kode lain yang bisa dibandingkan.</li>
										<li>Pengumpul memiliki pengalaman lebih di pemrograman.</li>
										<li><u>[Mungkin tidak pantas]</u> Memasukkan kode hasil kecerdasan buatan di pekerjaan anda.</li>
										<li><u>[Mungkin tidak pantas]</u> Meminta seseorang untuk mengerjakan tugas anda.</li>
										<li><u>[Mungkin tidak pantas]</u> Membeli kode yang ditulis oleh siswa lain untuk dimasukkan ke dalam pekerjaan anda sendiri.</li>
										<li><u>[Mungkin tidak pantas]</u> Membayar siswa lain untuk menulis kode dan mengirimkan sebagai karya anda sendiri.</li>
										<li><u>[Mungkin tidak pantas]</u> Mendasarkan kode pada kerjaan yang sudah dibuat untuk tugas lain.</li>
									</ol>
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal" aria-label="Understood">Understood</button>
								</div>
								</div>
							</div>
							</div>';
							}
								
							echo '<div class="longsubtitlewrapper my-2 kecilin">Tindakan-tindakan apa saja yang dapat menghasilkan kesamaan?
							
							<!-- Button trigger modal -->
							<button type="button" class="btn btn-primary buttonmobile " data-bs-toggle="modal" data-bs-target="#staticBackdropOverlyUnique3">
							Details
							</button>

							<!-- Modal -->
							<div class="modal fade" id="staticBackdropOverlyUnique3" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
							<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
								<div class="modal-content">
								<div class="modal-header">
									<h1 class="modal-title fs-5" id="staticBackdropLabel">Tindakan-tindakan apa saja yang dapat menghasilkan kesamaan?</h1>
									<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
								</div>
								<div class="modal-body">
									<ol>
										<!-- sorted from positive to negative accusation -->
										<li>Berdiskusi dengan siswa lain tentang cara mengerjakan tugas dan sumber-sumber apa yang sebaiknya digunakan, kemudian mengembangkan solusinya secara mandiri.</li>
										<li>Mendiskusikan kode anda secara detil dengan siswa lain pada saat mengerjakannya.</li>
										<li>Memperlihatkan kode yang bermasalah kepada siswa lain dan meminta saran tentang cara memperbaikinya.</li>
										<li><u>[Mungkin tidak pantas]</u> Menyalin langsung karya kecerdasan buatan.</li>
										<li><u>[Mungkin tidak pantas]</u> Meminta siswa lain untuk memperbaiki kode yang bermasalah.</li>
										<li><u>[Mungkin tidak pantas]</u> Menyalin draf awal hasil karya siswa lain dan mengembangkannya menjadi milik anda.</li>
										<li><u>[Mungkin tidak pantas]</u> Menyalin kode hasil karya siswa lain dan mengubahnya sehingga terlihat agak berbeda.</li>
										<li> Setelah menyelesaikan suatu tugas, anda menambahkan fitur-fitur yang terinspirasi setelah anda  melihat hasil karya siswa lain. </li>
										<li><u>[Mungkin tidak pantas]</u> Memasukkan pekerjaan siswa lain tanpa meminta izin yang kepada bersangkutan.</li>
										<li><u>[Mungkin tidak pantas]</u> Membeli kode yang ditulis oleh siswa lain untuk dimasukkan ke dalam pekerjaan anda sendiri.</li>
										<li><u>[Mungkin tidak pantas]</u> Membayar siswa lain untuk menulis kode dan mengirimkan sebagai karya anda sendiri.</li>
										<li>Menulis kode secara individu namun kecurigaan ini secara tidak diduga muncul.</li>
									</ol>
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal" aria-label="Understood">Understood</button>
								</div>
								</div>
							</div>
							</div>
							';
						}
					}
				echo'</div>';
				?>
			</div>
		</div>
    </div>
  <hr />
  <section >
    <div class="container-fluid" >
		<div class="row d-flex justify-content-center mb-5">
			
			<div class="col-md-6">
					<div class="subtitlewrapper fs-4" style="width:60%;"><?php echo ($human_language == 'en'? "Similar contents: ": "Konten yang sama: "); ?> </div>
			
						<table id="lecturerDashboardTable" class="table table-bordered table-striped responsive nowrap" style="width:100%" >
							<thead>
								<tr>
									<th>ID</th>
									<th><?php echo ($human_language == 'en'? "Similarity type": "Tipe kesamaan"); ?></th>
									<th><?php echo ($human_language == 'en'? "Length": "Panjang"); ?></th>
									<th><?php echo ($human_language == 'en'? "Warning level": "Level peringatan"); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php echo $tableInfo; ?>
							</tbody>
							</table>
					
			</div>
			<div class="col-md-6" style="max-height: 40vh; overflow-y: auto;">
			    <div class="longsubtitlewrapper fs-4 " id="penjelasan"><?php echo ($human_language == 'en'? "Similarity explanation:": "Penjelasan kesamaan:"); ?></div>

    			<div class="explanationpanel  border border-1">
    			 
    				<?php echo $explanationInfo; ?>
    			</div>
			</div>
	
		<div class="row d-flex justify-content-center">
		    <div class="col-md-12 mt-2">
		        <div class="row d-flex justify-content-center">
		            <div class="col-md-6">
		                <h3>Kode Anda</h3>
        		        <div class="codeview border border-1" style="max-height: 50vh; overflow-y: auto;">
        					<pre class="prettyprint linenums ">
        						<?php echo $markedCode; ?>
        					</pre>
        				</div>
        			</div>
        			<div class="col-md-6">
        			    <h3>Kode Rekan</h3>
        			    <div class="codefragmentview" style="max-height: 50vh; overflow-y: auto;">
							<div class="generatedfragment border border-1" style="max-height: 50vh; overflow-y: auto;" id='dg'> <pre class="prettyprint linenums"></pre></div>
							<?php echo $artificialCode; ?>
						</div>
        			</div>
		        </div>
		    </div>
			

		
			</div>
		</div>
	</div>
  </section>

  <script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function () {
    var table = new DataTable('#lecturerDashboardTable', {
        responsive: true,
        pageLength: 3,
        lengthMenu: [3, 5, 10, 25],
    });

    $('#lecturerDashboardTable tbody').on('click', 'a', function (e) {
        e.preventDefault(); 
        
        var row = $(this).closest('tr'); 
        
        if (row.hasClass('selected-row')) {
            row.removeClass('selected-row'); 
        } else {
            table.$('tr.selected-row').removeClass('selected-row'); 
            row.addClass('selected-row');
        }
    });

    // Menjaga warna tetap ada meskipun berpindah halaman di DataTables
    table.on('draw', function () {
        table.$('tr.selected-row').each(function () {
            $(this).addClass('selected-row');
        });
    });
});

</script>

<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>

    <script>
      // Menggunakan jQuery untuk menanggapi klik pada tombol
      $(document).ready(function () {
        $('#triggerButton').click(function () {
          // Memunculkan kedua modal
          $('#staticBackdroptanda').modal('show');
          $('#staticBackdroppembanding').modal('show');
        });
        
      });

          
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll("[id$='he'], [id$='g']").forEach(el => {
            el.style.display = "none";
        });
    
        // Delegasi event untuk semua klik pada tabel
        document.querySelector("table tbody").addEventListener("click", function (event) {
            let clickedLink = event.target.closest("a");
            if (!clickedLink) return; // Pastikan yang diklik adalah link
    
            setTimeout(function () {
                let nextLink = document.querySelector(clickedLink.getAttribute("href"));
                if (nextLink) {
                    nextLink.click();
                }
            }, 50); // Delay untuk memastikan elemen sudah muncul sebelum klik kedua
        });
    
        // Jika ingin klik otomatis pertama kali pada baris pertama:
        let firstRow = document.querySelector("table tbody tr:first-child a");
        if (firstRow) {
            firstRow.click();
        }
    });


    </script>
  </body>
</html>