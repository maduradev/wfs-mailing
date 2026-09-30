@extends('layouts.app')

@section('title', 'Dasbor')

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-200 pb-6">
        <div>
            <p class="text-sm font-medium text-slate-500">{{ $roleLabel }}</p>
            <h1 class="mt-1 text-2xl font-semibold">Selamat datang, {{ $user->name }}</h1>
        </div>
        <a class="rounded border border-slate-300 bg-white px-4 py-2 text-sm font-medium hover:bg-slate-100" href="{{ route($user->role->value.'.profile') }}">Buka profil</a>
    </div>

    <section class="mt-6" aria-labelledby="account-status">
        <h2 class="text-lg font-semibold" id="account-status">Ringkasan akun</h2>
        <dl class="mt-4 grid gap-4 sm:grid-cols-2">
            <div class="border border-slate-200 bg-white p-4">
                <dt class="text-sm text-slate-600">Departemen</dt>
                <dd class="mt-1 font-medium">{{ $user->department?->name ?? 'Belum ditetapkan' }}</dd>
            </div>
            <div class="border border-slate-200 bg-white p-4">
                <dt class="text-sm text-slate-600">Tanda tangan</dt>
                <dd class="mt-1 font-medium">{{ $hasSignature ? 'Sudah tersedia' : 'Belum diunggah' }}</dd>
            </div>
        </dl>
    </section>

    @if ($user->role->value === 'admin')
        <section class="mt-8 border-t border-slate-200 pt-6" aria-labelledby="administration-links">
            <h2 class="text-lg font-semibold" id="administration-links">Administrasi</h2>
            <div class="mt-4 flex flex-wrap gap-3">
                <a class="rounded border border-slate-300 bg-white px-4 py-2 text-sm font-medium hover:bg-slate-100" href="{{ route('admin.users.index') }}">Kelola pengguna</a>
                <a class="rounded border border-slate-300 bg-white px-4 py-2 text-sm font-medium hover:bg-slate-100" href="{{ route('admin.departments.index') }}">Kelola departemen</a>
                <a class="rounded border border-slate-300 bg-white px-4 py-2 text-sm font-medium hover:bg-slate-100" href="{{ route('admin.positions.index') }}">Kelola jabatan</a>
            </div>
        </section>
    @endif

    @if ($user->role->value !== 'admin')
        <section class="mt-8 border-t border-slate-200 pt-6" aria-labelledby="letter-access">
            <h2 class="text-lg font-semibold" id="letter-access">Persuratan</h2>
            <a class="mt-4 inline-block rounded border border-slate-300 bg-white px-4 py-2 text-sm font-medium hover:bg-slate-100" href="{{ route('letters.index') }}">Buka daftar surat</a>
        </section>
    @endif
@endsection