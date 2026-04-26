# Panduan Operasional Warung Setor

Panduan ini ditujukan untuk owner, admin, atau operator harian yang memakai aplikasi `Warung Setor`.

## Masuk ke Aplikasi

Buka:

```text
http://127.0.0.1:8000/admin
```

Login bisa menggunakan:

- `username`
- atau `email`

Contoh:

- `owner`
- `admin`
- `petani1`

## Menu Utama

Menu yang tersedia tergantung role pengguna.

Secara umum menu yang ada:

1. `Dashboard`
2. `Setoran`
3. `Pindah ke Gudang`
4. `Penyimpanan`
5. `Laporan`
6. `Petani`
7. `Pengguna`
8. `Role & Fitur`
9. `Profil Saya`

## Alur Kerja Harian

Urutan kerja yang disarankan:

1. pastikan data petani sudah ada
2. buatkan akun petani jika diperlukan
3. input setoran harian
4. cek stok di dashboard / penyimpanan
5. lakukan penjualan atau perpindahan stok jika diperlukan
6. cek laporan berkala

## 1. Menambahkan Data Petani

Masuk ke menu:

- `Petani`

Langkah:

1. klik `Tambah Petani`
2. isi:
   - nama lengkap
   - nomor telepon
   - alamat
   - catatan jika ada
3. klik simpan

Setelah tersimpan, petani bisa langsung dipakai saat input setoran.

## 2. Membuat Akun Login untuk Petani

Ada 2 cara.

### Cara A: Dari menu Pengguna

Masuk ke:

- `Pengguna`

Langkah:

1. klik `Tambah Pengguna`
2. isi:
   - nama lengkap
   - username
   - email jika ada
   - password
   - role = `Petani`
   - pilih `Profil Petani`
3. simpan

### Cara B: Dari menu Petani

Cara ini lebih praktis.

Masuk ke:

- `Petani`

Langkah:

1. cari data petani
2. klik `Buatkan Akun`
3. sistem akan membuka form `Tambah Pengguna`
4. beberapa data sudah terisi otomatis:
   - nama
   - username saran
   - role = `Petani`
   - profil petani
5. isi password
6. isi email jika perlu
7. simpan

## 3. Input Setoran

Masuk ke:

- `Setoran`

Langkah:

1. klik `Input Setoran`
2. pilih petani
3. isi berat setoran
4. isi harga per kg
5. total akan dihitung otomatis
6. pilih status pembayaran:
   - `Sudah Bayar`
   - `Belum Bayar`
7. isi catatan jika perlu
8. simpan

Hasilnya:

- transaksi setoran tersimpan
- stok gudang otomatis bertambah

## 4. Menandai Setoran Lunas

Masuk ke:

- `Setoran`

Jika ada setoran dengan status `Belum Bayar`:

1. cari transaksi
2. klik `Tandai Lunas`
3. konfirmasi

## 5. Memindahkan / Menjual Stok

Masuk ke:

- `Pindah ke Gudang`

Langkah:

1. klik `Buat Penjualan`
2. pilih tujuan:
   - gudang
   - pasar
   - eceran
3. pilih setoran terkait jika diperlukan
4. isi nama pembeli / tujuan
5. isi berat
6. isi harga per kg
7. total dihitung otomatis
8. simpan

Hasilnya:

- histori penjualan / perpindahan tersimpan
- stok gudang otomatis berkurang

## 6. Melihat Penyimpanan

Masuk ke:

- `Penyimpanan`

Di halaman ini Anda bisa melihat:

- stok masuk
- stok keluar
- sumber transaksi
- kode referensi
- saldo stok berjalan

## 7. Melihat Laporan

Masuk ke:

- `Laporan`

Gunakan filter periode untuk melihat:

1. total pembelian
2. total penjualan
3. laba kotor
4. top petani
5. distribusi penjualan
6. trend harian

## 8. Mengelola Pengguna

Masuk ke:

- `Pengguna`

Di sini admin/owner bisa:

1. menambah pengguna baru
2. mengubah username, email, password, role
3. mengaktifkan / menonaktifkan akun

Catatan:

- `email` boleh kosong
- `username` wajib diisi
- `username` dipakai untuk login

## 9. Mengatur Role dan Fitur

Masuk ke:

- `Role & Fitur`

Halaman ini hanya untuk `owner`.

Di sini owner bisa mengatur akses role terhadap:

1. dashboard
2. setoran
3. pindah ke gudang
4. penyimpanan
5. laporan
6. petani
7. pengguna
8. role & fitur

Jenis akses yang dapat diatur:

1. lihat halaman
2. tambah data
3. ubah data
4. hapus data
5. aksi khusus

## 10. Profil Saya

Menu `Profil Saya` ada di kanan atas.

Semua role bisa:

1. melihat data akun
2. mengubah nama
3. mengubah username
4. mengubah email
5. mengubah password

Khusus role `petani`, halaman ini juga menampilkan:

1. kode petani
2. nama petani
3. telepon
4. alamat
5. catatan

## 11. Aturan Khusus Role Petani

Role `petani` hanya bisa melihat data yang terkait dengan dirinya sendiri.

Secara umum:

1. bisa login
2. bisa melihat dashboard pribadi
3. bisa melihat setoran miliknya sendiri
4. tidak bisa melihat petani lain
5. tidak bisa melihat pengguna lain
6. tidak bisa melihat laporan global

Agar ini bekerja, akun petani harus ditautkan ke `Profil Petani`.

## 12. Tips Penggunaan Username

Untuk user yang tidak terbiasa memakai email, gunakan username yang mudah diingat.

Contoh yang baik:

1. `budi`
2. `siti`
3. `joko`
4. `budi2` jika nama sudah dipakai

Saran:

1. gunakan huruf kecil
2. hindari spasi
3. jangan sering ganti username

## Troubleshooting

### Petani tidak bisa melihat data setorannya

Periksa:

1. role user = `petani`
2. field `Profil Petani` sudah terisi
3. akun aktif

### Tombol Buatkan Akun tidak muncul

Periksa:

1. user yang login punya hak akses membuat pengguna
2. data petani belum punya akun login

### Login gagal

Periksa:

1. username atau email benar
2. password benar
3. akun aktif

### Menu tidak muncul

Periksa:

1. role user
2. pengaturan di `Role & Fitur`
3. logout lalu login kembali

## Penutup

Jika alur kerja berubah, panduan ini sebaiknya ikut diperbarui agar operator harian selalu punya referensi yang sesuai.
