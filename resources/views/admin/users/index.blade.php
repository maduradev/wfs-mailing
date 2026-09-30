@extends('layouts.app')

@section('title', 'Pengguna')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-5">
        <h1 class="text-2xl font-semibold">Pengguna</h1>
        <a class="rounded bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700" href="{{ route('admin.users.create') }}">Tambah pengguna</a>
    </div>
    <div class="mt-5 overflow-x-auto">
        <table class="w-full border-collapse text-left text-sm">
            <thead><tr class="border-b border-slate-300 text-slate-600"><th class="py-3 pr-4">Nama</th><th class="py-3 pr-4">Email</th><th class="py-3 pr-4">Peran</th><th class="py-3 pr-4">Departemen</th><th class="py-3 pr-4">Status</th><th class="py-3">Tindakan</th></tr></thead>
            <tbody>
                @forelse ($users as $listedUser)
                    <tr class="border-b border-slate-200">
                        <td class="py-3 pr-4 font-medium">{{ $listedUser->name }}</td>
                        <td class="py-3 pr-4">{{ $listedUser->email }}</td>
                        <td class="py-3 pr-4">{{ $listedUser->role->label() }}</td>
                        <td class="py-3 pr-4">{{ $listedUser->department?->name ?? 'Belum ditetapkan' }}</td>
                        <td class="py-3 pr-4">{{ $listedUser->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                        <td class="py-3">
                            @if ($listedUser->is(auth()->user()))
                                <span class="text-slate-500">Akun Anda</span>
                            @else
                                <a class="font-medium underline" href="{{ route('admin.users.edit', $listedUser) }}">Ubah</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td class="py-6 text-slate-600" colspan="6">Belum ada pengguna.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-5">{{ $users->links() }}</div>
@endsection