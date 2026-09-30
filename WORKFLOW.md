# Alur Persuratan

## Batasan

Alur ini adalah baseline teknis STEP 1. HRD menjadi pemilik keputusan substantif, sedangkan OA/OC menerbitkan dokumen yang telah disetujui. Pemilik proses harus mengesahkan hak persetujuan dan aturan perusahaan sebelum tindakan bisnis diaktifkan.

## Status dan Transisi

| Status | Transisi | Pelaku utama |
| --- | --- | --- |
| DRAFT | SUBMITTED | Pembuat berwenang |
| SUBMITTED | IN_REVIEW | Sistem setelah validasi |
| IN_REVIEW | REVISION, APPROVED, REJECTED | HRD Manager; masukan PM dapat menjadi tahap konsultasi |
| REVISION | SUBMITTED | Pemohon setelah perbaikan |
| APPROVED | ISSUED | OA/OC Manager setelah pemeriksaan nomor, template, dan signer |
| ISSUED | COMPLETED | Setelah distribusi/penerimaan selesai |
| COMPLETED | ARCHIVED | OA/OC Manager sesuai retensi perusahaan |

`REJECTED` terminal; pengajuan baru dibuat sebagai surat baru dengan referensi riwayat. Setiap transisi wajib merekam aktor, waktu, status asal/tujuan, komentar, dan audit event.

## Surat Cuti

1. Karyawan membuat draf dan mengajukan untuk dirinya.
2. Product Manager memberi tinjauan operasional hanya jika karyawan termasuk cakupan unit Produk yang ditetapkan; ini bukan persetujuan final.
3. HRD Manager memeriksa kebijakan dan kelengkapan, lalu menyetujui, menolak, atau meminta perbaikan.
4. OA/OC Manager menerbitkan surat disetujui dan mengarsipkan setelah distribusi.

Jenis cuti, saldo, hari kerja, dokumen wajib, dan perhitungan durasi belum diketahui; sistem tidak boleh mengasumsikannya.

## Surat Mutasi

1. Karyawan dapat mengajukan permintaan awal. HRD Manager atau Admin berwenang menyiapkan draf atas nama karyawan; karyawan tidak menetapkan perubahan jabatan/departemen.
2. Product Manager memberi masukan jika unit Produk terdampak.
3. HRD Manager lain (bukan pembuat) meninjau dan menyetujui, menolak, atau meminta perbaikan.
4. OA/OC Manager menerbitkan dan mengarsipkan. Pembaruan data organisasi dilakukan terpisah oleh Admin setelah berlaku.

## Surat Peringatan

1. HRD Manager menyiapkan draf setelah dasar dan prosedur dikonfirmasi perusahaan.
2. HRD Manager lain meninjau (maker-checker); pembuat tidak menyetujui suratnya sendiri.
3. OA/OC menerbitkan setelah persetujuan HRD; akses karyawan mengikuti kebijakan perusahaan.
4. Kategori SP dan masa berlaku tidak dibatasi ke SP1/SP2/SP3 tanpa kebijakan tertulis.

## Penandatanganan dan Dokumen Terbit

- Server menentukan signer berdasarkan alur, role, jabatan, departemen, dan persetujuan; pemohon tidak memilih signer/file.
- Penerbitan ditolak jika signature PNG private signer sah tidak tersedia.
- Saat terbit, simpan versi signature, snapshot nama/jabatan, path dan hash SHA-256 file, waktu tanda tangan, serta hash PDF.
- Saat terbit, bekukan seluruh data yang dirender (identitas subjek, detail surat, dan signer) pada snapshot JSON; simpan versi signature, snapshot nama/jabatan, path/hash SHA-256 file, waktu tanda tangan, serta hash PDF.
- Versi lama tidak dihapus selama dirujuk. PDF, signature, dan lampiran hanya dapat diakses melalui otorisasi server.

## Perlu Disahkan Perusahaan

- Cakupan PM dan kondisi tinjauan unit Produk.
- Kebijakan cuti, hitung hari, lampiran, dan pejabat persetujuan.
- Pengusul dan pejabat persetujuan mutasi.
- Prosedur investigasi, hak tanggapan, kategori, masa berlaku, distribusi, serta retensi surat peringatan.
- Format nomor, signer resmi per jenis surat, aturan arsip/retensi, dan kondisi selesai.