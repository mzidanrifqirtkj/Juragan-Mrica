# Panduan Singkat Petani

Panduan ini ditujukan untuk pengguna dengan role `petani`.

## Login

Buka halaman login:

```text
http://127.0.0.1:8000/admin
```

Anda bisa login menggunakan:

1. `username`
2. atau `email`

Contoh username:

```text
budi
```

## Menu yang Bisa Diakses

Secara umum, role `petani` hanya bisa mengakses:

1. `Dashboard`
2. `Setoran`
3. `Profil Saya`

Menu lain seperti `Petani`, `Pengguna`, `Laporan`, `Penyimpanan`, dan `Pindah ke Gudang` tidak ditampilkan untuk petani.

## Dashboard

Di dashboard, petani bisa melihat ringkasan data miliknya sendiri, seperti:

1. jumlah setoran
2. nilai setoran
3. transaksi yang menunggu pembayaran
4. transaksi yang sudah dibayar
5. riwayat setoran terbaru
6. grafik setoran pribadi

## Setoran

Menu `Setoran` untuk petani hanya menampilkan data setoran yang terkait dengan akun petani tersebut.

Petani tidak bisa:

1. melihat setoran petani lain
2. menambah data setoran sendiri
3. mengubah data setoran
4. menghapus data setoran

## Profil Saya

Menu `Profil Saya` ada di kanan atas.

Di halaman ini, petani bisa:

1. melihat nama akun
2. melihat username
3. melihat email jika ada
4. mengubah nama akun
5. mengubah username
6. mengubah email
7. mengubah password

Selain itu, petani juga bisa melihat profil petani yang tertaut, seperti:

1. kode petani
2. nama petani
3. telepon
4. alamat
5. catatan

Catatan: data profil petani saat ini hanya bisa dilihat, bukan diubah langsung oleh petani.

## Jika Data Tidak Muncul

Jika setoran tidak muncul atau dashboard kosong, minta admin memeriksa:

1. akun Anda sudah aktif
2. akun Anda sudah ditautkan ke `Profil Petani`
3. role akun Anda benar-benar `petani`

## Jika Lupa Password

Hubungi admin atau owner untuk dibuatkan password baru.

## Tips Penggunaan

1. gunakan username yang mudah diingat
2. simpan password di tempat aman
3. jangan bagikan akun ke orang lain
4. jika ada data yang tidak sesuai, laporkan ke admin
