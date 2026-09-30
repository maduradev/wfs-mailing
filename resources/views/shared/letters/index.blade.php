@extends('layouts.app')

@section('title', 'Surat')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-5">
        <div>
            <h1 class="text-2xl font-semibold">Persuratan</h1>
            <p class="mt-1 text-sm text-slate-600">Daftar surat sesuai kewenangan akun Anda.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @foreach ($types as $type)
                @can('createType', [App\Models\Letter::class, $type])
                    <a class="rounded bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700" href="{{ route('letters.create', $type->value) }}">Buat {{ $type->label() }}</a>
                @endcan
            @endforeach
        </div>
    </div>

    <form class="mt-5 grid gap-3 border-b border-slate-200 pb-5 sm:grid-cols-4" method="GET" action="{{ route('letters.index') }}">
        <div class="sm:col-span-2">
            <label class="sr-only" for="q">Cari surat</label>
            <input class="w-full rounded border border-slate-300 bg-white px-3 py-2" id="q" name="q" value="{{ $filters['q'] }}" placeholder="Cari judul, nomor, atau karyawan">
        </div>
        <div>
            <label class="sr-only" for="type">Jenis surat</label>
            <select class="w-full rounded border border-slate-300 bg-white px-3 py-2" id="type" name="type">
                <option value="">Semua jenis surat</option>
                @foreach ($types as $type)
                    <option value="{{ $type->value }}" @selected($filters['type'] === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="sr-only" for="status">Status surat</label>
            <select class="w-full rounded border border-slate-300 bg-white px-3 py-2" id="status" name="status">
                <option value="">Semua status</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected($filters['status'] === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2 sm:col-span-4">
            <button class="rounded bg-slate-900 px-4 py-2 text-sm font-medium text-white" type="submit">Cari</button>
            <a class="rounded border border-slate-300 bg-white px-4 py-2 text-sm font-medium" href="{{ route('letters.index') }}">Atur ulang</a>
        </div>
    </form>

    <div class="mt-5 overflow-x-auto">
        <table class="w-full border-collapse text-left text-sm">
            <thead><tr class="border-b border-slate-300 text-slate-600"><th class="py-3 pr-4">Nomor</th><th class="py-3 pr-4">Jenis</th><th class="py-3 pr-4">Judul</th><th class="py-3 pr-4">Karyawan</th><th class="py-3 pr-4">Status</th><th class="py-3">Diperbarui</th></tr></thead>
            <tbody>
                @forelse ($letters as $letter)
                    <tr class="border-b border-slate-200">
                        <td class="py-3 pr-4">{{ $letter->letter_number ?? 'Belum ditetapkan' }}</td>
                        <td class="py-3 pr-4">{{ $letter->type->label() }}</td>
                        <td class="py-3 pr-4 font-medium"><a class="underline" href="{{ route('letters.show', $letter) }}">{{ $letter->title }}</a></td>
                        <td class="py-3 pr-4">{{ $letter->subject?->name ?? '-' }}</td>
                        <td class="py-3 pr-4">{{ $letter->status->label() }}</td>
                        <td class="py-3">{{ $letter->updated_at->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td class="py-8 text-slate-600" colspan="6">Belum ada surat yang sesuai.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-5">{{ $letters->links() }}</div>
@endsection