package p5.educationalstrange.scheduledsuspiciongenerator;

import java.io.BufferedReader;
import java.io.File;
import java.io.FileOutputStream;
import java.io.FileReader;
import java.io.PrintStream;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.Statement;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.Iterator;
import java.util.Map.Entry;
import java.util.Random;

import p5.educationalstrange.SimilarityDetector;
import p5.educationalstrange.SimilarityDetectorSettingTuple;
import sstrange.PenaltySimDetInterfacer;

public class ScheduledSuspicionGenerator {
	// use it if the JAR and its supplementary files are grouped and located under
	// sub-directory.
	// all filepaths will be started from root regardless the location of the JAR
	// file
	public static String prefixPath = "mcu" + File.separator;
	

	public static void main(String[] args) throws Exception {

		java.util.Date date = new java.util.Date();
		System.out.println("Executed at : " + date);

		File file = new File(prefixPath + "err.txt");
		FileOutputStream fos = new FileOutputStream(file, true);
		PrintStream ps = new PrintStream(fos);
		System.setErr(ps);

		HashMap<String, String> serverInfo = new HashMap();
		try {
			BufferedReader bf = new BufferedReader(new FileReader(new File(prefixPath + "serverinfo.txt")));

			String st;
			while ((st = bf.readLine()) != null) {

				// if it is not an empty line
				if (st.length() > 0) {
					// split to an array of tuple
					String[] tuple = st.trim().split("===");

					String key = tuple[0].trim();
					String value = "";
					if (tuple.length > 1)
						value = tuple[1].trim();

					// add as an tuple
					serverInfo.put(key, value);
				}
			}

			bf.close();
		} catch (Exception e) {
			e.printStackTrace();
		}

		String username = serverInfo.get("username");
		String password = serverInfo.get("password");
		String dbName = serverInfo.get("db_name");
		String serverBasePath = serverInfo.get("server_base_path");

		String humanLanguage = serverInfo.get("human_lang");
		if (humanLanguage == null) {
			System.out.println("Human language is not set and its default value (en) is used");
			humanLanguage = "en";
		}

		String additionalKeywordPathJava = prefixPath + "java input output keywords.txt";
		String additionalKeywordPathPython = prefixPath + "python input output keywords.txt";

		String tmp = serverInfo.get("min_similarity_threshold");
		double suspicionSimilarityThreshold = 0.75;
		if (tmp != null) {
			suspicionSimilarityThreshold = Double.parseDouble(tmp);
		} else {
			System.out.println("Minimum similarity threshold is not set and its default value (75%) is used");
		}

		String javaRunCommand = serverInfo.get("java_run_command");
		if (javaRunCommand == null) {
			System.out.println("Java run command is not set and its default command is used");
			javaRunCommand = "java";
		}

		String pythonRunCommand = serverInfo.get("python_run_command");
		if (pythonRunCommand == null) {
			System.out.println("Python run command is not set and its default command is used");
			pythonRunCommand = "python";
		}

		if (username == null || password == null || dbName == null || serverBasePath == null) {
			System.out.println(
					"'serverinfo.txt' is not complete; at least one of the four minimum components (i.e., username, password, db_name, and server_base_path) is missing.");
			ps.close();
			return;
		}

		boolean isHalted = false;

		try {
			// Setup the connection with the DB
			Connection connect = DriverManager.getConnection(
					"jdbc:mysql://localhost/" + dbName + "?useLegacyDatetimeCode=false&serverTimezone=UTC", username,
					password);

			// Statements allow to issue SQL queries to the database
			Statement statement;
			ResultSet resultSet;

			// validate whether there are unprocessed submission entries
			statement = connect.createStatement();
			resultSet = statement.executeQuery(
					"SELECT COUNT(submission_id) AS total FROM submission WHERE submission_id IN (SELECT MAX(submission_id) FROM submission WHERE has_suspicion_report_created = 0 "
							+ "GROUP BY assessment_id, submitter_id ORDER BY submission_time ASC)");
			// move the result set to the start of data
			resultSet.next();
			// check whether there are entries unprocessed
			int count = resultSet.getInt("total");
			if (count == 0) {
				System.out.println("No unprocessed submissions are available");
				isHalted = true;
			}
			// close the streams
			resultSet.close();
			statement.close();

			if (!isHalted) {
				// get entries which suspicion has not been created and sort them based on
				// submission time ascending
				statement = connect.createStatement();
				resultSet = statement.executeQuery(
						"SELECT MAX(submission_id) AS max_sub_id, assessment_id, submitter_id FROM submission WHERE has_suspicion_report_created = 0 "
								+ "GROUP BY assessment_id, submitter_id "
								+ "ORDER BY MAX(submission_time) ASC LIMIT 1");
				// move the result set to the start of data
				resultSet.next();

				int target_submission_id = resultSet.getInt("max_sub_id");
				int target_submitter_id = resultSet.getInt("submitter_id");
				// get the assessment id
				int assessment_id = resultSet.getInt("assessment_id");

				// close the streams
				resultSet.close();
				statement.close();

				// get the filepath
				statement = connect.createStatement();
				resultSet = statement.executeQuery(
						"SELECT file_path FROM submission WHERE submission_id = '" + target_submission_id + "'");
				resultSet.next();

				// get target submission
				SubmissionTuple target = new SubmissionTuple(target_submission_id, resultSet.getString("file_path"),
						false, target_submitter_id);

				// close the streams
				resultSet.close();
				statement.close();

				/*
				 * get the latest submission IDs for that assessment. Assuming the assessment id
				 * is auto incremented.
				 */
				statement = connect.createStatement();
				resultSet = statement.executeQuery(
						"SELECT submission.submitter_id, max(submission_id) AS submission_id, assessment.submission_file_extension FROM submission "
								+ "INNER JOIN assessment ON assessment.assessment_id = submission.assessment_id "
								+ "WHERE assessment.assessment_id = '" + assessment_id
								+ "' AND submission.submitter_id != '" + target.getSubmitter_id() + "'"
								+ "GROUP by submission.submitter_id");

				// file extension for the assessment
				String assessmentFileExtension = null;

				ArrayList<SubmissionTuple> others = new ArrayList<SubmissionTuple>();
				while (resultSet.next()) {
					int student_id = resultSet.getInt("submitter_id");
					int submission_id = resultSet.getInt("submission_id");

					if (assessmentFileExtension == null) {
						// if no file extension defined, set with the current submission's file
						// extension
						assessmentFileExtension = resultSet.getString("submission_file_extension");
					}

					// get the remaining information for others
					Statement statement2 = connect.createStatement();
					ResultSet resultSet2 = statement2
							.executeQuery("SELECT file_path, has_suspicion_report_created FROM "
									+ "submission WHERE submission_id = '" + submission_id + "'");
					// move the result set to the start of data
					resultSet2.next();

					// create the submission tuple and add it to the list
					others.add(new SubmissionTuple(submission_id, resultSet2.getString("file_path"),
							resultSet2.getBoolean("has_suspicion_report_created"), student_id));

					// close the streams
					resultSet2.close();
					statement2.close();
				}

				// close the streams
				resultSet.close();
				statement.close();

				// if the extension is still unknown as no other submissions
				if (assessmentFileExtension == null) {
					// prepare statement
					statement = connect.createStatement();
					resultSet = statement.executeQuery("SELECT submission_file_extension FROM assessment "
							+ "WHERE assessment.assessment_id = '" + assessment_id + "'");
					// get the extension for the assessment
					resultSet.next();
					assessmentFileExtension = resultSet.getString("submission_file_extension");
					// close the streams
					resultSet.close();
					statement.close();
				}

				SimilarityDetectorSettingTuple.setHumanLanguage(humanLanguage);
				SimilarityDetectorSettingTuple setting = null;
				if (assessmentFileExtension.equalsIgnoreCase("java")) {
					setting = new SimilarityDetectorSettingTuple("java", false, suspicionSimilarityThreshold);
					SimilarityDetectorSettingTuple.setAdditionalKeywordPath(additionalKeywordPathJava);
				} else if (assessmentFileExtension.equalsIgnoreCase("py")) {
					setting = new SimilarityDetectorSettingTuple("py", false, suspicionSimilarityThreshold);
					SimilarityDetectorSettingTuple.setAdditionalKeywordPath(additionalKeywordPathPython);
				} else if (assessmentFileExtension.equalsIgnoreCase("txt")) {
					setting = new SimilarityDetectorSettingTuple("txt", false, suspicionSimilarityThreshold);
				} else if (assessmentFileExtension.equalsIgnoreCase("zip_java")) {
					setting = new SimilarityDetectorSettingTuple("java", true, suspicionSimilarityThreshold);
					SimilarityDetectorSettingTuple.setAdditionalKeywordPath(additionalKeywordPathJava);
				} else if (assessmentFileExtension.equalsIgnoreCase("zip_py")) {
					setting = new SimilarityDetectorSettingTuple("py", true, suspicionSimilarityThreshold);
					SimilarityDetectorSettingTuple.setAdditionalKeywordPath(additionalKeywordPathPython);
				} else if (assessmentFileExtension.equalsIgnoreCase("txt")) {
					setting = new SimilarityDetectorSettingTuple("txt", true, suspicionSimilarityThreshold);
				} 

				ArrayList<String> otherSubmissionIDs = new ArrayList<String>();
				ArrayList<String> otherCodePaths = new ArrayList<String>();
				for (SubmissionTuple st : others) {
					// System.out.println(st.getSubmitter_id() + " " + st.getSubmission_id());
					otherSubmissionIDs.add(st.getSubmission_id() + "");
					otherCodePaths.add(serverBasePath + st.getFile_path());
				}

				// do the generation of report(s)
				SimilarityDetector.process(setting, target.getSubmission_id() + "",
						serverBasePath + target.getFile_path(), otherSubmissionIDs, otherCodePaths, serverBasePath,
						javaRunCommand, pythonRunCommand, connect);
			}

			// For completed assessments, add queue tasks to generate similarity reports
			PenaltySimDetInterfacer.addGenerationTask(connect);

			// take one task from the queue and process
			PenaltySimDetInterfacer.generateForOldestAssessment(connect, serverInfo.get("server_base_path"),
					humanLanguage, additionalKeywordPathJava, additionalKeywordPathPython, javaRunCommand);

			// close the connection
			connect.close();

		} catch (Exception e) {
			System.out.println(e.getMessage());
			e.printStackTrace();
		}

		ps.close();
		fos.close();
	}
}
