package p6.codeclarity;

import java.io.File;
import java.io.FileInputStream;
import java.io.ObjectInputStream;
import java.sql.Connection;
import java.sql.DriverManager;
import java.util.ArrayList;
import java.util.Collections;

import p5.educationalstrange.tuple.ETokenTuple;

public class TempMain {

	public static void main(String[] args) throws Exception {
		// TODO Auto-generated method stub
		
		// Output square and square root of numbers 0-10
		int number = 0;
		double numSquare, num_root;
		String output = "Number Square Root";
		System.out.println(output);
		while (number<=10)
		{ numSquare = number * number;
		  num_root = Math.sqrt(number);
		  output = number + " " + numSquare + " " + num_root;
		  System.out.println(output);
		  number = number + 1;
		}
		

//		String programmingLanguageCode = "java";
//		String languageCode = "id";
//		
//		String dbName = "default_estrange";
//		String username = "root";
//		String password = "";
//		String serverBasePath = "C:\\xampp\\htdocs\\estrange\\";
//
//		String internalRepSubmissionsPath = serverBasePath + "internal_rep_submissions" + File.separator;
//		String filepath = serverBasePath + "1597274564.8647.code";
//
//		String submissionID = "2";
//		String publicSuspicionID = "7n81597274596509Uy6";
//
//		// remaining boolean arguments
//
//		Connection connect = DriverManager.getConnection(
//				"jdbc:mysql://localhost/" + dbName + "?useLegacyDatetimeCode=false&serverTimezone=UTC", username,
//				password);
//		
//		ArrayList<ArrayList<ETokenTuple>> targetTokenStrings = null;
//		File stringCompFile = new File(internalRepSubmissionsPath + new File(filepath).getName() + ".stringcomp");
//		if (stringCompFile.exists()) {
//			// get from the existing one
//			try {
//				FileInputStream fileIn = new FileInputStream(stringCompFile);
//				ObjectInputStream in = new ObjectInputStream(fileIn);
//				targetTokenStrings = (ArrayList<ArrayList<ETokenTuple>>) in.readObject();
//				in.close();
//				fileIn.close();
//			} catch (Exception e) {
//				e.printStackTrace();
//			}
//
//			// System.out.println(filepath + " is read from existing.");
//		}
//		
//		ArrayList<ETokenTuple> mergedTokenString = new ArrayList<ETokenTuple>();
//		mergedTokenString.addAll(targetTokenStrings.get(0));
//		mergedTokenString.addAll(targetTokenStrings.get(1));
//		Collections.sort(mergedTokenString);
//		
//		// start to process
//		CodeClarityContentGenerator.execute(mergedTokenString,programmingLanguageCode, languageCode, connect,
//				submissionID, publicSuspicionID);
	}
}
