| `letters` | Nomor nullable unik saat draf, jenis/status enum, pemohon/subjek, timestamp, path/hash dokumen dan snapshot JSON immutable saat terbit |
| `letter_number_counters` | Nomor urut server-side unik per jenis surat dan tahun, diperbarui di dalam transaction |
Signature di `storage/app/private/signatures/{user_id}/...`; lampiran dan PDF final di disk private. Database hanya menyimpan metadata. Versi menyimpan MIME `image/png`, ukuran, waktu, path unik, dan SHA-256. Surat menyimpan snapshot JSON semua data yang dirender, foreign key versi, snapshot path/hash/nama/jabatan/waktu, serta hash PDF. PDF surat terbit direproduksi dari snapshot, bukan profil aktif. File versi lama tidak dihapus selama dirujuk.
# Rancangan Database

## Koneksi dan Kondisi Awal

Runtime terverifikasi PostgreSQL 18.3, database `wsf_mail`, schema `public`; `.env.example` menyediakan SQLite. Katalog tidak menemukan tabel user-defined atau migration ledger sebelum pekerjaan ini. Database tidak di-reset dan data tidak dihapus.

Migration scaffold tetap dipertahankan untuk users, password reset, sessions, cache, jobs, batches, dan failed jobs. Migration baru hanya menambah domain.

## Tabel Domain

| Tabel | Fungsi dan constraint |
| --- | --- |
| `departments` | Kode dan nama unik, status aktif |
| `positions` | Jabatan per departemen; kode/nama unik per departemen |
| `users` (perluasan) | Username/NIK nullable unik, telepon, departemen/jabatan, lima role, status aktif |
| `letters` | Nomor nullable unik saat draf, jenis/status enum, pemohon/subjek, timestamp, path/hash dokumen |
| `leave_letters` | Detail cuti 1:1; jenis cuti string sampai kebijakan tersedia |
| `mutation_letters` | Departemen/jabatan asal-tujuan dan tanggal efektif |
| `warning_letters` | Tingkat opsional, kejadian, uraian, dasar, masa berlaku |
| `letter_approvals` | Tahap berurutan unik per surat, role dan approver |
| `letter_histories` | Status asal/tujuan, aktor, deskripsi dan metadata JSON |
| `letter_attachments` | Disk private, path, pengunggah, MIME, ukuran dan SHA-256 |
| `user_signatures` | Versi PNG, path private unik, ukuran/hash/waktu/status aktif |
| `letter_signatures` | Urutan signer, versi signature dan snapshot identitas/path/hash |
| `audit_logs` | Aktor, event, subject polymorphic, metadata minimal, IP/agent |
| `notifications` | Tabel kompatibel dengan database notification Laravel |

## Integritas

- Foreign key menghubungkan user, organisasi, surat, detail, approval, history, attachment, signature, audit; penghapusan yang merusak riwayat memakai `RESTRICT`.
- Unique constraint melindungi email (scaffold), username/NIK bila diisi, nomor surat, kode/nama organisasi, versi signature per pemilik, nomor tahap dan urutan signer.
- Indeks mendukung jenis/status, pemohon/subjek, approver/status, timeline, event dan status signature.
- Enum Laravel menjadi kolom string dengan constraint nilai yang diizinkan pada driver yang diperiksa; PHP enum adalah sumber daftar nilai.
- Rentang tanggal, satu signature aktif, detail cocok dengan jenis surat, dan transisi status harus divalidasi service/request pada tahap berikutnya.

## Signature, PDF, dan Lampiran

Signature di `storage/app/private/signatures/{user_id}/...`; lampiran dan PDF final di disk private. Database hanya menyimpan metadata. Versi menyimpan MIME `image/png`, ukuran, waktu, path unik, dan SHA-256. Surat menyimpan foreign key versi, snapshot path/hash/nama/jabatan/waktu, serta hash PDF. File versi lama tidak dihapus selama dirujuk.

## Nomor Surat

`LetterNumberService` mengalokasikan nomor dari `letter_number_counters` secara transaksional dan row-lock, dengan unique key jenis/tahun sebagai pertahanan terhadap nomor ganda. Format dan prefix dikonfigurasi lewat `config/letters.php`; service disiapkan untuk dipanggil pada penerbitan setelah persetujuan di STEP 4.

## Data yang Belum Ditentukan

Tidak ada seed organisasi/akun, jenis cuti, kategori peringatan, aturan hari kerja, prefix nomor, atau retensi tanpa sumber kebijakan perusahaan.