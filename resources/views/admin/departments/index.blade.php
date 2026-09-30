@extends('layouts.app')

@section('title', 'Departemen')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-5">
        <h1 class="text-2xl font-semibold">Departemen</h1>
        <a class="rounded bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700" href="{{ route('admin.departments.create') }}">Tambah departemen</a>
    </div>
    <div class="mt-5 overflow-x-auto">
        <table class="w-full border-collapse text-left text-sm">
            <thead><tr class="border-b border-slate-300 text-slate-600"><th class="py-3 pr-4">Kode</th><th class="py-3 pr-4">Nama</th><th class="py-3 pr-4">Jabatan</th><th class="py-3 pr-4">Pengguna</th><th class="py-3 pr-4">Status</th><th class="py-3">Tindakan</th></tr></thead>
            <tbody>
                @forelse ($departments as $department)
                    <tr class="border-b border-slate-200"><td class="py-3 pr-4">{{ $department->code }}</td><td class="py-3 pr-4 font-medium">{{ $department->name }}</td><td class="py-3 pr-4">{{ $department->positions_count }}</td><td class="py-3 pr-4">{{ $department->users_count }}</td><td class="py-3 pr-4">{{ $department->is_active ? 'Aktif' : 'Nonaktif' }}</td><td class="py-3"><a class="underline" href="{{ route('admin.departments.edit', $department) }}">Ubah</a></td></tr>
                @empty
                    <tr><td class="py-6 text-slate-600" colspan="6">Belum ada departemen.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-5">{{ $departments->links() }}</div>
@endsection