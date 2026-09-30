@extends('layouts.app')

@section('title', 'Masuk')

@section('content')
    <div class="mx-auto max-w-md">
        <h1 class="text-2xl font-semibold">Masuk ke akun</h1>
        <p class="mt-2 text-sm text-slate-600">Gunakan email atau nama pengguna perusahaan.</p>

        <form class="mt-6 space-y-5" method="POST" action="{{ route('login.store') }}">
            @csrf
            <div>
                <label class="mb-1 block text-sm font-medium" for="login">Email atau nama pengguna</label>
                <input class="w-full rounded border border-slate-300 bg-white px-3 py-2" id="login" name="login" value="{{ old('login') }}" autocomplete="username" required autofocus>
                @error('login') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium" for="password">Kata sandi</label>
                <input class="w-full rounded border border-slate-300 bg-white px-3 py-2" id="password" name="password" type="password" autocomplete="current-password" required>
                @error('password') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
            </div>
            <button class="w-full rounded bg-slate-900 px-4 py-2.5 font-medium text-white hover:bg-slate-700" type="submit">Masuk</button>
        </form>
    </div>
@endsection