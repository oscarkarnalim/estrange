package p5.educationalstrange.ehtmlgenerator;

import java.io.BufferedReader;
import java.io.BufferedWriter;
import java.io.File;
import java.io.FileReader;
import java.io.FileWriter;
import java.util.ArrayList;
import java.util.Collections;

import p5.educationalstrange.SimilarityDetector;
import p5.educationalstrange.ematchfragmentgenerator.EMatchFragment;
import p5.educationalstrange.eobfuscator.EDisguiseRandomiser;
import p5.educationalstrange.tuple.ETokenTuple;

public class EHtmlGenerator {
	/*
	 * for generating did you know section. The facts cover general knowledge about
	 * code plagiarism and collusion (adapted from Simon's survey questions),
	 * coincidental similarity, knowledge about surface similarities.
	 */
	private static String[] factList = new String[] {
			"Discussing with another student how to approach a task and what resources to use, then developing the solution independently tends to be considered as collusion.",
			"Discussing the detail of your code with another student while working on it tends to be considered as collusion.",
			"Showing troublesome code to another student and asking them for advice on how to fix it tends to be considered as collusion.",
			"Asking another student to take troublesome code and get it working is considered as collusion.",
			"Copying an early draft of another student's work and developing it into your own is considered as plagiarism or collusion.",
			"Copying another student's code and changing it so that it looks quite different is considered as plagiarism or collusion.",
			"After completing an assessment, adding features that you noticed when looking at another student's work is considered as plagiarism or collusion.",
			"Incorporating the work of another student without their permission is considered as plagiarism.",
			"Incorporating purchased code written by other students into your own work is considered as collusion.",
			"Submitting purchased code written by another student as your own work is considered as collusion.",
			"Your own code can be coincidentally similar to other colleagues' code if the task is too simple and it is okay.",
			"Your own code can be coincidentally similar to other colleagues' code if some template code is given by the lecturers, and it is okay.",
			"Your own code can be coincidentally similar to other colleagues' code if the task only has few possible solution and it is okay.",
			"Copying source code from another student without modification will lead to strong suspicions of plagiarism or collusion.",
			"Source code comments can be changed, added, or removed without understanding how the program works. Hence, two similar programs with different comments will lead to suspicions of collusion.",
			"Variable names can be changed without understanding how they are used in the program, so unique names do not guarantee that the author is not involved in plagiarism or collusion.",
			"The order of function/method declarations does not commonly affect how a program works, so two programs with different orders of declaration are not always both original.",
			"The highest level of content similarity occurs when two programs share the same syntactic program flow, code layout, and comments.",
			"Source code comments describe in human language what the program is doing, but in programs that do the same thing, the comments are still not expected to be very similar as they depend heavily on the author's human language style.",
			"The names of functions/methods can be changed without deeply understanding how the program works, so these names should be ignored when suggesting plagiarism and collusion.",
			"Changing the order of function/method calls requires more programming knowledge than changing the order of function declarations, so a different order of function/method calls is less likely to suggest plagiarism or collusion.",

//			"Students are required to use one of three algorithms in a programming assessment. Student A has known programming since high school and loves to use advanced programming techniques. Student B copies Student A's work and submits it as their own, "
//					+ "thinking that most students' programs will be similar as there are only three algorithms to choose from. However, Student B will still be suspected of collusion as Student A's code is unique and more advanced than others.",
//			"After completing a simple Hello World assignment, a student tries to make the comments really distinctive because they are all that will make the program different from those of other students. The student’s thinking is invalid as "
//					+ "comments are unrelated to the program flow and common code resulted from completing an extremely simple task cannot be used for suggesting plagiarism or collusion.",
//			"Two international students who speak different languages are taking an introductory programming course. In an assignment, their programs are identical except that the variable names are written in their respective mother language. "
//					+ "These two will still be suspected of plagiarism or collusion since their programs work similarly.",
//			"A programming assignment involves calculating a course grade from the marks for quizzes, tests, and assignments; each score will be calculated separately from its respective sub-scores. "
//					+ "Student A builds the program by calculating the quiz score, test score, and assignment score sequentially as instructed. "
//					+ "Student B’s program is similar except that the quiz score is calculated last. These students will be suspected of plagiarism or collusion as the differences are only about code order."

	};

	private static String[] factListInd = new String[] {
			"Berdiskusi dengan siswa lain tentang cara mengerjakan tugas dan sumber-sumber apa yang sebaiknya digunakan, kemudian mengembangkan solusinya secara mandiri dapat dianggap tindakan kerjasama ilegal (kolusi).",
			"Mendiskusikan kode anda secara detil dengan siswa lain pada saat mengerjakannya dapat dianggap tindakan kerjasama ilegal (kolusi).",
			"Memperlihatkan kode yang bermasalah kepada siswa lain dan meminta saran tentang cara memperbaikinya dapat dianggap tindakan kerjasama ilegal (kolusi).",
			"Meminta siswa lain untuk memperbaiki kode yang bermasalah dapat dianggap tindakan kerjasama ilegal (kolusi).",
			"Menyalin draf awal hasil karya siswa lain dan mengembangkannya menjadi milik anda merupakan tindakan plagiarisme atau kerjasama ilegal (kolusi).",
			"Menyalin kode hasil karya siswa lain dan mengubahnya sehingga terlihat agak berbeda merupakan tindakan plagiarisme atau kerjasama ilegal (kolusi).",
			"Setelah menyelesaikan suatu tugas, anda menambahkan fitur-fitur yang terinspirasi setelah anda melihat hasil karya siswa lain merupakan tindakan plagiarisme atau kerjasama ilegal (kolusi). ",
			"Memasukkan pekerjaan siswa lain tanpa meminta izin yang kepada bersangkutan merupakan tindakan plagiarisme.",
			"Membeli kode yang ditulis oleh siswa lain untuk dimasukkan ke dalam pekerjaan anda sendiri merupakan tindakan kerjasama ilegal (kolusi).",
			"Membayar siswa lain untuk menulis kode dan mengirimkan sebagai karya anda sendiri merupakan tindakan kerjasama ilegal (kolusi).",
			"Kode anda dapat secara tidak sengaja sama dengan rekan-rekan anda jika tugas yang diberikan sangat sederhana, dan hal ini tidak dianggap tindakan plagiarisme atau kerjasama ilegal (kolusi).",
			"Kode anda dapat secara tidak sengaja sama dengan rekan-rekan anda jika ada kode template yang diberikan oleh dosen, dan hal ini tidak dianggap tindakan plagiarisme atau kerjasama ilegal (kolusi).",
			"Kode anda dapat secara tidak sengaja sama dengan rekan-rekan anda jika tidak banyak alternatif solusi penyelesaian tugas, dan hal ini tidak dianggap tindakan plagiarisme atau kerjasama ilegal (kolusi).",

			"Menyalin kode sumber dari siswa lain tanpa modifikasi akan menimbulkan kecurigaan yang kuat terhadap plagiarisme atau kerjasama ilegal (kolusi).",
			"Komentar kode sumber dapat diubah, ditambah, atau dihapus tanpa memahami cara kerja program. Oleh karena itu, dua program yang serupa dengan komentar yang berbeda akan menimbulkan kecurigaan akan adanya kerjasama ilegal (kolusi).",
			"Nama-nama variabel dapat diubah tanpa memahami penggunaanya dalam program, jadi nama-nama yang unik tidak menjamin bahwa penulis tidak terlibat dalam plagiarisme atau kerjasama ilegal (kolusi).",
			"Urutan deklarasi fungsi / metode pada umumnya tidak mempengaruhi cara kerja suatu program, sehingga dua program dengan urutan deklarasi yang berbeda tidak selalu orisinal.",
			"Tingkat kesamaan konten tertinggi terjadi ketika dua program memiliki alur program sintaksis yang sama, tata letak kode yang sama, dan komentar yang sama.",
			"Komentar menjelaskan dalam bahasa manusia tentang apa yang sedang dilakukan oleh program. Namun dalam program-program yang serupa, komentarnya tetap diprediksi akan berbeda karena bergantung penuh pada gaya bahasa penulis.",
			"Nama-nama fungsi / metode dapat diubah tanpa mengerti alur program secara detil, jadi nama-nama ini tidak perlu diperhitungkan ketika mendeteksi plagiarisme dan kerjasama ilegal (kolusi).",
			"Mengubah urutan pemanggilan fungsi / metode membutuhkan lebih banyak pengetahuan pemrograman daripada mengubah urutan deklarasi fungsi, sehingga urutan pemanggilan fungsi / metode yang berbeda cenderung kurang mencurigakan untuk tindakan plagiarisme atau kerjasama ilegal (kolusi).",

//			"Students are required to use one of three algorithms in a programming assessment. Student A has known programming since high school and loves to use advanced programming techniques. Student B copies Student A's work and submits it as their own, "
//					+ "thinking that most students' programs will be similar as there are only three algorithms to choose from. However, Student B will still be suspected of collusion as Student A's code is unique and more advanced than others.",
//			"After completing a simple Hello World assignment, a student tries to make the comments really distinctive because they are all that will make the program different from those of other students. The student’s thinking is invalid as "
//					+ "comments are unrelated to the program flow and common code resulted from completing an extremely simple task cannot be used for suggesting plagiarism or collusion.",
//			"Two international students who speak different languages are taking an introductory programming course. In an assignment, their programs are identical except that the variable names are written in their respective mother language. "
//					+ "These two will still be suspected of plagiarism or collusion since their programs work similarly.",
//			"A programming assignment involves calculating a course grade from the marks for quizzes, tests, and assignments; each score will be calculated separately from its respective sub-scores. "
//					+ "Student A builds the program by calculating the quiz score, test score, and assignment score sequentially as instructed. "
//					+ "Student B’s program is similar except that the quiz score is calculated last. These students will be suspected of plagiarism or collusion as the differences are only about code order."

	};

	public static void generateHtml(ArrayList<ETokenTuple> targetMergedTokenString,
			ArrayList<EMatchFragment> matchFragments, String humanLanguage, String templateHTMLPath,
			String outputHTMLPath, boolean isSuspicious) throws Exception {

		String tableContent = generateTableContent(matchFragments, humanLanguage, isSuspicious);
		String explanation = generateExplanation(matchFragments, humanLanguage, isSuspicious);

		// System.out.println(explanation);

		String code1 = generateCode1(targetMergedTokenString, matchFragments, isSuspicious);
		String code2 = generateCode2(matchFragments, isSuspicious);

		File templateFile = new File(templateHTMLPath);
		File outputFile = new File(outputHTMLPath);
		BufferedReader fr = new BufferedReader(new FileReader(templateFile));
		BufferedWriter fw = new BufferedWriter(new FileWriter(outputFile));
		String line;
		while ((line = fr.readLine()) != null) {
			if (line.contains("@code1")) {
				line = line.replace("@code1", code1);
			}
			if (line.contains("@code2")) {
				line = line.replace("@code2", code2);
			}
			if (line.contains("@tablecontent")) {
				line = line.replace("@tablecontent", tableContent);
			}
			if (line.contains("@explanation")) {
				line = line.replace("@explanation", explanation);
			}
			if (line.contains("@didyouknow")) {
				line = line.replace("@didyouknow", getDidYouKnowText(humanLanguage));
			}

			fw.write(line);
			fw.write(System.lineSeparator());
		}
		fr.close();
		fw.close();
	}

	public static String getDidYouKnowText(String humanLanguage) {

		// get a random fact from factList
		if (humanLanguage.equalsIgnoreCase("en"))
			return factList[SimilarityDetector.r.nextInt(factList.length)];
		else
			return factListInd[SimilarityDetector.r.nextInt(factList.length)];
	}

	public static String generateCode2(ArrayList<EMatchFragment> matchFragments, boolean isSuspicious) {
		String codeClass = "commentsim";
		if (isSuspicious == false)
			codeClass = "syntaxsim";

		StringBuffer s = new StringBuffer();

		for (int i = 0; i < matchFragments.size(); i++) {
			EMatchFragment m = matchFragments.get(i);

			// set additional index for highlighted sections
			int additionalIndex = 1;
			s.append("<div class=\"generatedfragment\" id='" + m.getVisualId()
					+ "g'> <pre class=\"prettyprint linenums\">");
			ArrayList<ETokenTuple> copied = m.getCopied();
			for (int j = 0; j < copied.size(); j++) {
				ETokenTuple cur = copied.get(j);

				// to make sure the code is not wrongly visualised, replace all HTML escape
				// characters
				cur.setText(cur.getText().replaceAll("&", "&amp;").replaceAll("<", "&lt;").replaceAll(">", "&gt;"));

				// add an opening link tag
				if (j == m.getNumOfPretokens()) {
					s.append("<a class='" + codeClass + "' id='" + m.getVisualId() + "a' href=\"#" + m.getVisualId()
							+ "b\" onclick=\"markSelected('" + m.getVisualId() + "','origtablecontent')\" >");
				}

				if (j >= m.getNumOfPretokens() && j <= copied.size() - 1 - m.getNumOfPosttokens()
						&& cur.getType().equals("WS")) {
					// if it is in the matched region and it is a whitespace token

					// if the last visited element is a WS token, skip
					if (j > 0 && copied.get(j - 1).getType().equals("WS"))
						continue;

					// get the text (the disguised one)
					String tmp = cur.getText();

					// if it contains newline, add a closing link tag before the raw text, and an
					// opening link tag after that
					if (tmp.contains("\n")) {
						s.append("</a>" + tmp + "<a class='" + codeClass + "' id='" + m.getVisualId() + "a"
								+ additionalIndex + "' href=\"#" + m.getVisualId() + "b\" onclick=\"markSelected('"
								+ m.getVisualId() + "','origtablecontent')\" >");
						additionalIndex++;
					} else {
						// otherwise, just add as usual
						s.append(tmp);
					}
				} else {
					// append the text
					s.append(cur.getText());
				}

				// add a closing link tag
				if (j == copied.size() - 1 - m.getNumOfPosttokens())
					s.append("</a>");
			}

			s.append("</pre></div>");
			s.append(System.lineSeparator());
		}
		return s.toString();
	}

	public static String generateExplanation(ArrayList<EMatchFragment> matchFragments, String humanLanguage,
			boolean isSuspicious) {
		StringBuffer s = new StringBuffer();
		// add explanation for each fragment
		for (EMatchFragment m : matchFragments) {
			// set modification level
			// verbatim copy
			int modificationLevel = 0;
			// whitespace modification
			if (m.isWhitespaceModified())
				modificationLevel = 1;
			// comment modification
			if (m.isCommentModified())
				modificationLevel = 2;
			// identifier name modification
			if (m.isIdentifierNameModified())
				modificationLevel = 3;
			// constant modification
			if (m.isConstantValuesModified())
				modificationLevel = 4;
			// data type modification
			if (m.isDataTypeModified())
				modificationLevel = 5;

			// append the string
			s.append("<div class=\"explanationcontent\" id=\"" + m.getVisualId() + "he\">\n\t");
			// header text
			if (isSuspicious == false) {
				s.append((humanLanguage.equalsIgnoreCase("en"))
						? "Let us assume that the selected content is similar to code in some of your colleagues' submissions and one of those looks like below. "
						: "Asumsikan konten terpilih ditemukan di kode sebagian teman-teman anda dan salah satunya tampak seperti kode dibawah. ");
			} else if (modificationLevel == 0) {
				s.append((humanLanguage.equalsIgnoreCase("en"))
						? "The selected content is similar to code in some of your colleagues' submissions."
						: "Konten terpilih ditemukan di kode sebagian teman-teman anda. ");
			} else if (modificationLevel == 1 || modificationLevel == 2) {
				s.append((humanLanguage.equalsIgnoreCase("en"))
						? "The selected content is similar to code in some of your colleagues' submissions, except perhaps for comments and/or spacing."
						: "Konten terpilih ditemukan di kode sebagian teman-teman anda dan hanya berbeda dalam penggunaan komentar dan spasi.");
			} else if (modificationLevel == 3) {
				s.append((humanLanguage.equalsIgnoreCase("en"))
						? "The selected content is similar to code in some of your colleagues' submissions, except perhaps for comments, spacing, and/or "
								+ "names of identifiers (e.g., variables, functions, or classes), features that don’t affect how the program works."
						: "Konten terpilih ditemukan di kode sebagian teman-teman anda dan hanya berbeda dalam penggunaan komentar, spasi, dan/atau "
								+ "nama identifier (variabel, fungsi, kelas), komponen-komponen yang tidak berdampak pada jalannya program. ");
			} else if (modificationLevel >= 4) {
				s.append((humanLanguage.equalsIgnoreCase("en"))
						? "The selected content is similar to code in some of your colleagues' submissions after ignoring "
								+ "differences on comments, whitespaces, identifier names, constant values, and data types."
						: "Konten terpilih ditemukan di kode sebagian teman-teman anda setelah mengabaikan perbedaan pada komentar, whitespace, nama identifier, nilai konstan, dan tipe data. ");
			}

			if (m.getAppliedDisguises().size() > 0) {
				// header text for differences generated in artificial examples
				if (isSuspicious) {
					s.append((humanLanguage.equalsIgnoreCase("en"))
							? "\n\t<br /> <br />To help you understand the similarity, see the code example below. "
									+ "It is essentially similar to your code, despite having some differences such as: <br /><br />"
							: "\n\t<br /> <br />Untuk membantu anda mengerti kesamaan terkait, lihatlah contoh kode dibawah. "
									+ "Kode ini bermakna sama dengan kode anda walaupun terdapat perbedaan dalam hal: <br /><br />");
				} else {
					s.append((humanLanguage.equalsIgnoreCase("en"))
							? "It is essentially similar to your code, despite having some differences such as: <br /><br />"
							: "Kode ini bermakna sama dengan kode anda walaupun terdapat perbedaan dalam hal: <br /><br />");
				}
				// report the applied disguises
				ArrayList<Integer> appliedDisguises = m.getAppliedDisguises();
				s.append("\n\t<ol>\n");
				for (Integer x : appliedDisguises) {
					s.append("\t\t<li>" + EDisguiseRandomiser.getDisguiseMessage(x, humanLanguage) + "</li>\n");
				}

				s.append("\t</ol>");
			} else {
				s.append("<br /><br />");
			}

			if (isSuspicious) {
				s.append((humanLanguage.equalsIgnoreCase("en"))
						? "If there are many ways of writing the essential part of the code, and you were not instructed to write it in a particular way, "
								+ "similarity of this sort can be suspicious. In these circumstances it is rare to see two or more independent students "
								+ "share the same essential content. "
						: "Jika ada beberapa cara untuk menulis konten terpilih dan anda tidak diwajibkan untuk menggunakan cara tertentu, "
								+ "kesamaan ini dapat mencurigakan. Dalam kondisi tersebut, sangat jarang ditemukan dua atau lebih mahasiswa "
								+ "memiliki konten yang sama. ");
			} else {
				s.append((humanLanguage.equalsIgnoreCase("en"))
						? "If there are many ways of writing the essential part of the code, and you were not instructed to write it in a particular way, "
								+ "similarity of this sort can be suspicious. In these circumstances it is rare to see two or more independent students "
								+ "share the same essential content. "
						: "Jika ada beberapa cara untuk menulis konten terpilih dan anda tidak diwajibkan untuk menggunakan cara tertentu, "
						+ "kesamaan ini dapat mencurigakan. Dalam kondisi tersebut, sangat jarang ditemukan dua atau lebih mahasiswa "
						+ "memiliki konten yang sama. ");
			}

			s.append("\n</div>\n");

		}

		return s.toString();
	}

	public static String generateCode1(ArrayList<ETokenTuple> targetMergedTokenString,
			ArrayList<EMatchFragment> matchFragments, boolean isSuspicious) {

		String codeClass = "commentsim";
		if (isSuspicious == false)
			codeClass = "syntaxsim";

		StringBuffer s = new StringBuffer();

		// starting from the first match fragment, take all the required data
		int matchFragmentIdx = 0;
		EMatchFragment m = matchFragments.get(matchFragmentIdx);
		int mFragmentStart = targetMergedTokenString.indexOf(m.getStartToken());
		int mFragmentFinish = targetMergedTokenString.indexOf(m.getFinishToken());

		// for denoting sub-fragments
		int additionalIndex = 1;
		// for each token from code1
		for (int i = 0; i < targetMergedTokenString.size(); i++) {
			ETokenTuple cur = targetMergedTokenString.get(i);

			// to make sure the code is not wrongly visualised, replace all HTML escape
			// characters
			cur.setRawText(cur.getRawText().replaceAll("&", "&amp;").replaceAll("<", "&lt;").replaceAll(">", "&gt;"));

			if (i == mFragmentStart) {
				// if it is the start of the fragment, add an opening link tag
				s.append("<a class='" + codeClass + "' id='" + m.getVisualId() + "b' href=\"#" + m.getVisualId()
						+ "a\" onclick=\"markSelected('" + m.getVisualId() + "','origtablecontent')\" >");
			}

			if (i >= mFragmentStart && i <= mFragmentFinish && cur.getType().equals("WS")) {
				// if it is in the matched region and it is a whitespace token

				// get the raw text
				String tmp = cur.getRawText();

				// if it contains newline, add a closing link tag before the raw text, and an
				// opening link tag after that
				if (tmp.contains("\n")) {
					s.append("</a>" + tmp + "<a class='" + codeClass + "' id='" + m.getVisualId() + "b"
							+ additionalIndex + "' href=\"#" + m.getVisualId() + "a\" onclick=\"markSelected('"
							+ m.getVisualId() + "','origtablecontent')\" >");
					additionalIndex++;
				} else {
					// otherwise, just add as usual
					s.append(tmp);
				}
			} else {
				// append the raw text
				s.append(cur.getRawText());
			}

			if (i == mFragmentFinish) {
				// if it is the end of the fragment, add a closing link tag
				s.append("</a>");
				// check for next fragment
				if (matchFragmentIdx + 1 < matchFragments.size()) {
					// if any, increment the idx
					matchFragmentIdx++;
					// take the new data
					m = matchFragments.get(matchFragmentIdx);
					mFragmentStart = targetMergedTokenString.indexOf(m.getStartToken());
					mFragmentFinish = targetMergedTokenString.indexOf(m.getFinishToken());
					// reset additional index
					additionalIndex = 1;
				}
			}
		}
		return s.toString();
	}

	public static String generateTableContent(ArrayList<EMatchFragment> matchFragments, String humanLanguage,
			boolean isSuspicious) {
		String tableId = "origtablecontent";

		StringBuffer s = new StringBuffer();

		// put all fragments as html tuple list
		ArrayList<EHtmlTableTuple> list = new ArrayList<>();
		for (EMatchFragment m : matchFragments) {
			list.add(new EHtmlTableTuple(m));
		}

		// sort the list based on importance score in descending order.
		Collections.sort(list);

		// this to define concern priority
		int priority = 1;

		// start generating the resulted string
		for (int i = 0; i < list.size(); i++) {
			EHtmlTableTuple cur = list.get(i);

			// set the first line
			s.append("<tr id=\"" + cur.getEntity().getVisualId()
					+ "hr\" onclick=\"markSelectedWithoutChangingTableFocus('" + cur.getEntity().getVisualId() + "','"
					+ tableId + "')\">");

			/*
			 * Get table ID from visual ID and then aligns it for readability.
			 */
			String visualId = cur.getEntity().getVisualId();
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

			/*
			 * if the importance score is lower than previous, add the priority by one
			 * (which means this is less prioritised).
			 */
			if (i >= 1 && cur.getImportanceScore() < list.get(i - 1).getImportanceScore()) {
				priority++;
			}

			// visualising the rest of the lines
			s.append("\n\t<td><a href=\"#" + cur.getEntity().getVisualId() + "a\" id=\"" + cur.getEntity().getVisualId()
					+ "hl\">" + alignedTableID + "</a></td>");

			if (humanLanguage.equalsIgnoreCase("en")) {
				// suspicion risk
				if (isSuspicious == false)
					s.append("\n\t<td>Simulation only</td>");
				else if (cur.getModificationLevel() == 0)
					s.append("\n\t<td>Very strong</td>");
				else if (cur.getModificationLevel() == 1 || cur.getModificationLevel() == 2)
					s.append("\n\t<td>Fairly strong</td>");
				else if (cur.getModificationLevel() == 3)
					s.append("\n\t<td>Strong</td>");
				else if (cur.getModificationLevel() > 3)
					s.append("\n\t<td>Moderate</td>");

				// copied chars
				if (cur.getCopiedCharLength() > 1)
					s.append("\n\t<td>" + cur.getCopiedCharLength() + " chars</td>");
				else
					s.append("\n\t<td>1 char</td>");

			} else {
				// suspicion risk
				if (isSuspicious == false)
					s.append("\n\t<td>Hanya simulasi</td>");
				else if (cur.getModificationLevel() == 0)
					s.append("\n\t<td>Sangat tinggi</td>");
				else if (cur.getModificationLevel() == 1 || cur.getModificationLevel() == 2)
					s.append("\n\t<td>Tinggi</td>");
				else if (cur.getModificationLevel() == 3)
					s.append("\n\t<td>Sedikit tinggi</td>");
				else if (cur.getModificationLevel() > 3)
					s.append("\n\t<td>Menengah</td>");

				// copied chars
				s.append("\n\t<td>" + cur.getCopiedCharLength() + " karakter</td>");
			}

			s.append("\n\t<td>" + priority + "</td>");
			s.append("\n</tr>\n");
		}

		return s.toString();
	}

}
