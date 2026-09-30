@extends('layouts.app')

@section('title', $department->exists ? 'Ubah Departemen' : 'Tambah Departemen')

@section('content')
    <h1 class="text-2xl font-semibold">{{ $department->exists ? 'Ubah Departemen' : 'Tambah Departemen' }}</h1>
    <form class="mt-6 grid max-w-xl gap-5" method="POST" action="{{ $department->exists ? route('admin.departments.update', $department) : route('admin.departments.store') }}">
        @csrf
        @if ($department->exists) @method('PUT') @endif
        <div><label class="mb-1 block text-sm font-medium" for="code">Kode departemen</label><input class="w-full rounded border border-slate-300 bg-white px-3 py-2" id="code" name="code" value="{{ old('code', $department->code) }}" required>@error('code') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror</div>
        <div><label class="mb-1 block text-sm font-medium" for="name">Nama departemen</label><input class="w-full rounded border border-slate-300 bg-white px-3 py-2" id="name" name="name" value="{{ old('name', $department->name) }}" required>@error('name') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror</div>
        <label class="flex items-center gap-2 text-sm"><input name="is_active" type="checkbox" value="1" @checked(old('is_active', $department->is_active ?? true))> Departemen aktif</label>
            <label class="flex items-center gap-2 text-sm"><input name="is_active" type="hidden" value="0"><input name="is_active" type="checkbox" value="1" @checked(old('is_active', $department->is_active ?? true))> Departemen aktif</label>
        <div class="flex gap-3"><button class="rounded bg-slate-900 px-4 py-2 font-medium text-white" type="submit">Simpan</button><a class="rounded border border-slate-300 bg-white px-4 py-2 font-medium" href="{{ route('admin.departments.index') }}">Batal</a></div>
    </form>
@endsection