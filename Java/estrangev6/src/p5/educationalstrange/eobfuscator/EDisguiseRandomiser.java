package p5.educationalstrange.eobfuscator;

import java.util.ArrayList;
import java.util.Random;

import p5.educationalstrange.eobfuscator.disguisegenerator.ECodeObfuscatorComment;
import p5.educationalstrange.eobfuscator.disguisegenerator.ECodeObfuscatorConstantAndDataTypeChange;
import p5.educationalstrange.eobfuscator.disguisegenerator.ECodeObfuscatorIdentifier;
import p5.educationalstrange.eobfuscator.disguisegenerator.ECodeObfuscatorWhitespace;
import p5.educationalstrange.eobfuscator.tuple.EJavaDefaultObfuscatorSettingTuple;
import p5.educationalstrange.eobfuscator.tuple.EObfuscatorSettingTuple;
import p5.educationalstrange.tuple.ETokenTuple;

public class EDisguiseRandomiser {

	/*
	 * return the disguise message based on given index from an array of applicable
	 * disguises.
	 */
	public static String getDisguiseMessage(int i, String humanLanguage) {
		String out = "";
		switch (i) {
		case 0:
			out = (humanLanguage.equalsIgnoreCase("en")) ? "NOT IMPLEMENTED." : "Tidak diimplementasikan.";
			break;
		case 1:
			out = (humanLanguage.equalsIgnoreCase("en")) ? "All single-line comments are removed."
					: "Semua komentar single-line dibuang.";
			break;
		case 2:
			out = (humanLanguage.equalsIgnoreCase("en")) ? "NOT IMPLEMENTED." : "Tidak diimplementasikan.";
			break;
		case 3:
			out = (humanLanguage.equalsIgnoreCase("en")) ? "All multi-line comments are removed."
					: "Semua komentar multi-line dibuang.";
			break;
		case 4:
			out = (humanLanguage.equalsIgnoreCase("en")) ? "NOT IMPLEMENTED." : "Tidak diimplementasikan.";
			break;
		case 5:
			out = (humanLanguage.equalsIgnoreCase("en")) ? "All comments are removed." : "Semua komentar dibuang.";
			break;
		case 6:
			out = (humanLanguage.equalsIgnoreCase("en"))
					? "A single-line comment is added for each blank line before syntax."
					: "Satu komentar single-line ditambahkan untuk setiap baris kosong sebelum sintaks.";
			break;
		case 7:
			out = (humanLanguage.equalsIgnoreCase("en"))
					? "A multi-line comment is added for each blank line before syntax."
					: "Satu komentar multi-line ditambahkan untuk setiap baris kosong sebelum sintaks.";
			break;
		case 8:
			out = (humanLanguage.equalsIgnoreCase("en"))
					? "The single-line comments are changed to the multi-line ones."
					: "Semua komentar single-line diubah ke multi-line.";
			break;
		case 9:
			out = (humanLanguage.equalsIgnoreCase("en"))
					? "The single-line comments are changed to the multi-line ones with punctuations as the line separators."
					: "Semua komentar single-line diubah ke multi-line dengan tanda baca sebagai pemisah baris.";
			break;
		case 10:
			out = (humanLanguage.equalsIgnoreCase("en"))
					? "The single-line comments are changed to the multi-line ones with 80 characters per line."
					: "Semua komentar single-line diubah ke multi-line dengan 80 karakter per baris. ";
			break;
		case 11:
			out = (humanLanguage.equalsIgnoreCase("en"))
					? "Each single-line comment is split to several single-line comments with punctuations as the line separators."
					: "Setiap komentar single-line dipecah ke beberapa komentar yang lebih pendek dengan tanda baca sebagai pemisah baris.";
			break;
		case 12:
			out = (humanLanguage.equalsIgnoreCase("en"))
					? "Each single-line comment is split to several single-line comments with 80 characters per line."
					: "Setiap komentar single-line dipecah ke beberapa komentar yang lebih pendek dengan batasan 80 karakter per baris.";
			break;
		case 13:
			out = (humanLanguage.equalsIgnoreCase("en"))
					? "The multi-line comments are changed to the single-line ones with all newlines removed."
					: "Semua komentar multi-line diubah ke single-line dengan semua karakter newline dibuang.";
			break;
		case 14:
			out = (humanLanguage.equalsIgnoreCase("en"))
					? "Each multi-line comment is split to several single-line comments with newlines as the line separators."
					: "Setiap komentar multi-line dipecah ke beberapa komentar single-line dengan karakter newline sebagai pemisah baris.";
			break;
		case 15:
			out = (humanLanguage.equalsIgnoreCase("en"))
					? "Each multi-line comment is split to several single-line comments with punctuations as the line separators."
					: "Setiap komentar multi-line dipecah ke beberapa komentar single-line dengan tanda baca sebagai pemisah baris.";
			break;
		case 16:
			out = (humanLanguage.equalsIgnoreCase("en"))
					? "Each multi-line comment is split to several single-line comments with 80 characters per line."
					: "Setiap komentar multi-line dipecah menjadi beberapa komentar single-line dengan 80 karakter per baris.";
			break;
		case 17:
			out = (humanLanguage.equalsIgnoreCase("en")) ? "The first character of each comment word is capitalised."
					: "Karakter pertama dari setiap kata di komentar dibuat menjadi kapital.";
			break;
		case 18:
			out = (humanLanguage.equalsIgnoreCase("en")) ? "All comment characters are capitalised."
					: "Semua karakter di komentar dibuat menjadi kapital.";
			break;
		case 19:
			out = (humanLanguage.equalsIgnoreCase("en")) ? "All comment characters are lowercased."
					: "Semua karakter di komentar dibuat menjadi huruf kecil. ";
			break;
		case 20:
			out = (humanLanguage.equalsIgnoreCase("en"))
					? "All conjuction symbols are replaced with their corresponding words in comments."
					: "Pada komentar, semua simbol kata sambung diganti dengan kata sambungnya.";
			break;
		case 21:
			out = (humanLanguage.equalsIgnoreCase("en"))
					? "All conjuction words are replaced with their corresponding symbols in comments."
					: "Pada komentar, semua kata sambung diganti dengan simbolnya.";
			break;
		case 22:
			out = (humanLanguage.equalsIgnoreCase("en"))
					? "All math symbols are replaced with their corresponding words in comments."
					: "Pada komentar, semua simbol matematika diganti dengan kata.";
			break;
		case 23:
			out = (humanLanguage.equalsIgnoreCase("en"))
					? "All math words are replaced with their corresponding symbols in comments."
					: "Pada komentar, semua kata terkait simbol matematika diganti dengan simbol matematika.";
			break;
		case 24:
			out = (humanLanguage.equalsIgnoreCase("en"))
					? "All small numbers are replaced with their corresponding words in comments."
					: "Pada komentar, semua angka kecil digantikan dengan kata.";
			break;
		case 25:
			out = (humanLanguage.equalsIgnoreCase("en"))
					? "All words representing small numbers are replaced with their corresponding numbers in comments."
					: "Pada komentar, semua kata yang merepresentasikan angka kecil digantikan dengan angka.";
			break;
		case 26:
			out = (humanLanguage.equalsIgnoreCase("en")) ? "All comment contents are anonymised."
					: "Semua konten komentar dianonimkan.";
			break;
		case 27:
			out = (humanLanguage.equalsIgnoreCase("en")) ? "All blank newlines are removed."
					: "Semua baris kosong dibuang.";
			break;
		case 28:
			out = (humanLanguage.equalsIgnoreCase("en")) ? "All tabs and spaces before each statement are removed."
					: "Semua tab dan spasi sebelum setiap statemen dibuang.";
			break;
		case 29:
			out = (humanLanguage.equalsIgnoreCase("en")) ? "Each space is replaced with two spaces."
					: "Setiap spasi digantikan dengan dua spasi.";
			break;
		case 30:
			out = (humanLanguage.equalsIgnoreCase("en")) ? "Each tab is replaced with two tabs."
					: "Setiap tab digantikan dengan dua tab.";
			break;
		case 31:
			out = (humanLanguage.equalsIgnoreCase("en")) ? "Each newline is replaced with two newlines."
					: "Setiap karakter newline digantikan dengan dua karakter newline.";
			break;
		case 32:
			out = (humanLanguage.equalsIgnoreCase("en")) ? "Each tab is replaced with six spaces."
					: "Setiap tab digantikan dengan enam spasi.";
			break;
		case 33:
			out = (humanLanguage.equalsIgnoreCase("en")) ? "NOT APPLICABLE." : "Tidak diimplementasikan.";
			break;
		case 34:
			out = (humanLanguage.equalsIgnoreCase("en")) ? "NOT APPLICABLE." : "Tidak diimplementasikan.";
			break;
		case 35:
			out = (humanLanguage.equalsIgnoreCase("en")) ? "All common words from the identifier names are removed."
					: "Semua kata-kata umum dari nama identifier dibuang. ";
			break;
		case 36:
			out = (humanLanguage.equalsIgnoreCase("en")) ? "All underscores from the identifier names are removed."
					: "Semua undeskor dari nama identifier dibuang.";
			break;
		case 37:
			out = (humanLanguage.equalsIgnoreCase("en")) ? "All numbers from the identifier names are removed."
					: "Semua angka dari nama identifier dibuang.";
			break;
		case 38:
			out = (humanLanguage.equalsIgnoreCase("en")) ? "All identifier names are capitalised."
					: "Semua nama identifier dibuat menjadi kapital.";
			break;
		case 39:
			out = (humanLanguage.equalsIgnoreCase("en")) ? "All identifier names are lowercased."
					: "Semua nama identifier dibuat menjadi huruf kecil.";
			break;
		case 40:
			out = (humanLanguage.equalsIgnoreCase("en")) ? "All identifier names have no underscore characters."
					: "Semua nama identifier tidak memiliki karakter underskor. ";
			break;
		case 41:
			out = (humanLanguage.equalsIgnoreCase("en"))
					? "Some underscore characters are embedded in the identifier names."
					: "Beberapa karakter underskor ditambahkan pada nama identifier. ";
			break;
		case 42:
			out = (humanLanguage.equalsIgnoreCase("en"))
					? "All identifiers are renamed with their corresponding first characters "
							+ "(and with additional numbers if it conflicts with other names)."
					: "Semua identifier dinamai ulang hanya dengan karakter pertamanya "
							+ "(dan dengan angka untuk menghindari konflik nama identifier).";
			break;
		case 43:
			out = (humanLanguage.equalsIgnoreCase("en"))
					? "All identifiers are renamed with their corresponding acronyms."
					: "Semua identifier dinamai ulang dengan akronim masing-masing.";
			break;
		case 44:
			out = (humanLanguage.equalsIgnoreCase("en")) ? "All identifier names are anonymised."
					: "Semua nama identifier dianonimkan. ";
			break;
		case 45:
			out = (humanLanguage.equalsIgnoreCase("en"))
					? "All non-floating numeric data types are changed to the largest data type (e.g., 'int' to 'long')."
					: "Semua tipe data bilangan bulat diubah ke tipe data dengan ukuran terbesar ('int' ke 'long').";
			break;
		case 46:
			out = (humanLanguage.equalsIgnoreCase("en"))
					? "All floating numeric data types are changed to the largest data type (i.e., 'float' to 'double')."
					: "Semua tipe data bilangan riil diubah ke tipe data dengan ukuran terbesar ('float' ke 'double').";
			break;
		case 47:
			out = (humanLanguage.equalsIgnoreCase("en"))
					? "A blank space (' ') is added at the end of each string literal."
					: "Sebuah spasi (' ') ditambahkan pada setiap akhir string literal.";
			break;
		case 48:
			out = (humanLanguage.equalsIgnoreCase("en"))
					? "A newline ('\\n') is added at the end of each string literal."
					: "Sebuah newline ('\\n') ditambahkan pada setiap akhir string literal.";
			break;
		case 49:
			out = (humanLanguage.equalsIgnoreCase("en"))
					? "The first character of each word in string literals is capitalised."
					: "Karakter pertama dari setiap kata di string literal dibuat menjadi kapital. ";
			break;
		case 50:
			out = (humanLanguage.equalsIgnoreCase("en")) ? "All characters in string literals are capitalised."
					: "Semua karakter di string literal dibuat menjadi kapital.";
			break;
		case 51:
			out = (humanLanguage.equalsIgnoreCase("en")) ? "All characters in string literals are lowercased."
					: "Semua karakter di string literal dibuat menjadi huruf kecil.";
			break;
		case 52:
			out = (humanLanguage.equalsIgnoreCase("en"))
					? "All conjuction symbols are replaced with their corresponding words in string literals."
					: "Pada string literal, semua simbol kata sambung diganti dengan kata sambungnya.";
			break;
		case 53:
			out = (humanLanguage.equalsIgnoreCase("en"))
					? "All conjuction words are replaced with their corresponding symbols in string literals."
					: "Pada string literal, semua kata sambung diganti dengan simbolnya.";
			break;
		case 54:
			out = (humanLanguage.equalsIgnoreCase("en"))
					? "All math symbols are replaced with their corresponding words in string literals."
					: "Pada string literal, semua simbol matematika diganti dengan kata.";
			break;
		case 55:
			out = (humanLanguage.equalsIgnoreCase("en"))
					? "All math words are replaced with their corresponding symbols in string literals."
					: "Pada string literal, semua kata terkait simbol matematika digantikan dengna simbolnya.";
			break;
		case 56:
			out = (humanLanguage.equalsIgnoreCase("en"))
					? "All small numbers are replaced with their corresponding words in string literals."
					: "Pada string literal, semua angka kecil digantikan dengan kata.";
			break;
		case 57:
			out = (humanLanguage.equalsIgnoreCase("en"))
					? "All words representing small numbers are replaced with their corresponding numbers in string literals."
					: "Pada string literal, semua kata yang merepresentasikan angka kecil digantikan dengan angka.";
			break;
		case 58:
			out = (humanLanguage.equalsIgnoreCase("en")) ? "All string literal contents are anonymised."
					: "Semua string literal dianonimkan.";
			break;
		case 59:
			out = (humanLanguage.equalsIgnoreCase("en")) ? "More precision is added for each floating constant."
					: "Presisi konstanta riil ditambahkan. ";
			break;
		}
		return out;
	}

	public static ArrayList<Integer> getRandomisedDisguises(boolean isCommentTargeted, boolean isWhitespaceTargeted,
			boolean isIdentifierNameTargeted, boolean isConstantTargeted, boolean isDataTypeTargeted,
			EObfuscatorSettingTuple set, ArrayList<ETokenTuple> tokens) {
		/*
		 * this method generate one disguise for each aspect targeted
		 */

		Random r = new Random();

		// diagnose
		EApplicableObfuscationDiagnosticResult diagresult = EApplicableObfuscationDiagnosticResult
				.generateDiagnosticResult(tokens, set);

		// get the applicability per disguise
		boolean[] diagresultPerDisguise = EApplicableObfuscationDiagnosticResult.getApplicableDisguises(diagresult,
				set);

		// get only the applicable ones and get some of the applicable ones
		// randomly

		// to store all selected disguises
		ArrayList<Integer> combinedResults = new ArrayList<>();		

		// for comment
		if (isCommentTargeted) 
			addDisguises(0, 27, diagresultPerDisguise, 1, r, combinedResults);

		// for whitespace
		if (isWhitespaceTargeted)
			addDisguises(27, 35, diagresultPerDisguise, 1, r, combinedResults);

		// for identifier
		if (isIdentifierNameTargeted)
			addDisguises(35, 45, diagresultPerDisguise, 1, r, combinedResults);

		// for constant
		if (isConstantTargeted)
			addDisguises(47, 60, diagresultPerDisguise, 1, r, combinedResults);

		// for data type change
		if (isDataTypeTargeted)
			addDisguises(45, 47, diagresultPerDisguise, 1, r, combinedResults);

		return combinedResults;
	}

	private static void addDisguises(int startDisguiseIndex, int finishDisguiseIndex, boolean[] diagresultPerDisguise,
			int maxDisguises, Random r, ArrayList<Integer> combinedResults) {
		/*
		 * This method selects some disguises randomly and then put them in combined
		 * results. startDisguiseIndex and finishDisguiseIndex represent range of
		 * applicable disguises in diagresultPerDisguise. maxDisguises refers to max
		 * number of selected disguises.
		 */

		// get only the applicable ones
		ArrayList<Integer> applicableDisguises = new ArrayList<>();
		for (int i = startDisguiseIndex; i < finishDisguiseIndex; i++) {
			if (diagresultPerDisguise[i] == true)
				applicableDisguises.add(i);
		}

		// set the max of applicable disguise
		if (maxDisguises > applicableDisguises.size())
			maxDisguises = applicableDisguises.size();
		// select some of the disguises
		while (maxDisguises > 0) {
			// get a random number based on applicableDisguises' size
			int num = r.nextInt(applicableDisguises.size());
			// add the selected pos and remove that pos from applicableDisguises
			combinedResults.add(applicableDisguises.remove(num));
			// reduce the need of disguises
			maxDisguises--;
		}
	}

	public static ArrayList<ETokenTuple> applyDisguises(ArrayList<ETokenTuple> tokens,
			ArrayList<Integer> selectedDisguises, EObfuscatorSettingTuple set) {

		// dealing with other disguises
		for (int idx = 0; idx < selectedDisguises.size(); idx++) {
			// get the i denoting disguises
			Integer i = selectedDisguises.get(idx);

			// System.out.println(getDisguiseName(i));

			// record the condition before disguise
			StringBuilder predisguisedString = new StringBuilder();
			for (ETokenTuple t : tokens) {
				predisguisedString.append(t.getText());
			}

			// comment 0-26

			// 0 removing some single-line comments
			if (i == 0) {
				if (set instanceof EJavaDefaultObfuscatorSettingTuple)
					ECodeObfuscatorComment.c01JavaRemovingSomeSingleLineComments(tokens,
							set.getSingleLineCommentRemovalProb());
				else
					ECodeObfuscatorComment.c01PythonRemovingSomeSingleLineComments(tokens,
							set.getSingleLineCommentRemovalProb());
			}

			// 1 removing all single-line comments
			if (i == 1) {
				if (set instanceof EJavaDefaultObfuscatorSettingTuple)
					ECodeObfuscatorComment.c02JavaRemovingAllSingleLineComments(tokens);
				else
					ECodeObfuscatorComment.c02PythonRemovingAllSingleLineComments(tokens);
			}

			// 2 removing some multi-line comments.
			if (i == 2 && set instanceof EJavaDefaultObfuscatorSettingTuple)
				ECodeObfuscatorComment.c03JavaRemovingSomeMultiLineComments(tokens,
						set.getMultiLineCommentRemovalProb());

			// 3 removing all multi-line comments.
			if (i == 3 && set instanceof EJavaDefaultObfuscatorSettingTuple)
				ECodeObfuscatorComment.c04JavaRemovingAllMultiLineComments(tokens);

			// 4 removing some comments.
			if (i == 4 && set instanceof EJavaDefaultObfuscatorSettingTuple)
				ECodeObfuscatorComment.c05JavaRemovingSomeComments(tokens, set.getAllCommentRemovalProb());

			// 5 removing all comments.
			if (i == 5 && set instanceof EJavaDefaultObfuscatorSettingTuple)
				ECodeObfuscatorComment.c06JavaRemovingAllComments(tokens);

			// 6 adding a single-line comment for each line with syntax, which
			// content is randomly generated.
			if (i == 6) {
				if (set instanceof EJavaDefaultObfuscatorSettingTuple)
					ECodeObfuscatorComment.c07JavaAddingSingleLineComment(tokens, set.getHumanLanguage());
				else
					ECodeObfuscatorComment.c07PythonAddingSingleLineComment(tokens, set.getHumanLanguage());
			}

			// 7 adding a multi-line comment for each line with syntax, which
			// content is randomly generated.
			if (i == 7 && set instanceof EJavaDefaultObfuscatorSettingTuple)
				ECodeObfuscatorComment.c08JavaAddingMultiLineComment(tokens, set.getHumanLanguage());

			// 8 changing each single-line comment to the multi-line one.
			if (i == 8 && set instanceof EJavaDefaultObfuscatorSettingTuple)
				ECodeObfuscatorComment.c09JavaReplacingSingleToMultiComments(tokens);

			// 9 changing each single-line comment to the multi-line one with
			// '.', '?', '!', and ';' as the line separators.
			if (i == 9 && set instanceof EJavaDefaultObfuscatorSettingTuple)
				ECodeObfuscatorComment.c10JavaReplacingSingleToMultiCommentsWithPunctuationsAsLineDelimiters(tokens);

			// 10 changing each single-line comment to the multi-line one with n
			// characters per line.
			if (i == 10 && set instanceof EJavaDefaultObfuscatorSettingTuple)
				ECodeObfuscatorComment.c11JavaReplacingSingleToMultiCommentsWithNCharsPerLine(tokens,
						set.getnForMaxCharsInSingleLineComment());

			// 11 splitting each single-line comment to several single-line
			// comments with '.', '?', '!', and ';' as the line separators.
			if (i == 11) {
				if (set instanceof EJavaDefaultObfuscatorSettingTuple) {
					ECodeObfuscatorComment.c12JavaReplacingSingleToNSingleCommentsWithPunctuationsAsDelimiters(tokens);
				} else {
					ECodeObfuscatorComment
							.c12PythonReplacingSingleToNSingleCommentsWithPunctuationsAsDelimiters(tokens);
				}
			}

			// 12 splitting each single-line comment to several single-line
			// comments with n characters per line.
			if (i == 12) {
				if (set instanceof EJavaDefaultObfuscatorSettingTuple) {
					ECodeObfuscatorComment.c13JavaReplacingSingleToNSingleLineCommentsWithNCharsPerLine(tokens,
							set.getnForMaxCharsInSingleLineComment());
				} else {
					ECodeObfuscatorComment.c13PythonReplacingSingleToNSingleCommentsWithNCharsPerLine(tokens,
							set.getnForMaxCharsInSingleLineComment());
				}
			}

			// 13 changing each multi-line comment to the single-line one with
			// all newlines removed.
			if (i == 13 && set instanceof EJavaDefaultObfuscatorSettingTuple)
				ECodeObfuscatorComment.c14JavaReplacingMultiToSingleCommentsWithNewlinesRemoved(tokens);

			// 14 splitting each multi-line comment to several single-line
			// comments with newline as the line separators.
			if (i == 14 && set instanceof EJavaDefaultObfuscatorSettingTuple)
				ECodeObfuscatorComment.c15JavaSplitMultiToNSingleCommentsWithNewlineAsSeparator(tokens);

			// 15 splitting each multi-line comment to several single-line
			// comments with '.', '?', '!', and ';' as the line separators.
			if (i == 15 && set instanceof EJavaDefaultObfuscatorSettingTuple)
				ECodeObfuscatorComment.c16JavaSplitMultiToNSingleCommentsWithPunctuationsAsDelimiters(tokens);

			// 16 splitting each multi-line comment to several single-line
			// comments with n characters per line.
			if (i == 16 && set instanceof EJavaDefaultObfuscatorSettingTuple)
				ECodeObfuscatorComment.c17JavaReplacingMultiToSeveralSingleCommentsWithNCharsPerLine(tokens,
						set.getnForMaxCharsInMultiLineComment());

			// 17 capitalising the first character of each comment word.
			if (i == 17) {
				if (set instanceof EJavaDefaultObfuscatorSettingTuple)
					ECodeObfuscatorComment.c18JavaCapitalisingFirstCharEachWord(tokens);
				else
					ECodeObfuscatorComment.c18PythonCapitalisingFirstCharEachWord(tokens);
			}

			// 18 capitalising all comment characters.
			if (i == 18)
				ECodeObfuscatorComment.c19CapitalisingAllChars(tokens);

			// 19 decapitalising all comment characters.
			if (i == 19)
				ECodeObfuscatorComment.c20DecapitalisingAllChars(tokens);

			// 20 replacing conjuction symbols with their corresponding words in
			// comments.
			if (i == 20) {
				if (set instanceof EJavaDefaultObfuscatorSettingTuple)
					ECodeObfuscatorComment.c21JavaReplacingConjuctionSymbolsWithWords(tokens, set.getHumanLanguage());
				else
					ECodeObfuscatorComment.c21PythonReplacingConjuctionSymbolsWithWords(tokens, set.getHumanLanguage());
			}

			// 21 replacing conjuction words with their corresponding symbols in
			// comments.
			if (i == 21) {
				if (set instanceof EJavaDefaultObfuscatorSettingTuple)
					ECodeObfuscatorComment.c22JavaReplacingConjuctionWordsWithSymbols(tokens, set.getHumanLanguage());
				else
					ECodeObfuscatorComment.c22PythonReplacingConjuctionWordsWithSymbols(tokens, set.getHumanLanguage());
			}

			// 22 replacing math operators with their corresponding words in
			// comments. (+,-,*,/,=)
			if (i == 22) {
				if (set instanceof EJavaDefaultObfuscatorSettingTuple)
					ECodeObfuscatorComment.c23JavaReplacingMathSymbolsWithWords(tokens, set.getHumanLanguage());
				else
					ECodeObfuscatorComment.c23PythonReplacingMathSymbolsWithWords(tokens, set.getHumanLanguage());
			}

			// 23 replacing math words with their corresponding operators in
			// comments. (+,-,*,/,=)
			if (i == 23) {
				if (set instanceof EJavaDefaultObfuscatorSettingTuple)
					ECodeObfuscatorComment.c24JavaReplacingMathWordsWithSymbols(tokens, set.getHumanLanguage());
				else
					ECodeObfuscatorComment.c24PythonReplacingMathWordsWithSymbols(tokens, set.getHumanLanguage());
			}

			// 24 replacing small numbers (<12) with their corresponding words
			// in comments.
			if (i == 24) {
				if (set instanceof EJavaDefaultObfuscatorSettingTuple)
					ECodeObfuscatorComment.c25JavaReplacingSmallNumbersWithWords(tokens, set.getHumanLanguage());
				else
					ECodeObfuscatorComment.c25PythonReplacingSmallNumbersWithWords(tokens, set.getHumanLanguage());
			}

			// 25 replacing small number words (<12) with their corresponding
			// numbers in comments.
			if (i == 25) {
				if (set instanceof EJavaDefaultObfuscatorSettingTuple)
					ECodeObfuscatorComment.c26JavaReplacingSmallNumberWordsWithNumbers(tokens, set.getHumanLanguage());
				else
					ECodeObfuscatorComment.c26PythonReplacingSmallNumberWordsWithNumbers(tokens,
							set.getHumanLanguage());
			}

			// 26 anonymising all comment contents as 'anonymised comments'
			if (i == 26) {
				if (set instanceof EJavaDefaultObfuscatorSettingTuple)
					ECodeObfuscatorComment.c27JavaAnonymisingCommentContents(tokens, set.getHumanLanguage());
				else
					ECodeObfuscatorComment.c27PythonAnonymisingCommentContents(tokens, set.getHumanLanguage());
			}

			// whitespace 27-34, except 34 as it should be specifically handled.

			// 27 removing all blank newlines.
			if (i == 27)
				ECodeObfuscatorWhitespace.w01RemovingBlankLines(tokens);

			// 28 removing all tabs and spaces before each statement. Not
			// applicable for Python.
			if (i == 28 && set instanceof EJavaDefaultObfuscatorSettingTuple)
				ECodeObfuscatorWhitespace.w02JavaRemovingTabsAndSpacesBeforeEachStatement(tokens);

			// 29 replacing each space with n spaces.
			if (i == 29)
				ECodeObfuscatorWhitespace.w03ReplacingEachSpaceWithNSpaces(tokens, set.getNumReplacingSpaces());

			// 30 replacing each tab with n tabs.
			if (i == 30)
				ECodeObfuscatorWhitespace.w04ReplacingEachTabWithNTabs(tokens, set.getNumReplacingTabs());

			// 31 replacing each newline with n newlines.
			if (i == 31)
				ECodeObfuscatorWhitespace.w05ReplacingEachNewLineWithNLines(tokens, set.getNumReplacingNewlines());

			// 32 replacing all tabs with n spaces.
			if (i == 32)
				ECodeObfuscatorWhitespace.w06ReplacingEachTabWithNSpaces(tokens, set.getNumReplacingSpacesForTabs());

			// 33 replacing all n spaces with tabs.
			if (i == 33)
				ECodeObfuscatorWhitespace.w07ReplacingNSpacesWithTab(tokens, set.getnForSpacesReplacedByTab());

			// identifier 35-44

			// 35 removing all stop words from the identifiers' sub-words if
			// these sub-words are separated by underscore or next character
			// capitalisation.
			if (i == 35)
				ECodeObfuscatorIdentifier.i01RemovingStopWords(tokens, set.getHumanLanguage());

			// 36 removing all underscores from the identifiers.
			if (i == 36)
				ECodeObfuscatorIdentifier.i02Removing_(tokens);

			// 37 removing all numbers from the identifiers.
			if (i == 37)
				ECodeObfuscatorIdentifier.i03RemovingNumbers(tokens);

			// 38 capitalising all identifier's characters.
			if (i == 38)
				ECodeObfuscatorIdentifier.i04CapitalisingAllCharacters(tokens);

			// 39 decapitalising all identifier's characters.
			if (i == 39)
				ECodeObfuscatorIdentifier.i05DecapitalisingAllCharacters(tokens);

			// 40 replacing all identifiers' sub-word transitions from
			// underscore to next character capitalisation (e.g., 'this_is_var'
			// to 'thisIsVar').
			if (i == 40)
				ECodeObfuscatorIdentifier.i06replacingSubWordTransitionsfromthis_is_vartothisIsVar(tokens);

			// 41 replacing all identifiers' sub-word transitions from next
			// character capitalisation to underscore (e.g., 'thisIsVar' to
			// 'this_is_var').
			if (i == 41)
				ECodeObfuscatorIdentifier.i07replacingSubWordTransitionsfromthisIsVartothis_is_var(tokens);

			// 42 renaming all identifiers by keeping only the first character
			// each.
			if (i == 42)
				ECodeObfuscatorIdentifier.i08KeepingOnlyTheFirstCharacter(tokens);

			// 43 renaming all identifiers by keeping their acronyms (generated
			// by removing all vocals except the first char).
			if (i == 43)
				ECodeObfuscatorIdentifier.i09KeepingOnlyTheConsonants(tokens);

			// 44 anonymising all identifiers by renaming them as
			// 'anonymisedIdent'.
			if (i == 44)
				ECodeObfuscatorIdentifier.i10AnonymisingAllIdentifiers(tokens, set.getHumanLanguage());

			// constant and data type change 45-59

			// 45 changing all non-floating data types to the largest data type.
			if (i == 45 && set instanceof EJavaDefaultObfuscatorSettingTuple)
				ECodeObfuscatorConstantAndDataTypeChange.cd01JavaChangingNonFloatingTypesToTheLargest(tokens);

			// 46 changing all floating data types to the largest data type.
			if (i == 46 && set instanceof EJavaDefaultObfuscatorSettingTuple)
				ECodeObfuscatorConstantAndDataTypeChange.cd02JavaChangingFloatingTypesToTheLargest(tokens);

			// 47 adding a blank space at the end of each string literal.
			if (i == 47)
				ECodeObfuscatorConstantAndDataTypeChange.cd03AddingSpaceAtEndStringLiteral(tokens,
						set.isSingleQuoteAlsoStringSeparator());

			// 48 adding a newline at the end of each string literal.
			if (i == 48)
				ECodeObfuscatorConstantAndDataTypeChange.cd04AddingNewlineAtEndStringLiteral(tokens,
						set.isSingleQuoteAlsoStringSeparator());

			// 49 capitalising the first character of each string literal word.
			if (i == 49)
				ECodeObfuscatorConstantAndDataTypeChange.cd05CapitalisingFirstCharEachWord(tokens,
						set.isSingleQuoteAlsoStringSeparator());

			// 50 capitalising all string characters.
			if (i == 50)
				ECodeObfuscatorConstantAndDataTypeChange.cd06CapitalisingAllChars(tokens,
						set.isSingleQuoteAlsoStringSeparator());

			// 51 decapitalising all string characters.
			if (i == 51)
				ECodeObfuscatorConstantAndDataTypeChange.cd07DecapitalisingAllChars(tokens,
						set.isSingleQuoteAlsoStringSeparator());

			// 52 replacing conjuction symbols with their corresponding words in
			// strings.
			if (i == 52)
				ECodeObfuscatorConstantAndDataTypeChange.cd08ReplacingConjuctionSymbolsWithWords(tokens,
						set.getHumanLanguage(), set.isSingleQuoteAlsoStringSeparator());

			// 53 replacing conjuction words with their corresponding symbols in
			// strings.
			if (i == 53)
				ECodeObfuscatorConstantAndDataTypeChange.cd09ReplacingConjuctionWordsWithSymbols(tokens,
						set.getHumanLanguage(), set.isSingleQuoteAlsoStringSeparator());

			// 54 replacing math operators with their corresponding words in
			// strings. (+,-,*,/,=)
			if (i == 54)
				ECodeObfuscatorConstantAndDataTypeChange.cd10ReplacingMathSymbolsWithWords(tokens,
						set.getHumanLanguage(), set.isSingleQuoteAlsoStringSeparator());

			// 55 replacing math words with their corresponding operators in
			// strings. (+,-,*,/,=)
			if (i == 55)
				ECodeObfuscatorConstantAndDataTypeChange.cd11ReplacingMathWordsWithSymbols(tokens,
						set.getHumanLanguage(), set.isSingleQuoteAlsoStringSeparator());

			// 56 replacing small numbers (<12) with their corresponding words
			// in strings.
			if (i == 56)
				ECodeObfuscatorConstantAndDataTypeChange.cd12ReplacingSmallNumbersWithWords(tokens,
						set.getHumanLanguage(), set.isSingleQuoteAlsoStringSeparator());

			// 57 replacing small number words (<12) with their corresponding
			// numbers in strings.
			if (i == 57)
				ECodeObfuscatorConstantAndDataTypeChange.cd13ReplacingSmallNumberWordsWithNumbers(tokens,
						set.getHumanLanguage(), set.isSingleQuoteAlsoStringSeparator());

			// 58 anonymising all string contents as 'anonymised string content'
			if (i == 58)
				ECodeObfuscatorConstantAndDataTypeChange.cd14AnonymisingStringContents(tokens, set.getHumanLanguage(),
						set.isSingleQuoteAlsoStringSeparator());

			// 59 adding more precision for floating constants.
			if (i == 59)
				ECodeObfuscatorConstantAndDataTypeChange.cd15AddingExtraPrecisionForFloatingConstants(tokens);

			// record the condition after disguise
			StringBuilder postdisguisedString = new StringBuilder();
			for (ETokenTuple t : tokens) {
				postdisguisedString.append(t.getText());
			}

			// if no changes, remove the selected disguise, assuming it is futile
			if (predisguisedString.toString().equals(postdisguisedString.toString())) {
				selectedDisguises.remove(idx);
				idx--;
			}
		}

		return tokens;

	}

	public static void printDisguises(ArrayList<Integer> selectedDisguises) {
		for (Integer i : selectedDisguises) {
			String out = getDisguiseName(i);
			System.out.println(out);
		}
	}

	/*
	 * return the disguise name based on given index from an array of applicable
	 * disguises.
	 */
	public static String getDisguiseName(int i) {
		String out = "";
		switch (i) {
		case 0:
			out = "C01: removing some single-line comments with X% probability";
			break;
		case 1:
			out = "C02: removing all single-line comments";
			break;
		case 2:
			out = "C03: removing some multi-line comments with X% probability";
			break;
		case 3:
			out = "C04: removing all multi-line comments";
			break;
		case 4:
			out = "C05: removing some comments with X% probability";
			break;
		case 5:
			out = "C06: removing all comments";
			break;
		case 6:
			out = "C07: adding a random single-line comment for each blank line before syntax";
			break;
		case 7:
			out = "C08: adding a random multi-line comment for each blank line before syntax";
			break;
		case 8:
			out = "C09: changing each single-line comment to the multi-line one";
			break;
		case 9:
			out = "C10: changing each single-line comment to the multi-line one with punctuations as the line separators";
			break;
		case 10:
			out = "C11: changing each single-line comment to the multi-line one with N characters per line";
			break;
		case 11:
			out = "C12: splitting each single-line comment to several single-line comments with punctuations as the line separators";
			break;
		case 12:
			out = "C13: splitting each single-line comment to several single-line comments with N characters per line";
			break;
		case 13:
			out = "C14: changing each multi-line comment to the single-line one with all newlines removed";
			break;
		case 14:
			out = "C15: splitting each multi-line comment to several single-line comments with newlines as the line separators";
			break;
		case 15:
			out = "C16: splitting each multi-line comment to several single-line comments with punctuations as the line separators";
			break;
		case 16:
			out = "C17: splitting each multi-line comment to several single-line comments with N characters per line";
			break;
		case 17:
			out = "C18: capitalising the first character of each comment word";
			break;
		case 18:
			out = "C19: capitalising all comment characters";
			break;
		case 19:
			out = "C20: decapitalising all comment characters";
			break;
		case 20:
			out = "C21: replacing conjuction symbols with their corresponding words in comments";
			break;
		case 21:
			out = "C22: replacing conjuction words with their corresponding symbols in comments";
			break;
		case 22:
			out = "C23: replacing math symbols with their corresponding words in comments";
			break;
		case 23:
			out = "C24: replacing math words with their corresponding symbols in comments";
			break;
		case 24:
			out = "C25: replacing small numbers with their corresponding words in comments";
			break;
		case 25:
			out = "C26: replacing words representing small numbers with their corresponding numbers in comments";
			break;
		case 26:
			out = "C27: anonymising all comment contents";
			break;
		case 27:
			out = "W01: removing all blank newlines";
			break;
		case 28:
			out = "W02: removing all tabs and spaces before each statement";
			break;
		case 29:
			out = "W03: replacing each space with N spaces";
			break;
		case 30:
			out = "W04: replacing each tab with N tabs";
			break;
		case 31:
			out = "W05: replacing each newline with N newlines";
			break;
		case 32:
			out = "W06: replacing each tab with N spaces";
			break;
		case 33:
			out = "W07: replacing each N spaces with a tab";
			break;
		case 34:
			out = "W08: reformat the whitespaces based on the programming language's guideline";
			break;
		case 35:
			out = "I01: removing all stop words from the identifiers' sub-words";
			break;
		case 36:
			out = "I02: removing all underscores from the identifiers";
			break;
		case 37:
			out = "I03: removing all numbers from the identifiers";
			break;
		case 38:
			out = "I04: capitalising all identifiers' characters";
			break;
		case 39:
			out = "I05: decapitalising all identifiers' characters";
			break;
		case 40:
			out = "I06: replacing all identifiers' sub-word transitions from 'this_is_var' to 'thisIsVar'";
			break;
		case 41:
			out = "I07: replacing all identifiers' sub-word transitions from 'thisIsVar' to 'this_is_var'";
			break;
		case 42:
			out = "I08: renaming all identifiers with their corresponding first characters";
			break;
		case 43:
			out = "I09: renaming all identifiers with their corresponding acronyms";
			break;
		case 44:
			out = "I10: anonymising all identifiers";
			break;
		case 45:
			out = "CD01: changing all non-floating numeric data types to the largest data type";
			break;
		case 46:
			out = "CD02: changing all floating numeric data types to the largest data type";
			break;
		case 47:
			out = "CD03: adding a blank space at the end of each string literal";
			break;
		case 48:
			out = "CD04: adding a newline at the end of each string literal";
			break;
		case 49:
			out = "CD05: capitalising the first character of each word in string literals";
			break;
		case 50:
			out = "CD06: capitalising all characters in string literals";
			break;
		case 51:
			out = "CD07: decapitalising all characters in string literals";
			break;
		case 52:
			out = "CD08: replacing conjuction symbols with their corresponding words in string literals";
			break;
		case 53:
			out = "CD09: replacing conjuction words with their corresponding symbols in string literals";
			break;
		case 54:
			out = "CD10: replacing math symbols with their corresponding words in string literals";
			break;
		case 55:
			out = "CD11: replacing math words with their corresponding symbols in string literals";
			break;
		case 56:
			out = "CD12: replacing small numbers with their corresponding words in string literals";
			break;
		case 57:
			out = "CD13: replacing words representing small numbers with their corresponding numbers in string literals";
			break;
		case 58:
			out = "CD14: anonymising all string literal contents";
			break;
		case 59:
			out = "CD15: adding more precision for each floating constant";
			break;
		}
		return out;
	}

}
