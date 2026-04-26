# SOP Admin dan Operator Harian

Dokumen ini berisi SOP singkat agar penggunaan `Warung Setor` lebih konsisten.

## Tujuan

Menjaga agar proses:

1. pencatatan petani
2. pembuatan akun
3. pencatatan setoran
4. pengelolaan stok
5. pelaporan

berjalan rapi dan tidak saling bertabrakan.

## SOP 1: Menambah Petani Baru

1. Buka menu `Petani`.
2. Klik `Tambah Petani`.
3. Isi data lengkap petani.
4. Simpan.
5. Jika petani perlu login, lanjutkan dengan SOP 2.

## SOP 2: Membuatkan Akun untuk Petani

Disarankan menggunakan tombol `Buatkan Akun` dari menu `Petani`.

Langkah:

1. Buka menu `Petani`.
2. Cari data petani.
3. Klik `Buatkan Akun`.
4. Pastikan form pengguna sudah terisi:
   - nama
   - username saran
   - role `Petani`
   - profil petani
5. Isi password.
6. Isi email jika ada.
7. Simpan.
8. Catat username dan password untuk diberikan ke petani.

## SOP 3: Input Setoran Harian

1. Buka menu `Setoran`.
2. Klik `Input Setoran`.
3. Pilih petani.
4. Isi berat.
5. Isi harga per kg.
6. Cek total otomatis.
7. Pilih status pembayaran.
8. Simpan.

Pastikan satu transaksi dicatat satu kali saja.

## SOP 4: Menandai Pembayaran

Jika pembayaran dilakukan belakangan:

1. buka menu `Setoran`
2. cari transaksi dengan status `Belum Bayar`
3. klik `Tandai Lunas`
4. konfirmasi

## SOP 5: Penjualan / Perpindahan Stok

1. Buka menu `Pindah ke Gudang`.
2. Klik `Buat Penjualan`.
3. Pilih tujuan penjualan.
4. Isi berat dan harga.
5. Simpan.

Sebelum menyimpan, cek bahwa stok cukup.

## SOP 6: Pemeriksaan Harian

Setiap hari, admin/operator disarankan memeriksa:

1. dashboard
2. jumlah setoran hari ini
3. transaksi belum dibayar
4. stok berjalan di menu `Penyimpanan`

## SOP 7: Pemeriksaan Mingguan

Setiap minggu, admin/operator disarankan:

1. cek laporan periodik
2. cek data petani yang belum punya akun jika memang perlu login
3. cek akun pengguna yang tidak aktif
4. cek apakah ada data petani ganda

## SOP 8: Pengelolaan Username Petani

Saat membuat akun petani:

1. gunakan username yang pendek
2. gunakan huruf kecil
3. hindari spasi
4. hindari perubahan username terlalu sering

Contoh yang baik:

1. `budi`
2. `siti`
3. `joko2`

## SOP 9: Role dan Hak Akses

1. `owner` mengatur `Role & Fitur`
2. `admin/operator` tidak mengubah permission role tanpa persetujuan owner
3. akun `petani` wajib ditautkan ke profil petani yang benar

## SOP 10: Jika Ada Masalah

Jika user melaporkan masalah:

1. cek role user
2. cek status aktif user
3. cek username dan password
4. cek tautan `Profil Petani` untuk akun petani
5. cek apakah permission role berubah
6. cek log error aplikasi bila perlu

## Ringkasan Tanggung Jawab

### Owner

1. mengatur role dan fitur
2. memantau keseluruhan operasional
3. memeriksa laporan

### Admin / Operator

1. input data petani
2. buatkan akun petani
3. input setoran
4. update status pembayaran
5. kelola penjualan / perpindahan stok

### Petani

1. login
2. melihat dashboard pribadi
3. melihat data setorannya sendiri
4. melihat profil akunnya sendiri
