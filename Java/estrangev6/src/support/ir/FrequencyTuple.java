package support.ir;

// taken from IRPlag project

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Iterator;
import java.util.Map.Entry;

public class FrequencyTuple {
	public String content;
	public double frequency;

	public FrequencyTuple(String content, double frequency) {
		super();
		this.content = content;
		this.frequency = frequency;
	}

	public String toString() {
		return content + " | " + frequency;
	}

	public static HashMap<String, Integer> generateAttributeIndex(ArrayList<String> in) {
		// convert given token tuple list to index-like representation

		HashMap<String, Integer> out = new HashMap<String, Integer>();
		for (int i = 0; i < in.size(); i++) {
			// generating the key
			String key = in.get(i);

			// increment the data
			Integer count = out.get(key);
			if (count == null)
				count = 0;
			count++;
			out.put(key, count);
		}
		return out;
	}

	// for handling n-gram
	public static HashMap<String, Double> generateAttributeIndex(ArrayList<String> in, int ngram) {
		// convert given token tuple list to index-like representation

		HashMap<String, Double> out = new HashMap<String, Double>();
		for (int i = 0; i < in.size() - ngram + 1; i++) {
			// get key as a form of ngram
			String key = "";
			for (int j = 0; j < ngram; j++) {
				key = key + in.get(i + j);
				if(j != ngram-1)
					key = key + "|";
			}
			key = key + "";

			// System.out.println(key);

			// increment the data
			Double count = out.get(key);
			if (count == null)
				count = 0d;
			count++;
			out.put(key, count);
		}
		return out;
	}
	
	// only for adding local index to global index
	public static void addLocalToGlobalIndex(HashMap<String, Double> localIndex, HashMap<String, Double> globalIndex){
		Iterator<Entry<String, Double>> it = localIndex.entrySet().iterator();
		while(it.hasNext()){
			Entry<String,Double> cur = it.next();
			
			// add the data
			Double count = globalIndex.get(cur.getKey());
			if (count == null)
				count = 0d;
			count+= cur.getValue();
			globalIndex.put(cur.getKey(), count);
		}
	}
	
	public static void addTermOccurrencesInLocalIndexToIDFIndex(HashMap<String, Double> localIndex, HashMap<String, Integer> tokenIDFIndex){
		// merge terms from local index to idf index
		Iterator<Entry<String, Double>> it = localIndex.entrySet().iterator();
		while(it.hasNext()){
			Entry<String,Double> cur = it.next();
			
			// add the data by one
			Integer count = tokenIDFIndex.get(cur.getKey());
			if (count == null)
				count = 0;
			count++;
			tokenIDFIndex.put(cur.getKey(), count);
		}
	}
}
