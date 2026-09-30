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
        <h1>SURAT PERINGATAN</h1>
        <p>Nomor: {{ $letter->letter_number ?? 'Belum ditetapkan' }}</p>
    </header>
    <p>Dokumen ini adalah pratinjau untuk pemeriksaan internal dan bukan keputusan final.</p>
    <table>
        <tr><td>Nama</td><td>: {{ $letter->subject?->name ?? '-' }}</td></tr>
        <tr><td>NIK</td><td>: {{ $letter->subject?->nik ?? '-' }}</td></tr>
        <tr><td>Departemen</td><td>: {{ $letter->subject?->department?->name ?? '-' }}</td></tr>
        <tr><td>Jabatan</td><td>: {{ $letter->subject?->position?->name ?? '-' }}</td></tr>
        <tr><td>Tingkat peringatan</td><td>: {{ $letter->warningDetails?->warning_level ?? 'Belum ditetapkan' }}</td></tr>
        <tr><td>Tanggal kejadian</td><td>: {{ $letter->warningDetails?->offense_on?->format('d/m/Y') ?? '-' }}</td></tr>
        <tr><td>Uraian</td><td>: {{ $letter->warningDetails?->description ?? '-' }}</td></tr>
        <tr><td>Dasar</td><td>: {{ $letter->warningDetails?->legal_basis ?? '-' }}</td></tr>
        <tr><td>Masa berlaku</td><td>: {{ $letter->warningDetails?->valid_from?->format('d/m/Y') ?? '-' }} sampai {{ $letter->warningDetails?->valid_until?->format('d/m/Y') ?? '-' }}</td></tr>
    </table>
    <p>Dokumen ini belum disetujui, ditandatangani, atau diterbitkan.</p>
    <div class="signature">Penanda tangan ditetapkan melalui alur persetujuan.</div>
</body>
</html>