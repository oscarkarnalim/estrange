package support.ir;

//taken from IRPlag project

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Iterator;

public class VSM {

	public static double getVSMRetrievalSimilarity(HashMap<String, Double> data1, HashMap<String, Double> data2) {
		/*
		 * adapt the concept of cosine similarity in VSM retrieval mechanism
		 */

		// define which one is pattern and which one is text
		HashMap<String, Double> text, pattern;
		if (data1.size() < data2.size()) {
			pattern = data1;
			text = data2;
		} else {
			pattern = data2;
			text = data1;
		}

		// calculate dq
		double dq = 0;
		Iterator<String> it = pattern.keySet().iterator();
		while (it.hasNext()) {
			String key = it.next();
			Double s1 = pattern.get(key);
			if (s1 == null)
				s1 = 0d;
			Double s2 = text.get(key);
			if (s2 == null)
				s2 = 0d;
			dq += (s1 * s2);
		}

		// calculate d2
		double d2 = 0;
		Iterator<Double> iti = text.values().iterator();
		while (iti.hasNext()) {
			Double val = iti.next();
			d2 += (val * val);
		}

		// calculate q2
		double q2 = 0;
		iti = pattern.values().iterator();
		while (iti.hasNext()) {
			Double val = iti.next();
			q2 += (val * val);
		}

		// pembagi
		double bawah = Math.sqrt(d2 * q2);

		if (bawah == 0)
			return 0;
		else
			return (dq / bawah);
	}

	public static ArrayList<FrequencyTuple> getMatchedAttributes(HashMap<String, Double> data1,
			HashMap<String, Double> data2) {
		// this method returns matched attributes that shared by both indexes

		// define which one is pattern and which one is text
		HashMap<String, Double> text, pattern;
		if (data1.size() < data2.size()) {
			pattern = data1;
			text = data2;
		} else {
			pattern = data2;
			text = data1;
		}

		// create the result container
		ArrayList<FrequencyTuple> result = new ArrayList<FrequencyTuple>(pattern.size());

		// get the intersection between data1 and data2
		Iterator<String> it = pattern.keySet().iterator();
		while (it.hasNext()) {
			String key = it.next();
			Double s1 = pattern.get(key);
			if (s1 == null)
				s1 = 0d;
			Double s2 = text.get(key);
			if (s2 == null)
				s2 = 0d;

			// check whether the intersection is more than 0
			double min = Math.min(s1, s2);
			if (min != 0) {
				// if not zero, add it as a new tuple
				FrequencyTuple t = new FrequencyTuple(key, min);
				result.add(t);
			}
		}

		return result;
	}

	/*
	 * Returns similarity value for token of text A and B using average
	 * similarity
	 */
	public static double calcAverageSimilarity(ArrayList<FrequencyTuple> intersected, HashMap<String, Integer> data1, HashMap<String, Integer> data2) {
		double similarity = ((double) (2 * coverage(intersected)) / (double) (coverage(data1) + coverage(data2)));
		return similarity;
	}

	/*
	 * Returns similarity value for token of text A and B using maximum
	 * similarity
	 */
	public static double calcMaximumSimilarity(ArrayList<FrequencyTuple> intersected, HashMap<String, Integer> data1, HashMap<String, Integer> data2) {
		double similarity = ((double) coverage(intersected) / (double) Math.min(coverage(data1), coverage(data2)));
		return similarity;
	}

	/*
	 * Sum of length of all tiles
	 */
	public static int coverage(ArrayList<FrequencyTuple> intersected) {
		int accu = 0;
		Iterator<FrequencyTuple> vit = intersected.iterator();
		while(vit.hasNext())
			accu += vit.next().frequency;
		return accu;
	}
	
	public static int coverage(HashMap<String, Integer> in) {
		int accu = 0;
		Iterator<Integer> vit = in.values().iterator();
		while(vit.hasNext())
			accu += vit.next();
		return accu;
	}
}
