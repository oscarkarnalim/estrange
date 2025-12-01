package p5.educationalstrange.suspiciongenerator;

import java.sql.Connection;
import java.util.ArrayList;
import java.util.Collections;

import org.antlr.v4.codegen.model.MatchToken;

import p5.educationalstrange.SimilarityDetector;
import p5.educationalstrange.ehtmlgenerator.EHtmlGenerator;
import p5.educationalstrange.ematchfragmentgenerator.EMatchFragment;
import p5.educationalstrange.ematchfragmentgenerator.EMatchFragmentGenerator;
import p5.educationalstrange.eobfuscator.EDisguiseGenerator;
import p5.educationalstrange.eobfuscator.tuple.EObfuscatorSettingTuple;
import p5.educationalstrange.tuple.ETokenTuple;
import support.stringmatching.GSTMatchTuple;

public class RealSuspicionGenerator {
	public static double generateRealSuspicion(boolean[] usedTokenMarkerForTargetSyntaxString,
			boolean[][] usedTokenMarkerForOtherSyntaxStrings, ArrayList<ETokenTuple> targetSTokenString,
			ArrayList<ETokenTuple> targetCWTokenString, ArrayList<ArrayList<ETokenTuple>> suspiciousOtherSStrings,
			ArrayList<ArrayList<ETokenTuple>> suspiciousOtherCWStrings, EObfuscatorSettingTuple obfuscationSetting,
			int minMatchingLengthJavaForFeedbackGeneration, int minMatchingLengthPyForFeedbackGeneration, String ext,
			String humanLanguage, String targetSubmissionID, Connection connect, boolean isThisSubmitterReport,
			String targetCodePath, boolean isZip, String temporarySubmissionPath, String javaRunCommand,
			String pythonRunCommand, int javaExpectedTokensPerIssue, int pythonExpectedTokensPerIssue,
			int efficiencyScore) {
		// this method will return the similarity degree of the submitted program

		// generate match tuples by comparing the target to each other strings with
		// RKRGST
		ArrayList<ArrayList<GSTMatchTuple>> matches = EMatchFragmentGenerator.getRKRGSTMatches(
				usedTokenMarkerForTargetSyntaxString, usedTokenMarkerForOtherSyntaxStrings, targetSTokenString,
				suspiciousOtherSStrings, minMatchingLengthJavaForFeedbackGeneration,
				minMatchingLengthPyForFeedbackGeneration, ext);

		// convert the matches as match fragment tuple that lists any information
		// regarding the differences. All matches are aligned to the targed code.
		ArrayList<EMatchFragment> matchFragments = EMatchFragmentGenerator.getMatchFragments(matches,
				targetSTokenString, targetCWTokenString, suspiciousOtherSStrings, suspiciousOtherCWStrings, ext);

		// merge the overlapped ones
		matchFragments = EMatchFragmentGenerator.mergeOverlapMatcheFragments(matchFragments, targetSTokenString, ext);

		// generate merged token lists for the target submission
		ArrayList<ETokenTuple> targetMergedTokenString = new ArrayList<ETokenTuple>();
		// add the lists
		targetMergedTokenString.addAll(targetSTokenString);
		targetMergedTokenString.addAll(targetCWTokenString);
		// sort
		Collections.sort(targetMergedTokenString);
		
		//BELOMM

		// set copied content in each match fragment
		matchFragments = EMatchFragmentGenerator.setCopiedContentInMatchFragments(matchFragments,
				targetMergedTokenString);

		// check if at least one matched fragment exists
		int totalMatchTokens = 0;
		for (EMatchFragment match : matchFragments) {
			totalMatchTokens += match.getCopied().size();
		}

		// if no fragments or the size of similar code segments is lower than 50%, the
		// target is not suspicious, return matches prior overlapping check
		if (totalMatchTokens == 0) {
			return 0;
		} else if (totalMatchTokens < targetSTokenString.size() / 2) {
			return totalMatchTokens * 1.0 / targetSTokenString.size();
		}

		// generate the disguises at random and apply them
		matchFragments = EDisguiseGenerator.applyArtificialDisguises(matchFragments, obfuscationSetting);

		// assign visual ID for html. Starting with 'c' to show RED colour.
		// sort so that the assigned IDs are based on their first occurrence
		Collections.sort(matchFragments);
		for (int i = 0; i < matchFragments.size(); i++) {
			matchFragments.get(i).setVisualId("c" + (i + 1));
		}

		// generate html strings and pass it to SQL table
		String markedcode = EHtmlGenerator.generateCode1(targetMergedTokenString, matchFragments, true);
		String artificialcode = EHtmlGenerator.generateCode2(matchFragments, true);
		String tableinfo = EHtmlGenerator.generateTableContent(matchFragments, humanLanguage, true);
		String explanationinfo = EHtmlGenerator.generateExplanation(matchFragments, humanLanguage, true);
		String suspicionType = "real";

		// count the number of matched tokens
		int matchedTokensCount = 0;
		for (EMatchFragment m : matchFragments) {
			// deduct with the number of pre and post tokens as both are just for paddings
			matchedTokensCount += (m.getCopied().size() - m.getNumOfPretokens() - m.getNumOfPosttokens());
		}
		// calculate the number of matched tokens
		int simDegree = matchedTokensCount * 100 / targetMergedTokenString.size();

		SimilarityDetector.updateTableWithSuspicionAndSuggestion(targetSubmissionID, markedcode, artificialcode,
				tableinfo, explanationinfo, suspicionType, false, targetMergedTokenString, simDegree, efficiencyScore,
				ext, humanLanguage, connect, isThisSubmitterReport, targetCodePath, isZip, temporarySubmissionPath,
				javaRunCommand, pythonRunCommand, javaExpectedTokensPerIssue, pythonExpectedTokensPerIssue,
				efficiencyScore);

		// return the proportion of matches prior overlapping check
		return totalMatchTokens * 1.0 / targetSTokenString.size();
	}

	public static boolean generateRealSuspicionToHTML(boolean[] usedTokenMarkerForTargetSyntaxString,
			boolean[][] usedTokenMarkerForOtherSyntaxStrings, ArrayList<ETokenTuple> targetSTokenString,
			ArrayList<ETokenTuple> targetCWTokenString, ArrayList<ArrayList<ETokenTuple>> suspiciousOtherSStrings,
			ArrayList<ArrayList<ETokenTuple>> suspiciousOtherCWStrings, EObfuscatorSettingTuple obfuscationSetting,
			int minMatchingLengthJavaForFeedbackGeneration, int minMatchingLengthPyForFeedbackGeneration, String ext,
			String humanLanguage, String templateHTMLPath, String outputHTMLPath) {
		// this method will return true if at least one matched fragment exists

		// generate match tuples by comparing the target to each other strings with
		// RKRGST
		ArrayList<ArrayList<GSTMatchTuple>> matches = EMatchFragmentGenerator.getRKRGSTMatches(
				usedTokenMarkerForTargetSyntaxString, usedTokenMarkerForOtherSyntaxStrings, targetSTokenString,
				suspiciousOtherSStrings, minMatchingLengthJavaForFeedbackGeneration,
				minMatchingLengthPyForFeedbackGeneration, ext);

		// check if at least one matched fragment exists
		boolean isExist = false;
		for (ArrayList<GSTMatchTuple> matchesPerPair : matches) {
			if (matchesPerPair.size() > 0) {
				isExist = true;
				break;
			}
		}
		// if no fragments, the target is not suspicious
		if (isExist == false)
			return false;

		// convert the matches as match fragment tuple that lists any information
		// regarding the differences. All matches are aligned to the targed code.
		ArrayList<EMatchFragment> matchFragments = EMatchFragmentGenerator.getMatchFragments(matches,
				targetSTokenString, targetCWTokenString, suspiciousOtherSStrings, suspiciousOtherCWStrings, ext);

		// merge the overlapped ones
		matchFragments = EMatchFragmentGenerator.mergeOverlapMatcheFragments(matchFragments, targetSTokenString, ext);

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

		// only for the programming language, generate the disguises at random and apply them
		if (ext.equals("txt") == false)
			matchFragments = EDisguiseGenerator.applyArtificialDisguises(matchFragments, obfuscationSetting);

		// assign visual ID for html. Starting with 'c' to show RED colour.
		// sort so that the assigned IDs are based on their first occurrence
		Collections.sort(matchFragments);
		for (int i = 0; i < matchFragments.size(); i++) {
			matchFragments.get(i).setVisualId("c" + (i + 1));
		}

		// map the fragments to HTML
		try {
			EHtmlGenerator.generateHtml(targetMergedTokenString, matchFragments, humanLanguage, templateHTMLPath,
					outputHTMLPath, true);
		} catch (Exception e) {
			e.printStackTrace();
		}

		return true;
	}

	public static void generateSuspicionReportForOthersToHTML(boolean[] usedTokenMarkerForTargetSyntaxString,
			boolean[][] usedTokenMarkerForOtherSyntaxStrings, ArrayList<ETokenTuple> targetSTokenString,
			ArrayList<ETokenTuple> targetCWTokenString, ArrayList<ArrayList<ETokenTuple>> suspiciousOtherSStrings,
			ArrayList<ArrayList<ETokenTuple>> suspiciousOtherCWStrings, EObfuscatorSettingTuple obfuscationSetting,
			int minMatchingLengthJavaForFeedbackGeneration, int minMatchingLengthPyForFeedbackGeneration, String ext,
			String humanLanguage, String templateHTMLPath, ArrayList<String> suspiciousOtherSubmissionIDs) {
		// for others
		for (int i = 0; i < suspiciousOtherSStrings.size(); i++) {
			// set the 'target' in accordance to the current index
			boolean[] usedTokenMarkerForTargetSyntaxStringTemp = usedTokenMarkerForOtherSyntaxStrings[i];
			ArrayList<ETokenTuple> targetSTokenStringTemp = suspiciousOtherSStrings.get(i);
			ArrayList<ETokenTuple> targetCWTokenStringTemp = suspiciousOtherCWStrings.get(i);

			// reset the 'others' in accordance to the current index
			boolean[][] usedTokenMarkerForOtherSyntaxStringsTemp = new boolean[suspiciousOtherSStrings.size()][];
			ArrayList<ArrayList<ETokenTuple>> suspiciousOtherSStringsTemp = new ArrayList<ArrayList<ETokenTuple>>();
			ArrayList<ArrayList<ETokenTuple>> suspiciousOtherCWStringsTemp = new ArrayList<ArrayList<ETokenTuple>>();
			for (int j = 0; j < suspiciousOtherSStrings.size(); j++) {
				if (i == j) {
					// add the old 'target'
					usedTokenMarkerForOtherSyntaxStringsTemp[j] = usedTokenMarkerForTargetSyntaxString;
					suspiciousOtherSStringsTemp.add(targetSTokenString);
					suspiciousOtherCWStringsTemp.add(targetCWTokenString);
				} else {
					// otherwise, just copy whatever it is
					usedTokenMarkerForOtherSyntaxStringsTemp[j] = usedTokenMarkerForOtherSyntaxStrings[j];
					suspiciousOtherSStringsTemp.add(suspiciousOtherSStrings.get(j));
					suspiciousOtherCWStringsTemp.add(suspiciousOtherCWStrings.get(j));
				}
			}

			// generate for other
			boolean isSuspiciousOther = RealSuspicionGenerator.generateRealSuspicionToHTML(
					usedTokenMarkerForTargetSyntaxStringTemp, usedTokenMarkerForOtherSyntaxStringsTemp,
					targetSTokenStringTemp, targetCWTokenStringTemp, suspiciousOtherSStringsTemp,
					suspiciousOtherCWStringsTemp, obfuscationSetting, minMatchingLengthJavaForFeedbackGeneration,
					minMatchingLengthPyForFeedbackGeneration, ext, humanLanguage, templateHTMLPath,
					suspiciousOtherSubmissionIDs.get(i) + ".html");

			// if not suspicious, skip the remaining processes
			if (isSuspiciousOther == false) {
				suspiciousOtherSubmissionIDs.set(i, null);
			}
		}
	}
}
