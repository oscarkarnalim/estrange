package p5.educationalstrange.eobfuscator.disguisegenerator;

import java.util.ArrayList;

import p5.educationalstrange.tuple.ETokenTuple;

public class ECodeObfuscatorWhitespace {

	public static void w01RemovingBlankLines(ArrayList<ETokenTuple> tokenString) {
		// removes all blank newlines

		for (int i = 0; i < tokenString.size(); i++) {
			ETokenTuple t = tokenString.get(i);
			// check whether t is whitespace token
			if (t.getType().endsWith("WS")) {
				String text = t.getText();

				// get the first newline pos
				int firstIndex = text.indexOf("\n");
				// get the last newline pos
				int lastIndex = text.lastIndexOf("\n");

				// if the text contains less than two newlines, skip
				if (firstIndex == -1 || lastIndex == -1 || firstIndex == lastIndex)
					continue;

				// merge the text outside the first and the last newline pos
				String resultedText = text.substring(0, firstIndex) + text.substring(lastIndex, text.length());
				// set the text
				t.setText(resultedText);

				// count the number of reduced lines
				int reducedLine = 0;
				text = text.substring(firstIndex, lastIndex);
				for (int k = 0; k < text.length(); k++) {
					char c = text.charAt(k);
					if (c == '\n')
						reducedLine++;
				}

				// update the remaining tokens
				for (int j = i + 1; j < tokenString.size(); j++) {
					tokenString.get(j).setLine(tokenString.get(j).getLine() - reducedLine);
				}

			}
		}
	}

	public static void w02JavaRemovingTabsAndSpacesBeforeEachStatement(ArrayList<ETokenTuple> tokenString) {
		// remove all tabs and spaces before each statement

		for (int i = 0; i < tokenString.size(); i++) {
			ETokenTuple t = tokenString.get(i);
			// check whether t is whitespace token
			if (t.getType().endsWith("WS")) {
				String text = t.getText();
				// get the last newline pos
				int lastIndex = text.lastIndexOf(System.lineSeparator());
				// if not found, skip
				if (lastIndex == -1) {
					continue;
				}
				// set the text
				if (lastIndex > 0)
					// if the index is positive, white space before the newline should be added
					t.setText(text.substring(0, lastIndex - 1) + System.lineSeparator());
				else
					t.setText(System.lineSeparator());
			}
		}
	}

	public static void w03ReplacingEachSpaceWithNSpaces(ArrayList<ETokenTuple> tokenString, int n) {
		// replace each space with n spaces

		// generate the string for n spaces
		String s = "";
		for (int i = 0; i < n; i++)
			s += " ";

		for (int i = 0; i < tokenString.size(); i++) {
			ETokenTuple t = tokenString.get(i);
			// check whether t is whitespace token
			if (t.getType().endsWith("WS")) {
				String text = t.getText();
				t.setText(text.replaceAll(" ", s));
			}
		}
	}

	public static void w04ReplacingEachTabWithNTabs(ArrayList<ETokenTuple> tokenString, int n) {
		// replace each tab with n tabs

		// generate the string for n tabs
		String s = "";
		for (int i = 0; i < n; i++)
			s += "\t";

		for (int i = 0; i < tokenString.size(); i++) {
			ETokenTuple t = tokenString.get(i);
			// check whether t is whitespace token
			if (t.getType().endsWith("WS")) {
				String text = t.getText();
				String out = "";
				for (int k = 0; k < text.length(); k++) {
					char c = text.charAt(k);
					if (c == '\t')
						out += s;
					else
						out += c;
				}
				t.setText(out);
			}
		}
	}

	public static void w05ReplacingEachNewLineWithNLines(ArrayList<ETokenTuple> tokenString, int n) {
		// replace each newline with n lines

		// generate the string for n lines
		String s = "";
		for (int i = 0; i < n; i++)
			s += System.lineSeparator();

		for (int i = 0; i < tokenString.size(); i++) {
			ETokenTuple t = tokenString.get(i);
			// check whether t is whitespace token
			if (t.getType().endsWith("WS")) {
				String text = t.getText();
				/*
				 * set the updated version and count the number of affected newline
				 */
				String out = "";
				int affectedLines = 0;
				for (int k = 0; k < text.length(); k++) {
					char c = text.charAt(k);
					if (c == '\n') {
						affectedLines++;
						out += s;
					} else if (c != '\r')
						out += c;
				}

				// set the new text
				t.setText(out);

				// update the line pos of remaining tokens
				int addedLines = (n - 1) * affectedLines; // minus one to
															// compensate the
															// original newline
				for (int j = i + 1; j < tokenString.size(); j++) {
					tokenString.get(j).setLine(tokenString.get(j).getLine() + addedLines);
				}
			}
		}
	}

	public static void w06ReplacingEachTabWithNSpaces(ArrayList<ETokenTuple> tokenString, int n) {
		// replace each tab with n spaces

		// generate the string for n spaces
		String s = "";
		for (int i = 0; i < n; i++)
			s += " ";

		for (int i = 0; i < tokenString.size(); i++) {
			ETokenTuple t = tokenString.get(i);
			// check whether t is whitespace token
			if (t.getType().endsWith("WS")) {
				String text = t.getText();
				t.setText(text.replaceAll("\t", s));
			}
		}
	}

	public static void w07ReplacingNSpacesWithTab(ArrayList<ETokenTuple> tokenString, int n) {
		// replace each tab with n spaces

		// generate the string for n tabs
		String s = "";
		for (int i = 0; i < n; i++)
			s += " ";

		for (int i = 0; i < tokenString.size(); i++) {
			ETokenTuple t = tokenString.get(i);
			// check whether t is whitespace token
			if (t.getType().endsWith("WS")) {
				String text = t.getText();
				t.setText(text.replaceAll(s, "\t"));
			}
		}
	}
}
