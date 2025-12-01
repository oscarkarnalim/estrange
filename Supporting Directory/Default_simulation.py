# Kode program yang anda kumpulkan terlalu pendek atau berisi konten yang 
# umum ditemui pada tugas ini. Karenanya, tidak ada potongan kode yang dapat
# digunakan untuk simulasi. Kode dibawah ini dibentuk khusus untuk keperluan
# simulasi dan ini BUKAN kode anda.

# program utama

# ambil jumlah hello world yang akan ditampilkan
numberOfHelloWords = int(input())
# tetap minta masukan jika bilangan yang diberikan tidak positif
while(numberOfHelloWords <= 0):
    numberOfHelloWords = int(input())

# pilih mode
mode = input()
# tetap minta masukan jika string yang diberikan bukan "asc" atau "desc"
while (mode != "asc" and mode !="desc"):
    mode = input()

if (mode == "asc"):
    # jika urutan menaik yang dipilih
    for i  in range(0, numberOfHelloWords, 1):
        print((i + 1), ". Hello");
else:
    # selain itu, tampilkan terurut menurun
    while (numberOfHelloWords > 0):
        print(numberOfHelloWords, ". Hello");
        numberOfHelloWords = numberOfHelloWords - 1;

# tampilkan kata 'dunia'
print("w o r l d");
