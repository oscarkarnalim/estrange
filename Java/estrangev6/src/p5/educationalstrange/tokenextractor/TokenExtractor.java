package p5.educationalstrange.tokenextractor;

import java.io.BufferedReader;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileOutputStream;
import java.io.FileReader;
import java.io.IOException;
import java.io.ObjectInputStream;
import java.io.ObjectOutputStream;
import java.util.ArrayList;
import java.util.Collections;
import java.util.Iterator;
import java.util.Scanner;

import org.antlr.v4.runtime.CharStreams;
import org.antlr.v4.runtime.CommonTokenStream;
import org.antlr.v4.runtime.Lexer;
import org.antlr.v4.runtime.Token;
import org.apache.lucene.analysis.Analyzer;
import org.apache.lucene.analysis.TokenStream;
import org.apache.lucene.analysis.en.EnglishAnalyzer;
import org.apache.lucene.analysis.id.IndonesianAnalyzer;
import org.apache.lucene.analysis.tokenattributes.CharTermAttribute;
import org.apache.lucene.analysis.tokenattributes.OffsetAttribute;
import org.apache.lucene.analysis.tokenattributes.PositionIncrementAttribute;

import p5.educationalstrange.ECodeMerger;
import p5.educationalstrange.ZipManipulation;
import p5.educationalstrange.tuple.ETokenTuple;
import support.ir.NaturalLanguageProcesser;
import support.javaantlr.Java9Lexer;
import support.pythonantlr.Python3Lexer;

public class TokenExtractor {

	public static ArrayList<ArrayList<ETokenTuple>> getTokenStrings(String filepath, String ext,
			String internalRepSubmissionsPath, String temporarySubmissionPath, boolean isZip, String humanLang) {
		ArrayList<ArrayList<ETokenTuple>> tokens = null;
		// check whether the result is available from previous processes
		File stringCompFile = new File(internalRepSubmissionsPath + new File(filepath).getName() + ".stringcomp");
		if (stringCompFile.exists()) {
			// get from the existing one
			try {
				FileInputStream fileIn = new FileInputStream(stringCompFile);
				ObjectInputStream in = new ObjectInputStream(fileIn);
				tokens = (ArrayList<ArrayList<ETokenTuple>>) in.readObject();
				in.close();
				fileIn.close();
			} catch (Exception e) {
				e.printStackTrace();
			}

			// System.out.println(filepath + " is read from existing.");
		}

		// if not, generate the new one and store it as a file
		if (tokens == null) {
			// if zip, merge all the files first
			if (isZip)
				filepath = ZipManipulation.mergeAllFilesInZip(filepath, temporarySubmissionPath, ext);

			// get the token string
			tokens = getTokenStrings(filepath, ext, humanLang);

			try {
				// store as a file
				FileOutputStream fileOut = new FileOutputStream(stringCompFile);
				ObjectOutputStream out = new ObjectOutputStream(fileOut);
				out.writeObject(tokens);
				out.close();
				fileOut.close();
			} catch (Exception e) {
				e.printStackTrace();
			}
			// System.out.println(filepath + " is written to existing.");
		}

		// return the token
		return tokens;
	}

	public static ArrayList<ArrayList<ETokenTuple>> getTokenStrings(String filepath, String ext, String humanLang) {
		/*
		 * get two token strings: syntax and comment
		 */
		ArrayList<ArrayList<ETokenTuple>> tokens = null;
		if (ext.endsWith("java"))
			tokens = getJavaTokenStrings(filepath);
		else if (ext.endsWith("py"))
			tokens = getPythonTokenStrings(filepath);
		else if (ext.endsWith("txt"))
			tokens = getTextTokenStrings(filepath, humanLang);
		return tokens;
	}

	private static ArrayList<ArrayList<ETokenTuple>> getJavaTokenStrings(String filePath) {
		/*
		 * returns two arraylist. The first one contains all syntax tokens while keeping
		 * some tokens as keywords and generalised. Another string only contains
		 * comments and whitespaces.
		 */
		try {
			// prepare variables to store the results
			ArrayList<ArrayList<ETokenTuple>> results = new ArrayList<ArrayList<ETokenTuple>>();
			ArrayList<ETokenTuple> syntaxResult = new ArrayList<>();
			results.add(syntaxResult);
			ArrayList<ETokenTuple> commentWhitespaceResult = new ArrayList<>();
			results.add(commentWhitespaceResult);

			// build the lexer
			Lexer lexer = new Java9Lexer(CharStreams.fromFileName(filePath));
			// extract the tokens
			CommonTokenStream tokens = new CommonTokenStream(lexer);
			tokens.fill();
			// only till size-1 as the last one is EOF token
			for (int index = 0; index < tokens.size() - 1; index++) {
				Token token = tokens.get(index);
				String type = Java9Lexer.VOCABULARY.getDisplayName(token.getType());
				if (type.endsWith("COMMENT") || type.equals("WS")) {
					if (ECodeMerger.isGeneratedFromCodeMerging(token.getText(), "java"))
						type = "auto_generated_cmnt";

					// add to comment and whitespace result
					commentWhitespaceResult.add(
							new ETokenTuple(token.getText(), type, token.getLine(), token.getCharPositionInLine()));
				} else {
					// add to syntax result
					syntaxResult.add(
							new ETokenTuple(token.getText(), type, token.getLine(), token.getCharPositionInLine()));
				}
			}

			// generalise the content for java
			for (int i = 0; i < syntaxResult.size(); i++) {
				ETokenTuple cur = syntaxResult.get(i);
				cur.setText(getGeneralisedTokenJava(cur));
			}

			// add whitespace and comment tokens
			commentWhitespaceResult.addAll(_generateJavaWhitespaceTokens(filePath));

			// sort the result
			Collections.sort(commentWhitespaceResult);

			return results;
		} catch (Exception e) {
			e.printStackTrace();
			return null;
		}
	}

	public static ArrayList<ArrayList<ETokenTuple>> getPythonTokenStrings(String filePath) {
		/*
		 * returns two arraylist. The first one contains all syntax tokens while keeping
		 * some tokens as keywords and generalised. Another string only contains
		 * comments and whitespaces.
		 */
		try {
			// prepare variables to store the results
			ArrayList<ETokenTuple> mergedResult = new ArrayList<>();

			// build the lexer
			Lexer lexer = new Python3Lexer(CharStreams.fromFileName(filePath));
			// extract the tokens
			CommonTokenStream tokens = new CommonTokenStream(lexer);
			tokens.fill();
			// only till size-1 as the last one is EOF token
			for (int index = 0; index < tokens.size() - 1; index++) {
				Token token = tokens.get(index);
				String type = Python3Lexer.VOCABULARY.getDisplayName(token.getType());

				// this is used to make the generated tokens similar to Java's
				if (type.equals("NAME"))
					type = "Identifier";
				else if (type.equals("FLOAT_NUMBER"))
					type = "FloatingPointLiteral";

				// remove all whitespace tokens as these tokens are the
				// summarised version of whitespaces
				if (type.equals("93") || type.equals("94") || type.equals("NEWLINE"))
					continue;

				// take all tokens excluding whitespaces
				mergedResult
						.add(new ETokenTuple(token.getText(), type, token.getLine(), token.getCharPositionInLine()));
			}

			// generalise the content for python
			for (int i = 0; i < mergedResult.size(); i++) {
				ETokenTuple cur = mergedResult.get(i);
				cur.setText(getGeneralisedTokenPy(cur));
			}

			// add whitespace and comment tokens
			mergedResult.addAll(_generatePythonCommentAndWhitespaceTokens(filePath));

			// sort the result
			Collections.sort(mergedResult);

			// merging adjacent whitespaces AND updating tool-generated comments
			for (int i = 0; i < mergedResult.size() - 1; i++) {
				ETokenTuple cur = mergedResult.get(i);
				ETokenTuple next = mergedResult.get(i + 1);
				// if there are two adjacent whitespaces, merge them
				if (cur.getType().equals("WS") && next.getType().equals("WS")) {
					// merge the text for next token
					next.setText(cur.getText() + next.getText());
					next.setRawText(cur.getRawText() + next.getRawText());
					next.setLine(cur.getLine());
					next.setColumn(cur.getColumn()); // THIS IS EXCLUSIVELY APPLIED AS WE REQUIRE COLUMN DATA
					// and remove the current one
					mergedResult.remove(i);
					i--;
				}
			}

			// split the mergedresults
			ArrayList<ArrayList<ETokenTuple>> results = new ArrayList<ArrayList<ETokenTuple>>();
			ArrayList<ETokenTuple> syntaxResult = new ArrayList<>();
			results.add(syntaxResult);
			ArrayList<ETokenTuple> commentWhitespaceResult = new ArrayList<>();
			results.add(commentWhitespaceResult);

			for (ETokenTuple c : mergedResult) {
				if (c.getType().equals("WS") || c.getType().endsWith("COMMENT")
						|| c.getType().equals("auto_generated_cmnt")) {
					commentWhitespaceResult.add(c);
				} else
					syntaxResult.add(c);
			}

			return results;
		} catch (Exception e) {
			e.printStackTrace();
			return null;
		}
	}

	private static ArrayList<ArrayList<ETokenTuple>> getTextTokenStrings(String filePath, String humanLang) {
		/*
		 * returns two arraylist. The first one contains all content tokens while
		 * keeping some tokens lowercased and stemmed. All stop words are removed.
		 * Another string only contains comments and whitespaces.
		 */
		try {
			// prepare variables to store the results
			ArrayList<ArrayList<ETokenTuple>> results = new ArrayList<ArrayList<ETokenTuple>>();
			ArrayList<ETokenTuple> contentResult = new ArrayList<>();
			results.add(contentResult);
			ArrayList<ETokenTuple> whitespaceResult = new ArrayList<>(); // limited to space and tab
			results.add(whitespaceResult);

			Scanner r = new Scanner(new File(filePath));
			int row = 0;
			while (r.hasNextLine()) {
				String line = r.nextLine();

				String curWord = "";
				boolean isCurWordWhitespace = false;
				for (int i = 0; i < line.length(); i++) {
					char c = line.charAt(i);
					if (Character.isWhitespace(c)) {
						if(isCurWordWhitespace == false) {
							// transition from text to whitespace
							boolean isStopWord = NaturalLanguageProcesser.isStopWord(curWord, humanLang);
							String stemmed  = NaturalLanguageProcesser.getStem(curWord, humanLang);
							
							String type = "SW"; // stop word by default
							if(isStopWord == false)
								type = stemmed; // otherwise, the stemmed version
							contentResult.add(
									new ETokenTuple(curWord, type, row, i-curWord.length()+1));
							
							curWord = "";
						}
						
						curWord += c;
						isCurWordWhitespace = true;
					}else {
						if(isCurWordWhitespace == true) {
							// transition from whitespace to text
							whitespaceResult.add(
									new ETokenTuple(curWord, "WS", row, i-curWord.length()+1));
							
							curWord = "";
						}
						
						curWord += c;
						isCurWordWhitespace = false;
					}
				}
				
				// for processing the last word
				if(curWord.length() > 0) {
					if(isCurWordWhitespace == false) {
						boolean isStopWord = NaturalLanguageProcesser.isStopWord(curWord, humanLang);
						String stemmed  = NaturalLanguageProcesser.getStem(curWord, humanLang);
						
						String type = "$SW$"; // stop word by default
						if(isStopWord == false)
							type = stemmed; // otherwise, the stemmed version
						contentResult.add(
								new ETokenTuple(curWord, type, row, line.length()-curWord.length()));
					}else {
						whitespaceResult.add(
								new ETokenTuple(curWord, "WS", row, line.length()-curWord.length()));
					}
				}

				row++;
			}
			r.close();

			return results;
		} catch (Exception e) {
			e.printStackTrace();
			return null;
		}
	}

//	public static void main(String[] args) throws IOException {
//		ArrayList<ArrayList<ETokenTuple>> results = getTextTokenStrings("test.txt", "id");
//		System.out.println("Text");
//		for(ETokenTuple e: results.get(0)) {
//			System.out.println(e.getText() + " " + e.getType() + " " + e.getLine() +  " " + e.getColumn());
//		}
//		System.out.println("whitespace");
//		for(ETokenTuple e: results.get(1)) {
//			System.out.println(e.getText() + " " + e.getType() + " " + e.getLine() +  " " + e.getColumn());
//		}
//	}

	// copied and adapted from common fragment generator
	private static ArrayList<ETokenTuple> _generatePythonCommentAndWhitespaceTokens(String filePath) {
		/*
		 * generate comment and whitespace token list and return them as one
		 */
		ArrayList<ETokenTuple> commentWhitespaceTokens = new ArrayList<>();

		try {
			BufferedReader bufferedReader = new BufferedReader(new FileReader(filePath));

			boolean isInTripleQuoteString = false;

			int curLine = 1; // row starts from 1 but column from 0
			String line;
			String lineWithoutComment;
			while ((line = bufferedReader.readLine()) != null) {
				if (line.contains("'''")) {
					if (isInTripleQuoteString) {
						// closing triple quote
						int closingPos = line.indexOf("'''");
						// replace all chars in that region as -, we will skip
						// that anyway
						String o = "";
						for (int k = 0; k < closingPos + 3; k++)
							o += "-";
						o = o + line.substring(closingPos + 3, line.length());
						// set as the line
						line = o;
						// mark to be out of that quote string
						isInTripleQuoteString = false;
					} else {
						// opening triple quote
						int openingPos = line.lastIndexOf("'''");
						// replace all chars in that region as -, we will skip
						// that anyway
						String o = "";
						for (int k = openingPos; k < line.length(); k++)
							o += "-";
						o = line.substring(0, openingPos) + o;
						// set as the line
						line = o;
						// mark to be out of that quote string
						isInTripleQuoteString = true;
					}
				} else {
					// if it is still in triple quote, skip
					if (isInTripleQuoteString) {
						// increment curLine
						curLine++;
						// skip this iteration
						continue;
					}
				}

				int commentPos = -1;
				if (isInTripleQuoteString == false)
					commentPos = getPythonCommentStartCol(line);

				if (commentPos != -1) {
					// if there is a comment, create a line which comment is
					// removed
					lineWithoutComment = line.substring(0, commentPos);
				} else {
					lineWithoutComment = line;
				}

				// embed all whitespace tokens on that line
				String whitespacecontent = "";
				boolean isInSingleQuoteString = false;
				boolean isInDoubleQuoteString = false;
				for (int col = 0; col < lineWithoutComment.length(); col++) {
					char c = lineWithoutComment.charAt(col);
					if ((c == ' ' || c == '\t') && isInDoubleQuoteString == false && isInSingleQuoteString == false) {
						whitespacecontent += c;
					} else {
						if (c == '\'') {
							// dealing if that is escape character
							if (col > 0 && lineWithoutComment.charAt(col - 1) == '\\')
								continue;

							// dealing with spacing in single quote string
							// literal
							if (isInSingleQuoteString) {
								isInSingleQuoteString = false;
							} else if (isInDoubleQuoteString) {
								// do nothing
							} else {
								isInSingleQuoteString = true;
							}
						} else if (c == '\"') {
							// dealing if that is escape character
							if (col > 0 && lineWithoutComment.charAt(col - 1) == '\\')
								continue;

							// dealing with spacing in single quote string
							// literal
							if (isInDoubleQuoteString) {
								isInDoubleQuoteString = false;
							} else if (isInSingleQuoteString) {
								// do nothing
							} else {
								isInDoubleQuoteString = true;
							}
						}
						if (whitespacecontent.length() > 0) {
							commentWhitespaceTokens.add(new ETokenTuple(whitespacecontent, "WS", curLine,
									col - whitespacecontent.length()));
							whitespacecontent = "";

						}
					}
				}

				if (commentPos != -1) {
					// add the last whitespace content
					if (whitespacecontent.length() > 0) {
						int col = lineWithoutComment.length();
						commentWhitespaceTokens.add(new ETokenTuple(whitespacecontent, "WS", curLine,
								col - whitespacecontent.length() + 1));
					}

					// add the comment
					String comment = line.substring(commentPos);
					if (ECodeMerger.isGeneratedFromCodeMerging(comment, "py")) {
						commentWhitespaceTokens
								.add(new ETokenTuple(comment, "auto_generated_cmnt", curLine, commentPos));
					} else
						commentWhitespaceTokens.add(new ETokenTuple(comment, "COMMENT", curLine, commentPos));

					/*
					 * add newline as it ends the line now. startPos is assured to be non-negative
					 * as for each row transition, we assume it ends at the first column of the next
					 * line.
					 */
					commentWhitespaceTokens
							.add(new ETokenTuple(System.lineSeparator(), "WS", curLine, Math.max(0, line.length())));
				} else {
					/*
					 * add the last whitespace content with a newline. startPos is assured to be
					 * non-negative as for each row transition, we assume it ends at the first
					 * column of the next line.
					 */
					if (isInTripleQuoteString == false) {
						whitespacecontent += System.lineSeparator();
						commentWhitespaceTokens
								.add(new ETokenTuple(whitespacecontent, "WS", curLine, Math.max(0, line.length() - 1)));
					}
				}

				// increment curLine
				curLine++;

			}

			// Always close files.
			bufferedReader.close();
		} catch (Exception e) {
			e.printStackTrace();
		}

		return commentWhitespaceTokens;
	}

	private static ArrayList<ETokenTuple> _generateJavaWhitespaceTokens(String filePath) {
		/*
		 * generate whitespace token list and return it
		 */
		ArrayList<ETokenTuple> commentWhitespaceTokens = new ArrayList<>();

		try {
			BufferedReader bufferedReader = new BufferedReader(new FileReader(filePath));

			boolean isInMultipleComment = false;
			boolean isInSingleComment = false;
			boolean isInStringLiteral = false;

			int curLine = 1; // row starts from 1 but column from 0
			String curWhitespace = "";
			int curWhitespaceLine = -1;
			int curWhitespaceCol = -1;
			String line;
			while ((line = bufferedReader.readLine()) != null) {
				for (int i = 0; i < line.length(); i++) {
					char c = line.charAt(i);
					if (isInMultipleComment) {
						// if it is in multiple comment
						if (c == '*' && i + 1 < line.length() && line.charAt(i + 1) == '/') {
							// if it is the end of multiple comment, set the flag to false
							isInMultipleComment = false;
							i++; // the next char is part of the postfix
						} else
							;
						// otherwise, do nothing

					} else if (isInSingleComment) {
						// if it is in single comment, do nothing
					} else if (isInStringLiteral) {
						// if it is in string literal
						if (c == '"') {
							// if it is double quote
							if (i - 1 >= 0 && line.charAt(i - 1) == '\\') {
								// but the prev char is backslash, do nothing
							} else {
								// if not, set this as the end of string literal
								isInStringLiteral = false;
							}
						} else {
							// otherwise, do nothing
						}
					} else if (Character.isWhitespace(c)) {
						// if currently read whitespace is empty, mark this as the starting point
						if (curWhitespace.length() == 0) {
							curWhitespaceLine = curLine;
							curWhitespaceCol = i;
						}

						// update the current whitespace
						curWhitespace += c;
					} else {
						// if not whitespace, add currently read whitespace as a new token
						commentWhitespaceTokens
								.add(new ETokenTuple(curWhitespace, "WS", curWhitespaceLine, curWhitespaceCol));

						// reset the whitespace
						curWhitespace = "";

						if (c == '/' && i + 1 < line.length() && line.charAt(i + 1) == '*') {
							// if it is the start of multiple comment, set the flag to true
							isInMultipleComment = true;
							i++; // the next char is part of the prefix
						} else if (c == '/' && i + 1 < line.length() && line.charAt(i + 1) == '/') {
							// if it is the start of single line comment, set the flag to true
							isInSingleComment = true;
							i++; // the next char is part of the prefix
						} else if (c == '"') {
							// if it is the start of string literal, set the flag to true
							isInStringLiteral = true;
						}
					}
				}

				// only embed the whitespace if it is not in multiple line comment.
				// single line comment and string literal cases are not needed to be handled
				if (isInMultipleComment == false) {
					// if currently read whitespace is empty, mark this as the starting point
					if (curWhitespace.length() == 0) {
						curWhitespaceLine = curLine;
						curWhitespaceCol = line.length(); // end of line
					}

					curWhitespace += System.lineSeparator();
				}

				// always add line number
				curLine++;
				// each newline, single line comment and string literal will be automatically
				// ended
				isInSingleComment = false;
				isInStringLiteral = false;
			}

			// Always close files.
			bufferedReader.close();
		} catch (Exception e) {
			e.printStackTrace();
		}

		return commentWhitespaceTokens;
	}

	private static String getGeneralisedTokenJava(ETokenTuple c) {
		// this sect was copied and modified from
		// JavaFeedbackGenerator
		String type = c.getType();
		if (type.equals("additional_keyword")) {
			return c.getRawText();
		} else if (type.equals("Identifier")) {
			if (c.getText().equals("Integer") || c.getText().equals("Short") || c.getText().equals("Long")
					|| c.getText().equals("Byte") || c.getText().equals("Float") || c.getText().equals("Double")) {
				return "$numt$";
			} else if (c.getText().equals("String") || c.getText().equals("Character")) {
				return "$strt$";
			} else
				return "$idn$";
		} else if (type.equals("StringLiteral") || type.equals("CharacterLiteral")) {
			return "$strl$";
		} else if (type.equals("IntegerLiteral") || type.equals("FloatingPointLiteral"))
			return "$numl$";
		else if (type.equals("'char'"))
			return "$strt$";
		else if (type.equals("'int'") || type.equals("'short'") || type.equals("'long'") || type.equals("'byte'")
				|| type.equals("'float'") || type.equals("'double'"))
			return "$numt$";
		else
			return c.getText();
	}

	private static String getGeneralisedTokenPy(ETokenTuple c) {
		// this sect was copied and modified from
		// JavaFeedbackGenerator
		String type = c.getType();
		if (type.equals("additional_keyword"))
			return c.getRawText();
		else if (type.equals("Identifier")) {
			return "$idn$";
		} else if (type.equals("STRING_LITERAL"))
			return "$strl$";
		else if (type.equals("DECIMAL_INTEGER") || type.equals("FloatingPointLiteral"))
			return "$numl$";
		else
			return c.getText();
	}

	public static int indexOf(int pos, ArrayList<ETokenTuple> tokenString,
			ArrayList<ArrayList<String>> additionalKeywords) {
		/*
		 * This method will return the position of a keyword if any. The keyword can
		 * contain more than one token but the tokens should be separated by space.
		 * Additional keywords should be sorted from the most specific to general.
		 */

		// if no additionalKeywords provided, return not found
		if (additionalKeywords == null)
			return -1;

		for (int i = 0; i < additionalKeywords.size(); i++) {
			ArrayList<String> cur = additionalKeywords.get(i);

			// check whether it is a match
			boolean isMatch = true;
			for (int j = 0; j < cur.size(); j++) {
				String text = tokenString.get(pos + j).getText();

				if (text.equals(cur.get(j)) == false) {
					// once a pair is mismatched, break the loop
					isMatch = false;
					break;
				}

			}

			// if match, return the starting position
			if (isMatch)
				return i;
		}

		return -1;
	}

	private static int getPythonCommentStartCol(String line) {
		// get the start of Python comment
		boolean isInDoubleQuote = false;
		boolean isInSingleQuote = false;
		for (int i = 0; i < line.length(); i++) {
			char c = line.charAt(i);
			if (isInDoubleQuote) {
				if (c == '\"')
					isInDoubleQuote = false;
			} else if (isInSingleQuote) {
				if (c == '\'')
					isInSingleQuote = false;
			} else {
				if (c == '\"')
					isInDoubleQuote = true;
				else if (c == '\'')
					isInSingleQuote = true;
				else if (c == '#')
					return i;
			}
		}

		return -1;
	}

	private static int getJavaCommentStartCol(String line) {
		// check the start pos of // (java comment)
		boolean isInDoubleQuote = false;
		boolean isInSingleQuote = false;
		for (int i = 0; i < line.length(); i++) {
			char c = line.charAt(i);
			if (isInDoubleQuote) {
				if (c == '\"')
					isInDoubleQuote = false;
			} else if (isInSingleQuote) {
				if (c == '\'')
					isInSingleQuote = false;
			} else {
				if (c == '\"')
					isInDoubleQuote = true;
				else if (c == '\'')
					isInSingleQuote = true;
				else if (c == '/' && i + 1 < line.length() && line.charAt(i + 1) == '/')
					return i;
			}
		}

		return -1;
	}

//	public static void main(String[] args) {
//		// check whether the comment is a part of file separator comment like:
//		/* ==================================== */
//		/* Filepath: 'T10C/a/MyLinkedList.java' */
//		/* ==================================== */
//		String s = "/* ==================================== */";
//		s = "/* Filepath: 'T10C/a/MyLinkedList.java' */";
//		System.out.println(s.matches("/\\* =+ \\*/"));
//		System.out.println(s.matches("/\\* Filepath: '.+' \\*/"));
//	}
}
