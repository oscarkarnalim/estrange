package sstrange;

import java.io.BufferedReader;
import java.io.File;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.StandardCopyOption;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.Statement;
import java.util.HashMap;

import p5.educationalstrange.ZipManipulation;
import p5.educationalstrange.scheduledsuspiciongenerator.ScheduledSuspicionGenerator;

public class PenaltySimDetInterfacer {
	public static String sstrangeDirPath = ScheduledSuspicionGenerator.prefixPath + "sstrange";
	public static String sstrangeJarPath = sstrangeDirPath + File.separator + "sstrange.jar";

	public static void addGenerationTask(Connection connect) throws Exception {
		// add similarity detection generation task for completed assessments to the
		// queue

		// prepare variables
		Statement s;
		ResultSet rs;
		String sql;
		PreparedStatement p;

		// get all assessments without similarity reports for penalty
		s = connect.createStatement();
		rs = s.executeQuery("SELECT assessment_id FROM assessment "
				+ "WHERE TIMESTAMPDIFF(second, submission_close_time, now()) > 0 "
				+ "AND similarity_report_path = '' ");
		while (rs.next()) {
			int assessment_id = rs.getInt("assessment_id");

			// check whether it has been listed in the queue
			Statement s2 = connect.createStatement();
			ResultSet rs2 = s2
					.executeQuery("SELECT count(assessment_id) AS tot FROM similarity_report_generation_queue "
							+ "WHERE assessment_id = '" + assessment_id + "'");
			rs2.next();
			if (rs2.getInt("tot") == 0) {
				// add the entry to the queue if not already
				sql = "INSERT INTO similarity_report_generation_queue (assessment_id) VALUES (?) ";
				p = connect.prepareStatement(sql);
				p.setInt(1, assessment_id);
				p.executeUpdate();
				p.close();
			}
		}
	}

	public static void generateForOldestAssessment(Connection connect, String serverBasePath, String humanLang,
			String additionalKeywordPathJava, String additionalKeywordPathPython, String javaRunCommand)
			throws Exception {
		// get the oldest assessments from the queue and generate the report

		// prepare variables
		Statement s;
		ResultSet rs;
		String sql;
		PreparedStatement p;

		s = connect.createStatement();
		rs = s.executeQuery(
				"SELECT similarity_report_generation_queue.assessment_id, assessment.submission_file_extension, "
						+ "assessment.name, similarity_report_generation_queue.queue_id "
						+ "FROM similarity_report_generation_queue "
						+ "INNER JOIN assessment ON assessment.assessment_id = similarity_report_generation_queue.assessment_id "
						+ "ORDER BY similarity_report_generation_queue.timestamp ASC LIMIT 1 ");

		// if no result, skip
		if (rs.next() == false)
			return;

		// get the assessment ID
		int queue_id = rs.getInt("queue_id");
		int assessment_id = rs.getInt("assessment_id");
		String assessment_name = rs.getString("name");
		String submission_extension = rs.getString("submission_file_extension");

		// go to temp dir to create the assessment dir

		// check whether temp dir exists. This dir aims to cover all assessments
		File tempDir = new File("temp");
		// create a temp dir if not exist
		if (tempDir.exists() == false)
			tempDir.mkdir();

		// assessment dir
		File assessmentDir = new File("temp" + File.separator + assessment_name);
		// create the assessment dir if not exist
		if (assessmentDir.exists() == false)
			assessmentDir.mkdir();

		// copy all submissions for given assessment to the dir

		// to store all last submission IDs for students. The key is student ID while
		// the value is the last submission index
		HashMap<Integer, Integer> lastAttemptMap = new HashMap<Integer, Integer>();

		// get all the last attempt for each student
		s = connect.createStatement();
		rs = s.executeQuery("SELECT MAX(attempt) AS max_attempt, submitter_id FROM submission "
				+ "WHERE assessment_id = '" + assessment_id + "' GROUP BY submitter_id");
		while (rs.next()) {
			lastAttemptMap.put(rs.getInt("submitter_id"), rs.getInt("max_attempt"));
		}

		// if less than two, not enough for generating sim report
		if (lastAttemptMap.size() < 2) {
			// delete the assessment submission dir
			ZipManipulation.deleteAllTemporaryFiles(new File(assessmentDir.getAbsolutePath()));

			// remove the entry from similarity_report_generation_queue
			sql = "DELETE FROM similarity_report_generation_queue WHERE queue_id = ? ";
			p = connect.prepareStatement(sql);
			p.setInt(1, queue_id);
			p.executeUpdate();
			p.close();

			// update similarity_report_path in assessment as null
			sql = "UPDATE assessment SET similarity_report_path = ? WHERE assessment_id = ?";
			p = connect.prepareStatement(sql);
			p.setString(1, "null");
			p.setInt(2, assessment_id);
			p.executeUpdate();
			p.close();
			return;
		}

		// get all the latest submissions
		s = connect.createStatement();
		rs = s.executeQuery("SELECT submission.filename, submission.file_path, submission.attempt, user.username, "
				+ "user.name, submission.submitter_id FROM submission "
				+ "INNER JOIN user ON submission.submitter_id = user.user_id " + "WHERE submission.assessment_id = '"
				+ assessment_id + "'");
		while (rs.next()) {

			if (rs.getInt("attempt") == lastAttemptMap.get(rs.getInt("submitter_id"))) {
				// generate submission directory
				String destinationPath = assessmentDir.getAbsolutePath() + File.separator + rs.getString("username")
						+ "_" + rs.getString("name");
				File submissionDir = new File(destinationPath);
				submissionDir.mkdir();

				// copy the submission file to given dir and convert back to its original format
				Path copiedPath = new File(destinationPath + File.separator + rs.getString("filename")).toPath();
				try {
					Path originalPath = new File(serverBasePath + File.separator + rs.getString("file_path")).toPath();
					Files.copy(originalPath, copiedPath, StandardCopyOption.REPLACE_EXISTING);
				} catch (Exception e) {
					// do nothing if no files can be copied. skip it
				}

			}
		}

		// start the comparison with SSTRANGE
		String progLang = "py";
		int minMatchLength = 5; // 40;
		if (submission_extension.equalsIgnoreCase("java") || submission_extension.equalsIgnoreCase("zip_java")) {
			progLang = "java";
			minMatchLength = 10; // 80;
		}

		// maximum reported pairs equals to the number of students
		int maxPairs = lastAttemptMap.size();
		// corner case when only two students submitted
		if (maxPairs == 2)
			maxPairs = 1;

		double minSimThreshold = 0.5; // 0.75

		// generate the similarity report via SSTRANGE
		// accessed via cmd as the library is too complex to use here due to class name
		// conflicts etc
		String[] args = new String[] { javaRunCommand, "-jar", sstrangeJarPath, assessmentDir.getAbsolutePath(),
				(submission_extension.startsWith("zip_")) ? "zip" : "file", progLang, humanLang,
				(int) (minSimThreshold * 100) + "", minMatchLength + "", maxPairs + "", "none", true + "",
				"Sensitive Super-Bit", sstrangeDirPath, "2", "1" };

		for (String st : args) {
			System.out.print(st + ", ");
		}
		System.out.println();

		try {
			// create the process
			Process q = Runtime.getRuntime().exec(args);
			// wait till the process is completed
			q.waitFor();

			// get the filepath of generated similarity report's directory
			String simReportLocalPath = assessmentDir.getParentFile().getAbsolutePath() + File.separator + "[out] "
					+ assessment_name;

			// set the main directory storing all sim reports on the server
			String simReportServerPath = serverBasePath + File.separator + "simreports";
			File simReportServerFile = new File(simReportServerPath);
			if (simReportServerFile.exists() == false)
				simReportServerFile.mkdir();

			// set the destination path
			simReportServerPath += (File.separator + assessment_id + ".zip");

			// generate the zip
			ZipManipulation.zipFile(simReportLocalPath, simReportServerPath);

			// delete the similarity report dir
			ZipManipulation.deleteAllTemporaryFiles(new File(simReportLocalPath));

			// inform the user
			System.out.println(
					"Similarity report for " + assessment_name + " (" + assessment_id + ") has been generated");
		} catch (Exception e) {

			// if failed, report
			System.out.println(
					"Similarity report for " + assessment_name + " (" + assessment_id + ") is failed to be generated");
		}

		// delete the assessment submission dir
		ZipManipulation.deleteAllTemporaryFiles(new File(assessmentDir.getAbsolutePath()));

		// remove the entry from similarity_report_generation_queue
		sql = "DELETE FROM similarity_report_generation_queue WHERE queue_id = ? ";
		p = connect.prepareStatement(sql);
		p.setInt(1, queue_id);
		p.executeUpdate();
		p.close();

		// update similarity_report_path in assessment
		sql = "UPDATE assessment SET similarity_report_path = ? WHERE assessment_id = ?";
		p = connect.prepareStatement(sql);
		p.setString(1, "simreports" + File.separator + assessment_id + ".zip");
		p.setInt(2, assessment_id);
		p.executeUpdate();
		p.close();

	}
}
