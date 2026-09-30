@extends('layouts.app')

@section('title', $position->exists ? 'Ubah Jabatan' : 'Tambah Jabatan')

@section('content')
    <h1 class="text-2xl font-semibold">{{ $position->exists ? 'Ubah Jabatan' : 'Tambah Jabatan' }}</h1>
    <form class="mt-6 grid max-w-xl gap-5" method="POST" action="{{ $position->exists ? route('admin.positions.update', $position) : route('admin.positions.store') }}">
        @csrf
        @if ($position->exists) @method('PUT') @endif
        <div><label class="mb-1 block text-sm font-medium" for="department_id">Departemen</label><select class="w-full rounded border border-slate-300 bg-white px-3 py-2" id="department_id" name="department_id" required><option value="">Pilih departemen</option>@foreach ($departments as $department)<option value="{{ $department->id }}" @selected((string) old('department_id', $position->department_id) === (string) $department->id)>{{ $department->name }}</option>@endforeach</select>@error('department_id') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror</div>
        <div><label class="mb-1 block text-sm font-medium" for="code">Kode jabatan</label><input class="w-full rounded border border-slate-300 bg-white px-3 py-2" id="code" name="code" value="{{ old('code', $position->code) }}" required>@error('code') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror</div>
        <div><label class="mb-1 block text-sm font-medium" for="name">Nama jabatan</label><input class="w-full rounded border border-slate-300 bg-white px-3 py-2" id="name" name="name" value="{{ old('name', $position->name) }}" required>@error('name') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror</div>
        <label class="flex items-center gap-2 text-sm"><input name="is_active" type="checkbox" value="1" @checked(old('is_active', $position->is_active ?? true))> Jabatan aktif</label>
            <label class="flex items-center gap-2 text-sm"><input name="is_active" type="hidden" value="0"><input name="is_active" type="checkbox" value="1" @checked(old('is_active', $position->is_active ?? true))> Jabatan aktif</label>
        <div class="flex gap-3"><button class="rounded bg-slate-900 px-4 py-2 font-medium text-white" type="submit">Simpan</button><a class="rounded border border-slate-300 bg-white px-4 py-2 font-medium" href="{{ route('admin.positions.index') }}">Batal</a></div>
    </form>
@endsection