package p5.educationalstrange;

import java.io.File;
import java.io.FileWriter;
import java.util.Scanner;

public class ECodeMerger {

	public static boolean isGeneratedFromCodeMerging(String s, String extension) {
		if (extension.toLowerCase().endsWith("java")) {
			if (s.matches("/\\* =+ \\*/"))
				return true;
			else if (s.matches("/\\* Filepath: '.+' \\*/"))
				return true;
			else
				return false;
		} else {
			// python
			if (s.matches("# =+ #"))
				return true;
			else if (s.matches("# Filepath: '.+' #"))
				return true;
			else
				return false;
		}
	}

	private static void mergeCode(File inputDirFile, String outputFiepath, String ext) {
		/*
		 * Copied from P3 CodeMerger. This merges all files with a particular extension
		 * from given inputDirFilepath and store them in outputFilepath
		 */
		try {
			FileWriter fw = new FileWriter(new File(outputFiepath));
			mergeSourceCodeFiles(inputDirFile, ext, fw, inputDirFile.getAbsolutePath().length());
			fw.close();
		} catch (Exception e) {
			e.printStackTrace();
		}
	}

	private static void mergeSourceCodeFiles(File sfile, String ext, FileWriter fw, int studentDirPathLength) {
		if (sfile.isDirectory()) {
			File[] schildren = sfile.listFiles();
			for (File sc : schildren) {
				mergeSourceCodeFiles(sc, ext, fw, studentDirPathLength);
			}
		} else {
			String name = sfile.getName();
			// if the file does not end with the extension, ignore
			if (name.endsWith(ext) == false)
				return;

			// read the file and write it in filewriter
			try {
				// write the path of the file as a comment
				String path = "Filepath: '" + sfile.getAbsolutePath().substring(studentDirPathLength + 1) + "'";

				// begin a comment
				if (ext.endsWith("java")) {
					String pattern = "/* ";
					for (int i = 0; i < path.length(); i++)
						pattern += "=";
					pattern += " */" + System.lineSeparator();

					fw.write(pattern);
					fw.write("/* " + path + " */" + System.lineSeparator());
					fw.write(pattern);
				} else if (ext.endsWith("py")) {
					String pattern = "# ";
					for (int i = 0; i < path.length(); i++)
						pattern += "=";
					pattern += " #" + System.lineSeparator();

					fw.write(pattern);
					fw.write("# " + path + " #" + System.lineSeparator());
					fw.write(pattern);
				}

				Scanner sc = new Scanner(sfile);
				while (sc.hasNextLine()) {
					fw.write(sc.nextLine() + System.lineSeparator());
				}
				sc.close();
			} catch (Exception e) {
				e.printStackTrace();
			}
		}
	}
}
