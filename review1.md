# Review STEP 1

## Ringkasan

Fondasi analisis, workflow/permission, enum/model shared, migration domain, dan dokumentasi STEP 1 ditambahkan pada Laravel yang sudah tersedia. STEP 1 saja yang dikerjakan.

## Riwayat

- Proyek adalah scaffold Laravel yang sudah ada dan tersambung ke remote Git.
- Koneksi runtime PostgreSQL 18.3, database `wsf_mail`.
- Sebelum pekerjaan ini, katalog tidak menunjukkan tabel user-defined di schema publik atau migration ledger.

## Teknologi yang Digunakan

- Laravel 12.69.3, PHP CLI 8.2.12, PostgreSQL 18.3.
- Blade, Vite 7, Tailwind CSS 4, Axios.
- Paket langsung Laravel Framework dan Tinker; belum ada paket auth, RBAC, atau PDF.

## File Dibuat

- `ARCHITECTURE.md`, `DATABASE.md`, `PERMISSIONS.md`, `WORKFLOW.md`.
- Enum `UserRole`, `LetterType`, `LetterStatus`, `ApprovalStatus`.
- Model organisasi, surat/detail, approval/history, attachment, audit, signature dan snapshot di `app/Models/`.
- Lima migration domain bertanggal 2026-09-30.
- `tests/Feature/StepOneFoundationTest.php`.

## File Diubah

- `app/Models/User.php`: profil, role cast, dan relasi; role serta data organisasi tidak mass assignable.
- `app/Models/Letter.php` dan model server-managed: status/nomor/snapshot, approval, audit, signature, dan metadata file hanya dapat diisi service.
- `database/seeders/DatabaseSeeder.php`: menghentikan pembuatan akun contoh otomatis.

## Database

Skema domain meliputi organisasi, tiga surat, alur persetujuan, riwayat, lampiran, audit, notifikasi, versi signature, snapshot signer, dan snapshot isi surat terbit. Foreign key, indeks, dan unique constraint tersedia; migration scaffold tidak dihapus.
Migration scaffold dan lima migration domain berhasil diterapkan secara aditif ke PostgreSQL. Schema publik memiliki 22 tabel dan saat verifikasi `users` serta `letters` berisi 0 record. Tidak ada reset atau penghapusan data.

## Backend

Enum dan relasi Eloquent dasar tersedia. Login, middleware, policy, request, workflow service, signer resolver, number service, dan PDF service belum dibuat karena termasuk STEP berikutnya.

## Frontend

Tidak ada UI yang diubah. Scaffold Blade/Vite/Tailwind dan welcome page berbahasa Inggris masih seperti semula.

## Keamanan

Dokumentasi menetapkan least privilege, private storage, otorisasi server-side, maker-checker dan audit minimal. Field role/organisasi/workflow/signature server-managed dikeluarkan dari mass assignment. Pengamanan endpoint/file belum diimplementasikan atau diuji.

## Profil

Skema mendukung username, NIK, telepon, departemen, jabatan. Role/status tidak boleh diubah melalui profil; controller/Form Request belum dibuat.

## Tanda Tangan

Skema menyimpan versi, MIME, ukuran, path, hash dan snapshot signer. Validasi PNG dan `SignatureService` belum dibuat.

## Integrasi PDF

Belum ada engine atau template. Database menyediakan metadata path/hash dan snapshot signer; penerbitan PDF belum dibuat.

## Pengujian

Baseline sebelum perubahan: 2 test passed. Setelah perubahan, seluruh 5 test lulus dengan 30 assertion. PHP baru/diubah lolos lint dan Pint; test schema berjalan memakai SQLite in-memory.

## Masalah

- `php artisan db:show` gagal memformat angka karena ekstensi PHP `intl` tidak aktif.
- `db:show` melaporkan 24 tabel, sementara katalog tidak melihat relasi user-defined pada schema publik dan ledger migrasi belum ada.
- Runtime menggunakan PostgreSQL, `.env.example` menggunakan SQLite.
- Kebijakan perusahaan terkait workflow belum diberikan.

## Solusi

Memeriksa metadata katalog tanpa membaca data pribadi, mempertahankan migration scaffold, tidak membuat seed rekaan, dan mendokumentasikan keputusan sementara beserta hal yang menunggu pengesahan.

## Keputusan Teknis

Lihat `ARCHITECTURE.md`, `DATABASE.md`, `PERMISSIONS.md`, dan `WORKFLOW.md`. Baseline menempatkan keputusan substantif di HRD, penerbitan di OA/OC, serta tinjauan PM terbatas pada unit Produk. Pemilik perusahaan perlu menyetujui aturan.

## Kondisi Saat Ini

Fondasi dan dokumentasi STEP 1 tersedia; delapan migration tercatat `Ran` pada PostgreSQL lokal. Tidak ada project atau migration lama yang dihapus.

## Diketahui Belum Selesai

- Konfirmasi kebijakan/cakupan PM, HRD dan OA/OC.
- Autentikasi/RBAC/profile termasuk STEP 2; proses surat, workflow, dan PDF termasuk STEP 3-4.

## TODO Berikutnya

Menunggu instruksi user untuk STEP 2 setelah verifikasi STEP 1.

## Verifikasi
- `php artisan migrate`: delapan migration tercatat `Ran`; schema publik memiliki 22 tabel dan nol record `users`/`letters` saat diperiksa.

- `php artisan --version`: Laravel Framework 12.69.3.
- `php -v`: PHP 8.2.12.
- `php artisan route:list`: 4 route scaffold, tanpa route auth/domain.
- `php artisan test` baseline: 2 passed.
- `php artisan migrate --pretend`: SQL scaffold dan migration domain tergenerate.