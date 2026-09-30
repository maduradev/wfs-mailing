<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Persuratan Internal') | {{ config('app.name', 'Persuratan') }}</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3 sm:px-6">
            <a class="font-semibold" href="{{ route('dashboard') }}">Persuratan Internal</a>
            @auth
                <nav class="flex items-center gap-4 text-sm">
                    <a class="hover:underline" href="{{ route(auth()->user()->role->value.'.dashboard') }}">Dasbor</a>
                    @if (auth()->user()->role->value === 'admin')
                        <a class="hover:underline" href="{{ route('admin.users.index') }}">Pengguna</a>
                        <a class="hover:underline" href="{{ route('admin.departments.index') }}">Departemen</a>
                        <a class="hover:underline" href="{{ route('admin.positions.index') }}">Jabatan</a>
                    @else
                        <a class="hover:underline" href="{{ route('letters.index') }}">Surat</a>
                    @endif
                    <a class="hover:underline" href="{{ route(auth()->user()->role->value.'.profile') }}">Profil</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="font-medium text-rose-700 hover:underline" type="submit">Keluar</button>
                    </form>
                </nav>
            @endauth
        </div>
    </header>
    <main class="mx-auto w-full max-w-6xl px-4 py-8 sm:px-6">
        @if (session('status'))
            <p class="mb-5 border-l-4 border-emerald-600 bg-emerald-50 px-4 py-3 text-sm" role="status">{{ session('status') }}</p>
        @endif
        @yield('content')
    </main>
</body>
</html>