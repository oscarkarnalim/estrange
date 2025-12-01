package p6.codeclarity.expansion;

import java.io.BufferedReader;
import java.io.File;
import java.io.InputStreamReader;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.nio.file.StandardCopyOption;
import java.sql.Connection;
import java.sql.ResultSet;
import java.sql.Statement;
import java.util.ArrayList;
import java.util.Scanner;

import p5.educationalstrange.scheduledsuspiciongenerator.ScheduledSuspicionGenerator;
import p5.educationalstrange.tuple.ETokenTuple;

public class JavaAdditionalSuggestions {
	public static String checkStyleJarPath = ScheduledSuspicionGenerator.prefixPath + "style" + File.separator
			+ "checkstyle-8.43-all.jar";
	public static String checkStyleXMLEnPath = ScheduledSuspicionGenerator.prefixPath + "style" + File.separator
			+ "java_style_rules_en.xml";
	public static String checkStyleXMLIdPath = ScheduledSuspicionGenerator.prefixPath + "style" + File.separator
			+ "java_style_rules_id.xml";

	public static int addSuggestionTuplesFromCheckStyle(ArrayList<ExpandedClaritySuggestionTuple> out, String path,
			String languageCode, ArrayList<ETokenTuple> tokens, Connection connect, int baseRow, String javaRunCommand) {
		/*
		 * this method returns an integer totaling the number of rows of given file +
		 * baseRow. The mechanism is needed for mapping the output to a merged code file
		 */

		String[] args = new String[] { javaRunCommand, "-jar", checkStyleJarPath, "-c",
				((languageCode.equals("en")) ? checkStyleXMLEnPath : checkStyleXMLIdPath), path };
		try {
			Process p = Runtime.getRuntime().exec(args);
			BufferedReader br = new BufferedReader(new InputStreamReader(p.getInputStream()));
			String line;
			while ((line = br.readLine()) != null) {
				if (line.startsWith("[ERROR]")) {
					// split the message based on ':' to get row, column, issue type, and the
					// explanation
					String[] r = line.substring(line.lastIndexOf(".java:") + 6).split(":");

					// get the line
					int tokenLine = Integer.parseInt(r[0]) + baseRow;

					int processedIdx = 1; // to mark how many indexes have been processed

					// get the column if any
					int tokenCol = -1;
					if (r.length > 2) {
						/*
						 * only assign col if there are more than two ':' as one message has no column
						 * information the column is reduced by one since apparently check style counts
						 * column from one while ours from zero. Ours follows ANTLR definition.
						 */
						tokenCol = Integer.parseInt(r[1]) - 1;
						processedIdx++;
					}
					// get explanation and its "check style"'s issue type
					String explanation = "";
					String checkStyleIssueType = "";
					for (int i = processedIdx; i < r.length; i++)
						explanation = explanation + r[i] + ((i != r.length - 1) ? ":" : "");
					explanation = explanation.trim();
					checkStyleIssueType = explanation.substring(explanation.lastIndexOf("[") + 1,
							explanation.length() - 1);
					explanation = explanation.substring(0, explanation.lastIndexOf("["));

					// generate the new keyword
					String issueKeyword = getIssueKeywordFromCheckStyleKeyword(checkStyleIssueType, languageCode);

					// get the targeted token
					ETokenTuple targetedToken = UtilsForExpandingCodeClaritySuggestions.getTargetedToken(tokens,
							tokenLine, tokenCol);

					// add the message to the list
					// check if there are messages in that position
					int pos = UtilsForExpandingCodeClaritySuggestions.indexOfMessages(out, targetedToken.getLine(),
							targetedToken.getColumn());
					if (pos == -1) {
						// if not, add a new one
						ArrayList<String> issueKeywords = new ArrayList<String>();
						issueKeywords.add(issueKeyword);
						ArrayList<String> issueExplanations = new ArrayList<String>();
						issueExplanations.add(explanation);
						out.add(new ExpandedClaritySuggestionTuple(targetedToken, targetedToken.getLine(),
								targetedToken.getColumn(), targetedToken.getRawText(), issueKeywords,
								issueExplanations));
					} else {
						// otherwise, update the existing one
						out.get(pos).addIssueKeyword(issueKeyword);
						out.get(pos).addIssueExplanation(explanation);
					}
				}
			}
			br.close();
		} catch (Exception e) {
			e.printStackTrace();
			// compilation error

			// generate keyword and message
			String issueKeyword = "";
			String issueExplanation = "";
			if (languageCode.equals("en")) {
				issueKeyword = ("Incorrect and/or uncommon syntaxes");
				issueExplanation = ("The program seems to use incorrect and/or uncommon syntaxes! No syntax suggestions are provided");
			} else {
				issueKeyword = ("Sintaks tidak benar dan/atau tidak umum");
				issueExplanation = ("Program tampak menggunakan sintaks yang tidak benar dan/atau tidak umum! Tidak ada rekomendasi sintaks yang dapat diberikan");
			}

			// check whether there is a message at the first token FOR THIS FILE
			ETokenTuple firstToken = UtilsForExpandingCodeClaritySuggestions.getTargetedToken(tokens, baseRow, 0);
			int pos = UtilsForExpandingCodeClaritySuggestions.indexOfMessages(out, firstToken.getLine(),
					firstToken.getColumn());
			if (pos == -1) {
				// if not, add a new one
				ArrayList<String> issueKeywords = new ArrayList<String>();
				issueKeywords.add(issueKeyword);
				ArrayList<String> issueExplanations = new ArrayList<String>();
				issueExplanations.add(issueExplanation);
				out.add(0, new ExpandedClaritySuggestionTuple(firstToken, firstToken.getLine(), firstToken.getColumn(),
						firstToken.getRawText(), issueKeywords, issueExplanations));
			} else {
				// otherwise, update the existing one
				out.get(pos).addIssueKeyword(issueKeyword);
				out.get(pos).addIssueExplanation(issueExplanation);
			}
		}

		try {
			// update baseRow with number of lines in the file
			Scanner sc = new Scanner(new File(path));
			while (sc.hasNextLine()) {
				sc.nextLine();
				baseRow += 1;
			}
			sc.close();
		} catch (Exception e) {
			e.printStackTrace();
		}

		return baseRow;
	}

	private static String getIssueKeywordFromCheckStyleKeyword(String checkStyleKeyword, String languageCode) {
		if (checkStyleKeyword.equals("ArrayTypeStyle"))
			return (languageCode.equals("en") ? "Less explicit array data type" : "Tipe data array kurang eksplisit");
		else if (checkStyleKeyword.equals("AbstractClassName"))
			return (languageCode.equals("en") ? "Inconsistent use of abstract class name and/or 'Abstract' prefix"
					: "Ketidakkonsistenan penggunaan nama kelas abstrak dan prefik 'Abstract'");
		else if (checkStyleKeyword.equals("AvoidInlineConditionals"))
			return (languageCode.equals("en") ? "Using inline conditional" : "Penggunaan if-else satu baris");
		else if (checkStyleKeyword.equals("BooleanExpressionComplexity"))
			return (languageCode.equals("en") ? "Overly complex boolean expression"
					: "Ekspresi boolean terlalu kompleks");
		else if (checkStyleKeyword.equals("ConstantName"))
			return (languageCode.equals("en") ? "Constant name with lowercased letters"
					: "Nama konstan dengan huruf kecil");
		else if (checkStyleKeyword.equals("DeclarationOrder"))
			return (languageCode.equals("en") ? "Incorrect declaration order" : "Urutan deklarasi salah");
		else if (checkStyleKeyword.equals("EmptyBlock"))
			return (languageCode.equals("en") ? "Empty block" : "Blok kosong");
		else if (checkStyleKeyword.equals("EmptyCatchBlock"))
			return (languageCode.equals("en") ? "Empty catch block" : "Blok catch kosong");
		else if (checkStyleKeyword.equals("EmptyLineSeparator"))
			return (languageCode.equals("en") ? "Only one empty line separator needed"
					: "Hanya satu baris kosong pemisah dibutuhkan");
		else if (checkStyleKeyword.equals("EmptyStatement"))
			return (languageCode.equals("en") ? "Semicolon without program statement"
					: "Titik koma tanpa statemen program");
		else if (checkStyleKeyword.equals("ExecutableStatementCount"))
			return (languageCode.equals("en") ? "Too many program statements in a method"
					: "Terlalu banyak statemen program dalam sebuah method");
		else if (checkStyleKeyword.equals("MultipleVariableDeclarations"))
			return (languageCode.equals("en") ? "Multiple variable declaration" : "Deklarasi beberapa variabel");
		else if (checkStyleKeyword.equals("NeedBraces"))
			return (languageCode.equals("en") ? "Braces needed" : "Kurung kurawal dibutuhkan");
		else if (checkStyleKeyword.equals("NestedForDepth"))
			return (languageCode.equals("en") ? "Overly deep nested loop block"
					: "Pengulangan bersarang yang terlalu dalam");
		else if (checkStyleKeyword.equals("NestedIfDepth"))
			return (languageCode.equals("en") ? "Overly deep nested if-else block"
					: "Percabangan bersarang yang terlalu dalam");
		else if (checkStyleKeyword.equals("NestedTryDepth"))
			return (languageCode.equals("en") ? "Overly deep nested try-catch block"
					: "Try-catch bersarang yang terlalu dalam");
		else if (checkStyleKeyword.equals("OneStatementPerLine"))
			return (languageCode.equals("en") ? "Too many program statements in one line"
					: "Terlalu banyak statemen program di satu baris");
		else if (checkStyleKeyword.equals("RequireThis"))
			return (languageCode.equals("en") ? "'this' keyword required" : "Kata kunci 'this' dibutuhkan");
		else if (checkStyleKeyword.equals("SimplifyBooleanExpression"))
			return (languageCode.equals("en") ? "Inefficient boolean expression" : "Ekspresi boolean tidak efisien");
		else if (checkStyleKeyword.equals("SimplifyBooleanReturn"))
			return (languageCode.equals("en") ? "Inefficient boolean return"
					: "Pengembalian nilai boolean tidak efisien");
		else if (checkStyleKeyword.equals("StringLiteralEquality"))
			return (languageCode.equals("en") ? "'equals' or one of its derivations needed for comparison"
					: "'equals' dibutuhkan untuk komparasi");
		else if (checkStyleKeyword.equals("UnnecessaryParentheses"))
			return (languageCode.equals("en") ? "Unnecessary parentheses" : "Kurung yang tidak dibutuhkan");
		else if (checkStyleKeyword.equals("UnnecessarySemicolonAfterTypeMemberDeclaration"))
			return (languageCode.equals("en") ? "Unnecessary semicolon after declaration"
					: "Titik koma tidak dibutuhkan sehabis deklarasi");
		else if (checkStyleKeyword.equals("LineLength"))
			return (languageCode.equals("en") ? "Too many characters in one line"
					: "Terlalu banyak karakter di satu baris");

		return null;
	}

}
