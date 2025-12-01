package p6.codeclarity;

import java.io.File;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.nio.file.StandardCopyOption;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.Statement;
import java.util.ArrayList;
import java.util.Collections;

import p5.educationalstrange.ZipManipulation;
import p5.educationalstrange.tuple.ETokenTuple;
import p6.codeclarity.expansion.ExpandedClaritySuggestionTuple;
import p6.codeclarity.expansion.JavaAdditionalSuggestions;
import p6.codeclarity.expansion.PythonAdditionalSuggestion;

public class CodeClarityContentGenerator {

	public static int execute(ArrayList<ETokenTuple> tokens, String programmingLanguageCode, String languageCode,
			Connection connect, String submissionID, String targetSubmissionID, String publicSuggestionID,
			String targetCodePath, boolean isZip, String temporarySubmissionPath, String javaRunCommand,
			String pythonRunCommand, int javaExpectedTokensPerIssue, int pythonExpectedTokensPerIssue) throws Exception {
		
		/*
		 * This method generates code quality report and returns its code quality degree
		 */

		// for storing code clarity messages
		ArrayList<ExpandedClaritySuggestionTuple> messages = new ArrayList<ExpandedClaritySuggestionTuple>();

		if (tokens.size() > 0) {
			/*
			 * If zipped, unzip the code files and put them on temporary dir, process each
			 * one of them, and merge the report
			 */

			// to store the temporary location for removal after the process
			String temporaryRootPath = "";

			// to store the path of each code file
			ArrayList<String> targetCodePaths = null;

			if (isZip) {
				// if zipped, unzip the files
				targetCodePaths = ZipManipulation.extractZipAndGetCodeFiles(targetCodePath, temporarySubmissionPath,
						programmingLanguageCode);
				// get the temporary location for removal after the process
				temporaryRootPath = temporarySubmissionPath
						+ targetCodePath.substring(targetCodePath.lastIndexOf(File.separator) + 1);
			} else {
				/*
				 * if only one file, copy the file to temporary and rename to its original name.
				 * This is because Java's additional module needs the code file as it is, not
				 * the anonymised one.
				 */

				// get original name of the file
				Statement statement = connect.createStatement();
				ResultSet resultSet = statement
						.executeQuery("SELECT filename FROM submission WHERE submission_id = '" + submissionID + "'");
				String originalFileName = "";
				while (resultSet.next()) {
					originalFileName = resultSet.getString("filename");
				}
				resultSet.close();
				statement.close();

				// copy the file
				String newpath = temporarySubmissionPath + File.separator + originalFileName;
				Path copied = Paths.get(newpath);
				Path originalPath = new File(targetCodePath).toPath();
				Files.copy(originalPath, copied, StandardCopyOption.REPLACE_EXISTING);

				// add to the list
				targetCodePaths = new ArrayList<String>();
				targetCodePaths.add(newpath);

				// get the temporary path
				temporaryRootPath = newpath;
			}

			// to predict the row
			int baseRow = 0;
			int additionalRowsPerFile = 0;

			// this is due to added comments to separate all files
			if (isZip)
				additionalRowsPerFile = 3;

			// for each filepath
			for (String path : targetCodePaths) {

				// add additionalRowsPerFile
				baseRow += additionalRowsPerFile;

				// add messages from checkstyle if the programming language is Java
				if (programmingLanguageCode.equals("java"))
					baseRow = JavaAdditionalSuggestions.addSuggestionTuplesFromCheckStyle(messages, path, languageCode,
							tokens, connect, baseRow, javaRunCommand);
				// add messages from flake8 if the programming language is Python
				else if (programmingLanguageCode.equals("py")) {
					baseRow = PythonAdditionalSuggestion.addSuggestionTuplesFromFlake8(messages, path, languageCode,
							tokens, baseRow, pythonRunCommand);
				}

			}

			// delete the temporary files
			ZipManipulation.deleteAllTemporaryFiles(new File(temporaryRootPath));
		}

		// sort the messages and assign visual ID

		// sort
		Collections.sort(messages);

		// assign visual id
		for (int i = 0; i < messages.size(); i++) {
			messages.get(i).setVisualId("s" + (i + 1));
		}
		
		// calculate code quality degree, which is the proportion of issues with (token size/minTokens)
		int expectedTokensPerIssue = pythonExpectedTokensPerIssue;
		if (programmingLanguageCode.equals("java"))
			expectedTokensPerIssue = javaExpectedTokensPerIssue;
		int codeQualityDegree = Math.max(0, 100 - (int) (messages.size() * 100.0 * expectedTokensPerIssue / tokens.size()));
		
		// generate code quality report and update the table if any messages exist
		if (messages.size() > 0)
			updateSqlTable(tokens, messages, languageCode, connect, submissionID, publicSuggestionID, codeQualityDegree);
		else 
			// set quality degree as 100 as no messages are generated
			codeQualityDegree = 100;
		
		return codeQualityDegree;
	}

	private static void updateSqlTable(ArrayList<ETokenTuple> tokens,
			ArrayList<ExpandedClaritySuggestionTuple> messages, String humanLanguage, Connection connect,
			String submissionID, String publicSuggestionID, int codeQualityDegree) throws Exception {
		String code = generateCode1(tokens, messages);
		String tablecontent = generateTableContent(messages, humanLanguage);
		String explanation = generateExplanation(messages, humanLanguage);

		// check whether the ID exists
		Statement statement = connect.createStatement();
		ResultSet resultSet = statement.executeQuery(
				"SELECT suggestion_id FROM code_clarity_suggestion WHERE submission_id = '" + submissionID + "'");
		int suggestionID = -1;
		while (resultSet.next()) {
			suggestionID = resultSet.getInt("suggestion_id");
		}
		resultSet.close();
		statement.close();

		if (suggestionID == -1) {
			// create new entry
			String sql = "INSERT INTO code_clarity_suggestion (marked_code, table_info, explanation_info, submission_id, public_suggestion_id, quality_point) "
					+ "VALUES (?, ?, ?, ?, ?, ?)";
			PreparedStatement p = connect.prepareStatement(sql);
			p.setString(1, code);
			p.setString(2, tablecontent);
			p.setString(3, explanation);
			p.setInt(4, Integer.parseInt(submissionID));
			p.setString(5, publicSuggestionID);
			p.setInt(6, codeQualityDegree);
			p.executeUpdate();
		} else {
			// update
			String sql = "UPDATE code_clarity_suggestion SET marked_code = ?, table_info = ?, explanation_info = ?, public_suggestion_id = ?, quality_point = ? "
					+ "WHERE suggestion_id = ? ";
			PreparedStatement p = connect.prepareStatement(sql);
			p.setString(1, code);
			p.setString(2, tablecontent);
			p.setString(3, explanation);
			p.setString(4, publicSuggestionID);
			p.setInt(5, codeQualityDegree);
			p.setInt(6, suggestionID);
			p.executeUpdate();
		}
	}

	public static String generateExplanation(ArrayList<ExpandedClaritySuggestionTuple> messages, String humanLanguage) {
		StringBuffer s = new StringBuffer();
		// add explanation for each fragment
		for (ExpandedClaritySuggestionTuple m : messages) {
			// append the string
			s.append("<div class=\"explanationcontent\" id=\"" + m.getVisualId() + "he\">\n\t");
			s.append(m.getIssueExplanationsAsString().replaceAll("\n", "<br />").replaceAll("\t",
					"&nbsp;&nbsp;&nbsp;&nbsp;"));
			s.append("\n</div>\n");
		}

		return s.toString();
	}

	public static String generateTableContent(ArrayList<ExpandedClaritySuggestionTuple> list, String humanLanguage) {
		String tableId = "origtablecontent";

		StringBuffer s = new StringBuffer();

		// start generating the resulted string
		for (int i = 0; i < list.size(); i++) {
			ExpandedClaritySuggestionTuple cur = list.get(i);

			// set the first line
			s.append("<tr id=\"" + cur.getVisualId() + "hr\" onclick=\"markSelectedWithoutChangingTableFocus('"
					+ cur.getVisualId() + "','" + tableId + "')\">");

			/*
			 * Get table ID from visual ID and then aligns it for readability.
			 */
			String visualId = cur.getVisualId();
			// search for the numeric ID part
			int curIdNumPos = 0;
			for (int k = 0; k < visualId.length(); k++) {
				if (Character.isLetter(visualId.charAt(k)) == false) {
					curIdNumPos = k;
					break;
				}
			}
			// merge them together
			String alignedTableID = visualId.toUpperCase().charAt(0) + "";
			int curIdNum = Integer.parseInt(visualId.substring(curIdNumPos));
			if (curIdNum < 10) {
				alignedTableID += "00" + curIdNum;
			} else if (curIdNum < 100) {
				alignedTableID += "0" + curIdNum;
			} else {
				alignedTableID += curIdNum;
			}

			// visualising the rest of the lines
			s.append("\n\t<td style='width:10%;'><a href=\"#" + cur.getVisualId() + "a\" id=\"" + cur.getVisualId()
					+ "hl\">" + alignedTableID + "</a></td>");

			// hint text
			s.append("\n\t<td style='width:20%;'>" + cur.getHintTokenText().trim() + "</td>");

			// set location
			String location = cur.getLine() + "";
			s.append("\n\t<td style='width:10%;'>" + location + "</td>");

			// set issues
			String issues = cur.getIssueKeywordsAsString();
			s.append("\n\t<td>" + issues.replaceAll("\n", "<br />") + "</td>");

			s.append("\n</tr>\n");
		}
		return s.toString();
	}

	public static String generateCode1(ArrayList<ETokenTuple> tokenString,
			ArrayList<ExpandedClaritySuggestionTuple> messages) {
		String codeClass = "syntaxsim";

		StringBuffer s = new StringBuffer();

		// starting from the first message, take all the required data
		int matchIdx = 0;
		ExpandedClaritySuggestionTuple m = messages.get(matchIdx);
		String visualIdForM = m.getVisualId();
		int targetedIdx = tokenString.indexOf(m.getTargetedToken());

		// for each token from code1
		for (int i = 0; i < tokenString.size(); i++) {
			ETokenTuple cur = tokenString.get(i);

			/*
			 * unlike similar function on sim report, this one does not need to replace
			 * special characters as those have been done by the sim report
			 */

			if (i == targetedIdx) {
				// add an opening link tag
				s.append("<a class='" + codeClass + "' id='" + visualIdForM + "a' href=\"#" + visualIdForM
						+ "a\" onclick=\"markSelected('" + visualIdForM + "','origtablecontent')\" >");
				// append the raw text
				s.append(cur.getRawText());
				// add a closing link tag
				s.append("</a>");
				// check for next message if any
				if (matchIdx + 1 < messages.size()) {
					// increment the idx
					matchIdx++;
					// take the new data
					m = messages.get(matchIdx);
					visualIdForM = m.getVisualId();
					targetedIdx = tokenString.indexOf(m.getTargetedToken());
				}
			} else {
				// append the raw text
				s.append(cur.getRawText());
			}
		}
		return s.toString();
	}
}
