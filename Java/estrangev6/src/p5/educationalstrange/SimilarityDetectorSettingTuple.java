package p5.educationalstrange;

public class SimilarityDetectorSettingTuple {
	private String ext;
	private int nGramForIR = 3;
	private double suspicionThresholdForIR = 0.75;
	private double inclusionThresholdForCommonFragments = 0.75;
	private boolean isZip = false;
	// static attributes
	private static int minPyNgramLengthForCommonFragments = 10;
	private static int maxPyNgramLengthForCommonFragments = 30;
	private static int minJavaNgramLengthForCommonFragments = 20;
	private static int maxJavaNgramLengthForCommonFragments = 60;
	private static int minMatchingLengthJavaForFeedbackGeneration = 80; // previously 20
	private static int minMatchingLengthPyForFeedbackGeneration = 40; // previously 10
	private static int javaExpectedTokensPerIssue = 80; // to calculate quality score
	private static int pythonExpectedTokensPerIssue = 40;
	private static double minPercentageCollabScore = 0.5; // for gamification collab badge
	private static double maxPercentageCollabScore = 0.8;
	private static String additionalKeywordPath;
	private static int numSimulationDisguises = 3;
	private static String humanLanguage;

	public SimilarityDetectorSettingTuple(String ext, boolean isZip, double suspicionThresholdForIR) {
		this.ext = ext;
		this.isZip = isZip;
		this.suspicionThresholdForIR = suspicionThresholdForIR;
	}

	public String getExt() {
		return ext;
	}

	public void setExt(String ext) {
		this.ext = ext;
	}

	public int getnGramForIR() {
		return nGramForIR;
	}

	public void setnGramForIR(int nGramForIR) {
		this.nGramForIR = nGramForIR;
	}

	public double getSuspicionThresholdForIR() {
		return suspicionThresholdForIR;
	}

	public void setSuspicionThresholdForIR(double suspicionThresholdForIR) {
		this.suspicionThresholdForIR = suspicionThresholdForIR;
	}

	public double getInclusionThresholdForCommonFragments() {
		return inclusionThresholdForCommonFragments;
	}

	public void setInclusionThresholdForCommonFragments(double inclusionThresholdForCommonFragments) {
		this.inclusionThresholdForCommonFragments = inclusionThresholdForCommonFragments;
	}

	public boolean isZip() {
		return isZip;
	}

	public void setZip(boolean isZip) {
		this.isZip = isZip;
	}

	public static int getMinPyNgramLengthForCommonFragments() {
		return minPyNgramLengthForCommonFragments;
	}

	public static void setMinPyNgramLengthForCommonFragments(int minPyNgramLengthForCommonFragments) {
		SimilarityDetectorSettingTuple.minPyNgramLengthForCommonFragments = minPyNgramLengthForCommonFragments;
	}

	public static int getMaxPyNgramLengthForCommonFragments() {
		return maxPyNgramLengthForCommonFragments;
	}

	public static void setMaxPyNgramLengthForCommonFragments(int maxPyNgramLengthForCommonFragments) {
		SimilarityDetectorSettingTuple.maxPyNgramLengthForCommonFragments = maxPyNgramLengthForCommonFragments;
	}

	public static int getMinJavaNgramLengthForCommonFragments() {
		return minJavaNgramLengthForCommonFragments;
	}

	public static void setMinJavaNgramLengthForCommonFragments(int minJavaNgramLengthForCommonFragments) {
		SimilarityDetectorSettingTuple.minJavaNgramLengthForCommonFragments = minJavaNgramLengthForCommonFragments;
	}

	public static int getMaxJavaNgramLengthForCommonFragments() {
		return maxJavaNgramLengthForCommonFragments;
	}

	public static void setMaxJavaNgramLengthForCommonFragments(int maxJavaNgramLengthForCommonFragments) {
		SimilarityDetectorSettingTuple.maxJavaNgramLengthForCommonFragments = maxJavaNgramLengthForCommonFragments;
	}

	public static int getMinMatchingLengthJavaForFeedbackGeneration() {
		return minMatchingLengthJavaForFeedbackGeneration;
	}

	public static void setMinMatchingLengthJavaForFeedbackGeneration(int minMatchingLengthJavaForFeedbackGeneration) {
		SimilarityDetectorSettingTuple.minMatchingLengthJavaForFeedbackGeneration = minMatchingLengthJavaForFeedbackGeneration;
	}

	public static int getMinMatchingLengthPyForFeedbackGeneration() {
		return minMatchingLengthPyForFeedbackGeneration;
	}

	public static void setMinMatchingLengthPyForFeedbackGeneration(int minMatchingLengthPyForFeedbackGeneration) {
		SimilarityDetectorSettingTuple.minMatchingLengthPyForFeedbackGeneration = minMatchingLengthPyForFeedbackGeneration;
	}

	public static int getJavaExpectedTokensPerIssue() {
		return javaExpectedTokensPerIssue;
	}

	public static void setJavaExpectedTokensPerIssue(int javaExpectedTokensPerIssue) {
		SimilarityDetectorSettingTuple.javaExpectedTokensPerIssue = javaExpectedTokensPerIssue;
	}

	public static int getPythonExpectedTokensPerIssue() {
		return pythonExpectedTokensPerIssue;
	}

	public static void setPythonExpectedTokensPerIssue(int pythonExpectedTokensPerIssue) {
		SimilarityDetectorSettingTuple.pythonExpectedTokensPerIssue = pythonExpectedTokensPerIssue;
	}

	public static double getMinPercentageCollabScore() {
		return minPercentageCollabScore;
	}

	public static void setMinPercentageCollabScore(double minPercentageCollabScore) {
		SimilarityDetectorSettingTuple.minPercentageCollabScore = minPercentageCollabScore;
	}

	public static double getMaxPercentageCollabScore() {
		return maxPercentageCollabScore;
	}

	public static void setMaxPercentageCollabScore(double maxPercentageCollabScore) {
		SimilarityDetectorSettingTuple.maxPercentageCollabScore = maxPercentageCollabScore;
	}

	public static String getAdditionalKeywordPath() {
		return additionalKeywordPath;
	}

	public static void setAdditionalKeywordPath(String additionalKeywordPath) {
		SimilarityDetectorSettingTuple.additionalKeywordPath = additionalKeywordPath;
	}

	public static int getNumSimulationDisguises() {
		return numSimulationDisguises;
	}

	public static void setNumSimulationDisguises(int numSimulationDisguises) {
		SimilarityDetectorSettingTuple.numSimulationDisguises = numSimulationDisguises;
	}

	public static String getHumanLanguage() {
		return humanLanguage;
	}

	public static void setHumanLanguage(String humanLanguage) {
		SimilarityDetectorSettingTuple.humanLanguage = humanLanguage;
	}

}
