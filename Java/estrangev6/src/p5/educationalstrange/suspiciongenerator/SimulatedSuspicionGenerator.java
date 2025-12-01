package p5.educationalstrange.suspiciongenerator;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.util.ArrayList;
import java.util.Collections;

import p5.educationalstrange.SimilarityDetector;
import p5.educationalstrange.ehtmlgenerator.EHtmlGenerator;
import p5.educationalstrange.ematchfragmentgenerator.EMatchFragment;
import p5.educationalstrange.ematchfragmentgenerator.EMatchFragmentGenerator;
import p5.educationalstrange.eobfuscator.EDisguiseGenerator;
import p5.educationalstrange.eobfuscator.tuple.EObfuscatorSettingTuple;
import p5.educationalstrange.tuple.ETokenTuple;

public class SimulatedSuspicionGenerator {
	public static boolean generateSimulatedSuspicion(boolean[] usedTokenMarkerForTargetSyntaxString,
			ArrayList<ETokenTuple> targetSTokenString, ArrayList<ETokenTuple> targetCWTokenString,
			int numSimulationDisguises, boolean isOverlyUnique, EObfuscatorSettingTuple obfuscationSetting,
			int minMatchingLengthJavaForFeedbackGeneration, int minMatchingLengthPyForFeedbackGeneration, String ext,
			String humanLanguage, String targetSubmissionID, Connection connect,
			ArrayList<ETokenTuple> targetMergedTokenStringTemp, String targetCodePath, boolean isZip,
			String temporarySubmissionPath, String javaRunCommand, String pythonRunCommand,
			int javaExpectedTokensPerIssue, int pythonExpectedTokensPerIssue, int efficiencyScore) {
		// returns a boolean depicting whether the simulated suspicion has been
		// successfully created.
		// targetMergedTokenStringTemp stores the original code tokens once default code
		// is used.

		// get the minimum expected length for matches
		int minLength = minMatchingLengthJavaForFeedbackGeneration;
		if (ext.endsWith("py"))
			minLength = minMatchingLengthPyForFeedbackGeneration;

		ArrayList<int[]> disguiseCandidates = getDisguisedCandidates(usedTokenMarkerForTargetSyntaxString,
				targetSTokenString, minLength);

		// get the candidates for disguises
		ArrayList<EMatchFragment> matchFragments = getMatchFragmentsForSImulation(disguiseCandidates,
				numSimulationDisguises, targetSTokenString, targetCWTokenString);

		// if no matched fragments generated
		if (matchFragments.size() == 0)
			return false;

		// generate merged token lists for the target submission
		ArrayList<ETokenTuple> targetMergedTokenString = new ArrayList<ETokenTuple>();
		// add the lists
		targetMergedTokenString.addAll(targetSTokenString);
		targetMergedTokenString.addAll(targetCWTokenString);

		// sort
		Collections.sort(targetMergedTokenString);

		// set copied content in each match fragment
		matchFragments = EMatchFragmentGenerator.setCopiedContentInMatchFragments(matchFragments,
				targetMergedTokenString);

		// generate the disguises at random and apply them
		matchFragments = EDisguiseGenerator.applyArtificialDisguises(matchFragments, obfuscationSetting);

		// assign visual ID for html. Starting with 's' to show GREEN colour.
		// sort so that the assigned IDs are based on their first occurrence
		Collections.sort(matchFragments);
		for (int i = 0; i < matchFragments.size(); i++) {
			matchFragments.get(i).setVisualId("s" + (i + 1));
		}

		// generate html strings and pass it to SQL table
		String markedcode = EHtmlGenerator.generateCode1(targetMergedTokenString, matchFragments, false);
		String artificialcode = EHtmlGenerator.generateCode2(matchFragments, false);
		String tableinfo = EHtmlGenerator.generateTableContent(matchFragments, humanLanguage, false);
		String explanationinfo = EHtmlGenerator.generateExplanation(matchFragments, humanLanguage, false);
		String suspicionType = "simulation";

		/*
		 * if default code is used in the suspicion simulation, set
		 * targetMergedTokenString with the original token string
		 * (targetMergedTokenStringTemp) just to generate the code clarity suggestion.
		 */
		if (targetMergedTokenStringTemp != null)
			targetMergedTokenString = targetMergedTokenStringTemp;

		// update the SQL table based on the result and generate the suggestion report
		SimilarityDetector.updateTableWithSuspicionAndSuggestion(targetSubmissionID, markedcode, artificialcode,
				tableinfo, explanationinfo, suspicionType, isOverlyUnique, targetMergedTokenString, 0, efficiencyScore, ext, humanLanguage, connect,
				true, targetCodePath, isZip, temporarySubmissionPath, javaRunCommand, pythonRunCommand,
				javaExpectedTokensPerIssue, pythonExpectedTokensPerIssue, efficiencyScore);

		return true;
	}

	public static boolean generateSimulatedSuspicionToHTML(boolean[] usedTokenMarkerForTargetSyntaxString,
			ArrayList<ETokenTuple> targetSTokenString, ArrayList<ETokenTuple> targetCWTokenString,
			int numSimulationDisguises, EObfuscatorSettingTuple obfuscationSetting,
			int minMatchingLengthJavaForFeedbackGeneration, int minMatchingLengthPyForFeedbackGeneration, String ext,
			String humanLanguage, String templateHTMLPath, String outputHTMLPath) {
		// returns a boolean depicting whether the simulated suspicion has been
		// successfully created.

//		for (int i = 0; i < usedTokenMarkerForTargetSyntaxString.length; i++) {
//			if (usedTokenMarkerForTargetSyntaxString[i] == false)
//				System.out.print(targetSTokenString.get(i).getRawText() + " ");
//		}

		// get the minimum expected length for matches
		int minLength = minMatchingLengthJavaForFeedbackGeneration;
		if (ext.endsWith("py"))
			minLength = minMatchingLengthPyForFeedbackGeneration;

		ArrayList<int[]> disguiseCandidates = getDisguisedCandidates(usedTokenMarkerForTargetSyntaxString,
				targetSTokenString, minLength);

		// get the candidates for disguises
		ArrayList<EMatchFragment> matchFragments = getMatchFragmentsForSImulation(disguiseCandidates,
				numSimulationDisguises, targetSTokenString, targetCWTokenString);

		// if no matched fragments generated
		if (matchFragments.size() == 0)
			return false;

		// generate merged token lists for the target submission
		ArrayList<ETokenTuple> targetMergedTokenString = new ArrayList<ETokenTuple>();
		// add the lists
		targetMergedTokenString.addAll(targetSTokenString);
		targetMergedTokenString.addAll(targetCWTokenString);
		// sort
		Collections.sort(targetMergedTokenString);

		// set copied content in each match fragment
		matchFragments = EMatchFragmentGenerator.setCopiedContentInMatchFragments(matchFragments,
				targetMergedTokenString);

		// generate the disguises at random and apply them
		matchFragments = EDisguiseGenerator.applyArtificialDisguises(matchFragments, obfuscationSetting);

		// assign visual ID for html. Starting with 's' to show GREEN colour.
		// sort so that the assigned IDs are based on their first occurrence
		Collections.sort(matchFragments);
		for (int i = 0; i < matchFragments.size(); i++) {
			matchFragments.get(i).setVisualId("s" + (i + 1));
		}

		// map the fragments to HTML
		try {
			EHtmlGenerator.generateHtml(targetMergedTokenString, matchFragments, humanLanguage, templateHTMLPath,
					outputHTMLPath, false);
			return true;
		} catch (Exception e) {
			e.printStackTrace();
			return false;
		}
	}

	private static ArrayList<EMatchFragment> getMatchFragmentsForSImulation(ArrayList<int[]> disguiseCandidates,
			int numSimulationDisguises, ArrayList<ETokenTuple> targetSTokenString,
			ArrayList<ETokenTuple> targetCWTokenString) {
		// get the candidates for disguises
		ArrayList<EMatchFragment> matchFragments = new ArrayList<EMatchFragment>();
		for (int i = 0; i < numSimulationDisguises && disguiseCandidates.size() > 0; i++) {
			// select one index at random
			int takenIndex = SimilarityDetector.r.nextInt(disguiseCandidates.size());
			// get the pos tuple
			int[] pos = disguiseCandidates.remove(takenIndex);

			ETokenTuple targetSStartToken = targetSTokenString.get(pos[0]);
			ETokenTuple targetSFinishToken = targetSTokenString.get(pos[1]);

			// create the match fragment
			EMatchFragment cur = new EMatchFragment(targetSStartToken, targetSFinishToken);

			// get start and finish token for target's comment and whitespace token string
			int[] targetCWBoundaryIndex = EMatchFragmentGenerator.getStartFinishIndexesForCWString(targetSStartToken,
					targetSFinishToken, targetSTokenString, targetCWTokenString);

			// booleans to identify possible disguises
			boolean hasIdent = false, hasConstant = false, hasDataType = false, hasComment = false,
					hasWhitespace = false;

			// check the existence of identifier, constant and data type
			for (int k = pos[0]; k <= pos[1]; k++) {
				ETokenTuple tCurToken = targetSTokenString.get(k);
				if (tCurToken.getText().equals("$idn$")) {
					hasIdent = true;
				} else if (tCurToken.getText().equals("$strl$") || tCurToken.getText().equals("$numl$")) {
					hasConstant = true;
				} else if (tCurToken.getText().equals("$strt$") || tCurToken.getText().equals("$numt$")) {
					hasDataType = true;
				}
			}

			// check the existence of comment and whitespaces
			if (targetCWBoundaryIndex != null) {
				for (int k = 0; k < targetCWBoundaryIndex[1] - targetCWBoundaryIndex[0] + 1; k++) {
					ETokenTuple tCurToken = targetCWTokenString.get(targetCWBoundaryIndex[0] + k);
					if (tCurToken.getType().equals("WS")) {
						hasWhitespace = true;
					} else if (tCurToken.getType().endsWith("COMMENT")) {
						hasComment = true;
					}

				}
			}

			// whether ident disguises will be applied
			// gamble with 1/numSimulationDisguises % chance
			if (hasIdent && SimilarityDetector.r.nextBoolean() == true)
				// if succeed, mark identifier name modified as true
				cur.setIdentifierNameModified(true);

			// whether constant disguises will be applied
			// gamble with 1/numSimulationDisguises % chance
			if (hasConstant && SimilarityDetector.r.nextBoolean() == true)
				// if succeed, mark constant value name modified as true
				cur.setConstantValuesModified(true);

			// whether data type disguises will be applied
			// gamble with 1/numSimulationDisguises % chance
			if (hasDataType && SimilarityDetector.r.nextBoolean() == true)
				// if succeed, mark data type modified as true
				cur.setDataTypeModified(true);

			// whether whitespace disguises will be applied
			// gamble with 1/numSimulationDisguises % chance
			if (hasWhitespace && SimilarityDetector.r.nextBoolean() == true)
				// if succeed, mark data type modified as true
				cur.setWhitespaceModified(true);

			// whether comment disguises will be applied
			// gamble with 1/numSimulationDisguises % chance
			if (hasComment && SimilarityDetector.r.nextBoolean() == true)
				// if succeed, mark data type modified as true
				cur.setCommentModified(true);

//			// print the involved token
//			System.out.println("=====================================");
//			for (int k = pos[0]; k <= pos[1]; k++) {
//				System.out.print(targetSTokenString.get(k).getRawText() + " ");
//			}
//			System.out.println();
//
//			System.out
//					.println(hasIdent + " " + hasConstant + " " + hasDataType + " " + hasWhitespace + " " + hasComment);

			// add to the list
			matchFragments.add(cur);
		}

		return matchFragments;
	}

	private static ArrayList<int[]> getDisguisedCandidates(boolean[] usedTokenMarkerForTargetSyntaxString,
			ArrayList<ETokenTuple> targetSTokenString, int minLength) {
		ArrayList<int[]> disguiseCandidates = new ArrayList<int[]>();

		for (int i = 0; i < targetSTokenString.size(); i++) {

			// if it is a part of common fragment, skip
			if (usedTokenMarkerForTargetSyntaxString[i] == true)
				continue;

			// check whether it is at the start of the line
			boolean isStartLine = false;
			if (i == 0)
				isStartLine = true;
			else if (targetSTokenString.get(i).getLine() != targetSTokenString.get(i - 1).getLine())
				isStartLine = true;

			// if it is the start of the line, try to get the shortest possible code
			// fragment
			if (isStartLine) {
				// check whether any tokens for creating the shortest fragment are common
				boolean isCommon = false;
				for (int j = 1; j < minLength && i + j < usedTokenMarkerForTargetSyntaxString.length; j++) {
					if (usedTokenMarkerForTargetSyntaxString[i + j] == true) {
						isCommon = true;
						break;
					}
				}
				// if any of it is a part of common fragment, skip
				if (isCommon)
					continue;

				// loop starts with i + minlength as we need the fragment's length to be equal
				// or larger than minlength
				for (int j = i + minLength; j < targetSTokenString.size(); j++) {
					if (usedTokenMarkerForTargetSyntaxString[j] == true)
						break;

					// per token, check whether it is at the end of the line
					boolean isFinishLine = false;
					if (j == targetSTokenString.size() - 1)
						isFinishLine = true;
					else if (j + 1 < targetSTokenString.size()
							&& targetSTokenString.get(j).getLine() != targetSTokenString.get(j + 1).getLine())
						isFinishLine = true;

					// if it is, mark the fragment and set i to the next untouched token.
					if (isFinishLine) {
						// mark the fragment with a tuple
						int[] pos = new int[2];
						pos[0] = i; // start pos
						pos[1] = j; // finish pos

						// add to the list
						disguiseCandidates.add(pos);

						// jump to the next untouched token
						i = j;
						// break the innermost loop
						break;
					}
				}

			}
		}

		return disguiseCandidates;
	}

}
