# Matriks Hak Akses

Otorisasi harus ditegakkan server-side lewat middleware, policy, Form Request, dan query scope. Role tetap disimpan di enum `UserRole`; pengguna tidak dapat mengubah role sendiri.

| Kemampuan | admin | product-manager | hrd-manager | oa-oc-manager | karyawan |
| --- | --- | --- | --- | --- | --- |
| Akun, role, departemen, jabatan | Kelola | Tidak | Tidak | Tidak | Tidak |
| Profil dan signature sendiri | Ya | Ya | Ya | Ya | Ya |
| Surat Cuti | Konfigurasi saja | Tinjau/komentar unit Produk | Tinjau dan putuskan | Terbitkan setelah disetujui | Draf/ajukan/lihat milik sendiri |
| Surat Mutasi | Konfigurasi saja | Masukan jika Produk terdampak | Inisiasi, tinjau, putuskan | Terbitkan setelah disetujui | Permintaan awal/lihat milik sendiri |
| Surat Peringatan | Konfigurasi saja | Tidak | Buat atau tinjau dengan maker-checker | Terbitkan setelah disetujui | Lihat milik sendiri sesuai kebijakan |
| Nomor/PDF/arsip | Tidak | Tidak | Setujui konten | Terbitkan dan arsipkan | Tidak |
| Audit | Admin identitas terbatas | Perkara dalam cakupan | Perkara dalam tugas | Peristiwa penerbitan berwenang | Surat sendiri |

## Batasan Data

- Admin tidak otomatis dapat membaca isi surat confidential atau menyetujui keputusan bisnis.
- PM hanya melihat surat dalam cakupan unit Produk yang ditetapkan; role saja tidak membuka seluruh surat.
- HRD mengakses perkara untuk tugasnya. Pembuat tidak menyetujui surat yang sama bila maker-checker diwajibkan.
- OA/OC menerbitkan, mendistribusikan, dan mengarsipkan setelah semua persetujuan lengkap.
- Karyawan hanya mengakses data, berkas, dan riwayat miliknya; pembukaan surat peringatan menunggu kebijakan.
- Semua role dapat mengubah field profil yang diizinkan dan signature sendiri, tetapi tidak role, status akun, permission, atau data administratif.
- File private tidak diotorisasi berdasarkan URL/path. Unduh, preview, PDF dan signature harus memeriksa policy.
- Audit hanya menyimpan metadata minimal; jangan merekam password, token, bytes signature, atau rahasia lain.

## Permission Teknis

| Permission | Cakupan |
| --- | --- |
| `users.manage` | CRUD akun/role/departemen/jabatan oleh admin |
| `profile.view-own`, `profile.update-own` | Profil sendiri dengan field terbatas |
| `signature.manage-own`, `signature.view-authorized` | Signature sendiri dan akses untuk proses surat berwenang |
| `letters.create-own`, `letters.update-own-draft`, `letters.submit-own` | Draf dan pengajuan karyawan sendiri |
| `letters.view-own`, `letters.download-own` | Surat/berkas sendiri sesuai status dan kebijakan |
| `letters.review-product-scope` | Tinjauan PM pada unit Produk yang ditetapkan |
| `letters.review-hrd`, `letters.decide-hrd` | Pemeriksaan/keputusan HRD |
| `letters.issue-approved`, `letters.archive-issued` | Penerbitan/arsip OA/OC dengan persetujuan sah |
| `letters.audit-authorized` | Riwayat audit sesuai cakupan peran |

Daftar ini adalah kontrak policy, bukan role atau permission dinamis tambahan. Implementasi dan pengujian endpoint dilakukan pada STEP 2-4.