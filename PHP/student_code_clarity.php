<?php
	// if the suggestion id does not exist, redirect to login
	if(isset($_GET['id']) == false || $_GET['id'] == ''){
		header('Location: index.php');
		exit;
	}

	include("_config.php");
	session_start();

	// get all data required for this page
	$sqlt = "SELECT code_clarity_suggestion.suggestion_id,
		code_clarity_suggestion.marked_code, code_clarity_suggestion.table_info,
		code_clarity_suggestion.explanation_info, submission.submitter_id, 
		assessment.name AS assessment_name, assessment.submission_file_extension, course.name AS course_name, course.course_id AS course_id, submission.submission_id   
		FROM code_clarity_suggestion
		INNER JOIN submission ON submission.submission_id = code_clarity_suggestion.submission_id
		INNER JOIN assessment ON assessment.assessment_id = submission.assessment_id
		INNER JOIN course ON course.course_id = assessment.course_id
		WHERE code_clarity_suggestion.public_suggestion_id = '".$_GET['id']."'";
	$resultt = mysqli_query($db,$sqlt);
	$rowt = $resultt->fetch_assoc();

	// if the public suggestion id is invalid, redirect to login
	if(is_null($rowt)){
		header('Location: index.php');
		exit;
	}

	$markedCode = $rowt['marked_code'];
	$tableInfo = $rowt['table_info'];
	$explanationInfo = $rowt['explanation_info'];
	$submitter_id = $rowt['submitter_id'];
	$course_id = $rowt['course_id'];
	$assessment_name =  $rowt['assessment_name'];
	$course_name =  $rowt['course_name'];
	$submission_id = $rowt['submission_id'];
	$file_extension = $rowt['submission_file_extension'];
	$file_extension = str_replace('zip_', '', $file_extension); // remove prefix 'zip_' if any
	
	// for access statistics of suggestion page
	$sql = "INSERT INTO suggestion_access (suggestion_id) VALUES ('".$rowt['suggestion_id']."')";
	$db->query($sql);
	
?>
<html>
	<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">

		<title> E-Strange: <?php echo ($human_language == 'en'? "Code quality suggestion": "Rekomendasi kualitas kode"); ?></title>
		<link rel="icon" href="strange_html_layout_additional_files/icon.png">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
		<link href="bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">

		<!-- jQuery -->
		<script src="https://code.jquery.com/jquery-3.7.1.slim.min.js" integrity="sha256-kmHvs0B+OpCW5GVHUNjv9rOmY0IvSIRcf7zGUDTDQM8=" crossorigin="anonymous"></script>

		<!-- DataTables JS -->
		<link rel="stylesheet" type="text/css" href="datatables/jquery.dataTables.min.css">
		<script type="text/javascript" src="datatables/jquery.dataTables.min.js"></script>
		<link rel="stylesheet" type="text/css" href="datatables/responsive.bootstrap5.min.css">
		<script type="text/javascript" src="datatables/dataTables.responsive.min.js"></script>
		<script type="text/javascript" src="datatables/responsive.bootstrap5.min.js"></script>



		
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
			 if(isset($_SESSION['role']) && $_SESSION['role'] == 'student'){
				 // get three earliest notification for courses in which game feature is active
				 // and student participation in the game is also active
				 $sqlt = "SELECT game_unobserved_notif.notification_id, game_unobserved_notif.message 
						FROM game_unobserved_notif 
						INNER JOIN game_student_course ON game_student_course.gs_id = game_unobserved_notif.gs_id 
						INNER JOIN game_course ON game_course.course_id = game_student_course.course_id 
						WHERE game_student_course.student_id = '".$submitter_id."' 
						AND game_student_course.course_id = '".$course_id."' 
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
						   notification".$i.".on('click', ({target, event}) => {window.location.href = 'student_game.php?id=".$course_id."';});";
						   
						   
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
						rows[i].style.backgroundColor = "rgba(171,244,174,1)";
					}else {
						rows[i].style.backgroundColor = "rgba(244,224,104,1)";
					}
				}
			}

			function markSelected(id, tableId){
				// for header table, redirect to the row's link
				window.location.hash = '#' + id + "hl";
				// mark all related components on both code views and the table.
				markSelectedWithoutChangingTableFocus(id, tableId);
			}

            var selectedCodeFragmentId = null;
            var selectedTwice = false;
            
            function markSelectedWithoutChangingTableFocus(id, tableId) {
                if (selectedCodeFragmentId === id) {
                    selectedTwice = !selectedTwice;
                } else {
                    if (selectedCodeFragmentId !== null) {
                        resetCurrentFocus();
                    }
                    selectedTwice = false;
                }
            
                var defaultColour = id.startsWith("c") ? "rgba(244,224,104,1)" : "rgba(171,244,174,1)";
                var highlightColour = id.startsWith("c") ? "rgba(244,180,50,1)" : "rgba(50,200,50,1)";
                var tableHighlight = "rgba(200,200,255,0.5)";
            
                var appliedColour = selectedTwice ? defaultColour : highlightColour;
                highlightElement(id + "a", appliedColour);
                highlightElement(id + "hr", appliedColour);
            
                var explanationElement = document.getElementById(id + "he");
                if (explanationElement) {
                    explanationElement.style.display = "block";
                }
            
                selectedCodeFragmentId = selectedTwice ? null : id;
                updateHash(id + "a");
            }
            
            function resetCurrentFocus() {
                if (!selectedCodeFragmentId) return;
            
                var defaultColour = selectedCodeFragmentId.startsWith("c") ? "rgba(244,224,104,1)" : "rgba(101,244,104,1)";
                recolorCodeFragment(selectedCodeFragmentId + "a", defaultColour);
                recolorCodeFragment(selectedCodeFragmentId + "hr", defaultColour);
            
                var explanationElement = document.getElementById(selectedCodeFragmentId + "he");
                if (explanationElement) {
                    explanationElement.style.display = "none";
                }
                
                selectedCodeFragmentId = null;
            }
            
            function highlightElement(id, color) {
                var element = document.getElementById(id);
                if (element) {
                    element.style.backgroundColor = color;
                    element.style.transition = "background-color 0.3s ease";
                }
            }
            
            function recolorCodeFragment(id, color) {
                var element = document.getElementById(id);
                if (element) {
                    element.style.backgroundColor = color;
                    element.style.transition = "background-color 0.3s ease";
                }
            }

            
            function updateHash(id) {
                history.replaceState(null, null, " "); // Kosongkan hash sementara
                setTimeout(() => {
                    window.location.hash = "#" + id;
                }, 50);
            }

        
        // Perbaiki recolor agar tidak menghapus highlight baru
        function recolorCodeFragment(id, defaultColour) {
            var element = document.getElementById(id);
            if (element) {
                element.style.backgroundColor = defaultColour;
                element.style.transition = "background-color 0.3s ease"; // Animasi smooth
            }
        }
    </script>

  </head>
  	<style>
     @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300&display=swap');
    body {
      /* font-family: "Times New Roman", Times, serif; */
      font-family: 'Montserrat', sans-serif;
    }
	.khususquality{
		background: #a8c6e7 !important ;
		color: black  !important ;
	}
	a {
      cursor: pointer;
    }
    tr.selected-row td {
        background-color: #a8c6e7 !important; 
        transition: background-color 0.3s ease;
    }

	.btn-primary{
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
  <body onload="construct()">
  	<div class="container-fluid">
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
					.khususquality{
						margin-bottom: .5rem;
					}
				}
			</style>
			
			</div>
			<div class="col-md-6 layoutmobileend fs-3">
			<?php
				echo ($human_language == 'en'? "<div class='fs-1'>Code quality suggestion</div>": "<div class='fs-1'>Rekomendasi kualitas kode</div>");
				setHeaderReport("quality", $submission_id, $db);
				?>

			</div>
		</div>
		<div class="row d-flex justify-content-center  mx-1 mt-3 ">
				<?php
						// get the username and name of the submitter
						$sqlt = "SELECT username, name FROM user
							WHERE user_id = '".$submitter_id."'";
						$resultt = mysqli_query($db,$sqlt);
						$rowt = $resultt->fetch_assoc();
					?>
					<div class="col-md-6" >
							
							<div class="subcontentwrapper my-1 fs-4"><?php echo ($human_language == 'en'? "Student ID": "ID mahasiswa"); ?><b>:</b> <?php echo $rowt['username'].' / ' . $rowt['name']; ?> </div>
							
							<div class="subcontentwrapper my-1 fs-5"><?php echo ($human_language == 'en'? "Course": "Mata kuliah"); ?><b>:</b> <?php echo $course_name; ?>  </div>
					
							<div class="subcontentwrapper my-1 fs-5"><?php echo ($human_language == 'en'? "Assessment": "Tugas"); ?><b>:</b> <?php echo $assessment_name; ?> </div>
						</div>
						<?php
				echo '<div class="col-md-6 px-5">';
					if($human_language == 'en'){
                        echo '
                                <div class="longsubtitlewrapper">How the quality degree is calculated?  
                                
                                <!-- Button trigger modal -->
                                <button type="button" class="btn btn-primary buttonmobile " data-bs-toggle="modal" data-bs-target="#staticBackdropOverlyUnique1">
                                Details
                                </button>
                        
                                <!-- Modal -->
                                <div class="modal fade" id="staticBackdropOverlyUnique1" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                    <div class="modal-content">
                                    <div class="modal-header">
                                        <h1 class="modal-title fs-5" id="staticBackdropLabel">How the quality degree is calculated? </h1>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                <div class="longsubcontentwrapper" id="message2">
                                    It is the proportion of code blocks without code quality issues. Each code block is approximately eight program statements. High quality degree does not guarantee the submission to be readable; the system\'s code analysis does not check the semantic of identifiers (e.g., variable names, function names).
                                </div>
                                <div class="modal-footer">
                                <button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal" aria-label="Understood">Understood</button>
                            </div>
                            </div>
                        </div>
                        </div>
                        
                        </div>
                                <div class="longsubtitlewrapper mt-2">What are general guidelines for code quality?
                                
                                <!-- Button trigger modal -->
                                <button type="button" class="btn btn-primary buttonmobile " data-bs-toggle="modal" data-bs-target="#staticBackdropOverlyUnique2">
                                Details
                                </button>
                        
                                <!-- Modal -->
                                <div class="modal fade" id="staticBackdropOverlyUnique2" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                    <div class="modal-content">
                                    <div class="modal-header">
                                        <h1 class="modal-title fs-5" id="staticBackdropLabel">What are general guidelines for code quality?</h1>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                <div class="longsubcontentwrapper" id="message3">
                                    <ol>
										<li>Identifier names or comments should be at least three character long as shorter words are likely to be meaningless</li>
										<li>Identifier names or comments should contain at least one meaningful word</li>
										<li>Words in identifier names or comments should be correctly written</li>
										<li>Proper transtition between words in identifier names is necessary and should be consistent; it can be either capitalisation (thisIsVar) or underscore (this_is_var)</li>
										<li>Each syntax block should be accompanied with an explaining comment right before it</li>
										<li>Commented program code might be removed if they are unnecessary</li>';
            							// echo additional rules from third party library
            							if($file_extension == 'java')
            								echo "<li>Declarations should start with those of static variables, followed by attributes, constructors, and other methods</li>
            									 <li>Each program line is expected to be reasonably short</li>
            									 <li>Each statement is expected to be in its own line</li>
            									 <li>An empty line might be needed to separate code components</li>
            									 <li>No empty syntax blocks are expected</li>
            									 <li>Parentheses and semicolons are expected to be adequately used</li>
            									 <li>Braces are expected even when a syntax block only has one program statement</li>
            									 <li>Strings are expected to be compared with 'equals' or one of its derivations</li>
            									 <li>Each variable declaration is expected to be in its own line and statement</li>
            									 <li>Each time a non-static attribute is accessed, it is expected to use 'this'</li>
            									 <li>Constant names are expected to be without lowercased letters</li>
            									 <li>Array brackets are expected to be declared before the identifier name to make the data type more explicit</li>
            									 <li>Boolean expressions and logic are expected to be efficient</li>
            									 <li>Each complex boolean expression should be broken down to a few simpler expressions</li>
            									 <li>Inline conditionals are hard to understand and they are not expected to be used</li>
            									 <li>Each method is expected to contain a reasonable number of program statements</li>
            									 <li>Nested syntax blocks are expected to have reasonable depth</li>
            									 <li>Abstract class name and prefix 'Abstract' are expected to be consistently used together</li>";
            							else if($file_extension == 'py')
            								echo "<li>For readability, each line is expected to have fewer than 80 characters</li>
            									 <li>Each statement is expected to be on its own line</li>
            									 <li>Only assigned identifiers should be used</li>
            									 <li>Indentation is expected to be adequately used</li>
            									 <li>Comment should have at least one space between '#' and the content</li>
            									 <li>A semicolon is not expected if it is at the end of a line</li>
            									 <li>In a function, each parameter is expected to have its own name</li>
            									 <li>'break' and 'continue' should be in loop</li>
            									 <li>'return' should be in a function</li>  <li>Modules are imported only when used</li>";
            							// embed closing header here
            							echo '	</ol>

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal" aria-label="Understood">Understood</button>
                            </div>
                            </div>
                        </div>
                        </div>
                        </div>
                            ';
					}else{
							echo '
								<div class="longsubtitlewrapper">Bagaimana quality degree dihitung? 
								
								<!-- Button trigger modal -->
								<button type="button" class="btn btn-primary buttonmobile " data-bs-toggle="modal" data-bs-target="#staticBackdropOverlyUnique1">
								Details
								</button>
	
								<!-- Modal -->
								<div class="modal fade" id="staticBackdropOverlyUnique1" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
								<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
									<div class="modal-content">
									<div class="modal-header">
										<h1 class="modal-title fs-5" id="staticBackdropLabel">Bagaimana quality degree dihitung?</h1>
										<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
									</div>
									<div class="modal-body">
								<div class="longsubcontentwrapper" id="message2">
									Ini adalah proporsi blok kode yang tidak memiliki isu terkait kualitas kode. Setiap blok kode berukuran sekitar delapan statemen program. Quality degree yang tinggi tidak menjamin tugas ini mudah dibaca; analisis kode sistem tidak mengecek makna dari identifier (misal nama variabel dan nama fungsi). 
								</div>
								<div class="modal-footer">
								<button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal" aria-label="Understood">Understood</button>
							</div>
							</div>
						</div>
						</div>
						
						</div>
								<div class="longsubtitlewrapper mt-2">Apa panduan umum terkait kualitas kode?
								
								<!-- Button trigger modal -->
								<button type="button" class="btn btn-primary buttonmobile " data-bs-toggle="modal" data-bs-target="#staticBackdropOverlyUnique2">
								Details
								</button>
	
								<!-- Modal -->
								<div class="modal fade" id="staticBackdropOverlyUnique2" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
								<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
									<div class="modal-content">
									<div class="modal-header">
										<h1 class="modal-title fs-5" id="staticBackdropLabel">Apa panduan umum terkait kualitas kode?</h1>
										<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
									</div>
									<div class="modal-body">
								<div class="longsubcontentwrapper" id="message3">
									<ol>
										<li>Nama identifier atau komentar harus paling tidak berisi tiga karakter karena kata-kata yang lebih pendek biasanya tidak bermakna</li>
										<li>Nama identifier atau komentar harus berisi paling tidak satu kata bermakna</li>
										<li>Kata-kata di nama identifier atau komentar harus tertulis dengan benar</li>
										<li>Transisi antar kata di nama identifier diperlukan dan harus konsisten; transisi dapat berupa kapitalisasi (iniSebuahVar) atau underskor (ini_sebuah_var)</li>
										<li>Setiap blok sintaks harus dilengkapi dengan komentar penjelas tepat sebelum blok sintaks tersebut</li>
										<li>Kode program yang dijadikan komentar mungkin dapat dihapus jika tidak diperlukan</li>';
							// echo additional rules from third party library
							if($file_extension == 'java')
								echo "<li>Deklarasi sebaiknya dimulai dari variabel static, diikuti dengan atribut, konstruktor, dan method-method lainnya</li>
									 <li>Setiap baris program sebaiknya pendek</li>
									 <li>Setiap statemen sebaiknya ada di baris tersendiri</li>
									 <li>Sebuah baris kosong mungkin dibutuhkan untuk memisahkan komponen kode</li>
									 <li>Sebaiknya tidak ada blok sintaks kosong</li>
									 <li>Kurung dan titik koma sebaiknya digunakan secukupnya</li>
									 <li>Kurung sebaiknya ada walaupun sebuah blok sintaks hanya berisi satu statemen program</li>
									 <li>String sebaiknya dibandingkan dengan 'equals' atau turunannya</li>
									 <li>Setiap deklarasi variabel sebaiknya ada di baris dan statemen tersendiri</li>
									 <li>Setiap kali atribut non-static diakses, ada baiknya menggunakan 'this'</li>
									 <li>Nama konstan sebaiknya tanpa huruf kecil</li>
									 <li>Kurung siku array sebaiknya dideklarasi sebelum nama identifiernya agar tipe datanya terlihat lebih eksplisit</li>
									 <li>Ekspresi dan logika boolean sebaiknya efisien</li>
									 <li>Setiap ekspresi boolean kompleks perlu dipecah menjadi beberapa ekspresi yang lebih simpel</li>
									 <li>If-else satu baris sulit dimengerti dan sebaiknya tidak digunakan</li>
									 <li>Setiap method sebaiknya berisi program statemen dengan jumlah yang masuk akal</li>
									 <li>Blok sintaks bersarang sebaiknya memiliki kedalaman yang masuk akal</li>
									 <li>Nama kelas abstrak dan prefik 'Abstract' sebaiknya konsisten digunakan bersamaan</li>";
							else if($file_extension == 'py')
								echo "<li>Untuk kemudahan pembacaan, setiap baris sebaiknya berisi paling banyak 80 karakter</li>
									 <li>Setiap statemen sebaiknya memiliki baris tersendiri</li>
									 <li>Hanya identifier yang sudah diberi nilai yang boleh dipakai</li>
									 <li>Indentasi sebaiknya digunakan secukupnya</li>
									 <li>Komentar harus memiliki paling tidak satu spasi antara '#' dan konten komentar</li>
									 <li>Titik koma tidak dibutuhkan kalau karakter tersebut ada di akhir baris</li>
									 <li>Di dalam sebuah fungsi, setiap parameter sebaiknya memiliki nama tersendiri</li>
									 <li>'break' dan 'continue' harus ada di dalam pengulangan</li>
									 <li>'return' harus ada di dalam fungsi</li>
									 <li>Modul-modul hanya diimpor jika dipakai</li>";			
							// embed closing header here
							echo '	</ol>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal" aria-label="Understood">Understood</button>
							</div>
							</div>
						</div>
						</div>
						</div>
							';
					}
				echo '</div>';
				 ?>			
	</div>
    
    </div>
    <hr />
    <section >
		<div class="container-fluid" >
			<div class="row d-flex justify-content-center">			
				<div class="col-md-6">
				<div class="codetitle fs-4"><?php echo ($human_language == 'en'? "Submitted code: ": "Kode yang dikumpulkan: "); ?></div>
					<div class="codeview border border-1" style="max-height: 50vh; overflow-y: auto;">
							<pre class="prettyprint linenums">
								<?php echo $markedCode; ?>
							</pre>
						</div>
			
				</div>
                <?php
                // Inisialisasi array untuk explanations
                $explanations = [];
                
                // Ambil ID dan explanation dari $explanationInfo
                preg_match_all('/<div class="explanationcontent" id="([^"]+)">(.+?)<\/div>/s', $explanationInfo, $matches, PREG_SET_ORDER);
                
                // Simpan dalam array dengan format ID S001, S002, dst.
                foreach ($matches as $match) {
                    $id = strtoupper(str_replace("he", "", trim($match[1]))); // Hapus "he" di akhir
                    $id = sprintf("S%03d", intval(substr($id, 1))); // Ubah S1 -> S001, S2 -> S002, dst.
                    $text = trim(strip_tags($match[2])); // Bersihkan teks explanation
                    $explanations[$id] = $text;
                }
                
                // Debug: hasil proses
                // echo "<pre>Hasil array explanations:\n";
                // print_r($explanations);
                // echo "</pre>";
                
                // Perbaiki $tableInfo agar explanations muncul
                $tableInfo = preg_replace_callback(
                    '/<tr id="(.*?)".*?<td.*?>(S\d+)<\/a><\/td>.*?<td.*?>(.*?)<\/td>.*?<td.*?>(.*?)<\/td>.*?<td.*?>(.*?)<\/td>/s',
                    function ($matches) use ($explanations) {
                        $id = sprintf("S%03d", intval(substr($matches[2], 1))); // Ubah jadi format S001, S002, dst.
                        $explanation = isset($explanations[$id]) ? $explanations[$id] : "Tidak ditemukan"; // Cocokkan ID
                        $formattedId = strtolower("S" . intval(substr($id, 1))) . "a";
                        $formattedExplanation = nl2br(htmlspecialchars($explanation));
                        $idNumber = "s" . intval(substr($matches[2], 1));
                        return "<tr id='{$matches[1]}' onclick=\"markSelectedWithoutChangingTableFocus('{$idNumber}','origtablecontent')\">
                                   
                                    <td style='width:10%;'><a style='color:blue;' href='#" . $formattedId . "'id='{$id}hl'>{$id}</a></td>
                                    <td style='width:20%;'>{$matches[3]}</td>
                                    <td style='width:10%;'>{$matches[4]}</td>
                                    <td>{$matches[5]}</td>
                                    <td style='white-space: pre-wrap; word-wrap: break-word;'>
                                        <pre style='font-family: \"Montserrat\", sans-serif;margin-top:5px; white-space: normal; word-wrap: break-word; overflow-wrap: break-word; max-width: 100%;'>
                                            {$formattedExplanation}
                                        </pre>
                                    </td>
                                </tr>";
                    },
                    $tableInfo
                );
                
                // Debug: hasil akhir $tableInfo  
                // echo "<pre>Hasil tableInfo setelah diproses:\n";
                // echo htmlspecialchars($tableInfo);
                // echo "</pre>";
                ?>



                
                <div class="col-md-6 px-5">
                    <div class="subtitlewrapper fs-4" style="width:60%;">
                        <?php echo ($human_language == 'en' ? "Representative suggestions per issue: " : "Representasi rekomendasi per isu: "); ?>
                    </div>
                    <table id="lecturerDashboardTable" class="table table-bordered table-striped responsive nowrap" style="width:100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th><?php echo ($human_language == 'en' ? "Hint text" : "Teks petunjuk"); ?></th>
                                <th><?php echo ($human_language == 'en' ? "Row" : "Baris"); ?></th>
                                <th><?php echo ($human_language == 'en' ? "Issue" : "Isu"); ?></th>
                                <th><?php echo ($human_language == 'en' ? "Explanation" : "Penjelasan"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php echo $tableInfo; ?>
                        </tbody>
                    </table>
                </div>

		</div>
	</div>
  </section>

  <script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function() {
        var table = $('#lecturerDashboardTable').DataTable({
            responsive: true,
            lengthMenu: [
                [3, 5, 10, -1],
                [3, 5, 10, 'All']
            ]
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
    
        // ✅ Mencegah event klik pada <a> agar tidak memperluas row
        $('#lecturerDashboardTable tbody').on('click', 'a', function(e) {
            e.stopPropagation(); // Mencegah event klik dari menyebar ke elemen <tr>
        });
    });
</script>
  </body>
</html>
