<?php

	session_start();

	// if the SUSPICION id does not exist
	if(isset($_GET['id']) == false || $_GET['id'] == ''){
 		header('Location: index.php?invalidreport=true');
		exit;
	}

	include("_config.php");

	// escape sql injection
	$_GET['id'] = mysqli_real_escape_string($db,$_GET['id']);

	// check whether the suspicion id is actually exist
	$sql = "SELECT suspicion_id, marked_code, artificial_code, table_info, explanation_info FROM suspicion
		 WHERE public_suspicion_id = '".$_GET['id']."'";
	$result = mysqli_query($db,$sql);
	// if the result is zero, redirect to login
	if($result->num_rows == 0){
		header('Location: index.php?invalidreport=true');
		exit;
	}else{
		$row = $result->fetch_assoc();

		$_GET['id'] = $row['suspicion_id'];
		$markedCode = $row['marked_code'];
		$artificialCode = $row['artificial_code'];
		$tableInfo = $row['table_info'];
		$explanationInfo = $row['explanation_info'];
	}

	// check whether the suspicion id is listed to a course which the submitter enrolled to
	$sql = "SELECT assessment.name AS assessment_name, assessment.assessment_id, suspicion.is_overly_unique, 
		 course.name AS course_name, submission.submitter_id, submission.submission_id, course.course_id, suspicion.suspicion_type,
		 suspicion.efficiency_point  
		 FROM assessment INNER JOIN course ON course.course_id = assessment.course_id
		 INNER JOIN submission ON submission.assessment_id = assessment.assessment_id
		 INNER JOIN suspicion ON suspicion.submission_id = submission.submission_id
		 WHERE suspicion.suspicion_id = '".$_GET['id']."'";
	$result = mysqli_query($db,$sql);
	$row = $result->fetch_assoc();

	// if the given assessment id is not listed, redirect to login
	if(is_null($row)){
		header('Location: index.php?invalidreport=true');
		exit;
	}else{
		// set all temporary variables
		$myassessmentid = $row['assessment_id'];
		$myassessmentname = $row['assessment_name'];
		$mycoursename = $row['course_name'];
		$mysubmitterid = $row['submitter_id'];
		$submission_id = $row['submission_id'];
		$courseId = $row['course_id'];
		$suspicion_type = $row['suspicion_type'];
		$isOverlyUnique = $row['is_overly_unique'];
		$efficiencyPoint = $row['efficiency_point'];
	}
	recordAccess($db, $_GET['id']);
?>
<html>
	<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<?php 
	        if($suspicion_type == 'real'){
				echo ($human_language == 'en'? "<title>Similarity alert</title>": "<title>Laporan kesamaan</title>");
			}else{
    		    if($isOverlyUnique == false){
    			    echo ($human_language == 'en'? "<title>E-Strange: Similarity simulation</title>": "<title>E-Strange: Simulasi kesamaan</title>");
    		    }else{
    		        echo ($human_language == 'en'? "<title>E-Strange: Similarity simulation: overly unique submission</title>": "<title>E-Strange: Simulasi kesamaan: pekerjaan terlalu unik</title>");
    		    }
			}
	?>
		  <link href="bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
		  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="icon" href="../strange_html_layout_additional_files/icon.png">
    <!-- Google Prettify to generate highlight https://github.com/google/code-prettify -->
	<script src="../strange_html_layout_additional_files/run_prettify.js"></script>
	<!-- The use of Notyf library https://github.com/caroso1222/notyf -->
	<link rel="stylesheet" href="../strange_html_layout_additional_files/notyf.min.css">
	<script src="../strange_html_layout_additional_files/notyf.min.js"></script>
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
			 // get three earliest notification for courses in which game feature is active
			 // and student participation in the game is also active
			 $sqlt = "SELECT game_unobserved_notif.notification_id, game_unobserved_notif.message 
					FROM game_unobserved_notif 
					INNER JOIN game_student_course ON game_student_course.gs_id = game_unobserved_notif.gs_id 
					INNER JOIN game_course ON game_course.course_id = game_student_course.course_id 
					WHERE game_student_course.student_id = '".$mysubmitterid."' 
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
				 echo "const notification".$i." = notyf.success(\"".$row['message']."<br />Log in for details!!\");
					   notification".$i.".on('click', ({target, event}) => {window.location.href = 'index.php';});";
					   
					   
				 // remove the notification
				 $sql = "DELETE FROM game_unobserved_notif WHERE notification_id = '".$row['notification_id']."'";
				 $db->query($sql);
				 
				 // increment the i
				 $i = $i+1;
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
				window.location.hash = '#' + id + "hl";
				// mark all related components on both code views and the table.
				markSelectedWithoutChangingTableFocus(id, tableId);
			}

			// to highlight code
			var selectedCodeFragmentId = null;
			function markSelectedWithoutChangingTableFocus(id, tableId){
				if(selectedCodeFragmentId != null){
					resetCurrentFocus();
					// for header table, recolor the contents
					recolorTableContent(tableId);
				}
				// set the CSS of currently selected fragment
				var defaultColour = "";
				if(id.startsWith("c")){
					defaultColour = "rgba(244,161,164,1)";
				}else if(id.startsWith("s")){
					defaultColour = "rgba(101,244,104,1)";
				}
				// for left panel
				recolorCodeFragment(id + "a",defaultColour);
				window.location.hash = '#' + id + "a";
				// for right panel
				recolorCodeFragment(id + "b",defaultColour);
				window.location.hash = '#' + id + "b";
				// for header table, recolor the row
				recolorCodeFragment(id + "hr",defaultColour);
				// for natural language explanation
				document.getElementById(id +"he").style.display = "block";
				// hide the default one
				document.getElementById("dg").style.display = "none";
				// for code fragment counterpart
				document.getElementById(id +"g").style.display = "block";
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
		a{
			text-decoration: none;
			color: black;
		}
		div{
			float:left;
		}

		/* left panel */
		div.leftpanel{
			width:48%;
			height:100%;
			margin-right:1.5%;
		}

		/* title panel */
		div.titlepanel{
			width:100%;
		}
		div.image{
			width:15%;
			height:8%;
			margin-right:1%;
			margin-bottom:1%;
		}
		img{
			width:100%;
			margin-bottom:1%;
		}
		div.titlewrapper{
			width:84%;
			font-weight: bold;
			font-size: 22px;
			padding-top:20px;
			color: rgba(0,65,111,1);
		}

		/* message panel */
		div.messagepanel{
			width:98%;
			height:25%;
			border: 1px solid #dddddd;
			padding:1%;
			overflow:auto;
		}
		ol{
			margin-top:-2px;
		}
		div.subtitlewrapper{
			width:30%;
			font-size:14px;
			padding-bottom:3px;
			font-weight:bold;
		}
		div.subcontentwrapper{
			width:69%;
			font-size:14px;
			padding-bottom:3px;
		}
		div.longsubtitlewrapper{
			width:100%;
			font-size:14px;
			padding-bottom:3px;
			font-weight:bold;
		}
		div.longsubcontentwrapper{
			width:100%;
			font-size:14px;
			padding-bottom:3px;
			display: none;
			overflow: hidden;
		}
		div.longsubcontentwrapperdisplay{
			display: block;
		}
		button.collapsible {
			background-color: rgba(0,140,186,1);
			border: none;
			color: white;
			padding: 2px 4px;
			text-align: center;
			text-decoration: none;
			display: inline-block;
			cursor: pointer;
		}

		/* for left code view */
		div.codetitle{
			width:100%;
			font-size:14px;
			font-weight:bold;
			margin-top:1%;
			margin-bottom:-1.5%;
		}
		div.codeview{
			width: 100%;
			height:52%;
		}
		pre{
			tab-size: 2;
			overflow: auto;
			width: 100%;
			height:100%;
			float:left;
			border-color: #dddddd;
		}
		.commentsim{
			background-color:rgba(244,211,214,1);
		}
		.syntaxsim{
			background-color:rgba(171,244,174,1);
		}
		.linenums li {
			list-style-type: decimal;
		}

		/* for table, copied and modified from https://www.w3schools.com/html/tryit.asp?filename=tryhtml_table_intro*/
		div.tablecontainer{
			width: 100%;
			height:20%;
			margin-bottom:1%;
		}
		div.tableheader{
			width: 100%;
			height:20%;
		}
		div.tablecontentcontainer{
			width:100%;
			height:80%;
			overflow-y:scroll;
			overflow-x: hidden;
			border: 1px solid #dddddd;
		}
		table {
			width:100%;
			font-family: inherit;
			font-size: inherit;
			border-collapse: collapse;
		}
		table.header {
			width:97.5%;
		}
		td, th {
			border: 1px solid #dddddd;
			text-align: center;
		}
		td{
			width:20%;
			padding: 2px;
		}
		th{
			border-top: none;
			background-color: rgba(0,140,186,1);
			color: white;
			padding: 4px 8px;
			text-align: center;
			text-decoration: none;
			font-weight: normal;
			width:25%;
			height:100%;
			cursor: pointer;
		}
		tr:nth-child(even) {
			background-color: #eeeeee;
		}
		tr{
			cursor: pointer;
		}
		img.sortpic{
			float:right;
			width:12px;
			margin-bottom:0px;
		}

		/* right panel */
		div.rightpanel{
			width:49%;
			height:100%;
		}
		div.explanationpanel{
			width:98%;
			height:30%;
			border: 1px solid #dddddd;
			padding:1%;
			overflow-y:auto;
			overflow-x:hidden;
		}
		div.explanationcontent{
			float:left;
			width:98%;
			padding:1%;
			display: none;
			text-align: justify;
		}

		/* only for table inside the explanation */
		td.inexplanation, th.inexplanation {
			border: 1px solid black;
			text-align: center;
			padding: 0;
			height:16px;
			font-size: 12px;
		}
		th.inexplanation{
			border: 1px solid black;
			background-color: white;
			color: black;
			width:20%;
			cursor: none;
			font-weight:bold;
		}
		tr.inexplanation:nth-child(even) {
			background-color: white;
		}
		tr.inexplanation{
			cursor: default;
			padding: 0;
			height:16px;
			font-size: 12px;
		}

		div.codefragmentview{
			width: 99.5%;
			height:35%;
			margin-bottom:3.5%;

		}

		button.tablink{
			float:left;
			width:100%;
			border:none;
			outline: none;
			cursor: pointer;
			padding: 6px 20px;
			transition: 0.3s;
			background-color: rgba(0,112,149,1);
			text-align: center;
			color: white;
		}

		div.generatedfragment{
			width:100%;
			height:100%;
			display: none;
		}
		div.didyouknowpanel, div.explanationpanel{
			font-size:14px;
		}
		
		/* for header */
		/* copied and modified from https://www.w3schools.com/css/css3_buttons.asp */
		button {
			background-color: rgba(0,140,186,1);
			border: none;
			color: white;
			padding: 2px 4px;
			text-align: center;
			text-decoration: none;
			display: inline-block;
			cursor: pointer;
		}
		/* for tabbed view. copied and modified from https://www.w3schools.com/howto/howto_js_tabs.asp */
		.tab {
			float:left;
			width:100%;
			background-color: rgba(0,140,186,1);
			margin-bottom:10px;
		}
		button.tablinks {
			border:none;
			outline: none;
			float: left;
			cursor: pointer;
			padding: 6px 20px;
			height:30px;
			transition: 0.3s;
			background-color: rgba(0,140,186,1);
		}
		.tab button:hover {
		  background-color: rgba(20,160,206,1);
		}
		.tab button.active {
		  background-color: rgba(40,180,226,1);
		}
		form.tablinks{
			float:left;
			height:10px;
		}
    </style>
  </head>
  <body onload="construct()">
    <div class="leftpanel">
      <div class="titlepanel">
        <div class="image"><img src="../strange_html_layout_additional_files/logo.png" alt="logo"></div>
		<?php 
			if($human_language == 'en'){
				if($suspicion_type == 'real')
					echo '<div class="titlewrapper">Similarity report</div>';
				else{
				    if($isOverlyUnique == false){
					    echo '<div class="titlewrapper">Similarity simulation</div>';
				    } else{
				        echo '<div class="titlewrapper">Similarity simulation: overly unique submission</div>';
				    }
				}
			}else{
				if($suspicion_type == 'real')
					echo '<div class="titlewrapper">Laporan kesamaan</div>';
				else{
				    if($isOverlyUnique == false){
					    echo '<div class="titlewrapper">Simulasi kesamaan</div>';
				    }else{
				         echo '<div class="titlewrapper">Simulasi kesamaan: pekerjaan terlalu unik</div>';
				    }
				}
			}
			setHeaderReport("originality", $submission_id, $db); 
		?>
      </div>
			<div class="messagepanel">
				<div class="subtitlewrapper"><?php echo ($human_language == 'en'? "Student ID": "ID mahasiswa"); ?></div>
				<?php
				 	// get username and name based on user id
					$sqlt = "SELECT username, name FROM user
						WHERE user_id = '".$mysubmitterid."'";
					$resultt = mysqli_query($db,$sqlt);
					$rowt = $resultt->fetch_assoc();
				?>
				<div class="subcontentwrapper"><b>:</b> <?php echo $rowt['username'].' / ' . $rowt['name']; ?></div>
				<div class="subtitlewrapper"><?php echo ($human_language == 'en'? "Course": "Mata kuliah"); ?></div>
				<div class="subcontentwrapper"><b>:</b> <?php echo $mycoursename; ?> </div>
				<div class="subtitlewrapper"><?php echo ($human_language == 'en'? "Assessment": "Tugas"); ?></div>
				<div class="subcontentwrapper"><b>:</b> <?php echo $myassessmentname; ?></div>
				<?php
					if($human_language == 'en'){
					    /*
					    DIBUKA DI SEMESTER DEPAN
					    echo '<div class="longsubtitlewrapper">Efficiency&nbsp;:'.$efficiencyPoint.' <button class="collapsible" onclick="toggleCollapsible(\'messageeff\')">details</button></div>
								<div class="longsubcontentwrapper" id="messageeff">
									Efficiency is calculated based on the submission size. If yours is larger than the average size of already submitted works for that assessment, 
									the score will be below 100. While this is not the best way to measure efficiency, it is the most efficient.
								</div>';*/
						if($suspicion_type == 'real'){
							echo '
								<div class="longsubtitlewrapper">Why the code is alerted? <button class="collapsible" onclick="toggleCollapsible(\'message1\')">details</button></div>
								<div class="longsubcontentwrapper" id="message1">
									The alert is raised since the code shares obvious similarity to other students\' code that has been previously submitted.
								</div>
								<div class="longsubtitlewrapper">How the originality degree is calculated? <button class="collapsible" onclick="toggleCollapsible(\'message3\')">details</button></div>
								<div class="longsubcontentwrapper" id="message3">
									It is the proportion of code found different to those previously submitted by other students. High originality degree does not guarantee the submission to be not suspected for misconduct; the system\'s detection is not comprehensive. 
								</div>
								<div class="longsubtitlewrapper">What actions did the student possibly do that lead to this alerted similarity? <button class="collapsible" onclick="toggleCollapsible(\'message2\')">details</button></div>
								<div class="longsubcontentwrapper" id="message2">
									<ol>
										<!-- sorted from positive to negative accusation -->
										<li>Discussing with another student how to approach a task and what resources to use, then developing the solution independently.</li>
										<li>Discussing the detail of your code with another student while working on it.</li>
										<li>Showing troublesome code to another student and asking them for advice on how to fix it.</li>
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
							';
						}
						else{
						    
						 	echo '<div class="longsubtitlewrapper">How the originality degree is calculated? <button class="collapsible" onclick="toggleCollapsible(\'message3\')">details</button></div>
								<div class="longsubcontentwrapper" id="message3">
									It is the proportion of code found different to those previously submitted by other students. High originality degree does not guarantee the submission to be not suspected for misconduct; the system\'s detection is not comprehensive. 
								</div>';
						    
						    if($isOverlyUnique){
						        echo '
								<div class="longsubtitlewrapper">The code is overly unique. What are possible reasons? <button class="collapsible" onclick="toggleCollapsible(\'message1\')">details</button></div>
								<div class="longsubcontentwrapper" id="message1">
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
								</div>';
						    }
							echo '<div class="longsubtitlewrapper">What actions that may lead similarity? <button class="collapsible" onclick="toggleCollapsible(\'message2\')">details</button></div>
								<div class="longsubcontentwrapper" id="message2">
									<ol>
										<!-- sorted from positive to negative accusation -->
										<li>Discussing with another student how to approach a task and what resources to use, then developing the solution independently.</li>
										<li>Discussing the detail of your code with another student while working on it.</li>
										<li>Showing troublesome code to another student and asking them for advice on how to fix it.</li>
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
							';
						}
					}else{
					    /*
					    DIBUKA DI SEMESTER DEPAN
					    echo '<div class="longsubtitlewrapper">Efisiensi:&nbsp;'.$efficiencyPoint.' <button class="collapsible" onclick="toggleCollapsible(\'messageeff\')">detil</button></div>
								<div class="longsubcontentwrapper" id="messageeff">
									Efisiensi dihitung berdasarkan ukuran karya. Jika ukurannya lebih besar daripada ukuran rata-rata karya yang sudah dikumpulkan sebelumnya untuk tugas ini,
									nilainya akan dibawah 100. Ini bukan cara terbaik menghitung efisiensi namun ini yang paling efisien. 
								</div>';*/
						if($suspicion_type == 'real'){
							echo '
								<div class="longsubtitlewrapper">Mengapa kode ini ditandai? <button class="collapsible" onclick="toggleCollapsible(\'message1\')">detil</button></div>
								<div class="longsubcontentwrapper" id="message1">
									Alert didasarkan dari kesamaan kentara dengan sebagian kode program dari mahasiswa-mahasiswa lain yang telah dikumpulkan sebelumnya.
								</div>
								<div class="longsubtitlewrapper">Bagaimana originality degree dihitung? <button class="collapsible" onclick="toggleCollapsible(\'message3\')">detil</button></div>
								<div class="longsubcontentwrapper" id="message3">
									Ini adalah proporsi kode yang ditemukan berbeda dengan kode-kode yang sudah dikumpulkan oleh mahasiswa-mahasiswa lain sebelumnya. Originality degree yang tinggi tidak menjamin tugas ini tidak dicurigai mencontek; deteksi sistem tidak komprehensif.
								</div>
								<div class="longsubtitlewrapper">Apa saja kemungkinan tindakan yang dapat menghasilkan kesamaan ini? <button class="collapsible" onclick="toggleCollapsible(\'message2\')">detil</button></div>
								<div class="longsubcontentwrapper" id="message2">
									<ol>
										<!-- sorted from positive to negative accusation -->
										<li>Berdiskusi dengan siswa lain tentang cara mengerjakan tugas dan sumber-sumber apa yang sebaiknya digunakan, kemudian mengembangkan solusinya secara mandiri.</li>
										<li>Mendiskusikan kode anda secara detil dengan siswa lain pada saat mengerjakannya.</li>
										<li>Memperlihatkan kode yang bermasalah kepada siswa lain dan meminta saran tentang cara memperbaikinya.</li>
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
							';
						}else{
							echo '<div class="longsubtitlewrapper">Bagaimana originality degree dihitung? <button class="collapsible" onclick="toggleCollapsible(\'message3\')">detil</button></div>
								<div class="longsubcontentwrapper" id="message3">
									Ini adalah proporsi kode yang ditemukan berbeda dengan kode-kode yang sudah dikumpulkan oleh mahasiswa-mahasiswa lain sebelumnya. Originality degree yang tinggi tidak menjamin tugas ini tidak dicurigai mencontek; deteksi sistem tidak komprehensif.
								</div>';
								
							if($isOverlyUnique){
						        echo '
								<div class="longsubtitlewrapper">Kode ini terlalu berbeda dengan yang lainnya. Apa saja kemungkinan penyebabnya? <button class="collapsible" onclick="toggleCollapsible(\'message1\')">details</button></div>
								<div class="longsubcontentwrapper" id="message1">
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
								</div>';
						    }
								
							echo '<div class="longsubtitlewrapper">Tindakan-tindakan apa saja yang dapat menghasilkan kesamaan? <button class="collapsible" onclick="toggleCollapsible(\'message2\')">detil</button></div>
								<div class="longsubcontentwrapper" id="message2">
									<ol>
										<!-- sorted from positive to negative accusation -->
										<li>Berdiskusi dengan siswa lain tentang cara mengerjakan tugas dan sumber-sumber apa yang sebaiknya digunakan, kemudian mengembangkan solusinya secara mandiri.</li>
										<li>Mendiskusikan kode anda secara detil dengan siswa lain pada saat mengerjakannya.</li>
										<li>Memperlihatkan kode yang bermasalah kepada siswa lain dan meminta saran tentang cara memperbaikinya.</li>
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
							';
						}
					}
				?>
			</div>
			<div class="codetitle" style="width:60%;"><?php echo ($human_language == 'en'? "The code with similar contents highlighted: ": "Kode dengan konten sama ditandai: "); ?></div>
			<div class="codeview">
				<pre class="prettyprint linenums">
<?php echo $markedCode; ?>
				</pre>
			</div>
    </div>
    <div class="rightpanel">
			<div class="subtitlewrapper" style="width:60%;"><?php echo ($human_language == 'en'? "Similar contents: ": "Konten yang sama: "); ?> </div>
			<div class="subcontentwrapper"></div>
			<div class="tablecontainer">
				<div class="tableheader">
					<table class="header">
						<tr>
							<th onclick="sortTable(0,'origtablecontent',false, 'origcontainer')" >ID <img class="sortpic" src="../strange_html_layout_additional_files/sort icon.png" alt="logo"></th>
							<th onclick="sortTable(1,'origtablecontent',false, 'origcontainer')"><?php echo ($human_language == 'en'? "Similarity type": "Tipe kesamaan"); ?> <img class="sortpic" src="../strange_html_layout_additional_files/sort icon.png" alt="logo"></th>
							<th onclick="sortTable(2,'origtablecontent',true, 'origcontainer')"><?php echo ($human_language == 'en'? "Length": "Panjang"); ?> <img class="sortpic" src="../strange_html_layout_additional_files/sort icon.png" alt="logo"></th>
							<th onclick="sortTable(3,'origtablecontent',true, 'origcontainer')"><?php echo ($human_language == 'en'? "Warning level": "Level peringatan"); ?> <img class="sortpic" src="../strange_html_layout_additional_files/sort icon.png" alt="logo"></th>
						</tr>
					</table>
				</div>
				<div class="tablecontentcontainer" id="origcontainer">
					<table id="origtablecontent">
						<?php echo $tableInfo; ?>
					</table>
				</div>
			</div>
			<div class="longsubtitlewrapper"><?php echo ($human_language == 'en'? "Similarity explanation:": "Penjelasan kesamaan:"); ?></div>
      <div class="explanationpanel">
<?php echo $explanationInfo; ?>
      </div>
			<div class="codetitle"><?php echo ($human_language == 'en'? "Code counterpart example:": "Contoh kode pembanding:"); ?></div>
			<div class="codefragmentview">
				<div class="generatedfragment" id='dg' style="display:block"> <pre class="prettyprint linenums"></pre></div>
<?php echo $artificialCode; ?>
			</div>
		</div>
    </div>
	<script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>

  </body>
</html>
