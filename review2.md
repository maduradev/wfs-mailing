# Review STEP 2

## Ringkasan

Autentikasi session, RBAC, administrasi pengguna/organisasi, dashboard dasar, profil untuk lima role, perubahan kata sandi, dan signature PNG private berversi telah diimplementasikan. STEP 2 saja yang dikerjakan.

## Riwayat

- STEP 1 dan `review1.md` dibaca sebelum perubahan.
- Proyek tetap Laravel 12.69.3, PHP CLI 8.2.12; database lokal PostgreSQL 18.3.
- Tidak ada migration baru pada STEP 2. Route/file generik untuk serving disk private dinonaktifkan.

## Teknologi yang Digunakan

- Laravel session guard, middleware role, Policy, Form Request, Eloquent, Blade.
- Vite 7, Tailwind CSS 4, Axios; Node dan npm melalui shim `npm.cmd`.
- Locale aplikasi `id` dengan katalog validasi `lang/id/validation.php`.
- Tidak menambah package autentikasi atau RBAC.

## File Dibuat

- `app/Console/Commands/CreateFirstAdmin.php`.
- Controller auth, admin, dashboard, profil bersama, dan adapter profil untuk kelima role.
- Middleware role, Form Request login/admin/profil, `UserPolicy`, `AuditLogService`, dan `SignatureService`.
- View login, layout, dashboard, profil, dan administrasi pengguna/departemen/jabatan.
- `lang/id/validation.php`, `package-lock.json`, dan `tests/Feature/StepTwoAccessProfileTest.php`.

## File Diubah

- `bootstrap/app.php`, `routes/web.php`, base `Controller`, `config/app.php`, `config/filesystems.php`.
- `.env.example` memakai locale Indonesia; pengaturan `.env` lokal juga disetel ke `id` tetapi tetap di-ignore Git.
- `tests/Feature/ExampleTest.php` sekarang memverifikasi guest diarahkan ke login.
- `public/build` adalah hasil build lokal dan tetap di-ignore Git.

## Database

- Skema STEP 1 digunakan tanpa migration tambahan; seluruh migration tetap berstatus `Ran`.
- Pengujian menggunakan SQLite in-memory. Tidak ada akun atau data perusahaan dibuat pada PostgreSQL lokal.
- Role dan status akun dikelola hanya melalui admin; role tidak tersedia pada Form Request profil.
- `AuditLogService` mencatat pembuatan/perubahan akun, profil, kata sandi, dan signature tanpa merekam nilai rahasia.
- `AuditLogService` mencatat pembuatan/perubahan akun, profil, kata sandi, departemen, jabatan, dan signature tanpa merekam nilai rahasia.

## Backend

- Login menerima email atau nama pengguna, menolak akun nonaktif, membatasi lima percobaan per akun/IP dalam satu menit, serta meregenerasi session. Logout menginvalidasi session dan token CSRF.
- Middleware role melindungi route dashboard/profil untuk `admin`, `product-manager`, `hrd-manager`, `oa-oc-manager`, dan `karyawan`.
- Hanya admin dapat membuat/mengubah akun, departemen, dan jabatan. Tidak ada hard-delete; penetapan posisi divalidasi terhadap departemen.
- `app:create-first-admin` membuat administrator pertama secara interaktif, tanpa akun atau kata sandi bawaan.
- Profil membolehkan nama, email, username, NIK, dan telepon; sandi lama wajib benar untuk mengganti sandi. Departemen, jabatan, role, dan status akun tidak dapat diubah dari profil.

## Frontend

- Halaman login, dashboard dasar, profil, pengelolaan pengguna/departemen/jabatan menggunakan Blade dan teks antarmuka Bahasa Indonesia.
- `view:cache` dan `npm.cmd run build` berhasil. Dashboard role masih berupa ringkasan akun, belum dashboard operasional STEP 5.

## Keamanan

- Route dilindungi session auth, CSRF web middleware, middleware role, Policy, dan Form Request.
- Kata sandi tetap memakai cast hashing Laravel; perubahan memvalidasi sandi saat ini dan minimum 12 karakter.
- Percobaan login dibatasi per identifier/IP. Admin tidak dapat mengubah akunnya sendiri melalui CRUD pengguna.
- Signature dibatasi maksimal 2 MiB; extension dan MIME diperiksa, metadata PNG/dimensi diverifikasi sebelum decode GD, lalu gambar harus dapat diproses.
- Batas dimensi signature adalah 64x24 sampai 4096x2048 piksel. File disimpan pada disk lokal private dengan path acak dan SHA-256.
- Setiap unggahan membuat versi baru, menonaktifkan versi sebelumnya tanpa menghapus file, dan mencatat event audit.
- Preview signature memeriksa Policy pemilik, memakai `no-store` dan `nosniff`. Serving URL generik disk private dimatikan; test mengonfirmasi path `/storage/...` tidak tersedia.
- `npm audit --omit=dev`: 0 kerentanan ditemukan.

## Profil

Route profil tersedia untuk seluruh lima role. Pengujian memastikan tiap role dapat membuka profil sendiri dan mengunggah signature sendiri; kolom administratif tampil hanya-baca.

## Tanda Tangan

PNG valid dapat dipratinjau, diganti, dan diberi versi; berkas rusak, JPEG, serta dimensi di luar batas ditolak. Versi lama tetap tersimpan. Akses signature user lain ditolak oleh Policy.

## Integrasi PDF

Belum dibuat; berada pada STEP 3-4. Versi dan snapshot STEP 1 tetap tersedia untuk integrasi berikutnya.

## Pengujian

- `php artisan test`: 13 test passed, 141 assertion.
- `php artisan test`: 13 test passed, 143 assertion.
- `vendor/bin/pint --test`: lulus.
- `php artisan view:cache`: berhasil.
- `npm.cmd run build`: berhasil dengan Vite 7.3.6.
- `php artisan route:list`: login, admin CRUD, dashboard, dan 25 route profil/signature terdaftar.
- `php artisan migrate:status`: seluruh 8 migration `Ran`; tidak ada migration baru.
- Diagnostics VS Code: tidak ada error.

## Masalah

- `npm` PowerShell shim diblokir ExecutionPolicy; `npm.cmd` berhasil digunakan tanpa mengubah policy mesin.
- Test `ExampleTest` scaffold awalnya mengharapkan root 200; perilaku guest kini diarahkan ke login dan test disesuaikan.
- Perintah `php artisan db:show` pada sesi STEP 1 masih membutuhkan ekstensi PHP `intl` untuk pemformatan; status migration diverifikasi terpisah.

## Solusi

Menggunakan session auth Laravel tanpa package baru, menguji alur lewat SQLite in-memory, mematikan route serving generik untuk file private, dan menyediakan command interaktif untuk admin pertama.

## Keputusan Teknis

- Role tetap berasal dari enum STEP 1; tidak ada role atau jenis surat baru.
- Otorisasi profil/signature hanya untuk pemilik; admin memiliki jalur terpisah untuk administrasi akun.
- Audit menyimpan actor, event, subject dan nama field yang berubah, bukan nilai profil/sandi.
- `.env` lokal disetel ke `APP_LOCALE=id`; file tersebut tidak dilacak Git. `.env.example` dan default config juga menggunakan Bahasa Indonesia.

## Kondisi Saat Ini

STEP 2 diimplementasikan dan diverifikasi pada test suite lokal. Perubahan belum di-commit atau di-push. Build `public/build` tersedia lokal tetapi di-ignore.

## Diketahui Belum Selesai

- Command pembuatan admin pertama belum dijalankan; belum ada akun admin pada database lokal.
- Reset kata sandi, verifikasi email, MFA, halaman error khusus Bahasa Indonesia, dan pemeriksaan browser manual belum termasuk atau diverifikasi pada STEP 2.
- Dashboard masing-masing role belum memuat metrik/persuratan; itu termasuk STEP 5.
- Kebijakan cuti, mutasi, peringatan, cakupan Product Manager, dan signer resmi masih menunggu pengesahan perusahaan sesuai `review1.md`.

## TODO Berikutnya

Menunggu instruksi untuk STEP 3. Jangan melanjutkan otomatis.

## Verifikasi

- `php artisan test`: 13 passed (141 assertions).
- `php artisan test`: 13 passed (143 assertions).
- `vendor/bin/pint --test`: passed.
- `php artisan view:cache`: berhasil.
- `npm.cmd run build`: berhasil.
- `npm audit --omit=dev`: 0 kerentanan.
- `php artisan route:list --path=profile` dan `--path=login`: route terdaftar.
- `php artisan migrate:status`: 8 migration berstatus `Ran`.
- `get_errors` pada workspace: tidak menemukan error.