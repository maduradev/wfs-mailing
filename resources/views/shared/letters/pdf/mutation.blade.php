<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 22mm 20mm; }
        body { color: #17212b; font-family: DejaVu Sans, sans-serif; font-size: 11pt; line-height: 1.5; }
        .notice { border: 2px solid #9a3412; color: #9a3412; font-size: 12pt; font-weight: bold; padding: 10px; text-align: center; }
        .heading { border-bottom: 2px solid #17212b; margin: 24px 0; padding-bottom: 12px; text-align: center; }
        .heading h1 { font-size: 16pt; margin: 0; }
        .heading p { margin: 5px 0 0; }
        table { border-collapse: collapse; margin: 20px 0; width: 100%; }
        td { padding: 7px 4px; vertical-align: top; }
        td:first-child { width: 34%; }
        .signature { margin-top: 48px; text-align: right; }
    </style>
</head>
<body>
    <div class="notice">{{ $previewNotice }}</div>
    <header class="heading">
        <h1>SURAT PERMOHONAN MUTASI</h1>
        <p>Nomor: {{ $letter->letter_number ?? 'Belum ditetapkan' }}</p>
    </header>
    <p>Dengan hormat,</p>
    <p>Berikut rincian permohonan mutasi untuk ditinjau:</p>
    <table>
        <tr><td>Nama</td><td>: {{ $letter->subject?->name ?? '-' }}</td></tr>
        <tr><td>NIK</td><td>: {{ $letter->subject?->nik ?? '-' }}</td></tr>
        <tr><td>Departemen asal</td><td>: {{ $letter->mutationDetails?->fromDepartment?->name ?? '-' }}</td></tr>
        <tr><td>Jabatan asal</td><td>: {{ $letter->mutationDetails?->fromPosition?->name ?? '-' }}</td></tr>
        <tr><td>Departemen tujuan</td><td>: {{ $letter->mutationDetails?->toDepartment?->name ?? '-' }}</td></tr>
        <tr><td>Jabatan tujuan</td><td>: {{ $letter->mutationDetails?->toPosition?->name ?? '-' }}</td></tr>
        <tr><td>Tanggal berlaku</td><td>: {{ $letter->mutationDetails?->effective_on?->format('d/m/Y') ?? '-' }}</td></tr>
        <tr><td>Alasan</td><td>: {{ $letter->reason ?? '-' }}</td></tr>
        <tr><td>Catatan</td><td>: {{ $letter->mutationDetails?->details ?? '-' }}</td></tr>
    </table>
    <p>Dokumen ini merupakan pratinjau dan belum menjadi surat resmi.</p>
    <div class="signature">Penanda tangan ditetapkan melalui alur persetujuan.</div>
</body>
</html>