# Formulir dan ekspor Portomatis

Jalankan `composer install` setelah mengambil perubahan untuk memasang Dompdf.
Tidak ada migrasi database baru. Formulir berada di `/builder`.

## Domisili

Pengguna memilih provinsi dan kabupaten/kota dari data lokal: 38 provinsi dan
514 wilayah. Pilihan tidak memerlukan jaringan ke layanan peta. Nilai draft lama
tetap dipertahankan sampai pengguna mengganti atau mengosongkannya.

GPS opsional memerlukan HTTPS atau localhost serta izin browser. GPS menawarkan
lima perkiraan wilayah berdasarkan titik referensi. Pengguna harus memilih dan
mengonfirmasi; posisi saat ini tidak otomatis menjadi domisili. Koordinat tidak
dikirim atau disimpan. Sumber, lisensi, dan cara memperbarui data ada di
[resources/data/README.md](../resources/data/README.md).

## Langkah formulir

JavaScript menyimpan data lewat `fetch` dan mengganti isi langkah dengan animasi
geser. Navigasi sidebar dan tombol kembali menyimpan perubahan terlebih dahulu.
Validasi atau kegagalan koneksi mempertahankan isian di formulir. Navigasi riwayat
browser didukung dan animasi mengikuti preferensi pengurangan gerakan pengguna.
Formulir tetap memakai pengiriman HTML biasa bila JavaScript tidak tersedia.

## Dua format hasil

- Website memakai template pilihan, navigasi, dan tombol menuju karya/kontak.
  Unduhan HTML mandiri tetap tersedia di `/builder/download`.
- PDF memakai layout A4 dengan isi teks lengkap, warna aksen template, dan alamat
  tautan tertulis serta dapat diklik. Pratinjau ada di `/builder/pdf`; unduhan ada
  di `/builder/download/pdf`. Semua endpoint mengambil draft sesi pengguna.
- Pratinjau memiliki tab Website dan PDF. Browser yang tidak menampilkan PDF
  tertanam dapat memakai tautan buka tab baru atau unduh.

`app/Services/PortfolioPdf.php` membatasi aset PDF ke foto raster dari draft dan
menonaktifkan pengambilan aset jarak jauh. Template PDF berada di
`resources/views/builder/pdf.blade.php`; template website tetap terpisah.

## Verifikasi

Jalankan pengujian PHP dengan `php artisan test`.

Uji browser memakai Node 22+ dan Chrome lokal dengan remote debugging. Gunakan
profil sementara tersendiri dan server pengujian, lalu jalankan:

```powershell
php artisan serve --host=127.0.0.1 --port=8010
# Di terminal terpisah, setelah Chrome pengujian berjalan pada port 9223:
node tests/Browser/builder-flow.cjs
```

Atur `PORTOMATIS_BASE_URL` dan `PORTOMATIS_CDP_URL` jika memakai port lain.
Uji membuat konteks browser terisolasi, tidak memublikasikan portofolio, dan
menutup konteksnya setelah selesai. Tangkapan layar disimpan dalam direktori
diabaikan Git `storage/framework/testing/browser`.
