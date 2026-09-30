@extends('layouts.app')

@section('title', $letter->title)

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-200 pb-5">
        <div>
            <p class="text-sm text-slate-600">{{ $letter->type->label() }} · {{ $letter->status->label() }}</p>
            <h1 class="mt-1 text-2xl font-semibold">{{ $letter->title }}</h1>
            <p class="mt-1 text-sm text-slate-600">Nomor: {{ $letter->letter_number ?? 'Belum ditetapkan' }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @can('update', $letter)
                <a class="rounded border border-slate-300 bg-white px-3 py-2 text-sm font-medium" href="{{ route('letters.edit', $letter) }}">Ubah draf</a>
            @endcan
            @can('previewPdf', $letter)
                <a class="rounded border border-slate-300 bg-white px-3 py-2 text-sm font-medium" href="{{ route('letters.preview', $letter) }}" target="_blank" rel="noopener">Pratinjau PDF</a>
            @endcan
        </div>
    </div>

    <section class="border-b border-slate-200 py-6">
        <h2 class="text-lg font-semibold">Informasi Surat</h2>
        <dl class="mt-4 grid gap-4 sm:grid-cols-2">
            <div><dt class="text-sm text-slate-600">Pemohon</dt><dd class="mt-1 font-medium">{{ $letter->requester->name }}</dd></div>
            <div><dt class="text-sm text-slate-600">Karyawan</dt><dd class="mt-1 font-medium">{{ $letter->subject?->name ?? '-' }}</dd></div>
            <div><dt class="text-sm text-slate-600">Alasan</dt><dd class="mt-1">{{ $letter->reason ?? '-' }}</dd></div>
            @if ($letter->leaveDetails)
                <div><dt class="text-sm text-slate-600">Jenis cuti</dt><dd class="mt-1">{{ $letter->leaveDetails->leave_type }}</dd></div>
                <div><dt class="text-sm text-slate-600">Tanggal</dt><dd class="mt-1">{{ $letter->leaveDetails->starts_on->format('d/m/Y') }} sampai {{ $letter->leaveDetails->ends_on->format('d/m/Y') }}</dd></div>
                <div><dt class="text-sm text-slate-600">Jumlah hari</dt><dd class="mt-1">{{ $letter->leaveDetails->total_days }}</dd></div>
            @elseif ($letter->mutationDetails)
                <div><dt class="text-sm text-slate-600">Perpindahan</dt><dd class="mt-1">{{ $letter->mutationDetails->fromDepartment?->name ?? '-' }} / {{ $letter->mutationDetails->fromPosition?->name ?? '-' }} ke {{ $letter->mutationDetails->toDepartment?->name ?? '-' }} / {{ $letter->mutationDetails->toPosition?->name ?? '-' }}</dd></div>
                <div><dt class="text-sm text-slate-600">Tanggal berlaku</dt><dd class="mt-1">{{ $letter->mutationDetails->effective_on->format('d/m/Y') }}</dd></div>
            @elseif ($letter->warningDetails)
                <div><dt class="text-sm text-slate-600">Tingkat peringatan</dt><dd class="mt-1">{{ $letter->warningDetails->warning_level ?? 'Belum ditetapkan' }}</dd></div>
                <div class="sm:col-span-2"><dt class="text-sm text-slate-600">Uraian</dt><dd class="mt-1 whitespace-pre-line">{{ $letter->warningDetails->description }}</dd></div>
                <div class="sm:col-span-2"><dt class="text-sm text-slate-600">Dasar</dt><dd class="mt-1 whitespace-pre-line">{{ $letter->warningDetails->legal_basis ?? '-' }}</dd></div>
            @endif
        </dl>
    </section>

    <section class="border-b border-slate-200 py-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-lg font-semibold">Lampiran</h2>
            @can('addAttachment', $letter)
                <form class="flex flex-wrap items-center gap-2" method="POST" action="{{ route('letters.attachments.store', $letter) }}" enctype="multipart/form-data">
                    @csrf
                    <label class="sr-only" for="attachment">Pilih lampiran</label>
                    <input class="max-w-xs text-sm" id="attachment" name="attachment" type="file" accept=".pdf,.png,.jpg,.jpeg,application/pdf,image/png,image/jpeg" required>
                    <button class="rounded border border-slate-300 bg-white px-3 py-2 text-sm font-medium" type="submit">Unggah lampiran</button>
                    @error('attachment') <span class="text-sm text-rose-700">{{ $message }}</span> @enderror
                </form>
            @endcan
        </div>
        <ul class="mt-3 divide-y divide-slate-200">
            @forelse ($letter->attachments as $attachment)
                <li class="flex flex-wrap items-center justify-between gap-2 py-3 text-sm">
                    <span>{{ $attachment->original_name }} · {{ number_format($attachment->file_size / 1024, 0) }} KB</span>
                    @can('downloadAttachment', $letter)
                        <a class="underline" href="{{ route('letters.attachments.download', $attachment) }}">Unduh</a>
                    @endcan
                </li>
            @empty
                <li class="py-3 text-sm text-slate-600">Belum ada lampiran.</li>
            @endforelse
        </ul>
    </section>

    <section class="border-b border-slate-200 py-6">
        <h2 class="text-lg font-semibold">Riwayat</h2>
        <ol class="mt-4 space-y-3">
            @forelse ($letter->histories as $history)
                <li class="border-l-2 border-slate-300 pl-4">
                    <p class="font-medium">{{ str($history->event)->replace('_', ' ')->title() }}</p>
                    <p class="text-sm text-slate-600">{{ $history->actor?->name ?? 'Sistem' }} · {{ $history->created_at->format('d/m/Y H:i') }}</p>
                    @if ($history->description)<p class="mt-1 text-sm">{{ $history->description }}</p>@endif
                </li>
            @empty
                <li class="text-sm text-slate-600">Belum ada riwayat.</li>
            @endforelse
        </ol>
    </section>

    @can('submit', $letter)
        <form class="mt-6" method="POST" action="{{ route('letters.submit', $letter) }}">
            @csrf
            <button class="rounded bg-slate-900 px-4 py-2 font-medium text-white" type="submit">Ajukan surat</button>
        </form>
    @endcan
@endsection