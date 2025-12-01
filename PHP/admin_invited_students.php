<?php
	include("_sessionchecker.php");
	include("_config.php");

	if($_SERVER["REQUEST_METHOD"] == "POST") {
		// deleting an entry
		if(isset($_POST['id']) == true){
			$id = mysqli_real_escape_string($db,$_POST['id']);
			// delete a user
			$sql = "DELETE FROM invited_student WHERE invitation_id = '$id'";
			if ($db->query($sql) === TRUE) {
				// if removed well, redirect to dashboard
				header('Location: admin_invited_students.php');
				exit;
			} else {
				echo "Error removing record: " . $db->error;
			}
		}
	}
?>
<html>
	<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
		<title> E-STRANGE: Student accounts</title>
    <link rel="icon" href="strange_html_layout_additional_files/icon.png">
	<!-- Untuk Icon -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
	<link href="bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">



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
			body{
				font-family: "Times New Roman", Times, serif;
				font-size: 12px;
				background-color: rgba(250,250,250,1);
			}
			div{
				float:left;
			}

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
			}
			button.tablinks {
				border:none;
				outline: none;
			  float: left;
			  cursor: pointer;
			  padding: 6px 20px;
				height:30px;
			  transition: 0.3s;
			}
			.tab button:hover {
			  background-color: rgba(20,160,206,1);
			}
			.tab button.active {
			  background-color: rgba(40,180,226,1);
			}
			div.tabcontent {
				float:left;
				width:99%;
				height:80%;
				display: none;
				border-top: none;
			}

			/* for header */
			div.header{
				width:100%;
				height:8%;
				margin-bottom:10px;
			}
			img{
				float:left;
				height:100%;
				margin-right:10px;
			}
			div.headertitle{
				font-weight: bold;
				font-size: 22px;
				height:100%;
				padding-top:20px;
				color: rgba(0,65,111,1);
			}
			div.logintext{
				float:right;
			}

			div.bodycontent{
				width:98%;
				padding-top:30px;
				padding-bottom:30px;
				padding-left:1%;
				padding-right:1%;
			}

			div.infotitle{
				width:100%;
				font-size: 20px;
				font-weight: bold;
				margin-bottom:10px;
			}

			/* for table, copied and modified from https://www.w3schools.com/html/tryit.asp?filename=tryhtml_table_intro*/
			div.tablecontainer{
				float:left;
				width: 100%;
			}
			table {
				width:100%;
			  font-family: inherit;
				font-size: inherit;
			  border-collapse: collapse;
			}
			table.header{
				width:99%;
			}
			td, th {
			  border: 1px solid #dddddd;
				padding: 4px;
			}
			td{
				width:51%;
				text-align: justify;
				vertical-align: top;
			}
			th{
				border-top: none;
				background-color: rgba(0,140,186,1);
				color: white;
				padding: 4px 8px;
				text-align: justify;
				text-decoration: none;
				font-weight: normal;
				width:51%;
				height:100%;
				cursor: pointer;
			}
			tr{
				background-color: rgba(255,255,255,1);
				cursor: pointer;
			}
			tr:nth-child(even) {
			  background-color: #eeeeee;
			}

			/* additional CSS for tables */
			div.tablecontentpanel{
				width:100%;
				height:50%;
				overflow-y: scroll;
				border: 1px solid #dddddd;
			}


			/* for action buttons */
			th.thactions{
				width:34%;
				cursor: default;
			}
			td.tdactions{
				width:34%;
				text-align: left;
			}
			button.actions{
				margin-left:2px;
				margin-right:2px;
			}
			button.addcourse{
				float:right;
				margin-top:10px;
				margin-left:10px;
				padding: 6px 20px;
				height:30px;
			}

			img.sortpic{
				float:right;
				height:14px;
				margin-right:-4px;
				margin-bottom:0px;
			}

			form.invisform{
				float:left;
				height:10px;
			}
		
    </style>
  </head>
  <body>
		<div class="header">
			<img src="strange_html_layout_additional_files/logo.png" alt="logo" />
			<div class="headertitle">Invited students</div>
			<?php
			  echo '<div class="logintext">Hello '.$_SESSION['name'].' You logged in as '.$_SESSION['role'].'!</div>';
			?>
		</div>

		<div class="tab">
			<button class="tablinks" onclick="window.open('admin_dashboard.php', '_self');">Lecturers</button>
			<button class="tablinks" onclick="window.open('admin_students.php', '_self');">Students</button>
			<button class="tablinks" onclick="window.open('admin_student_submissions.php', '_self');">Student submissions</button>
			<button class="tablinks" onclick="window.open('user_info_self_update.php', '_self');">Update personal information</button>
			<button class="tablinks" onclick="window.open('user_about.php', '_self');">About</button>
			<form action=" <?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post">
			  <input type="hidden" name="logout" value="logout">
			  <button class="tablinks" type="submit">Logout</button>
			</form>
		</div>

		<div class="bodycontent">
			<div class="infotitle"> Invited student email list: </div>
			<div class="tablecontainer">
				<table class="header">
					<tr>
						<th onclick="sortTable(0,'sumtablecontent',false, 'sumcontainer')">Email <img class="sortpic" src="strange_html_layout_additional_files/sort icon.png" alt="logo"></th>
						<th class="thactions"> Actions </th>
					</tr>
				</table>
				<div class="tablecontentpanel">
					<table id="sumtablecontent">
						<?php
							// get all students
							$sql = "SELECT invitation_id, email FROM invited_student";
							$result = mysqli_query($db,$sql);
							// adapted from https://www.w3schools.com/php/php_mysql_select.asp
							if ($result->num_rows > 0) {
									while($row = $result->fetch_assoc()) {
											echo "
												<tr id=\"".$row['invitation_id']."\" onclick=\"selectRow('".$row['invitation_id']."','sumtablecontent')\">
													<td><a>".$row['email']."</a></td>
													<td class=\"tdactions\">
														<form class=\"invisform\" action=\"". htmlentities($_SERVER['PHP_SELF']). "\" method=\"post\">
															<input type=\"hidden\" name=\"id\" value=\"".$row['invitation_id']."\">
															<button class=\"actions\" type=\"submit\" onclick=\"return confirm('Delete this entry?');\">delete</button>
														</form>
													</td>
												</tr>
											";
									}
							} else {
									// if no students
									echo "
										<tr>
											<td colspan=2 style='text-align:center'>No entries</td>
										</tr>
									";
							}
						?>
					</table>
				</div>
				<button class="addcourse" onclick="window.open('admin_inviting_student.php', '_self');">Invite</button>
			</div>
		</div>
		<script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
