@extends('layouts.app')

@section('title', 'Jabatan')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-5">
        <h1 class="text-2xl font-semibold">Jabatan</h1>
        <a class="rounded bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700" href="{{ route('admin.positions.create') }}">Tambah jabatan</a>
    </div>
    <div class="mt-5 overflow-x-auto">
        <table class="w-full border-collapse text-left text-sm">
            <thead><tr class="border-b border-slate-300 text-slate-600"><th class="py-3 pr-4">Kode</th><th class="py-3 pr-4">Nama</th><th class="py-3 pr-4">Departemen</th><th class="py-3 pr-4">Status</th><th class="py-3">Tindakan</th></tr></thead>
            <tbody>
                @forelse ($positions as $position)
                    <tr class="border-b border-slate-200"><td class="py-3 pr-4">{{ $position->code }}</td><td class="py-3 pr-4 font-medium">{{ $position->name }}</td><td class="py-3 pr-4">{{ $position->department->name }}</td><td class="py-3 pr-4">{{ $position->is_active ? 'Aktif' : 'Nonaktif' }}</td><td class="py-3"><a class="underline" href="{{ route('admin.positions.edit', $position) }}">Ubah</a></td></tr>
                @empty
                    <tr><td class="py-6 text-slate-600" colspan="5">Belum ada jabatan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-5">{{ $positions->links() }}</div>
@endsection