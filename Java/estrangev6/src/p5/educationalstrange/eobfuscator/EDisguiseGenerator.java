package p5.educationalstrange.eobfuscator;

import java.util.ArrayList;

import p5.educationalstrange.ematchfragmentgenerator.EMatchFragment;
import p5.educationalstrange.eobfuscator.tuple.EObfuscatorSettingTuple;
import p5.educationalstrange.tuple.ETokenTuple;

public class EDisguiseGenerator {
	public static ArrayList<EMatchFragment> applyArtificialDisguises(ArrayList<EMatchFragment> matchFragments,
			EObfuscatorSettingTuple obfuscationSetting) {
		// generate the disguises per content
		for (int i = 0; i < matchFragments.size(); i++) {
			EMatchFragment cur = matchFragments.get(i);

//			System.out.println(cur.isCommentModified());
//			System.out.println(cur.isWhitespaceModified());
//			System.out.println(cur.isIdentifierNameModified());
//			System.out.println(cur.isConstantValuesModified());
//			System.out.println(cur.isDataTypeModified());
//			System.out.println("=================================");
//			for (int k = 0; k < cur.getCopied().size(); k++) {
//				System.out.print(cur.getCopied().get(k).getRawText());
//			}
//			System.out.println();
//			System.out.println("=================================");

			/*
			 * filter so that only 'real' copied tokens are disguised. Remember that cur's
			 * copied also contains many pre and post tokens that occur in the same line.
			 */
			ArrayList<ETokenTuple> tempCopied = new ArrayList<ETokenTuple>();
			for (int k = cur.getNumOfPretokens(); k < cur.getCopied().size() - cur.getNumOfPosttokens(); k++) {
				tempCopied.add(cur.getCopied().get(k));
			}

			// get applied disguises
			ArrayList<Integer> selectedDisguises = EDisguiseRandomiser.getRandomisedDisguises(cur.isCommentModified(),
					cur.isWhitespaceModified(), cur.isIdentifierNameModified(), cur.isConstantValuesModified(),
					cur.isDataTypeModified(), obfuscationSetting, tempCopied);

			// apply the disguises
			ArrayList<ETokenTuple> disguisedContent = EDisguiseRandomiser.applyDisguises(cur.getCopied(),
					selectedDisguises, obfuscationSetting);

			// set the disguised content and the applied disguises to the current match
			// fragment
			cur.setCopied(disguisedContent);
			cur.setAppliedDisguises(selectedDisguises);

//			EDisguiseRandomiser.printDisguises(selectedDisguises);
//			System.out.println("=================================");
//			for (ETokenTuple c : disguisedContent) {
//				System.out.print(c.getText());
//			}
//			System.out.println();
//			System.out.println("=================================");
		}

		return matchFragments;
	}
}
