# Warung Setor

Panduan singkat untuk menjalankan dan mengoperasikan aplikasi `Warung Setor`.

## Dokumen Panduan

Dokumen yang tersedia:

1. `README.md`
Ini fokus ke setup project, instalasi, akun default, role, dan gambaran fitur.

2. `PANDUAN-OPERASIONAL.md`
Ini fokus ke cara mengoperasikan aplikasi untuk owner, admin, dan operator harian.

3. `PANDUAN-PETANI.md`
Ini fokus ke panduan singkat untuk pengguna role `petani`.

4. `SOP-ADMIN-OPERATOR.md`
Ini fokus ke SOP kerja harian admin/operator agar alur operasional konsisten.

## Ringkasan

Warung Setor adalah aplikasi internal berbasis Laravel 12 dan Filament 4 untuk mengelola:

- data petani
- setoran lada dari petani
- perpindahan stok ke gudang / pasar / eceran
- penyimpanan dan histori stok
- laporan
- akun pengguna dan hak akses per role

## Teknologi

- Laravel 12
- Filament 4
- Livewire 3
- Spatie Laravel Permission
- MySQL

## Persiapan

Pastikan kebutuhan berikut tersedia:

- PHP 8.2+
- Composer
- Node.js dan npm
- MySQL

## Instalasi

1. Install dependency PHP.

```bash
composer install
```

2. Install dependency frontend.

```bash
npm install
```

3. Siapkan file environment.

```bash
cp .env.example .env
php artisan key:generate
```

4. Atur koneksi database di `.env`.

Contoh:

```env
APP_NAME="Warung Setor"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

APP_LOCALE=id
APP_FALLBACK_LOCALE=id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=warung_setor
DB_USERNAME=root
DB_PASSWORD=
```

5. Jalankan migration.

```bash
php artisan migrate
```

6. Isi data awal.

```bash
php artisan db:seed
```

7. Jalankan aplikasi.

```bash
php artisan serve
```

8. Jalankan Vite jika diperlukan.

```bash
npm run dev
```

## Akses Aplikasi

URL panel admin:

```text
http://127.0.0.1:8000/admin
```

## Akun Default Seeder

Setelah `db:seed`, akun contoh yang tersedia:

1. Owner

- username: `owner`
- email: `owner@warungsetor.test`
- password: `12345678`

2. Admin

- username: `admin`
- email: `admin@warungsetor.test`
- password: `12345678`

3. Petani

- username: `petani1`
- email: `petani@warungsetor.test`
- password: `12345678`

Login bisa memakai:

- `username`
- atau `email`

Contoh di form login:

- `owner`
- `admin`
- `petani1`

## Role Pengguna

Role yang dipakai saat ini:

1. `owner`
2. `admin`
3. `petani`

### Owner

Umumnya memiliki akses penuh, termasuk:

- dashboard
- setoran
- penjualan / pindah ke gudang
- penyimpanan
- laporan
- petani
- pengguna
- role dan fitur

### Admin

Umumnya mengelola operasional harian:

- dashboard
- setoran
- penjualan / pindah ke gudang
- penyimpanan
- laporan
- petani
- pengguna jika diizinkan oleh setting role

### Petani

Akses petani dibatasi ke data miliknya sendiri.

Secara default:

- bisa login
- bisa melihat dashboard pribadi
- bisa melihat data setoran miliknya sendiri
- tidak bisa melihat daftar petani lain
- tidak bisa melihat pengguna
- tidak bisa melihat laporan global
- tidak bisa melihat penyimpanan
- tidak bisa melihat penjualan global

## Login dan Profile

### Login

Form login menggunakan satu input:

- `Username atau Email`

Artinya user bisa login memakai:

- username pendek seperti `budi`
- atau email bila tersedia

### Profil Saya

Menu `Profil Saya` tersedia di user menu kanan atas.

Semua role bisa:

- mengubah nama
- mengubah username
- mengubah email jika ingin diisi
- mengubah password

Untuk role `petani`, halaman profile juga menampilkan:

- kode petani
- nama petani
- telepon
- alamat
- catatan

Data profil petani saat ini bersifat baca saja.

## Alur Operasional Utama

### 1. Membuat Data Petani

Masuk ke menu:

- `Petani`

Lalu:

1. klik `Tambah Petani`
2. isi data petani
3. simpan

### 2. Membuat Akun Login untuk Petani

Ada 2 cara.

#### Cara A: Dari menu Pengguna

Masuk ke menu:

- `Pengguna`

Lalu:

1. klik `Tambah Pengguna`
2. isi:
   - nama lengkap
   - username
   - email jika perlu
   - password
   - role = `Petani`
   - pilih `Profil Petani`
3. simpan

#### Cara B: Dari data Petani

Masuk ke menu:

- `Petani`

Lalu:

1. buka detail atau daftar petani
2. klik tombol `Buatkan Akun`
3. form `Tambah Pengguna` akan otomatis terisi:
   - nama
   - username saran otomatis
   - role = `Petani`
   - profil petani sudah terpilih
4. isi password
5. isi email jika perlu
6. simpan

Tombol `Buatkan Akun` hanya muncul jika:

- petani belum punya akun user
- user yang login punya izin membuat pengguna

### 3. Mencatat Setoran

Masuk ke menu:

- `Setoran`

Lalu:

1. klik `Input Setoran`
2. pilih petani
3. isi berat
4. isi harga per kg
5. cek total otomatis
6. tentukan status pembayaran
7. simpan

Setelah disimpan:

- stok akan bertambah
- histori setoran tersimpan

### 4. Memindahkan / Menjual Stok

Masuk ke menu:

- `Pindah ke Gudang`

Lalu:

1. klik `Buat Penjualan`
2. pilih tujuan:
   - gudang
   - pasar
   - eceran
3. isi berat
4. isi harga per kg
5. isi nama pembeli / tujuan jika perlu
6. simpan

Setelah disimpan:

- stok akan berkurang
- histori perpindahan / penjualan tersimpan

### 5. Melihat Penyimpanan

Masuk ke menu:

- `Penyimpanan`

Halaman ini menampilkan:

- stok masuk
- stok keluar
- sumber transaksi
- saldo stok berjalan

### 6. Melihat Laporan

Masuk ke menu:

- `Laporan`

Gunakan filter periode untuk melihat:

- total pembelian
- total penjualan
- laba kotor
- top petani
- distribusi kanal penjualan
- trend harian

## Hak Akses dan Role Fitur

Menu:

- `Role & Fitur`

dipakai untuk mengatur hak akses per role.

Halaman ini hanya bisa diakses oleh `owner`.

Di halaman ini, owner dapat mengatur permission untuk:

- dashboard
- setoran
- pindah ke gudang
- penyimpanan
- laporan
- petani
- pengguna
- role & fitur

Jenis akses yang bisa diatur meliputi:

- lihat menu & halaman
- tambah data
- ubah data
- hapus data
- aksi khusus

## Aturan Khusus Role Petani

Role `petani` bekerja berdasarkan relasi:

- `users.farmer_id -> farmers.id`

Artinya akun petani harus ditautkan ke satu profil petani.

Jika akun `petani` belum ditautkan:

- dashboard akan menampilkan peringatan
- data setoran pribadi belum bisa dibaca dengan benar

Karena itu, pastikan field `Profil Petani` selalu diisi saat membuat user role `petani`.

## Pengoperasian Harian yang Disarankan

Urutan kerja paling aman:

1. input / update data petani
2. buatkan akun login petani bila diperlukan
3. catat setoran harian
4. cek stok di dashboard dan penyimpanan
5. lakukan penjualan / perpindahan stok saat diperlukan
6. cek laporan periodik

## Catatan Penting

1. `email` sekarang tidak wajib diisi.
2. `username` wajib diisi dan harus unik.
3. login bisa memakai `username` atau `email`.
4. untuk petani, lebih baik gunakan username sederhana yang mudah diingat.

Contoh username yang disarankan:

- `budi`
- `siti`
- `joko`
- `budi2` jika nama sudah dipakai

## Troubleshooting

### 1. Petani tidak bisa melihat data setorannya

Periksa:

1. user role = `petani`
2. user punya `Profil Petani`
3. permission role `petani` masih mengizinkan `transactions.view`

### 2. Tombol `Buatkan Akun` tidak muncul

Periksa:

1. user yang login punya izin `users.create`
2. petani tersebut belum punya akun user
3. migration terbaru sudah dijalankan

### 3. Login gagal

Periksa:

1. username atau email benar
2. password benar
3. status user aktif

### 4. Fitur baru tidak muncul sesuai perubahan role

Periksa:

1. permission role di menu `Role & Fitur`
2. user memang punya role yang benar
3. logout lalu login ulang

## Perintah Penting

Menjalankan server:

```bash
php artisan serve
```

Migration:

```bash
php artisan migrate
```

Seeder:

```bash
php artisan db:seed
```

Lihat route:

```bash
php artisan route:list
```

## Penutup

Jika ada perubahan alur bisnis, README ini sebaiknya ikut diperbarui, terutama pada bagian:

- role dan permission
- alur pembuatan akun petani
- login username/email
- batasan akses role `petani`
