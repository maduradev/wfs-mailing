# Review STEP 3

## Ringkasan

Manajemen draft Surat Cuti, Surat Mutasi, dan Surat Peringatan telah diimplementasikan bersama pembatasan akses, riwayat, lampiran private, penomoran transaksional, dan PDF pratinjau unsigned. STEP 3 saja yang dikerjakan.

## Riwayat

- `review1.md` dan `review2.md` dibaca sebelum perubahan.
- Proyek tetap Laravel 12.69.3, PHP CLI 8.2.12, PostgreSQL lokal 18.3.
- STEP 2 commit terakhir berada di `4722c4b`; perubahan STEP 3 saat ini belum di-commit atau di-push.

## Teknologi yang Digunakan

- Eloquent, Form Request, Policy, service, Blade, dan route web Laravel.
- `barryvdh/laravel-dompdf` 3.1.2 untuk PDF pratinjau.
- PostgreSQL 18.3 untuk database lokal; SQLite in-memory untuk feature test.
- Vite 7, Tailwind CSS 4, dan label/validasi Bahasa Indonesia.

## File Dibuat

- `LetterController`, `LetterAttachmentController`, policy surat, dan Form Request create/update/lampiran.
- `LetterService`, `LetterNumberService`, `AttachmentService`, `PdfService`, serta model counter nomor.
- Tiga template: `resources/views/shared/letters/pdf/leave.blade.php`, `mutation.blade.php`, `warning.blade.php`.
- View daftar/filter, form, dan detail surat; migration `letter_number_counters`.
- `tests/Feature/StepThreeLetterServiceTest.php` dan `tests/Feature/StepThreeLetterWorkflowTest.php`.

## File Diubah

- `composer.json` dan `composer.lock`: DomPDF `^3.1`.
- `routes/web.php`, navigasi/layout/dashboard persuratan.
- `config/letters.php`, `DATABASE.md`, dan `ARCHITECTURE.md`.
- `lang/id/validation.php` untuk label/error tanggal dan detail surat.

## Database

- Migration baru membuat counter unik per jenis surat/tahun; seluruh migration aktif berstatus `Ran`.
- Counter surat dibuat dan dikunci dalam transaction. Nomor belum dialokasikan saat draft; service siap dipanggil saat penerbitan STEP 4.
- PostgreSQL saat diperiksa memiliki 0 row pada `letter_number_counters`, `letters`, dan `letter_attachments`. Test menggunakan SQLite in-memory.
- Tidak ada database reset, data existing dihapus, atau surat dibuat pada database lokal.

## Backend

- Karyawan dapat membuat/mengubah draft dan mengajukan Surat Cuti/Mutasi miliknya sendiri; asal mutasi diambil dari profil server.
- HRD dapat membuat tiga jenis draft. Product Manager hanya melihat surat yang terkait departemennya. OA/OC hanya melihat surat yang telah disetujui/diterbitkan/selesai/diarsipkan. Admin tidak mendapat akses isi surat otomatis.
- Submit mengubah status `DRAFT` ke `SUBMITTED` dan mencatat `LetterHistory`; draft yang sudah diajukan tidak dapat diubah.
- Detail cuti/mutasi/peringatan berada pada tabel masing-masing; posisi mutasi divalidasi sesuai departemen.
- Daftar mendukung pencarian case-insensitive, filter tipe/status, eager loading, dan pagination.
- Attachment hanya menerima PDF/PNG/JPEG, maksimum 10 MiB, dengan validasi MIME/extension/content signature, hash, nama berkas aman, dan storage private.
- PDF dibuat sebagai response preview, dicatat audit, serta tidak diberi signature atau nomor final.

## Frontend

- Halaman daftar surat dengan filter/pencarian/pagination; form dinamis per tipe; detail, riwayat, attachment, dan tautan PDF pratinjau.
- UI memakai Bahasa Indonesia dan hanya menampilkan tindakan menurut policy.
- Blade berhasil dikompilasi dan Vite berhasil membangun aset produksi.

## Keamanan

- Akses list/show/create/update/submit/attachment/PDF diperiksa server-side lewat Policy dan Form Request.
- Karyawan tidak dapat mengganti subjek/asal mutasi melalui payload; field server-managed diisi oleh service.
- Lampiran disimpan pada disk `local` private. Download memeriksa Policy surat; URL serving generik untuk disk private tetap dinonaktifkan.
- PDF pratinjau hanya untuk pengguna yang berwenang, dikirim `private, no-store`, dan memiliki banner “belum diterbitkan atau ditandatangani”.
- Audit mencatat pembuatan, perubahan, pengajuan, upload lampiran, dan preview PDF tanpa menyimpan isi file.

## Profil

Profil/role tidak diubah pada STEP 3. Subjek surat ditautkan ke `User`; snapshot final untuk dokumen terbit tetap menunggu proses issuance STEP 4.

## Tanda Tangan

Preview PDF tidak memuat tanda tangan. Resolver signer, validasi kelengkapan signature untuk issuance, dan snapshot signature final belum diaktifkan; itu termasuk STEP 4.

## Integrasi PDF

DomPDF 3.1.2 merender ketiga template sebagai pratinjau A4 unsigned. PDF final, penyimpanan immutable, nomor final, dan pengambilan snapshot saat penerbitan belum dibuat.

## Pengujian

- `php artisan test`: 24 test passed, 237 assertion.
- `vendor/bin/pint --test`: lulus.
- `php artisan view:cache`: berhasil.
- `npm.cmd run build`: berhasil dengan Vite 7.3.6.
- `composer validate --no-check-publish`: composer.json valid.
- `npm.cmd audit --omit=dev`: 0 kerentanan.
- `php artisan migrate:status`: seluruh 9 migration berstatus `Ran`.
- Diagnostics VS Code: tidak ada error.

## Masalah

- Kebijakan cuti, kategori/masa berlaku peringatan, retensi, format nomor resmi, serta signer per jenis surat belum diberikan perusahaan.
- `total_days` pada cuti dicatat dari input; tidak dihitung otomatis karena kalender dan kebijakan cuti belum ditetapkan.
- Penomoran masih memakai prefix/kode default configurable; pemanggilan saat penerbitan belum terintegrasi.

## Solusi

Tidak mengarang aturan perusahaan: jenis cuti dan tingkat peringatan tetap input teks yang akan ditinjau; PDF diberi tanda pratinjau; nomor ditahan sampai penerbitan; aturan yang belum pasti dicatat untuk pengesahan.

## Keputusan Teknis

- Draft hanya memiliki nomor `null`; `LetterNumberService` mengalokasikan nomor transaksional ketika dipakai pada STEP 4.
- Attachment allowlist dibatasi ke PDF, PNG, dan JPEG untuk STEP 3.
- Admin tidak otomatis dapat membaca surat; cakupan PM mengikuti departemen; HRD memiliki akses lintas surat sesuai proses.
- Perubahan workflow persetujuan, notifikasi, resolver signer, dan PDF final ditunda ke STEP 4.

## Kondisi Saat Ini

STEP 3 diimplementasikan dan diverifikasi. Satu migration counter telah diterapkan secara additive ke PostgreSQL. Perubahan lokal belum di-commit atau di-push.

## Diketahui Belum Selesai

- Approval/revisi/penolakan/distribusi/arsip dan notifikasi pada STEP 4.
- Penomoran final saat issuance, signer sah, snapshot PDF/signature, dan PDF final immutable pada STEP 4.
- Pencarian belum memiliki indeks khusus/strategi full-text; saat ini memakai LIKE/ILIKE terparameterisasi.
- Kebijakan perusahaan yang tercantum di `review1.md` masih perlu disahkan.

## TODO Berikutnya

Menunggu instruksi untuk STEP 4. Jangan melanjutkan otomatis.

## Verifikasi

- `php artisan test`: 24 passed (237 assertions).
- `vendor/bin/pint --test`: passed.
- `php artisan view:cache`: berhasil.
- `npm.cmd run build`: berhasil.
- `composer validate --no-check-publish`: valid.
- `npm.cmd audit --omit=dev`: 0 kerentanan.
- `php artisan migrate`: migration counter berhasil diterapkan; status total 9 `Ran`.
- PostgreSQL `letter_number_counters`, `letters`, `letter_attachments`: masing-masing 0 row.
- `get_errors` pada workspace: tidak menemukan error.