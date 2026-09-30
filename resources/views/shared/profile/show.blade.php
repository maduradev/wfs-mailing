@extends('layouts.app')

@section('title', 'Profil')

@section('content')
    <div class="border-b border-slate-200 pb-5">
        <p class="text-sm font-medium text-slate-500">{{ $user->role->label() }}</p>
        <h1 class="mt-1 text-2xl font-semibold">Profil</h1>
        <nav class="mt-5 flex flex-wrap gap-5 text-sm font-medium" aria-label="Bagian profil">
            <a class="hover:underline" href="#informasi-pribadi">Informasi Pribadi</a>
            <a class="hover:underline" href="#keamanan">Keamanan</a>
            <a class="hover:underline" href="#tanda-tangan">Tanda Tangan</a>
        </nav>
    </div>

    <section class="border-b border-slate-200 py-7" id="informasi-pribadi">
        <h2 class="text-lg font-semibold">Informasi Pribadi</h2>
        <form class="mt-5 grid gap-5 sm:grid-cols-2" method="POST" action="{{ route($user->role->value.'.profile.update') }}">
            @csrf
            @method('PUT')
            <div>
                <label class="mb-1 block text-sm font-medium" for="name">Nama lengkap</label>
                <input class="w-full rounded border border-slate-300 bg-white px-3 py-2" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                @error('name') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium" for="email">Email</label>
                <input class="w-full rounded border border-slate-300 bg-white px-3 py-2" id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required>
                @error('email') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium" for="username">Nama pengguna</label>
                <input class="w-full rounded border border-slate-300 bg-white px-3 py-2" id="username" name="username" value="{{ old('username', $user->username) }}">
                @error('username') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium" for="nik">NIK</label>
                <input class="w-full rounded border border-slate-300 bg-white px-3 py-2" id="nik" name="nik" value="{{ old('nik', $user->nik) }}">
                @error('nik') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium" for="phone">Nomor telepon</label>
                <input class="w-full rounded border border-slate-300 bg-white px-3 py-2" id="phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}">
                @error('phone') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <span class="block text-sm font-medium">Departemen</span>
                    <p class="mt-1 rounded border border-slate-200 bg-slate-100 px-3 py-2 text-sm">{{ $user->department?->name ?? 'Belum ditetapkan' }}</p>
                </div>
                <div>
                    <span class="block text-sm font-medium">Jabatan</span>
                    <p class="mt-1 rounded border border-slate-200 bg-slate-100 px-3 py-2 text-sm">{{ $user->position?->name ?? 'Belum ditetapkan' }}</p>
                </div>
            </div>
            <div class="sm:col-span-2">
                <button class="rounded bg-slate-900 px-4 py-2 font-medium text-white hover:bg-slate-700" type="submit">Simpan profil</button>
            </div>
        </form>
    </section>

    <section class="border-b border-slate-200 py-7" id="keamanan">
        <h2 class="text-lg font-semibold">Keamanan</h2>
        <h3 class="mt-4 font-medium">Ubah Kata Sandi</h3>
        <form class="mt-4 grid gap-4 sm:max-w-xl" method="POST" action="{{ route($user->role->value.'.profile.password') }}">
            @csrf
            @method('PUT')
            <div>
                <label class="mb-1 block text-sm font-medium" for="current_password">Kata sandi saat ini</label>
                <input class="w-full rounded border border-slate-300 bg-white px-3 py-2" id="current_password" name="current_password" type="password" autocomplete="current-password" required>
                @error('current_password') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium" for="password">Kata sandi baru</label>
                <input class="w-full rounded border border-slate-300 bg-white px-3 py-2" id="password" name="password" type="password" autocomplete="new-password" required>
                @error('password') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium" for="password_confirmation">Ulangi kata sandi baru</label>
                <input class="w-full rounded border border-slate-300 bg-white px-3 py-2" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
            </div>
            <div><button class="rounded border border-slate-300 bg-white px-4 py-2 font-medium hover:bg-slate-100" type="submit">Ubah Kata Sandi</button></div>
        </form>
    </section>

    <section class="py-7" id="tanda-tangan">
        <h2 class="text-lg font-semibold">Tanda Tangan</h2>
        <div class="mt-4 flex flex-wrap items-center gap-5">
            @if ($signature)
                <img class="max-h-24 max-w-xs border border-slate-200 bg-white p-2" src="{{ route($user->role->value.'.profile.signature') }}" alt="Pratinjau tanda tangan">
                <p class="text-sm text-slate-600">Versi {{ $signature->version }} · Diperbarui {{ $signature->uploaded_at->format('d/m/Y H:i') }}</p>
            @else
                <p class="text-sm text-slate-600">Belum ada tanda tangan aktif.</p>
            @endif
        </div>
        <form class="mt-5 space-y-3" method="POST" action="{{ route($user->role->value.'.profile.signature.store') }}" enctype="multipart/form-data">
            @csrf
            <div>
                <label class="mb-1 block text-sm font-medium" for="signature">Unggah tanda tangan PNG</label>
                <input class="block w-full max-w-xl rounded border border-slate-300 bg-white px-3 py-2 text-sm" id="signature" name="signature" type="file" accept=".png,image/png" required>
                <p class="mt-1 text-xs text-slate-600">PNG, maksimal 2 MB. Versi sebelumnya tetap tersimpan.</p>
                @error('signature') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
            </div>
            <button class="rounded border border-slate-300 bg-white px-4 py-2 font-medium hover:bg-slate-100" type="submit">{{ $signature ? 'Ganti tanda tangan' : 'Simpan tanda tangan' }}</button>
        </form>
    </section>
@endsection