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
        <h1>SURAT PERMOHONAN CUTI</h1>
        <p>Nomor: {{ $letter->letter_number ?? 'Belum ditetapkan' }}</p>
    </header>
    <p>Dengan hormat,</p>
    <p>Yang bertanda tangan di bawah ini mengajukan permohonan cuti dengan rincian:</p>
    <table>
        <tr><td>Nama</td><td>: {{ $letter->subject?->name ?? '-' }}</td></tr>
        <tr><td>NIK</td><td>: {{ $letter->subject?->nik ?? '-' }}</td></tr>
        <tr><td>Departemen</td><td>: {{ $letter->subject?->department?->name ?? '-' }}</td></tr>
        <tr><td>Jabatan</td><td>: {{ $letter->subject?->position?->name ?? '-' }}</td></tr>
        <tr><td>Jenis cuti</td><td>: {{ $letter->leaveDetails?->leave_type ?? '-' }}</td></tr>
        <tr><td>Tanggal</td><td>: {{ $letter->leaveDetails?->starts_on?->format('d/m/Y') }} sampai {{ $letter->leaveDetails?->ends_on?->format('d/m/Y') }}</td></tr>
        <tr><td>Jumlah hari</td><td>: {{ $letter->leaveDetails?->total_days ?? '-' }}</td></tr>
        <tr><td>Alasan</td><td>: {{ $letter->reason ?? '-' }}</td></tr>
        <tr><td>Catatan</td><td>: {{ $letter->leaveDetails?->details ?? '-' }}</td></tr>
        <tr><td>Alamat selama cuti</td><td>: {{ $letter->leaveDetails?->return_address ?? '-' }}</td></tr>
    </table>
    <p>Dokumen ini merupakan pratinjau dan belum menjadi surat resmi.</p>
    <div class="signature">Penanda tangan ditetapkan melalui alur persetujuan.</div>
</body>
</html>