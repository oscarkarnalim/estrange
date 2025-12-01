package p5.educationalstrange.eobfuscator.disguisegenerator;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Iterator;

import p5.educationalstrange.tuple.ETokenTuple;
import support.ir.NaturalLanguageProcesser;

public class ECodeObfuscatorIdentifier {

	public static void i01RemovingStopWords(
			ArrayList<ETokenTuple> tokenString,
			String languageCode) {
		/*
		 * remove all stop words from identifiers. 'en' is the language code for
		 * english while 'id' is for indonesian. There is a mechanism to deal
		 * with conflicting names.
		 */

		// get the identifiers
		ArrayList<String> identifiers = _getAllIdentifierNames(tokenString);
		HashMap<String, String> namePairs = new HashMap<>();
		Iterator<String> it = identifiers.iterator();
		while (it.hasNext()) {
			String in = it.next();

			// tokenise the text
			ArrayList<String> subwords = tokenizeIdentifier(in);

			// per word, check whether it is a stop word. If true, remove
			for (int j = 0; j < subwords.size(); j++) {
				String c = subwords.get(j);
				if (NaturalLanguageProcesser.isStopWord(c.toLowerCase(),
						languageCode)) {
					subwords.remove(j);
					j--;
				}
			}

			// create the new identifier
			String newText = "";
			for (int j = 0; j < subwords.size(); j++) {
				newText += subwords.get(j);
			}
			
			// generate the non conflicting name
			newText = _getNonConflictingIdentName(newText, namePairs);

			// put as the new pair
			namePairs.put(in, newText);
		}

		// update the identifier names
		for (int i = 0; i < tokenString.size(); i++) {
			ETokenTuple t = tokenString.get(i);
			// check whether t is identifier
			if (t.getType().equals("Identifier")) {
				String text = t.getText();
				// update the name
				t.setText(namePairs.get(text));
			}
		}

	}

	private static ArrayList<String> _getAllIdentifierNames(
			ArrayList<ETokenTuple> tokenString) {
		// get all identifier names
		ArrayList<String> identifiers = new ArrayList<>();
		for (int i = 0; i < tokenString.size(); i++) {
			ETokenTuple t = tokenString.get(i);
			// check whether t is identifier
			if (t.getType().equals("Identifier")) {
				// add to identifier if unique
				if (!identifiers.contains(t.getText()))
					identifiers.add(t.getText());
			}
		}

		return identifiers;
	}

	private static String _getNonConflictingIdentName(String name,
			HashMap<String, String> namePairs) {
		// rename the ident if redundant. Adding a single number as the prefix.

		// if the name is empty, add dummy name
		if (name.length() == 0)
			name = "a";

		int counter = 1;
		String newname = name;
		while (namePairs.values().contains(newname)) {
			newname = name + counter;
			counter++;
		}
		return newname;
	}

	public static ArrayList<String> tokenizeIdentifier(String ident) {
		/*
		 * This method tokenise the ident to several subwords based on capital
		 * transition or underscore. Adapted from source code authorship
		 * project, IdentDataHandler class
		 */
		ArrayList<String> output = new ArrayList<String>();
		String tempTerm = "";
		/*
		 * lastType merupakan tipe karakter sebelumnya. 0 merupakan karakter
		 * biasa, 1 kapital, 2 angka
		 */
		int lastType = -1;
		for (int i = 0; i < ident.length(); i++) {
			char c = ident.charAt(i);
			if (c >= 'a' && c <= 'z') {
				/*
				 * Jika berbeda tipe dan jumlah karakter lebih besar dari 1,
				 * lakukan proses pemotongan.
				 */
				if (lastType != 0) {
					if (lastType == 1) {
						/*
						 * Jika karakter sebelumnya kapital, tambahkan substring
						 * dari tempterm tampa melibatkan karakter terakhir, set
						 * tempterm dengan karakter terakhir yang dilowercase
						 */
						// ambil semua karakter awal
						String tempTerm2 = tempTerm.substring(0,
								tempTerm.length() - 1);
						// tambahkan dalam list
						if (tempTerm2.length() > 0)
							output.add(tempTerm2);
						// set dengan karakter pertama
						tempTerm = tempTerm.charAt(tempTerm.length() - 1) + "";
					} else {
						if (tempTerm.length() > 0)
							output.add(tempTerm);
						tempTerm = "";
					}
				}
				tempTerm += c;
				lastType = 0;
			} else if (c >= '0' && c <= '9') {
				if (lastType != 2) {
					if (tempTerm.length() > 0)
						output.add(tempTerm);
					tempTerm = "";
				}
				tempTerm += c;
				lastType = 2;
			} else if (c >= 'A' && c <= 'Z') {
				/*
				 * jika karakter sebelumnya bukan kapital, tambahkan dulu string
				 * tersebut dalam termList. kasus osCar jadi os dan car
				 */
				if (lastType != 1) {
					if (tempTerm.length() > 0)
						output.add(tempTerm);
					tempTerm = "";
				}
				tempTerm += c;
				lastType = 1;
			} else {
				if (tempTerm.length() > 0)
					output.add(tempTerm);
				tempTerm = "";
				lastType = -1;
				// so that the underscore is still stored in the result
				output.add("_");
			}
		}
		if (tempTerm.length() > 0)
			output.add(tempTerm);

		return output;
	}

	public static void i02Removing_(
			ArrayList<ETokenTuple> tokenString) {
		/*
		 * remove all underscores from identifiers. There is a mechanism to deal
		 * with conflicting names.
		 */

		// get the identifiers
		ArrayList<String> identifiers = _getAllIdentifierNames(tokenString);
		HashMap<String, String> namePairs = new HashMap<>();
		Iterator<String> it = identifiers.iterator();
		while (it.hasNext()) {
			String in = it.next();

			// remove all underscores
			String newText = in.replaceAll("_", "");

			// generate the non conflicting name
			newText = _getNonConflictingIdentName(newText, namePairs);

			// put as the new pair
			namePairs.put(in, newText);
		}

		for (int i = 0; i < tokenString.size(); i++) {
			ETokenTuple t = tokenString.get(i);
			// check whether t is identifier
			if (t.getType().equals("Identifier")) {
				String text = t.getText();
				// update the name
				t.setText(namePairs.get(text));
			}
		}
	}

	public static void i03RemovingNumbers(
			ArrayList<ETokenTuple> tokenString) {
		/*
		 * remove all numbers from identifiers. There is a mechanism to deal
		 * with conflicting names.
		 */

		// get the identifiers
		ArrayList<String> identifiers = _getAllIdentifierNames(tokenString);
		HashMap<String, String> namePairs = new HashMap<>();
		Iterator<String> it = identifiers.iterator();
		while (it.hasNext()) {
			String in = it.next();

			// remove all numbers
			String newText = in.replaceAll("0", "").replaceAll("1", "")
					.replaceAll("2", "").replaceAll("3", "")
					.replaceAll("4", "").replaceAll("5", "")
					.replaceAll("6", "").replaceAll("7", "")
					.replaceAll("8", "").replaceAll("9", "");

			// generate the non conflicting name
			newText = _getNonConflictingIdentName(newText, namePairs);

			// put as the new pair
			namePairs.put(in, newText);
		}

		for (int i = 0; i < tokenString.size(); i++) {
			ETokenTuple t = tokenString.get(i);
			// check whether t is identifier
			if (t.getType().equals("Identifier")) {
				String text = t.getText();
				// update the name
				t.setText(namePairs.get(text));
			}
		}
	}

	public static void i04CapitalisingAllCharacters(
			ArrayList<ETokenTuple> tokenString) {
		/*
		 * capitalise all chars in identifiers. There is a mechanism to deal
		 * with conflicting names.
		 */

		// get the identifiers
		ArrayList<String> identifiers = _getAllIdentifierNames(tokenString);
		HashMap<String, String> namePairs = new HashMap<>();
		Iterator<String> it = identifiers.iterator();
		while (it.hasNext()) {
			String in = it.next();

			// capitalise
			String newText = in.toUpperCase();

			// generate the non conflicting name
			newText = _getNonConflictingIdentName(newText, namePairs);

			// put as the new pair
			namePairs.put(in, newText);
		}

		for (int i = 0; i < tokenString.size(); i++) {
			ETokenTuple t = tokenString.get(i);
			// check whether t is identifier
			if (t.getType().equals("Identifier")) {
				String text = t.getText();
				// update the name
				t.setText(namePairs.get(text));
			}
		}
	}

	public static void i05DecapitalisingAllCharacters(
			ArrayList<ETokenTuple> tokenString) {
		/*
		 * decapitalise all chars in identifiers. There is a mechanism to deal
		 * with conflicting names.
		 */

		// get the identifiers
		ArrayList<String> identifiers = _getAllIdentifierNames(tokenString);
		HashMap<String, String> namePairs = new HashMap<>();
		Iterator<String> it = identifiers.iterator();
		while (it.hasNext()) {
			String in = it.next();

			// decapitalise
			String newText = in.toLowerCase();

			// generate the non conflicting name
			newText = _getNonConflictingIdentName(newText, namePairs);

			// put as the new pair
			namePairs.put(in, newText);
		}

		for (int i = 0; i < tokenString.size(); i++) {
			ETokenTuple t = tokenString.get(i);
			// check whether t is identifier
			if (t.getType().equals("Identifier")) {
				String text = t.getText();
				// update the name
				t.setText(namePairs.get(text));
			}
		}
	}

	public static void i06replacingSubWordTransitionsfromthis_is_vartothisIsVar(
			ArrayList<ETokenTuple> tokenString) {
		/*
		 * replacing all identifiers' sub-word transitions from underscore to
		 * next character capitalisation (e.g., 'this_is_var' to 'thisIsVar').
		 * There is a mechanism to deal with conflicting names.
		 */

		// get the identifiers
		ArrayList<String> identifiers = _getAllIdentifierNames(tokenString);
		HashMap<String, String> namePairs = new HashMap<>();
		Iterator<String> it = identifiers.iterator();
		while (it.hasNext()) {
			String in = it.next();

			// tokenise the text
			ArrayList<String> subwords = tokenizeIdentifier(in);

			// remove each _ and capitalise the next token
			// exclude the last token from consideration
			for (int j = 0; j < subwords.size()-1; j++) {
				String c = subwords.get(j);
				if (c.equals("_")) {
					// remove the token
					subwords.remove(j);
					
					// capitalise the first char of the next sub word
					String nextSubWord = subwords.get(j);
					String firstchar = (nextSubWord.charAt(0) + "");
					// capitalise if it is not the first word
					if (j != 0)
						firstchar = firstchar.toUpperCase();
					// merge to remaining characters
					nextSubWord = firstchar + nextSubWord.substring(1);
					subwords.set(j, nextSubWord);
				}
			}

			// create the new identifier
			String newText = "";
			for (int j = 0; j < subwords.size(); j++) {
				newText += subwords.get(j);
			}

			// generate the non conflicting name
			newText = _getNonConflictingIdentName(newText, namePairs);

			// put as the new pair
			namePairs.put(in, newText);
		}

		// update the identifier names
		for (int i = 0; i < tokenString.size(); i++) {
			ETokenTuple t = tokenString.get(i);
			// check whether t is identifier
			if (t.getType().equals("Identifier")) {
				String text = t.getText();
				// update the name
				t.setText(namePairs.get(text));
			}
		}
	}

	public static void i07replacingSubWordTransitionsfromthisIsVartothis_is_var(
			ArrayList<ETokenTuple> tokenString) {
		/*
		 * replacing all identifiers' sub-word transitions from next character
		 * capitalisation to underscore (e.g., 'thisIsVar' to 'this_is_var').
		 * There is a mechanism to deal with conflicting names.
		 */

		// get the identifiers
		ArrayList<String> identifiers = _getAllIdentifierNames(tokenString);
		HashMap<String, String> namePairs = new HashMap<>();
		Iterator<String> it = identifiers.iterator();
		while (it.hasNext()) {
			String in = it.next();

			// tokenise the text
			ArrayList<String> subwords = tokenizeIdentifier(in);

			// for each two adjacent non underscore words, put underscore
			// between them
			for (int j = 0; j < subwords.size(); j++) {
				String c = subwords.get(j);
				if (j > 0) {
					String prev = subwords.get(j - 1);
					if (!c.equals("_") && !prev.equals("_")) {
						// add an underscore
						subwords.add(j, "_");
						j++;
						// lowercase the c word
						subwords.set(j, c.toLowerCase());
					}
				}
			}

			// create the new identifier
			String newText = "";
			for (int j = 0; j < subwords.size(); j++) {
				newText += subwords.get(j);
			}

			// generate the non conflicting name
			newText = _getNonConflictingIdentName(newText, namePairs);

			// put as the new pair
			namePairs.put(in, newText);
		}

		// update the identifier names
		for (int i = 0; i < tokenString.size(); i++) {
			ETokenTuple t = tokenString.get(i);
			// check whether t is identifier
			if (t.getType().equals("Identifier")) {
				String text = t.getText();
				// update the name
				t.setText(namePairs.get(text));
			}
		}
	}

	public static void i08KeepingOnlyTheFirstCharacter(
			ArrayList<ETokenTuple> tokenString) {
		/*
		 * shorten the identifiers by only keeping the first char There is a
		 * mechanism to deal with conflicting names.
		 */

		// get the identifiers
		ArrayList<String> identifiers = _getAllIdentifierNames(tokenString);
		HashMap<String, String> namePairs = new HashMap<>();
		Iterator<String> it = identifiers.iterator();
		while (it.hasNext()) {
			String in = it.next();

			// get the first char only
			String newText = (in.charAt(0) + "");

			// generate the non conflicting name
			newText = _getNonConflictingIdentName(newText, namePairs);

			// put as the new pair
			namePairs.put(in, newText);
		}

		for (int i = 0; i < tokenString.size(); i++) {
			ETokenTuple t = tokenString.get(i);
			// check whether t is identifier
			if (t.getType().equals("Identifier")) {
				String text = t.getText();
				// update the name
				t.setText(namePairs.get(text));
			}
		}
	}

	public static void i09KeepingOnlyTheConsonants(
			ArrayList<ETokenTuple> tokenString) {
		/*
		 * shorten the identifiers by only keeping the consonants as acronyms.
		 * Per subword, the vocal is only stored if it is the first char. There
		 * is a mechanism to deal with conflicting names.
		 */

		// get the identifiers
		ArrayList<String> identifiers = _getAllIdentifierNames(tokenString);
		HashMap<String, String> namePairs = new HashMap<>();
		Iterator<String> it = identifiers.iterator();
		while (it.hasNext()) {
			String in = it.next();

			// tokenise the text
			ArrayList<String> subwords = tokenizeIdentifier(in);

			// for each subword
			for (int j = 0; j < subwords.size(); j++) {
				String s = subwords.get(j);
				// remove all vocals
				String newText = "";
				for (int k = 0; k < s.length(); k++) {
					char c = s.charAt(k);

					// if it is vocal in non-first pos, skip
					if ((c == 'a' || c == 'A' || c == 'i' || c == 'I'
							|| c == 'u' || c == 'U' || c == 'e' || c == 'E'
							|| c == 'o' || c == 'O')
							&& k != 0)
						continue;

					newText += c;
				}

				// store the text as the updated sub word
				subwords.set(j, newText);
			}

			// create the new identifier
			String newText = "";
			for (int j = 0; j < subwords.size(); j++) {
				newText += subwords.get(j);
			}

			// generate the non conflicting name
			newText = _getNonConflictingIdentName(newText, namePairs);

			// put as the new pair
			namePairs.put(in, newText);
		}

		for (int i = 0; i < tokenString.size(); i++) {
			ETokenTuple t = tokenString.get(i);
			// check whether t is identifier
			if (t.getType().equals("Identifier")) {
				String text = t.getText();
				// update the name
				t.setText(namePairs.get(text));
			}
		}
	}

	public static void i10AnonymisingAllIdentifiers(
			ArrayList<ETokenTuple> tokenString, String language) {
		/*
		 * anonymise all identifiers by renaming them as anonymisedIdent. The
		 * vocal is only stored if it is the first char. There is a mechanism to
		 * deal with conflicting names.
		 */

		// get the identifiers
		ArrayList<String> identifiers = _getAllIdentifierNames(tokenString);
		HashMap<String, String> namePairs = new HashMap<>();
		Iterator<String> it = identifiers.iterator();
		while (it.hasNext()) {
			String in = it.next();

			// get the new name
			String newText = "anonymisedIdent";
			if (language.equals("id"))
				newText = "identifierAnonim";

			// generate the non conflicting name
			newText = _getNonConflictingIdentName(newText, namePairs);

			// put as the new pair
			namePairs.put(in, newText);
		}

		for (int i = 0; i < tokenString.size(); i++) {
			ETokenTuple t = tokenString.get(i);
			// check whether t is identifier
			if (t.getType().equals("Identifier")) {
				String text = t.getText();
				// update the name
				t.setText(namePairs.get(text));
			}
		}
	}

}
