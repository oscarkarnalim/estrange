package p5.educationalstrange;

import java.io.File;
import java.io.FileInputStream;
import java.io.FileOutputStream;
import java.io.FileWriter;
import java.io.IOException;
import java.io.InputStream;
import java.nio.charset.Charset;
import java.util.ArrayList;
import java.util.Enumeration;
import java.util.Scanner;
import java.util.zip.ZipEntry;
import java.util.zip.ZipFile;
import java.util.zip.ZipOutputStream;

public class ZipManipulation {
	private static void unzip(String zipFileDir, String zipFileName, String unzipDir, String ext) {
		// copied and modified from
		// http://tutorials.jenkov.com/java-zip/zipfile.html#unzipping-all-entries-in-zipfile
		/*
		 * extract given zipfile to unzipdir
		 */
		String zipFilePath = zipFileDir + File.separator + zipFileName;
		try {
			Charset CP437 = Charset.forName("CP437");
			ZipFile zipFile = new ZipFile(zipFilePath, CP437);

			Enumeration<? extends ZipEntry> entries = zipFile.entries();

			while (entries.hasMoreElements()) {
				ZipEntry entry = entries.nextElement();
				if (entry.isDirectory()) {
					String destPath = unzipDir + File.separator + entry.getName();
					File file = new File(destPath);
					file.mkdirs();
				} else {
					// this is mac files, ignore
					if (entry.getName().contains("__MACOSX"))
						continue;

					// if the entry is placed under several non-existing subdirectories
					if (entry.getName().contains("/") || entry.getName().contains("\\")) {

						// set the separator. This cannot be based on File.separator given that it
						// depends on how the zip has been created
						String separator = "/";
						if (entry.getName().contains("\\"))
							separator = "\\";

						// get remaining file path whose subdirectories need to be created
						String remainingDirPath = entry.getName();
						// mark what subdirectories that have been created
						String createdDirPath = unzipDir;

						// while remainingDirPath still needs some subdirectories created
						while (remainingDirPath.contains(separator)) {

							// update the marker
							createdDirPath = createdDirPath + File.separator
									+ remainingDirPath.substring(0, remainingDirPath.indexOf(separator));

							// create the subdirectory
							File file = new File(createdDirPath);
							file.mkdirs();

							// shorten the remaining file path
							remainingDirPath = remainingDirPath.substring(remainingDirPath.indexOf(separator) + 1);
						}
					}

					String destPath = unzipDir + File.separator + entry.getName();
					if (destPath.endsWith(ext)) {
						InputStream inputStream = zipFile.getInputStream(entry);
						FileOutputStream outputStream = new FileOutputStream(destPath);
						int data = inputStream.read();
						while (data != -1) {
							outputStream.write(data);
							data = inputStream.read();
						}
						outputStream.close();
					}
				}
			}
			zipFile.close();
		} catch (IOException e) {
			e.printStackTrace();
		}
	}

	private static ArrayList<String> unzipAndGetAllFilePaths(String zipFileDir, String zipFileName, String unzipDir,
			String ext) {
		// copied and modified from
		// http://tutorials.jenkov.com/java-zip/zipfile.html#unzipping-all-entries-in-zipfile

		/*
		 * extract given zipfile to unzipdir and get all the filepaths
		 */

		ArrayList<String> filePaths = new ArrayList<String>();

		String zipFilePath = zipFileDir + File.separator + zipFileName;
		try {
			Charset CP437 = Charset.forName("CP437");
			ZipFile zipFile = new ZipFile(zipFilePath, CP437);

			Enumeration<? extends ZipEntry> entries = zipFile.entries();

			while (entries.hasMoreElements()) {
				ZipEntry entry = entries.nextElement();
				if (entry.isDirectory()) {
					String destPath = unzipDir + File.separator + entry.getName();
					File file = new File(destPath);
					file.mkdirs();
				} else {
					// this is mac files, ignore
					if (entry.getName().contains("__MACOSX"))
						continue;

					// if the entry is placed under several non-existing subdirectories
					if (entry.getName().contains("/") || entry.getName().contains("\\")) {

						// set the separator. This cannot be based on File.separator given that it
						// depends on how the zip has been created
						String separator = "/";
						if (entry.getName().contains("\\"))
							separator = "\\";

						// get remaining file path whose subdirectories need to be created
						String remainingDirPath = entry.getName();
						// mark what subdirectories that have been created
						String createdDirPath = unzipDir;

						// while remainingDirPath still needs some subdirectories created
						while (remainingDirPath.contains(separator)) {

							// update the marker
							createdDirPath = createdDirPath + File.separator
									+ remainingDirPath.substring(0, remainingDirPath.indexOf(separator));

							// create the subdirectory
							File file = new File(createdDirPath);
							file.mkdirs();

							// shorten the remaining file path
							remainingDirPath = remainingDirPath.substring(remainingDirPath.indexOf(separator) + 1);
						}
					}

					String destPath = unzipDir + File.separator + entry.getName();
					if (destPath.endsWith(ext)) {
						InputStream inputStream = zipFile.getInputStream(entry);
						FileOutputStream outputStream = new FileOutputStream(destPath);
						int data = inputStream.read();
						while (data != -1) {
							outputStream.write(data);
							data = inputStream.read();
						}
						outputStream.close();

						// add destPath to the list
						filePaths.add(destPath);
					}
				}
			}
			zipFile.close();
		} catch (IOException e) {
			e.printStackTrace();
		}

		return filePaths;
	}

	public static String mergeAllFilesInZip(String zipPath, String temporaryDirRootPath, String ext) {
		/*
		 * unzip the files, merge all the contents, and return the path of the result.
		 */

		int pos = zipPath.lastIndexOf(File.separator);
		String zipName = zipPath.substring(pos + 1);
		String zipDirPath = zipPath.substring(0, pos);

		String mergedPath = zipDirPath + File.separator + "[unzipped] " + zipName;

		// if the unzipped form does not exist, create the new one
		if (new File(mergedPath).exists() == false) {

			// create a temporary directory to unzip
			File unzipDir = new File(temporaryDirRootPath + zipName);
			unzipDir.mkdir();

			// get all the file paths
			ArrayList<String> filepaths = extractZipAndGetCodeFiles(zipPath, temporaryDirRootPath, ext);

			// merge them
			try {
				FileWriter fw = new FileWriter(new File(mergedPath));
				for (int j = 0; j < filepaths.size(); j++) {
					// for each file
					File sfile = new File(filepaths.get(j));
					
					// write the path of the file as a comment
					String path = "Filepath: '"
							+ sfile.getAbsolutePath().substring(unzipDir.getAbsolutePath().length() + 1) + "'";

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
					}else if (ext.endsWith("txt")) {
						String pattern = "# ";
						for (int i = 0; i < path.length(); i++)
							pattern += "=";
						pattern += " #" + System.lineSeparator();

						fw.write(pattern);
						fw.write("# " + path + " #" + System.lineSeparator());
						fw.write(pattern);
					}

					// write the content of the file
					Scanner sc = new Scanner(sfile);
					while (sc.hasNextLine()) {
						fw.write(sc.nextLine() + System.lineSeparator());
					}
					sc.close();
				}
				fw.close();
			} catch (Exception e) {
				e.printStackTrace();
			}

			// delete all file in the temp directory
			deleteAllTemporaryFiles(unzipDir);
		}

		return mergedPath;
	}

	public static ArrayList<String> extractZipAndGetCodeFiles(String zipPath, String temporaryDirRootPath, String ext) {
		/*
		 * unzip the files and return paths of all code files
		 */

		int pos = zipPath.lastIndexOf(File.separator);
		String zipName = zipPath.substring(pos + 1);
		String zipDirPath = zipPath.substring(0, pos);

		// create a temporary directory to unzip
		File unzipDir = new File(temporaryDirRootPath + zipName);
		unzipDir.mkdir();
		// unzip and return all the filepaths
		return unzipAndGetAllFilePaths(zipDirPath, zipName, unzipDir.getAbsolutePath(), ext);
	}

	public static void deleteAllTemporaryFiles(File f) {
		// recursively delete all files
		if (f.isDirectory()) {
			File[] children = f.listFiles();
			for (File c : children) {
				deleteAllTemporaryFiles(c);
			}
		}
		f.delete();
	}
	
	// copied and adapted from https://www.baeldung.com/java-compress-and-uncompress
	public static void zipFile(String sourceFilePath, String targetFilepath) throws IOException{
		// create a zip file from a directory
		FileOutputStream fos = new FileOutputStream(targetFilepath);
        ZipOutputStream zipOut = new ZipOutputStream(fos);
        File fileToZip = new File(sourceFilePath);
        zipFile(fileToZip, fileToZip.getName(), zipOut);
        zipOut.close();
        fos.close();
	}
	
	// copied and adapted from https://www.baeldung.com/java-compress-and-uncompress
    private static void zipFile(File fileToZip, String fileName, ZipOutputStream zipOut) throws IOException {
        if (fileToZip.isHidden()) {
            return;
        }
        if (fileToZip.isDirectory()) {
            if (fileName.endsWith("/")) {
                zipOut.putNextEntry(new ZipEntry(fileName));
                zipOut.closeEntry();
            } else {
                zipOut.putNextEntry(new ZipEntry(fileName + "/"));
                zipOut.closeEntry();
            }
            File[] children = fileToZip.listFiles();
            for (File childFile : children) {
                zipFile(childFile, fileName + "/" + childFile.getName(), zipOut);
            }
            return;
        }
        FileInputStream fis = new FileInputStream(fileToZip);
        ZipEntry zipEntry = new ZipEntry(fileName);
        zipOut.putNextEntry(zipEntry);
        byte[] bytes = new byte[1024];
        int length;
        while ((length = fis.read(bytes)) >= 0) {
            zipOut.write(bytes, 0, length);
        }
        fis.close();
    }
}
