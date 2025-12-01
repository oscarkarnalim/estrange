package p5.educationalstrange.ematchfragmentgenerator;

import java.util.ArrayList;
import java.util.Collections;

import p5.educationalstrange.tuple.ETokenTuple;
import support.stringmatching.GSTMatchTuple;
import support.stringmatching.GreedyStringTiling;

public class EMatchFragmentGenerator {
	public static ArrayList<EMatchFragment> setCopiedContentInMatchFragments(ArrayList<EMatchFragment> matchFragments,
			ArrayList<ETokenTuple> targetMergedTokenString) {

		// set the copied content in each match fragment. The content will be ready to
		// be disguised. These include tokens before and after the content if those
		// tokens share the same line as the first or last token.
		for (int i = 0; i < matchFragments.size(); i++) {
			EMatchFragment cur = matchFragments.get(i);
			int startIndex = targetMergedTokenString.indexOf(cur.getStartToken());
			int finishIndex = targetMergedTokenString.indexOf(cur.getFinishToken());

			// count the number of copied chars
			int copiedCharLength = 0;
			for (int j = startIndex; j <= finishIndex; j++) {
				ETokenTuple nc = targetMergedTokenString.get(j);
				copiedCharLength += nc.getRawText().length();
			}
			cur.setCopiedCharLength(copiedCharLength);

			// update startIndex so that it covers previous tokens with the same line as the
			// start. If the last terminating token is a whitespace one, take that token
			// before breaking the loop.
			int startLine = cur.getStartToken().getLine();
			int numOfPretokens = 0;
			while (startIndex > 0) {
				ETokenTuple prev = targetMergedTokenString.get(startIndex - 1);
				if (prev.getLine() == startLine) {
					startIndex--;
					numOfPretokens++;
				} else {
					if (prev.getType().equals("WS")) {
						startIndex--;
						numOfPretokens++;
					}
					break;
				}
			}

			// update finishIndex so that it covers next tokens with the same line as the
			// finish.
			int finishLine = cur.getFinishToken().getLine();
			int numOfPostokens = 0;
			while (finishIndex < targetMergedTokenString.size() - 1) {
				ETokenTuple next = targetMergedTokenString.get(finishIndex + 1);
				if (next.getLine() == finishLine) {
					finishIndex++;
					numOfPostokens++;
				} else {
					if (next.getType().equals("WS")) {
						finishIndex++;
						numOfPostokens++;
					}
					break;
				}
			}

			// set the number of pre and post tokens
			cur.setNumOfPretokens(numOfPretokens);
			cur.setNumOfPosttokens(numOfPostokens);

			// set the copied fragment
			ArrayList<ETokenTuple> copiedFragment = new ArrayList<ETokenTuple>();
			for (int j = startIndex; j <= finishIndex; j++) {
				ETokenTuple nc = targetMergedTokenString.get(j);

				// copy as a new tuple but without generalisation (take the raw ones)
				copiedFragment.add(new ETokenTuple(nc.getRawText(), nc.getType(), nc.getLine(), nc.getColumn()));
			}
			cur.setCopied(copiedFragment);
		}
		return matchFragments;
	}

	public static ArrayList<EMatchFragment> mergeOverlapMatcheFragments(ArrayList<EMatchFragment> matchFragments,
			ArrayList<ETokenTuple> targetSTokenString, String ext) {
		// sort first
		Collections.sort(matchFragments);
		// merge any overlap match fragments
		for (int i = 0; i < matchFragments.size(); i++) {
			EMatchFragment cur = matchFragments.get(i);
			int curStartIdx = targetSTokenString.indexOf(cur.getStartToken());
			int curFinishIdx = targetSTokenString.indexOf(cur.getFinishToken());

			for (int j = i + 1; j < matchFragments.size(); j++) {
				EMatchFragment ano = matchFragments.get(j);
				int anoStartIdx = targetSTokenString.indexOf(ano.getStartToken());
				int anoFinishIdx = targetSTokenString.indexOf(ano.getFinishToken());
				// get the merged region
				int[] mergedRegion = getMergedRegion(curStartIdx, curFinishIdx, anoStartIdx, anoFinishIdx);

				if (mergedRegion != null) {
					// set the new region to current
					cur.setStartToken(targetSTokenString.get(mergedRegion[0]));
					cur.setFinishToken(targetSTokenString.get(mergedRegion[1]));
					// including the indexes
					curStartIdx = mergedRegion[0];
					curFinishIdx = mergedRegion[1];

					if (ext.equals("txt") == false) {
						// get the characteristics as specific as possible with 'AND' operator so that
						// the merged fragment still records the most obvious copying attempt possible.
						cur.setCommentModified(cur.isCommentModified() && ano.isCommentModified());
						cur.setWhitespaceModified(cur.isWhitespaceModified() && ano.isWhitespaceModified());
						cur.setDataTypeModified(cur.isDataTypeModified() && ano.isDataTypeModified());
						cur.setConstantValuesModified(cur.isConstantValuesModified() && ano.isConstantValuesModified());
						cur.setIdentifierNameModified(cur.isIdentifierNameModified() && ano.isIdentifierNameModified());
					}
					// remove ano
					matchFragments.remove(j);
					// decrement j
					j--;
				}

			}
		}

		return matchFragments;
	}

	private static int[] getMergedRegion(int curStartIdx, int curFinishIdx, int anoStartIdx, int anoFinishIdx) {
		// This method returns two
		// elements; the first one is start index of the merged region while the second
		// one is the finish index IF the regions are overlapped. Otherwise, return
		// null;
		int[] mergedRegionIndexes = new int[2];

		if (anoStartIdx >= curStartIdx && anoStartIdx <= curFinishIdx) {
			mergedRegionIndexes[0] = curStartIdx;
			mergedRegionIndexes[1] = Math.max(curFinishIdx, anoFinishIdx);
			return mergedRegionIndexes;
		} else {
			return null;
		}
	}

	public static ArrayList<EMatchFragment> getMatchFragments(ArrayList<ArrayList<GSTMatchTuple>> matches,
			ArrayList<ETokenTuple> targetSTokenString, ArrayList<ETokenTuple> targetCWTokenString,
			ArrayList<ArrayList<ETokenTuple>> suspiciousOtherSStrings,
			ArrayList<ArrayList<ETokenTuple>> suspiciousOtherCWStrings, String ext) {
		/*
		 * for each suspicious file, generate the differences based on given matches and
		 * list the differences.
		 */

		// to store the result
		ArrayList<EMatchFragment> result = new ArrayList<EMatchFragment>();

		// for each match, get the original form and then list the differences
		for (int i = 0; i < matches.size(); i++) {
			ArrayList<GSTMatchTuple> matchesPerComparison = matches.get(i);
			for (int j = 0; j < matchesPerComparison.size(); j++) {
				GSTMatchTuple m = matchesPerComparison.get(j);

				// get start and finish token for target's syntax token string
				ETokenTuple targetSStartToken = targetSTokenString.get(m.patternPosition);
				ETokenTuple targetSFinishToken = targetSTokenString.get(m.patternPosition + m.length - 1);

				// get start and finish token for target's comment and whitespace token string
				int[] targetCWBoundaryIndex = getStartFinishIndexesForCWString(targetSStartToken, targetSFinishToken,
						targetSTokenString, targetCWTokenString);

				// get start and finish token for another's syntax token string
				ETokenTuple anotherSStartToken = suspiciousOtherSStrings.get(i).get(m.textPosition);
				ETokenTuple anotherSFinishToken = suspiciousOtherSStrings.get(i).get(m.textPosition + m.length - 1);

				// get start and finish token for another's comment and whitespace token string
				int[] anotherCWBoundaryIndex = getStartFinishIndexesForCWString(anotherSStartToken, anotherSFinishToken,
						suspiciousOtherSStrings.get(i), suspiciousOtherCWStrings.get(i));

				EMatchFragment r = new EMatchFragment(targetSStartToken, targetSFinishToken);

				// check for identifier name, literal (constant), and data type modification
				for (int k = 0; k < m.length; k++) {
					ETokenTuple tCurToken = targetSTokenString.get(m.patternPosition + k);
					ETokenTuple aCurToken = suspiciousOtherSStrings.get(i).get(m.textPosition + k);

					if (ext.equals("txt") == false) {
						// for programming language only
						if (tCurToken.getText().equals("$idn$")) {
							// if it is identifier AND the raw texts are different
							if (tCurToken.getRawText().equals(aCurToken.getRawText()) == false) {
								// mark identifier name modified as true
								r.setIdentifierNameModified(true);
							}
						} else if (tCurToken.getText().equals("$strl$") || tCurToken.getText().equals("$numl$")) {
							// if it is string or number literal AND the raw texts are different
							if (tCurToken.getRawText().equals(aCurToken.getRawText()) == false) {
								// mark constant value name modified as true
								r.setConstantValuesModified(true);
							}
						} else if (tCurToken.getText().equals("$strt$") || tCurToken.getText().equals("$numt$")) {
							// if it is string or number data type AND the raw texts are different
							if (tCurToken.getRawText().equals(aCurToken.getRawText()) == false) {
								// mark data type modified as true
								r.setDataTypeModified(true);
							}
						}
					}
				}

				// check for comment and whitespace modification
				if (targetCWBoundaryIndex != null && anotherCWBoundaryIndex != null) {
					ArrayList<String> commentTarget = new ArrayList<String>();
					ArrayList<String> commentAnother = new ArrayList<String>();

					for (int k = 0; k < targetCWBoundaryIndex[1] - targetCWBoundaryIndex[0] + 1; k++) {
						if (targetCWBoundaryIndex[0] + k >= targetCWTokenString.size()
								|| anotherCWBoundaryIndex[0] + k >= suspiciousOtherCWStrings.get(i).size())
							break;

						ETokenTuple tCurToken = targetCWTokenString.get(targetCWBoundaryIndex[0] + k);
						ETokenTuple aCurToken = suspiciousOtherCWStrings.get(i).get(anotherCWBoundaryIndex[0] + k);

						// for whitespaces
						if (tCurToken.getType().equals("WS") && aCurToken.getType().equals("WS")) {
							// if both are whitespaces
							if (!tCurToken.getText().equals(aCurToken.getText())) {
								// if the raw text is different, mark the whitespaces modified
								r.setWhitespaceModified(true);
							}
						} else if (tCurToken.getType().equals("WS") && !aCurToken.getType().equals("WS")) {
							// if either one of it is not a whitespace (comment), mark the whitespaces as
							// modified
							r.setWhitespaceModified(true);
						} else if (!tCurToken.getType().equals("WS") && aCurToken.getType().equals("WS")) {
							// if either one of it is not a whitespace (comment), mark the whitespaces as
							// modified
							r.setWhitespaceModified(true);
						}

						// get all the comments for target
						if (tCurToken.getType().endsWith("COMMENT"))
							commentTarget.add(tCurToken.getText());

						// get all the comments for another
						if (aCurToken.getType().endsWith("COMMENT"))
							commentAnother.add(aCurToken.getText());
					}

					// for comments
					// if they have different number of comments, mark the comments modified
					if (commentTarget.size() != commentAnother.size()) {
						r.setCommentModified(true);
					} else {
						// if they have the same size, check each member
						for (int k = 0; k < commentTarget.size(); k++) {
							// if the contents are different, mark the comments modified
							if (commentTarget.get(k).equals(commentAnother.get(k)) == false) {
								r.setCommentModified(true);
							}
						}
					}
				}

				result.add(r);

			}
		}

		return result;
	}

	public static ArrayList<ArrayList<GSTMatchTuple>> getRKRGSTMatches(boolean[] usedTokenMarkerForTargetSyntaxString,
			boolean[][] usedTokenMarkerForOtherSyntaxStrings, ArrayList<ETokenTuple> targetSTokenString,
			ArrayList<ArrayList<ETokenTuple>> suspiciousOtherSStrings, int minMatchingLengthJavaForFeedbackGeneration,
			int minMatchingLengthPyForFeedbackGeneration, String ext) {
		// generate match tuples by comparing the target to each other strings
		ArrayList<ArrayList<GSTMatchTuple>> matches = new ArrayList<ArrayList<GSTMatchTuple>>();
		// get the target syntax array
		String[] targetSyntaxArray = convertTokenTupleToString(targetSTokenString);
		// start the comparison
		for (int i = 0; i < suspiciousOtherSStrings.size(); i++) {
			// get other syntax array
			String[] anotherSyntaxArray = convertTokenTupleToString(suspiciousOtherSStrings.get(i));

			// safe copy the used token marker first as it is by reference
			boolean[] usedTokenMarkerForTargetSyntaxStringTemp = new boolean[usedTokenMarkerForTargetSyntaxString.length];
			for (int j = 0; j < usedTokenMarkerForTargetSyntaxString.length; j++) {
				usedTokenMarkerForTargetSyntaxStringTemp[j] = usedTokenMarkerForTargetSyntaxString[j];
			}

			// safe copy also the counterpart
			boolean[] usedTokenMarkerForOtherSyntaxString = new boolean[usedTokenMarkerForOtherSyntaxStrings[i].length];
			for (int j = 0; j < usedTokenMarkerForOtherSyntaxStrings[i].length; j++) {
				usedTokenMarkerForOtherSyntaxString[j] = usedTokenMarkerForOtherSyntaxStrings[i][j];
			}

			// add the matches resulted from RKRGST
			int minMatchLength = minMatchingLengthPyForFeedbackGeneration;
			if (ext.equals("java")) {
				// for Java, the min match will be different to Python and text
				minMatchLength = minMatchingLengthJavaForFeedbackGeneration;
			}

			matches.add(GreedyStringTiling.getMatchedTilesWithPredefinedMarkedArrays(targetSyntaxArray,
					usedTokenMarkerForTargetSyntaxStringTemp, anotherSyntaxArray, usedTokenMarkerForOtherSyntaxString,
					minMatchLength));
		}

		return matches;
	}

	public static int[] getStartFinishIndexesForCWString(ETokenTuple startSToken, ETokenTuple finishSToken,
			ArrayList<ETokenTuple> sTokenString, ArrayList<ETokenTuple> cwTokenString) {
		/*
		 * This method returns the first and last index in comment and whitespace (CW)
		 * token string that are in between given syntax start and finish tokens
		 */

		int tStartCWIndex = -1;
		int tFinishCWIndex = -1;

		// get target start line and column
		int tStartLine = startSToken.getLine();
		int tStartColumn = startSToken.getColumn();

		// loop till a tuple's position is later than the start one
		for (int n = 0; n < cwTokenString.size(); n++) {
			ETokenTuple cur = cwTokenString.get(n);

			// check the position
			boolean isFirstCWToken = false;
			if (cur.getLine() > tStartLine)
				isFirstCWToken = true;
			else if (cur.getLine() == tStartLine && cur.getColumn() > tStartColumn)
				isFirstCWToken = true;

			// if later than the start, store as tStartCWIndex and then break the loop
			if (isFirstCWToken) {
				tStartCWIndex = n;
				break;
			}
		}

		// get target finish line and column
		int tFinishLine = finishSToken.getLine();
		int tFinishColumn = finishSToken.getColumn();

		// if not found, return null
		if (tStartCWIndex == -1)
			return null;

		// loop till a tuple's position is later than the finish one
		for (int n = tStartCWIndex; n < cwTokenString.size(); n++) {
			ETokenTuple cur = cwTokenString.get(n);

			// check the position
			boolean isLastCWToken = false;
			if (cur.getLine() > tFinishLine)
				isLastCWToken = true;
			else if (cur.getLine() == tFinishLine && cur.getColumn() > tFinishColumn)
				isLastCWToken = true;

			// if later than the finish, store the previous index as tFinishCWIndex and then
			// break the loop
			if (isLastCWToken) {
				tFinishCWIndex = n - 1;
				break;
			}
		}
		// if the second loop does not break, assign with the last element
		if (tFinishCWIndex == -1)
			tFinishCWIndex = cwTokenString.size() - 1;

//		System.out.println("Start pos: " + tStartLine + " " + tStartColumn);
//		System.out.println(cwTokenString.get(tStartCWIndex - 1).getLine());
//		System.out.println(cwTokenString.get(tStartCWIndex - 1).getColumn());
//		System.out.println(cwTokenString.get(tStartCWIndex).getLine());
//		System.out.println(cwTokenString.get(tStartCWIndex).getColumn());
//		System.out.println(cwTokenString.get(tStartCWIndex + 1).getLine());
//		System.out.println(cwTokenString.get(tStartCWIndex + 1).getColumn());
//
//		System.out.println("Finish pos: " + tFinishLine + " " + tFinishColumn);
//		System.out.println(cwTokenString.get(tFinishCWIndex - 1).getLine());
//		System.out.println(cwTokenString.get(tFinishCWIndex - 1).getColumn());
//		System.out.println(cwTokenString.get(tFinishCWIndex).getLine());
//		System.out.println(cwTokenString.get(tFinishCWIndex).getColumn());
//		System.out.println(cwTokenString.get(tFinishCWIndex + 1).getLine());
//		System.out.println(cwTokenString.get(tFinishCWIndex + 1).getColumn());

		return new int[] { tStartCWIndex, tFinishCWIndex };
	}

	private static String[] convertTokenTupleToString(ArrayList<ETokenTuple> arr) {
		/*
		 * probably the simplest method in here. Just convert the syntax token strings
		 * to an array of string
		 */
		String[] s = new String[arr.size()];
		for (int i = 0; i < arr.size(); i++) {
			s[i] = arr.get(i).getText();
		}
		return s;
	}

}
