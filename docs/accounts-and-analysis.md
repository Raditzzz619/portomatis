# Akun, dashboard admin, dan analisis Portomatis

Jalankan `php artisan migrate` pada database aplikasi yang sudah dikonfigurasi. Migrasi menambahkan peran akun, draft portofolio, log aktivitas, dan akun admin `admin@gmail.com` / `admin123`. `php artisan db:seed` juga dapat menyediakan akun tersebut jika belum ada, tanpa mereset kata sandi akun yang sudah ada.

## Alur akun

- `/register`: registrasi pengguna dengan persetujuan penyimpanan data. Peran tidak bisa dipilih melalui registrasi.
- `/login`: pengguna diarahkan ke pembuat portofolio; admin diarahkan ke `/admin`.
- Setiap langkah yang berhasil disimpan masuk ke `builder_portfolios`, terkait pemiliknya. Draft dipulihkan setelah login. Daftar “Portofolio tersimpan” dapat membuka draft sebelumnya.
- Membuat portofolio baru mempertahankan draft sebelumnya. Logout mengosongkan sesi browser tanpa menghapus draft database.
- `users.email` hanya email login. `builder_portfolios.content.personal.email` hanya kontak portofolio. Mengedit salah satunya tidak menimpa yang lain.
- Semua pembuat/unduhan portofolio membutuhkan akun pengguna. Admin memiliki akses khusus ke dashboard dan detail. Tautan `/p/{token}` tetap publik hanya setelah pengguna memublikasikan portofolio.
- Data yang sudah dipublikasikan tetap berupa snapshot. Mengedit draft tidak otomatis mengubah snapshot publik.
- CRUD portofolio lama dibatasi untuk admin; endpoint placeholder proyek/keahlian/pengalaman yang tidak digunakan tidak lagi didaftarkan.

## Dashboard admin

Pengguna aktif dihitung dari `COUNT(DISTINCT user_id)` log aktivitas yang terjadi dalam hari, minggu (Senin–Minggu), bulan, dan tahun kalender saat ini. Akun admin dikecualikan. Batas waktu mengikuti `ANALYTICS_TIMEZONE` (default `Asia/Singapore`), dengan penyimpanan timestamp UTC. Statistik tidak merekonstruksi aktivitas sebelum fitur diaktifkan.

Aktivitas meliputi registrasi, login, logout, membuka/menyimpan langkah, membuat/melanjutkan portofolio, pratinjau PDF, unduhan, dan publikasi. Halaman pengguna yang terlihat mengirim heartbeat tiap 60 detik; server menyimpan paling banyak satu heartbeat per menit per akun. “Aktif dalam 5 menit terakhir” berarti memiliki aktivitas dalam interval tersebut, termasuk bila baru saja keluar. Dashboard diperbarui lewat tombol Perbarui data.

Pencarian mencakup nama/email akun dan nama/email portofolio. Karakter `%` dan `_` dicari secara literal. Halaman detail memisahkan identitas akun, semua isi draft/portofolio, hasil analisis, dan riwayat aktivitas. Kata sandi tidak ditampilkan.

## Analisis kompetensi dan bidang pekerjaan

`app/Services/PortfolioInsights.php` mencocokkan kata kunci dengan batas kata, tanpa membedakan huruf besar/kecil, pada tiga sumber: keahlian, posisi/deskripsi pengalaman, serta judul/deskripsi proyek. Nama template dan profesi pada profil tidak menentukan rekomendasi.

Setiap bidang menampilkan kata kunci dan sumber pendukung. Rekomendasi mengutamakan jumlah sumber, kemudian kecocokan kata kunci (maksimal lima kata per sumber; keahlian berbobot 3, pengalaman/proyek berbobot 2). Tiga bidang teratas ditampilkan. Satu sumber diberi label eksplorasi awal. Tidak ada data yang cocok menghasilkan penjelasan data belum mencukupi, tanpa rekomendasi buatan.

Kamus awal mencakup front-end, pengelolaan web, UI/UX, desain grafis, back-end, mobile, data, fotografi, dan pemasaran digital. Hasil adalah indikasi dari teks yang diisi, bukan pengukuran tingkat penguasaan, pemahaman semantik penuh, atau prediksi penerimaan kerja.

## Penilaian PDF dengan SAW

Referensi: Anderias Eko Wijaya dan Dadan Marwan, *Sistem Penentu Penilaian Siswa pada Kurikulum 2013 Menggunakan Algoritma Simple Additive Weighting (SAW) (Studi Kasus SDN Darmaga V Subang)*, JUTISI volume 2 nomor 2, Agustus 2016, hlm. 121–131; rumus hlm. 122, contoh bobot 0,2/0,4/0,4 hlm. 128. Berkas sumber pengguna: `616-Article Text-759-1-10-20180207.pdf`.

Artikel menilai siswa. Portomatis mengadaptasi metode dan pola bobotnya; kriteria portofolio dan ambang kategori berikut adalah rancangan aplikasi, bukan kriteria tervalidasi dari artikel.

Semua kriteria adalah atribut benefit. Setiap kelompok mempunyai lima pemeriksaan biner (terpenuhi = 1, belum = 0), sehingga `x` bernilai 0–5. Sebuah alternatif ideal eksplisit bernilai 5 pada setiap kelompok disertakan sebagai pembanding: `r = x / max(x) = x / 5`. Dengan demikian nilai tidak berubah karena isi portofolio pengguna lain dan portofolio tunggal tidak otomatis bernilai sempurna.

`V = Σ(w × r)` dan `persentase = V × 100%`.

| Kelompok | Bobot | Lima pemeriksaan |
| --- | --- | --- |
| Profil dan kontak | 20% | Nama dan profesi; bio minimal 80 karakter; email kontak valid; telepon/website/LinkedIn; domisili |
| Kompetensi dan pengalaman | 40% | Minimal 3 keahlian unik; pendidikan dengan institusi/program/periode; pengalaman dengan perusahaan/posisi/periode; deskripsi pengalaman minimal 80 karakter; keahlian disebut dalam deskripsi pengalaman/proyek |
| Karya dan bukti proyek | 40% | Minimal 1 proyek; semua deskripsi proyek minimal 80 karakter; semua memiliki URL HTTP(S) valid; semua menjelaskan kontribusi; semua menjelaskan hasil/dampak |

Deteksi kontribusi/dampak memakai kosakata dalam service. Saran muncul untuk setiap pemeriksaan yang belum terpenuhi. Kategori: ≥85 sangat baik, ≥70 baik, ≥50 cukup, <50 perlu dilengkapi. Contoh skor mentah 4, 5, 4 menghasilkan `20×4/5 + 40×5/5 + 40×4/5 = 88%`.

Penilaian membaca data draft yang sama dengan PDF yang dihasilkan, bukan file PDF eksternal. Tidak ada upload atau parser PDF tambahan. Skor ini menilai kelengkapan/bukti tertulis, bukan kualitas visual, kebenaran klaim, keaslian karya, atau kelayakan profesional. Hasil dan rincian perhitungan terlihat pada langkah “Pratinjau & selesai”.

## Verifikasi

- `php artisan test --compact`: unit SAW/rekomendasi, autentikasi, otorisasi, pemisahan email, pencarian, batas kalender, persistensi draft, serta regresi builder/PDF/domisili.
- `npm.cmd run build` pada Windows PowerShell (atau `npm run build` pada shell lain).
- `node tests/Browser/accounts-analysis.cjs` dengan server terisolasi (default `127.0.0.1:8011`) dan Chrome CDP (default `127.0.0.1:9224`). Gunakan `PORTOMATIS_BASE_URL`/`PORTOMATIS_CDP_URL` untuk mengganti alamat. Jalankan hanya pada database pengujian: skrip mendaftarkan akun QA, membuat dan memublikasikan portofolio. Memeriksa alur pengguna/admin, skor, PDF asli, pencarian, serta lebar 320/375/768/1440 piksel.
