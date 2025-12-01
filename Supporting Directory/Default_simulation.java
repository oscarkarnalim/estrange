import java.util.Scanner;

public class Default_simulation {
	/*
	 * Kode program yang anda kumpulkan terlalu pendek atau berisi konten yang 
	 * umum ditemui pada tugas ini. Karenanya, tidak ada potongan kode yang dapat
	 * digunakan untuk simulasi. Kode dibawah ini dibentuk khusus untuk keperluan
	 * simulasi dan ini BUKAN kode anda.
	 */

	// program utama
	public static void main(String[] args) {
		Scanner sc = new Scanner(System.in);

		// ambil jumlah hello world yang akan ditampilkan
		int numberOfHelloWords;
		// tetap minta masukan jika bilangan yang diberikan tidak positif
		do {
			numberOfHelloWords = sc.nextInt();
		} while (numberOfHelloWords <= 0);

		// pilih mode
		String mode = sc.next();
		// tetap minta masukan jika string yang diberikan bukan "asc" atau "desc"
		while (!mode.equals("asc") && !mode.equals("desc")) {
			mode = sc.next();
		}

		if (mode.equals("asc")) {
			// jika urutan menaik yang dipilih
			for (int i = 0; i < numberOfHelloWords; i++) {
				System.out.println((i + 1) + ". Hello");
			}
		} else {
			// selain itu, tampilkan terurut menurun
			while (numberOfHelloWords > 0) {
				System.out.println(numberOfHelloWords + ". Hello");
				numberOfHelloWords = numberOfHelloWords - 1;
			}
		}

		// tampilkan kata 'dunia'
		System.out.println("w o r l d");

		sc.close();
	}
}