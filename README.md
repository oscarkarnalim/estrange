# E-STRANGE
E-STRANGE is a performance support platform for students learning programming. It helps students to understand code ethics, quality, and efficiency. Students will get formative feedback about those aspects, mapped to their own submissions. The platform also features gamification for student engagement.

## Installation Guideline
1.	Copy the supporting directory to the server and name it accordingly. It is typically located in the root.
2.	In the Java code project’s ScheduledSuspicionGenerator.java, set “prefixPath” to the server path of the supporting directory (including the directory name). 
3.	Compile the Java code project and convert it into a runnable JAR file with ScheduledSuspicionGenerator.java as its launch configuration / main class.
4.	Add the resulting JAR file to the supporting directory.
5.	Import the MySQL database to the server and name it accordingly.
6.	Copy the PHP code project into the server’s “public_html”.
7.	In the PHP code project, set “_config.php” with MySQL credentials. “$baseDomainLink” should be assigned to the public web link. “$registered_email_domain” will limit registered emails to a particular institution.
8.	In the supporting directory, set “serverinfo.txt” with MySQL credentials. “server_base_path” should be assigned to the public html directory where the PHP project is located.
9.	In the PHP code project, set “_phpmailerlib.php” with Google email credentials. The email account will be used to send notifications to students. Please check Google XOAUTH2 authentication.
10.	Start a CRON job for the runnable JAR file once per minute.
11.	The platform is now ready to use. Start the login process using the username “adminacc” and the password “adminacc”. Please change the password and email afterwards.
