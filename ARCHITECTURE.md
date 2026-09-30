# Arsitektur Aplikasi

## Kondisi Awal

- Laravel Framework 12.69.3, PHP requirement `^8.2`, runtime PHP CLI 8.2.12.
- Paket langsung: Laravel Framework dan Tinker. Tidak ada scaffolding auth, RBAC, maupun PDF.
- Blade, Vite 7, Tailwind CSS 4, Axios; halaman awal masih welcome scaffold.
- Route aplikasi hanya `/`; middleware kustom, policy, controller domain, dan service domain belum ada.
- Model User dan tiga migration masih scaffold Laravel. Test awal hanya contoh feature dan unit bawaan.

## Keputusan

- Pertahankan monolith Laravel 12, Blade/Vite/Tailwind, Eloquent, Form Request, Policy, serta service seperlunya.
- Model tetap shared di `app/Models`, enum domain di `app/Enums`; tidak membuat model per role.
- Lima role tetap di `users.role`; gunakan Policy, middleware, dan scope organisasi. Tidak menambah package RBAC atau tabel permission dinamis untuk role tetap.
- Surat umum disimpan di `letters`, dengan detail 1:1 untuk cuti, mutasi, dan peringatan.
- Approval berurutan di `letter_approvals`; transisi di `letter_histories`; audit keamanan dipisahkan.
- Metadata/hash file ada di database, file ada di private storage.
- Signature memiliki versi/path unik. `letter_signatures` merekam relasi versi beserta snapshot nama/jabatan/path/hash. `letters.issued_snapshot` membekukan data yang dirender agar surat lama tidak berubah ketika profil atau signature diperbarui.
- Nomor surat nullable selama draf dan unik saat diterbitkan; alokasi transactional melalui service akan dibuat pada tahap surat.
- Nomor surat nullable selama draf dan unik saat diterbitkan; `LetterNumberService` mengalokasikan nomor dari counter jenis/tahun secara transaksional dan dipanggil saat penerbitan pada STEP 4.

## Penempatan

Pertahankan struktur Laravel saat ini. Controller dan request kelak dapat dikelompokkan untuk `Admin`, `ProductManager`, `HrdManager`, `OaOcManager`, dan `Karyawan`; layanan lintas peran di `Services/Shared`. Route bisnis dipisah setelah kebutuhan autentikasi siap. PDF direncanakan pada `resources/views/shared/letters/pdf`; belum ada PDF engine dan tidak ada paket dipilih pada STEP 1.

## Keamanan

- `User.role`, departemen, dan jabatan tidak mass assignable; perubahan administratif harus lewat alur admin terotorisasi.
- Nomor/status/snapshot surat, approval, audit, signature, dan metadata file hanya dapat ditetapkan service.
- Penghapusan organisasi, pengguna, signer, dan uploader yang masih direferensikan dibatasi foreign key.
- Server menentukan signer dari workflow; pemohon tidak mengirim ID signer/signature.
- Signature, lampiran, dan PDF tidak dipublikasikan melalui storage link; akses melalui controller ber-policy.
- SHA-256 mendeteksi perubahan, bukan pengganti backup, penyimpanan immutable, otorisasi, atau tanda tangan digital tersertifikasi.

## Batasan

- Guard autentikasi Laravel tersedia, tetapi route/UI login belum dibuat.
- Session/cache/queue default menggunakan database. `.env.example` menggunakan SQLite, runtime lokal menggunakan PostgreSQL.
- Kebijakan cuti, PM, mutasi, disiplin, retensi, nomor surat, dan signer resmi belum diberikan.
- `db:show` melaporkan 24 tabel namun membutuhkan ekstensi `intl`; katalog PostgreSQL tidak menunjukkan tabel user-defined di schema publik, dan migration ledger awalnya tidak ada. `migrate --pretend` membuat ledger kosong tetapi tidak menjalankan skema domain.