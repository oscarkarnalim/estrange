package p5.educationalstrange;

import java.io.File;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.util.ArrayList;
import java.util.Collections;
import java.util.Random;

import p5.educationalstrange.eobfuscator.tuple.EJavaDefaultObfuscatorSettingTuple;
import p5.educationalstrange.eobfuscator.tuple.EObfuscatorSettingTuple;
import p5.educationalstrange.eobfuscator.tuple.EPythonDefaultObfuscatorSettingTuple;
import p5.educationalstrange.eobfuscator.tuple.ETextDefaultObfuscatorSettingTuple;
import p5.educationalstrange.scheduledsuspiciongenerator.ScheduledSuspicionGenerator;
import p5.educationalstrange.suspiciongenerator.RealSuspicionGenerator;
import p5.educationalstrange.suspiciongenerator.SimulatedSuspicionGenerator;
import p5.educationalstrange.tokenextractor.TokenExtractor;
import p5.educationalstrange.tuple.ETokenTuple;
import p6.codeclarity.CodeClarityContentGenerator;

public class SimilarityDetector {

	public static Random r = new Random();

	private static String random_str(int length) {
		String keyspace = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
		String str = "";
		for (int i = 0; i < length; ++i) {
			str += keyspace.charAt(r.nextInt(keyspace.length()));
		}
		return str;
	}

	public static String generatePublicSuspicionID(Connection connect) throws Exception {
		String publicSuspicionId = "";
		while (true) {
			publicSuspicionId = SimilarityDetector.random_str(3) + System.currentTimeMillis()
					+ SimilarityDetector.random_str(3);

			Statement statement2 = connect.createStatement();
			ResultSet resultSet2 = statement2
					.executeQuery("SELECT public_suspicion_id FROM suspicion WHERE public_suspicion_id = '"
							+ publicSuspicionId + "'");
			boolean isExist = false;
			while (resultSet2.next()) {
				// if this iteration works, the id exist
				isExist = true;
			}
			resultSet2.close();
			statement2.close();

			// if not found, the id is unique, break the loop
			if (isExist == false)
				break;
		}

		return publicSuspicionId;
	}

	public static void updateTableWithSuspicionAndSuggestion(String targetSubmissionID, String markedcode,
			String artificialcode, String tableinfo, String explanationinfo, String suspicionType,
			boolean isOverlyUnique, ArrayList<ETokenTuple> tokens, int simDegree, int efficiencyDegree,
			String programmingLanguage, String humanLanguage, Connection connect, boolean isThisSubmitterReport,
			String targetCodePath, boolean isZip, String temporarySubmissionPath, String javaRunCommand,
			String pythonRunCommand, int javaExpectedTokensPerIssue, int pythonExpectedTokensPerIssue,
			int efficiencyScore) {

		try {
			// check whether there is a suspicion entry for the submission
			Statement statement = connect.createStatement();
			ResultSet resultSet = statement.executeQuery(
					"SELECT suspicion.suspicion_id AS suspicion_id, suspicion.suspicion_type AS suspicion_type "
							+ "FROM suspicion "
							+ "INNER JOIN submission ON submission.submission_id = suspicion.submission_id "
							+ "INNER JOIN assessment ON assessment.assessment_id = submission.assessment_id "
							+ "WHERE submission.submission_id = '" + targetSubmissionID + "'");
			int suspicionID = -1;
			String suspicionTypeFromTable = "";
			while (resultSet.next()) {
				suspicionID = resultSet.getInt("suspicion_id");
				suspicionTypeFromTable = resultSet.getString("suspicion_type");
			}
			resultSet.close();
			statement.close();

			String publicSuspicionId = SimilarityDetector.generatePublicSuspicionID(connect);

			boolean isNewEntry = false;

			// create or update the corresponding suspicion
			if (suspicionID == -1) {
				// create new entry
				String sql = "INSERT INTO suspicion (suspicion_type, marked_code, artificial_code, table_info, explanation_info, submission_id, public_suspicion_id, originality_point, is_overly_unique, efficiency_point) "
						+ "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
				PreparedStatement p = connect.prepareStatement(sql);
				p.setString(1, suspicionType);
				p.setString(2, markedcode);
				p.setString(3, artificialcode);
				p.setString(4, tableinfo);
				p.setString(5, explanationinfo);
				p.setInt(6, Integer.parseInt(targetSubmissionID));
				p.setString(7, publicSuspicionId);
				p.setInt(8, 100 - simDegree);
				p.setBoolean(9, isOverlyUnique);
				p.setInt(10, efficiencyScore);
				p.executeUpdate();
				p.close();

				// get suspicion id to generate email request
				sql = "SELECT suspicion_id FROM suspicion WHERE public_suspicion_id = ? ";
				p = connect.prepareStatement(sql);
				p.setString(1, publicSuspicionId);
				ResultSet rs = p.executeQuery();
				rs.next();
				suspicionID = rs.getInt("suspicion_id");
				p.close();

				// mark the boolean as true
				isNewEntry = true;
			} else {
				// update
				String sql = "UPDATE suspicion SET suspicion_type = ?,  marked_code = ?, artificial_code = ?, table_info = ?, explanation_info = ?, public_suspicion_id = ?, originality_point = ?, is_overly_unique = ?, efficiency_point = ? "
						+ "WHERE suspicion_id = ? ";
				PreparedStatement p = connect.prepareStatement(sql);
				p.setString(1, suspicionType);
				p.setString(2, markedcode);
				p.setString(3, artificialcode);
				p.setString(4, tableinfo);
				p.setString(5, explanationinfo);
				p.setString(6, publicSuspicionId);
				p.setInt(7, 100 - simDegree);
				p.setBoolean(8, isOverlyUnique);
				p.setInt(9, efficiencyScore);
				p.setInt(10, suspicionID);
				p.executeUpdate();
				p.close();
			}

			String sql;
			PreparedStatement p;

			// mark target submission as 'processed'
			sql = "UPDATE submission SET has_suspicion_report_created = 1 " + "WHERE submission_id = ? ";
			p = connect.prepareStatement(sql);
			p.setInt(1, Integer.parseInt(targetSubmissionID));
			p.executeUpdate();
			p.close();

			// generate code clarity suggestion only if this is the submitter's report
			if (isThisSubmitterReport) {

				// code quality report
				int codeQualityDegree = CodeClarityContentGenerator.execute(tokens, programmingLanguage, humanLanguage,
						connect, targetSubmissionID, targetSubmissionID, publicSuspicionId, targetCodePath, isZip,
						temporarySubmissionPath, javaRunCommand, pythonRunCommand, javaExpectedTokensPerIssue,
						pythonExpectedTokensPerIssue);

				// gamification part
				// if this is the submitter's report, calculate points for gamification
				updateGamificationDataForSubmitter(targetSubmissionID, simDegree, connect, codeQualityDegree,
						efficiencyDegree);

			}

			// generate email request if this is the submitter not others, new entry, or it
			// is previously a simulation
			if (isThisSubmitterReport || isNewEntry || suspicionTypeFromTable.equals("simulation")) {
				sql = "INSERT INTO suspicion_email_request (suspicion_id) VALUES (?) ";
				p = connect.prepareStatement(sql);
				p.setInt(1, suspicionID);
				p.executeUpdate();
				p.close();
			}

		} catch (SQLException e) {
			System.out.println(e.getMessage());
			System.err.format("SQL State: %s\n%s", e.getSQLState(), e.getMessage());
		} catch (Exception e) {
			System.out.println(e.getMessage());
			e.printStackTrace();
		}
	}

	private static void updateGamificationDataForSubmitter(String targetSubmissionID, int simDegree, Connection connect,
			int codeQualityDegree, int efficiencyDegree) throws Exception {
		// this method is used to update all data related to gamification for given
		// submission ID. It will return game_student_assessment_id

		// prepare variables
		Statement statement;
		ResultSet resultSet;
		String sql;
		PreparedStatement p;

		// get required data for game student assessment entry
		statement = connect.createStatement();
		resultSet = statement.executeQuery(
				"SELECT course.course_id, submission.attempt AS attempt, TIMESTAMPDIFF(minute,submission.submission_time, assessment.submission_close_time) AS minutes_before_due, "
						+ "TIMESTAMPDIFF(minute,assessment.submission_open_time, assessment.submission_close_time) AS total_minutes_due, "
						+ "assessment.assessment_id AS assessment_id, assessment.name AS assessment_name, submission.submitter_id AS submitter_id "
						+ "FROM submission "
						+ "INNER JOIN assessment ON assessment.assessment_id = submission.assessment_id "
						+ "INNER JOIN course ON course.course_id = assessment.course_id "
						+ "WHERE submission.submission_id = '" + targetSubmissionID + "'");
		int attempt = 0;
		long minutesBeforeDeadline = 0;
		int assessmentID = -1;
		int submitterID = -1;
		int courseID = -1;
		String assessmentName = null;
		while (resultSet.next()) {
			attempt = resultSet.getInt("attempt");
			// get the normalised minutes difference
			minutesBeforeDeadline = resultSet.getLong("minutes_before_due") * 100
					/ resultSet.getLong("total_minutes_due");
			// limiting time bonus
			if (minutesBeforeDeadline > 100)
				minutesBeforeDeadline = 100;
			if (minutesBeforeDeadline < 0)
				minutesBeforeDeadline = 0;

			// just to ensure no negative numbers
			if (minutesBeforeDeadline < 0)
				minutesBeforeDeadline = 0;
			assessmentID = resultSet.getInt("assessment_id");
			submitterID = resultSet.getInt("submitter_id");
			courseID = resultSet.getInt("course_id");
			assessmentName = resultSet.getString("assessment_name");
		}
		resultSet.close();
		statement.close();

		// check whether the student has an entry in the game student table for given
		// course
		statement = connect.createStatement();
		resultSet = statement.executeQuery("SELECT gs_id FROM game_student_course WHERE student_id = '" + submitterID
				+ "' AND course_id = '" + courseID + "'");
		int gsID = -1;
		while (resultSet.next()) {
			gsID = resultSet.getInt("gs_id");
		}
		resultSet.close();
		statement.close();

		// if no entry exists for the submitter, add a new one
		if (gsID == -1) {
			// add new entry
			sql = "INSERT INTO game_student_course (student_id, course_id) " + "VALUES (?, ?) ";
			p = connect.prepareStatement(sql, Statement.RETURN_GENERATED_KEYS);
			p.setInt(1, submitterID);
			p.setInt(2, courseID);
			p.executeUpdate();
			// get the newly-generated ID
			ResultSet rs = p.getGeneratedKeys();
			if (rs.next())
				gsID = rs.getInt(1);
			p.close();
		}

		// add notification for the submission
		sql = "INSERT INTO game_unobserved_notif (gs_id, message) VALUES (?, ?) ";
		p = connect.prepareStatement(sql);
		p.setInt(1, gsID);
		p.setString(2, "Your submissions has " + (100 - simDegree) + " uniqueness score, " + codeQualityDegree
				+ " quality score, dan " + efficiencyDegree + " efficiency score!<br/>");
		p.executeUpdate();
		p.close();
	}

	public static void process(SimilarityDetectorSettingTuple setting, String targetSubmissionID, String targetCodePath,
			ArrayList<String> otherSubmissionIDs, ArrayList<String> otherCodePaths, String serverBasePath,
			String javaRunCommand, String pythonRunCommand, Connection connect) {

		String internalRepSubmissionsPath = serverBasePath + "internal_rep_submissions" + File.separator;
		String temporarySubmissionPath = serverBasePath + "temporary" + File.separator;

		String ext = setting.getExt();
		int ngramForIR = setting.getnGramForIR();
		double suspicionThresholdForIR = setting.getSuspicionThresholdForIR();
		double inclusionThresholdForCommonFragments = setting.getInclusionThresholdForCommonFragments();
		int minPyNgramLengthForCommonFragments = setting.getMinPyNgramLengthForCommonFragments();
		int maxPyNgramLengthForCommonFragments = setting.getMaxPyNgramLengthForCommonFragments();
		int minJavaNgramLengthForCommonFragments = setting.getMinJavaNgramLengthForCommonFragments();
		int maxJavaNgramLengthForCommonFragments = setting.getMaxJavaNgramLengthForCommonFragments();
		int minMatchingLengthJavaForFeedbackGeneration = setting.getMinMatchingLengthJavaForFeedbackGeneration();
		int minMatchingLengthPyForFeedbackGeneration = setting.getMinMatchingLengthPyForFeedbackGeneration();
		int numSimulationDisguises = setting.getNumSimulationDisguises();
		String humanLanguage = setting.getHumanLanguage();

		// generate the token strings
		// for the target
		ArrayList<ArrayList<ETokenTuple>> targetTokenStrings = TokenExtractor.getTokenStrings(targetCodePath, ext,
				internalRepSubmissionsPath, temporarySubmissionPath, setting.isZip(), humanLanguage);
		ArrayList<ETokenTuple> targetSTokenString = targetTokenStrings.get(0);
		ArrayList<ETokenTuple> targetCWTokenString = targetTokenStrings.get(1);
		int tokenSize = targetSTokenString.size(); // get the token size
		int totalTokenSize = tokenSize; // total tokens in already-submitted submissions, started from the target's
										// token size
		int totalSubs = 1; // total already-submitted submissions, starting from 1, the target submission
		// get all the token strings
		ArrayList<ArrayList<ETokenTuple>> sTokenStrings = new ArrayList<ArrayList<ETokenTuple>>();
		ArrayList<ArrayList<ETokenTuple>> cwTokenStrings = new ArrayList<ArrayList<ETokenTuple>>();
		// add other token strings
		for (int i = 0; i < otherCodePaths.size(); i++) {
			String anotherPath = otherCodePaths.get(i);
			ArrayList<ArrayList<ETokenTuple>> tmp = TokenExtractor.getTokenStrings(anotherPath, ext,
					internalRepSubmissionsPath, temporarySubmissionPath, setting.isZip(), humanLanguage);
			sTokenStrings.add(tmp.get(0));
			cwTokenStrings.add(tmp.get(1));
			// total the token size and the num of subs
			totalTokenSize += tmp.get(0).size();
			totalSubs++;
		}
		// and add the target ones
		sTokenStrings.add(targetSTokenString);
		cwTokenStrings.add(targetCWTokenString);

		// calculate efficiency score from the program length
		// 100 if the program length is <= average + 10%
		// otherwise, 100 - difference
		double avgTokenSize = 1.0 * totalTokenSize / totalSubs;
		int efficiencyScore = 100;
		if (tokenSize > (avgTokenSize * 1.1)) {
			// if more than given threshold, reset efficiency score
			int difference = (int) (1.0 * (tokenSize - avgTokenSize) / avgTokenSize);
			efficiencyScore = 100 - difference;
			if (efficiencyScore < 0)
				efficiencyScore = 0;
			if (efficiencyScore > 100)
				efficiencyScore = 100;
		}

		// IR filtering
		ArrayList<Integer> suspiciousSubmissionIndexes = EIRFiltering.getIRFilteringResult(targetCodePath,
				otherCodePaths, ngramForIR, suspicionThresholdForIR, ext, internalRepSubmissionsPath,
				temporarySubmissionPath, setting.isZip());

		// filter from given syntaxTokenStrings and cwTokenStrings and take only
		// the suspicious ones
		ArrayList<ArrayList<ETokenTuple>> suspiciousOtherSStrings = new ArrayList<ArrayList<ETokenTuple>>();
		ArrayList<ArrayList<ETokenTuple>> suspiciousOtherCWStrings = new ArrayList<ArrayList<ETokenTuple>>();
		// storing the IDs of the suspicious submissions
		ArrayList<String> suspiciousOtherSubmissionIDs = new ArrayList<String>();
		for (int i = 0; i < suspiciousSubmissionIndexes.size(); i++) {
			Integer ni = suspiciousSubmissionIndexes.get(i);
			// add to the filtered lists
			suspiciousOtherSStrings.add(sTokenStrings.get(ni));
			suspiciousOtherCWStrings.add(cwTokenStrings.get(ni));
			// add the submission id
			suspiciousOtherSubmissionIDs.add(otherSubmissionIDs.get(ni));
		}

		// boolean markers
		boolean[] usedTokenMarkerForTargetSyntaxString = new boolean[targetSTokenString.size()];
		// for other token strings
		boolean[][] usedTokenMarkerForOtherSyntaxStrings = new boolean[suspiciousOtherSStrings.size()][];
		for (int i = 0; i < usedTokenMarkerForOtherSyntaxStrings.length; i++) {
			usedTokenMarkerForOtherSyntaxStrings[i] = new boolean[suspiciousOtherSStrings.get(i).size()];
		}

		// set obfuscation setting tuple
		EObfuscatorSettingTuple obfuscationSetting = null;
		if (ext.endsWith("java"))
			obfuscationSetting = new EJavaDefaultObfuscatorSettingTuple(setting.getHumanLanguage());
		else if (ext.endsWith("py"))
			obfuscationSetting = new EPythonDefaultObfuscatorSettingTuple(setting.getHumanLanguage());
		else if(ext.endsWith("txt"))
			obfuscationSetting = new ETextDefaultObfuscatorSettingTuple(setting.getHumanLanguage());

		// BELOM DARI SINIIII
		
		// generate the process for target
		double matchesProportion = RealSuspicionGenerator.generateRealSuspicion(usedTokenMarkerForTargetSyntaxString,
				usedTokenMarkerForOtherSyntaxStrings, targetSTokenString, targetCWTokenString, suspiciousOtherSStrings,
				suspiciousOtherCWStrings, obfuscationSetting, minMatchingLengthJavaForFeedbackGeneration,
				minMatchingLengthPyForFeedbackGeneration, ext, humanLanguage, targetSubmissionID, connect, true,
				targetCodePath, setting.isZip(), temporarySubmissionPath, javaRunCommand, pythonRunCommand,
				setting.getJavaExpectedTokensPerIssue(), setting.getPythonExpectedTokensPerIssue(), efficiencyScore);

		if (matchesProportion < 0.5) {// || otherSubmissionIDs.size() == 0) {
			// if it is unique or has no other submissions to compare to

			boolean isOverlyUnique = false;
			// if the matches are less than 0.1, mark as overly unique
			if (matchesProportion < 0.1) {
				isOverlyUnique = true;
			}

			// if not suspicious, create a simulation instead of the real one
			boolean isGenerated = SimulatedSuspicionGenerator.generateSimulatedSuspicion(
					usedTokenMarkerForTargetSyntaxString, targetSTokenString, targetCWTokenString,
					numSimulationDisguises, isOverlyUnique, obfuscationSetting,
					minMatchingLengthJavaForFeedbackGeneration, minMatchingLengthPyForFeedbackGeneration, ext,
					humanLanguage, targetSubmissionID, connect, null, targetCodePath, setting.isZip(),
					temporarySubmissionPath, javaRunCommand, pythonRunCommand, setting.getJavaExpectedTokensPerIssue(),
					setting.getPythonExpectedTokensPerIssue(), efficiencyScore);
			if (isGenerated) {
				// if the student's code can be used for generating suspicion
				System.out.println("Target submission: " + targetSubmissionID);
				System.out.println(
						"The submission is not similar to those of their colleagues and a simulation has been successfully created.");
			} else {
				// otherwise, create an artificial code for this

				// create a temporary list storing the original token string
				ArrayList<ETokenTuple> targetMergedTokenStringTemp = new ArrayList<ETokenTuple>();
				// add the lists
				targetMergedTokenStringTemp.addAll(targetSTokenString);
				targetMergedTokenStringTemp.addAll(targetCWTokenString);
				// sort
				Collections.sort(targetMergedTokenStringTemp);

				if (ext.endsWith("py")) {
					// generate new token strings from the default simulation code
					targetTokenStrings = TokenExtractor.getTokenStrings(
							ScheduledSuspicionGenerator.prefixPath + "Default_simulation.py", ext,
							internalRepSubmissionsPath, temporarySubmissionPath, false, humanLanguage);
					targetSTokenString = targetTokenStrings.get(0);
					targetCWTokenString = targetTokenStrings.get(1);

					// generate boolean marker for python
					usedTokenMarkerForTargetSyntaxString = new boolean[targetSTokenString.size()];
				} else {
					// generate new token strings from the default simulation code
					targetTokenStrings = TokenExtractor.getTokenStrings(
							ScheduledSuspicionGenerator.prefixPath + "Default_simulation.java", ext,
							internalRepSubmissionsPath, temporarySubmissionPath, false, humanLanguage);
					targetSTokenString = targetTokenStrings.get(0);
					targetCWTokenString = targetTokenStrings.get(1);

					// generate boolean marker for java
					usedTokenMarkerForTargetSyntaxString = new boolean[targetSTokenString.size()];
					// mark public static void main and scanner declaration as common
					for (int k = 11; k <= 32; k++)
						usedTokenMarkerForTargetSyntaxString[k] = true;

					// mark sc.close and two closing curly brackets
					for (int k = 0; k < 8; k++)
						usedTokenMarkerForTargetSyntaxString[usedTokenMarkerForTargetSyntaxString.length - k
								- 1] = true;
				}

				SimulatedSuspicionGenerator.generateSimulatedSuspicion(usedTokenMarkerForTargetSyntaxString,
						targetSTokenString, targetCWTokenString, numSimulationDisguises, isOverlyUnique,
						obfuscationSetting, minMatchingLengthJavaForFeedbackGeneration,
						minMatchingLengthPyForFeedbackGeneration, ext, humanLanguage, targetSubmissionID, connect,
						targetMergedTokenStringTemp, targetCodePath, setting.isZip(), temporarySubmissionPath,
						javaRunCommand, pythonRunCommand, setting.getJavaExpectedTokensPerIssue(),
						setting.getPythonExpectedTokensPerIssue(), efficiencyScore);

				System.out.println("Target submission: " + targetSubmissionID);
				System.out.println(
						"The submission is not similar to those of their colleagues and a simulation with default code has been successfully created.");
			}
		} else {
			// print the suspected submissions
			System.out.println("Target submission: " + targetSubmissionID);
			if (suspiciousOtherSubmissionIDs.size() > 0) {
				System.out.println("The submission is similar to those of their colleagues:");
				System.out.println(suspiciousOtherSubmissionIDs.size());
				for (String s : suspiciousOtherSubmissionIDs) {
					if (s != null)
						System.out.println(s);
				}
			} else {
				System.out.println(
						"The submission is similar to those of their colleagues but for others they are not evident enough.");
			}
		}
	}

	// if templateHTMLPath is null, the result will be passed to table
	public static void processToHTML(SimilarityDetectorSettingTuple setting, String targetSubmissionID,
			String targetCodePath, ArrayList<String> otherSubmissionIDs, ArrayList<String> otherCodePaths,
			String templateHTMLPath) {

		String ext = setting.getExt();
		int ngramForIR = setting.getnGramForIR();
		double suspicionThresholdForIR = setting.getSuspicionThresholdForIR();
		double inclusionThresholdForCommonFragments = setting.getInclusionThresholdForCommonFragments();
		int minPyNgramLengthForCommonFragments = setting.getMinPyNgramLengthForCommonFragments();
		int maxPyNgramLengthForCommonFragments = setting.getMaxPyNgramLengthForCommonFragments();
		int minJavaNgramLengthForCommonFragments = setting.getMinJavaNgramLengthForCommonFragments();
		int maxJavaNgramLengthForCommonFragments = setting.getMaxJavaNgramLengthForCommonFragments();
		int minMatchingLengthJavaForFeedbackGeneration = setting.getMinMatchingLengthJavaForFeedbackGeneration();
		int minMatchingLengthPyForFeedbackGeneration = setting.getMinMatchingLengthPyForFeedbackGeneration();
		String additionalKeywordPath = setting.getAdditionalKeywordPath();
		int numSimulationDisguises = setting.getNumSimulationDisguises();
		String humanLanguage = setting.getHumanLanguage();

		// generate the token strings
		// for the target
		ArrayList<ArrayList<ETokenTuple>> targetTokenStrings = TokenExtractor.getTokenStrings(targetCodePath, ext, humanLanguage);
		ArrayList<ETokenTuple> targetSTokenString = targetTokenStrings.get(0);
		ArrayList<ETokenTuple> targetCWTokenString = targetTokenStrings.get(1);
		// get all the token strings
		ArrayList<ArrayList<ETokenTuple>> sTokenStrings = new ArrayList<ArrayList<ETokenTuple>>();
		ArrayList<ArrayList<ETokenTuple>> cwTokenStrings = new ArrayList<ArrayList<ETokenTuple>>();
		// add other token strings
		for (int i = 0; i < otherCodePaths.size(); i++) {
			String anotherPath = otherCodePaths.get(i);
			ArrayList<ArrayList<ETokenTuple>> tmp = TokenExtractor.getTokenStrings(anotherPath, ext, humanLanguage);
			sTokenStrings.add(tmp.get(0));
			cwTokenStrings.add(tmp.get(1));
		}
		// and add the target ones
		sTokenStrings.add(targetSTokenString);
		cwTokenStrings.add(targetCWTokenString);

//		// Common code generator
//		ArrayList<ECommonFragmentTuple> commonFragments = ECommonFragmentGenerator.generateCommonFragments(
//				sTokenStrings, inclusionThresholdForCommonFragments, minPyNgramLengthForCommonFragments,
//				maxPyNgramLengthForCommonFragments, minJavaNgramLengthForCommonFragments,
//				maxJavaNgramLengthForCommonFragments, ext);

//		System.out.println("size: " + commonFragments.size());

//		for(ECommonFragmentTuple c: commonFragments) {
//			System.out.println(c.getContent());
//		}

		// IR filtering
		ArrayList<Integer> suspiciousSubmissionIndexes = EIRFiltering.getIRFilteringResult(targetCodePath,
				otherCodePaths, ngramForIR, suspicionThresholdForIR, ext, null, null, false);

		// filter from given syntaxTokenStrings and cwTokenStrings and take only
		// the suspicious ones
		ArrayList<ArrayList<ETokenTuple>> suspiciousOtherSStrings = new ArrayList<ArrayList<ETokenTuple>>();
		ArrayList<ArrayList<ETokenTuple>> suspiciousOtherCWStrings = new ArrayList<ArrayList<ETokenTuple>>();
		// storing the IDs of the suspicious submissions
		ArrayList<String> suspiciousOtherSubmissionIDs = new ArrayList<String>();
		for (int i = 0; i < suspiciousSubmissionIndexes.size(); i++) {
			Integer ni = suspiciousSubmissionIndexes.get(i);
			// add to the filtered lists
			suspiciousOtherSStrings.add(sTokenStrings.get(ni));
			suspiciousOtherCWStrings.add(cwTokenStrings.get(ni));
			// add the submission id
			suspiciousOtherSubmissionIDs.add(otherSubmissionIDs.get(ni));
		}

		// common code removal for the target token string
		boolean[] usedTokenMarkerForTargetSyntaxString = new boolean[targetSTokenString.size()];
		// common code removal for other token strings
		boolean[][] usedTokenMarkerForOtherSyntaxStrings = new boolean[suspiciousOtherSStrings.size()][];
		for (int i = 0; i < suspiciousOtherSStrings.size(); i++) {
			usedTokenMarkerForOtherSyntaxStrings[i] = new boolean[suspiciousOtherSStrings.get(i).size()];
		}

		// set obfuscation setting tuple
		EObfuscatorSettingTuple obfuscationSetting = null;
		if (ext.endsWith("java"))
			obfuscationSetting = new EJavaDefaultObfuscatorSettingTuple("en");
		else if (ext.endsWith("py"))
			obfuscationSetting = new EPythonDefaultObfuscatorSettingTuple("en");

		// generate the process for target
		boolean isSuspicious = RealSuspicionGenerator.generateRealSuspicionToHTML(usedTokenMarkerForTargetSyntaxString,
				usedTokenMarkerForOtherSyntaxStrings, targetSTokenString, targetCWTokenString, suspiciousOtherSStrings,
				suspiciousOtherCWStrings, obfuscationSetting, minMatchingLengthJavaForFeedbackGeneration,
				minMatchingLengthPyForFeedbackGeneration, ext, humanLanguage, templateHTMLPath,
				targetSubmissionID + ".html");

		if (isSuspicious == false) {
			// if not suspicious, create a simulation instead of the real one
			boolean isGenerated = SimulatedSuspicionGenerator.generateSimulatedSuspicionToHTML(
					usedTokenMarkerForTargetSyntaxString, targetSTokenString, targetCWTokenString,
					numSimulationDisguises, obfuscationSetting, minMatchingLengthJavaForFeedbackGeneration,
					minMatchingLengthPyForFeedbackGeneration, ext, humanLanguage, templateHTMLPath,
					targetSubmissionID + ".html");
			if (isGenerated) {
				// if the student's code can be used for generating suspicion
				System.out.println("Target submission: " + targetSubmissionID);
				System.out.println(
						"The submission is not similar to other students and a simulation has been successfully created.");
			} else {
				// otherwise, create an artificial code for this
				if (ext.endsWith("py")) {
					// generate new token strings from the default simulation code
					targetTokenStrings = TokenExtractor
							.getTokenStrings(ScheduledSuspicionGenerator.prefixPath + "Default_simulation.py", ext, humanLanguage);
					targetSTokenString = targetTokenStrings.get(0);
					targetCWTokenString = targetTokenStrings.get(1);

					// generate boolean marker for python
					usedTokenMarkerForTargetSyntaxString = new boolean[targetSTokenString.size()];
				} else {
					// generate new token strings from the default simulation code
					targetTokenStrings = TokenExtractor
							.getTokenStrings(ScheduledSuspicionGenerator.prefixPath + "Default_simulation.java", ext, humanLanguage);
					targetSTokenString = targetTokenStrings.get(0);
					targetCWTokenString = targetTokenStrings.get(1);

					// generate boolean marker for java
					usedTokenMarkerForTargetSyntaxString = new boolean[targetSTokenString.size()];
					// mark public static void main and scanner declaration as common
					for (int k = 11; k <= 32; k++)
						usedTokenMarkerForTargetSyntaxString[k] = true;

					// mark sc.close and two closing curly brackets
					for (int k = 0; k < 8; k++)
						usedTokenMarkerForTargetSyntaxString[usedTokenMarkerForTargetSyntaxString.length - k
								- 1] = true;
				}

				// two constants are set here to assure many fragment candidates are given
				SimulatedSuspicionGenerator.generateSimulatedSuspicionToHTML(usedTokenMarkerForTargetSyntaxString,
						targetSTokenString, targetCWTokenString, numSimulationDisguises, obfuscationSetting, 20, 10,
						ext, humanLanguage, templateHTMLPath, targetSubmissionID + ".html");

				System.out.println("Target submission: " + targetSubmissionID);
				System.out.println(
						"The submission is not similar to those of other students and a simulation with default code has been successfully created.");
			}
		} else {
			// generate suspicion report for others
			RealSuspicionGenerator.generateSuspicionReportForOthersToHTML(usedTokenMarkerForTargetSyntaxString,
					usedTokenMarkerForOtherSyntaxStrings, targetSTokenString, targetCWTokenString,
					suspiciousOtherSStrings, suspiciousOtherCWStrings, obfuscationSetting,
					minMatchingLengthJavaForFeedbackGeneration, minMatchingLengthPyForFeedbackGeneration, ext,
					humanLanguage, templateHTMLPath, suspiciousOtherSubmissionIDs);

			// print the suspected submissions
			System.out.println("Target submission: " + targetSubmissionID);
			System.out.println("The submission is suspicious.");
			boolean isFirst = true;
			for (String s : suspiciousOtherSubmissionIDs) {
				if (s != null) {
					if (isFirst) { // if it is the first colleague
						System.out.println("Their involved colleagues:");
						isFirst = false;
					}
					System.out.println(s);
				}
			}

			if (isFirst) {
				// if other colleagues' submissions are not suspicious
				System.out.println("But other colleagues' submissions are not alerted.");
			}
		}
	}

}
