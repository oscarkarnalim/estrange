package p5.educationalstrange;

import java.io.File;
import java.io.FileInputStream;
import java.io.FileOutputStream;
import java.io.ObjectInputStream;
import java.io.ObjectOutputStream;
import java.util.ArrayList;
import java.util.HashMap;

import org.antlr.v4.runtime.CharStreams;
import org.antlr.v4.runtime.CommonTokenStream;
import org.antlr.v4.runtime.Lexer;
import org.antlr.v4.runtime.Token;

import p5.educationalstrange.tuple.ETokenTuple;
import support.ir.FrequencyTuple;
import support.ir.VSM;
import support.javaantlr.Java9Lexer;
import support.pythonantlr.Python3Lexer;

public class EIRFiltering {
	public static ArrayList<Integer> getIRFilteringResult(String targetCodePath, ArrayList<String> otherCodePaths,
			int ngramForIR, double suspicionThreshold, String ext, String internalRepSubmissionsPath,
			String temporarySubmissionPath, boolean isZip) {
		// return a list of indexed of suspicious code files from given othercodepaths

		// IR filtering for both target and existing

		// generate the indexes
		// for the target
		HashMap<String, Double> indexTarget = EIRFiltering.getIRIndex(targetCodePath, ngramForIR, ext,
				internalRepSubmissionsPath, temporarySubmissionPath, isZip);
		// for the others
		ArrayList<HashMap<String, Double>> indexOthers = new ArrayList<HashMap<String, Double>>();
		for (String existing : otherCodePaths) {
			indexOthers.add(EIRFiltering.getIRIndex(existing, ngramForIR, ext, internalRepSubmissionsPath,
					temporarySubmissionPath, isZip));
		}
		
		int thresholdForPreciseSimilarityDegree = 1000000;

		// get the IR similarity
		double totalIRSimilarity = 0;
		ArrayList<Double> irSimilarities = new ArrayList<Double>();
		for (HashMap<String, Double> anotherIndex : indexOthers) {
			// generate IR sim between target and one other
			double irSim = VSM.getVSMRetrievalSimilarity(indexTarget, anotherIndex) * thresholdForPreciseSimilarityDegree;
			// add to the list
			irSimilarities.add(irSim);
			// add the score to the total sim
			totalIRSimilarity += irSim;
		}
		// generate the average
		double averageSim = totalIRSimilarity / indexOthers.size();
		double minThresholdForIR = Math.max(averageSim, suspicionThreshold * thresholdForPreciseSimilarityDegree);

		ArrayList<Integer> suspiciousOtherCodeIndexes = new ArrayList<Integer>();
		// filter only those undoubtly suspicious
		for (int i = 0; i < irSimilarities.size(); i++) {
			if (irSimilarities.get(i) > minThresholdForIR) {
				suspiciousOtherCodeIndexes.add(i);
			}
		}

		return suspiciousOtherCodeIndexes;
	}

	// generate an index for IR comparison
	public static HashMap<String, Double> getIRIndex(String filepath, int ngramForIR, String ext,
			String internalRepSubmissionsPath, String temporarySubmissionPath, boolean isZip) {
		HashMap<String, Double> index = null;

		if (internalRepSubmissionsPath == null) {
			// get the hash map
			ArrayList<String> tokenString = getAbstractSyntaxTreePreorderLinearisationWithoutLeafTokens(filepath,
					ext);
			index = FrequencyTuple.generateAttributeIndex(tokenString, ngramForIR);
		} else {
			// check whether the result is available from previous processes
			File irCompFile = new File(internalRepSubmissionsPath + new File(filepath).getName() + ".ircomp");
			if (irCompFile.exists()) {
				// get from the existing one
				try {
					FileInputStream fileIn = new FileInputStream(irCompFile);
					ObjectInputStream in = new ObjectInputStream(fileIn);
					index = (HashMap<String, Double>) in.readObject();
					in.close();
					fileIn.close();
				} catch (Exception e) {
					e.printStackTrace();
				}

				//System.out.println(filepath + " is read from existing (TREE).");
			}

			// if not, generate the new one and store it as a file
			if (index == null) {
				if (isZip) {
					filepath = ZipManipulation.mergeAllFilesInZip(filepath, temporarySubmissionPath, ext);
				}
				// get the hash map
				ArrayList<String> tokenString = getAbstractSyntaxTreePreorderLinearisationWithoutLeafTokens(filepath,
						 ext);
				index = FrequencyTuple.generateAttributeIndex(tokenString, ngramForIR);

				try {
					// store as a file
					FileOutputStream fileOut = new FileOutputStream(irCompFile);
					ObjectOutputStream out = new ObjectOutputStream(fileOut);
					out.writeObject(index);
					out.close();
					fileOut.close();
				} catch (Exception e) {
					e.printStackTrace();
				}
				//System.out.println(filepath + " is written to existing (TREE).");
			}
		}

		return index;
	}

	// two methods below are copied and adapted from
	// IRFilteringJavaTokenStringGenerator

	private static ArrayList<String> getAbstractSyntaxTreePreorderLinearisationWithoutLeafTokens(String filePath,
			String ext) {
		
		// generate index of generalised token string 

		ArrayList<String> result = new ArrayList<>();
		try {
			// build the token string
			if (ext.equals("java")) {
				// java
				// build the lexer
				Lexer lexer = new Java9Lexer(CharStreams.fromFileName(filePath));
				// extract the tokens
				CommonTokenStream tokens = new CommonTokenStream(lexer);
				tokens.fill();
				// only till size-1 as the last one is EOF token
				for (int index = 0; index < tokens.size() - 1; index++) {
					Token token = tokens.get(index);
					String type = Java9Lexer.VOCABULARY.getDisplayName(token.getType());
					if (!(type.endsWith("COMMENT") || type.equals("WS"))) {
						result.add(getGeneralisedTokenJava(token.getText(), type));
					}
				}
			} else {
				// Python
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
					result.add(getGeneralisedTokenPy(token.getText(), type));
				}
			}

			// return the result
			return result;
		} catch (Exception e) {
			e.printStackTrace();
			return null;
		}
	}
	
	private static String getGeneralisedTokenJava(String text, String type) {
		// this sect was copied and modified from
		// JavaFeedbackGenerator
		if (type.equals("additional_keyword")) {
			return text;
		} else if (type.equals("Identifier")) {
			if (text.equals("Integer") || text.equals("Short") || text.equals("Long")
					|| text.equals("Byte") || text.equals("Float") || text.equals("Double")) {
				return "$numt$";
			} else if (text.equals("String") || text.equals("Character")) {
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
			return text;
	}

	private static String getGeneralisedTokenPy(String text, String type) {
		// this sect was copied and modified from
		// JavaFeedbackGenerator
		if (type.equals("additional_keyword"))
			return text;
		else if (type.equals("Identifier")) {
			return "$idn$";
		} else if (type.equals("STRING_LITERAL"))
			return "$strl$";
		else if (type.equals("DECIMAL_INTEGER") || type.equals("FloatingPointLiteral"))
			return "$numl$";
		else
			return text;
	}

	private static HashMap<String, Double> generateAttributeIndex(ArrayList<String> in) {
		// convert given token tuple list to index-like representation

		HashMap<String, Double> out = new HashMap<String, Double>();
		for (int i = 0; i < in.size(); i++) {
			// get key as a form of ngram
			String key = in.get(i);

			// increment the data
			Double count = out.get(key);
			if (count == null)
				count = 0d;
			count++;
			out.put(key, count);
		}
		return out;
	}
}
