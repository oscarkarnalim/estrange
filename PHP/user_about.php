<?php 
	include("_sessionchecker.php"); 
	include("_config.php");
?>
<html>
	<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">

		<title> E-STRANGE: About</title>
		<!-- Untuk Icon -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
	
    <link rel="icon" href="strange_html_layout_additional_files/icon.png">
	<link href="bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">

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
	</style>
	</head>
	<body>
		<?php
			// set the menu based on given role
			if($_SESSION['role'] == 'admin'){
				setHeaderAdmin("about", "About");
			}
			else if($_SESSION['role'] == 'lecturer'){
				setHeaderLecturer("about", "About");
			}else if($_SESSION['role'] == 'student'){
				setHeaderStudent("about", "About");
			}
			
			if($human_language == 'en'){
				echo '
				<div class="container mt-3">
					<div class="row d-flex justify-content-center align-items-center" style="min-height: 60vh">
						<div class="col-md-6">
							<div class="card mx-auto">
								<div class="card-body">
									<div style="text-align: justify">
										<b>STRANGE Educational mode (E-STRANGE)</b> is a code submission system for academia that support students in learning programming in code ethics, code quality, and code efficiency. 
										<br />
										<b>List of contributors:</b>
										<ol>
                                          <li>Oscar Karnalim for the primary contributor and being the owner</li>
                                          <li>Simon for major advises to the first three versions of the system</li>
                                          <li>William Chivers for minor advises to the first three versions of the system</li>
                                          <li>Billy Susanto Panca for the initial server architecture</li>
										  <li>Gisela Kurniawati for the logo design</li>
                                          <li>Yehezkiel David Setiawan for designing and upgrading the UI</li>
                                          <li>Rossevine Artha Nathasya and Sendy Ferdian Sujadi for maintaining the server</li>
                                        </ol>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				';
			}else{
				echo '
				<div class="container mt-3">
				<div class="row d-flex justify-content-center align-items-center" style="min-height: 60vh">
				  <div class="col-md-12">
					<div class="card mx-auto border-0">
					  <div class="row justify-content-center align-items-center g-0">
						<div class="col-md-6">
						  <div class="card-body">
							<div style="text-align: justify">
							  <b>STRANGE Educational mode (E-STRANGE)</b> adalah sistem pengumpulan tugas kode program dalam ranah akademik. Sistem ini mengajarkan siswa tentang plagiarisme dan kolusi di bidang pemrograman. Siswa-siswa yang tugasnya
							  tampak serupa akan diinformasikan terkait hal tersebut beserta alasan-alasannya. Sistem ini merupakan ekspansi dari <b>STRANGE</b>, kakas untuk mengobservasi kesamaan kode program. Informasi lebih lanjut dapat diperoleh
							  dengan menghubungi <b>Oscar Karnalim</b>
							  <br />
							<b>Daftar kontributor:</b>
							<ol>
                              <li>Oscar Karnalim untuk kontribusi utama sekaligus pemilik sistem</li>
                              <li>Simon untuk saran-saran major pada tiga versi pertama sistem</li>
                              <li>William Chivers untuk saran-saran minor pada tiga versi pertama sistem</li>
                              <li>Billy Susanto Panca untuk arsitektur awal sistem</li>
                              <li>Gisela Kurniawati untuk lencana permainan pada versi ketiga hingga kelima sistem</li>
                              <li>Yehezkiel David Setiawan untuk desain dan perubahan tampilan</li>
                              <li>Rossevine Artha Nathasya untuk menjaga jalannya server</li>
                            </ol>
							  <div class="container-fluid mt-4">
								<div class="row d-flex justify-content-center align-items-center">
		
								  <div class="col-md-6 mb-3">
									<a href="mailto:oscar.karnalim@it.maranatha.edu" class="btn w-100 btn-primary" style="font-size: 0.9em">contact</a>
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
			  </div>
				';
			}
			
		?>
		</div>

		<script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
	</body>
</html>
