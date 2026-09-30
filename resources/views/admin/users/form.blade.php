@extends('layouts.app')

@section('title', $user->exists ? 'Ubah Pengguna' : 'Tambah Pengguna')

@section('content')
    <h1 class="text-2xl font-semibold">{{ $user->exists ? 'Ubah Pengguna' : 'Tambah Pengguna' }}</h1>
    <form class="mt-6 grid max-w-3xl gap-5 sm:grid-cols-2" method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}">
        @csrf
        @if ($user->exists) @method('PUT') @endif
        @foreach (['name' => 'Nama lengkap', 'email' => 'Email', 'username' => 'Nama pengguna', 'nik' => 'NIK', 'phone' => 'Nomor telepon'] as $field => $label)
            <div>
                <label class="mb-1 block text-sm font-medium" for="{{ $field }}">{{ $label }}</label>
                <input class="w-full rounded border border-slate-300 bg-white px-3 py-2" id="{{ $field }}" name="{{ $field }}" value="{{ old($field, $user->{$field}) }}" @required(in_array($field, ['name', 'email'], true)) @if ($field === 'email') type="email" @endif>
                @error($field) <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
            </div>
        @endforeach
        <div>
            <label class="mb-1 block text-sm font-medium" for="role">Peran</label>
            <select class="w-full rounded border border-slate-300 bg-white px-3 py-2" id="role" name="role" required>
                @foreach ($roles as $role)
                    <option value="{{ $role->value }}" @selected(old('role', $user->role?->value ?? 'karyawan') === $role->value)>{{ $role->label() }}</option>
                @endforeach
            </select>
            @error('role') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium" for="department_id">Departemen</label>
            <select class="w-full rounded border border-slate-300 bg-white px-3 py-2" id="department_id" name="department_id">
                <option value="">Belum ditetapkan</option>
                @foreach ($departments as $department)
                    <option value="{{ $department->id }}" @selected((string) old('department_id', $user->department_id) === (string) $department->id)>{{ $department->name }}</option>
                @endforeach
            </select>
            @error('department_id') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium" for="position_id">Jabatan</label>
            <select class="w-full rounded border border-slate-300 bg-white px-3 py-2" id="position_id" name="position_id">
                <option value="">Belum ditetapkan</option>
                @foreach ($departments as $department)
                    @foreach ($department->positions as $position)
                        <option value="{{ $position->id }}" @selected((string) old('position_id', $user->position_id) === (string) $position->id)>{{ $department->name }} · {{ $position->name }}</option>
                    @endforeach
                @endforeach
            </select>
            @error('position_id') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium" for="password">{{ $user->exists ? 'Kata sandi baru (opsional)' : 'Kata sandi' }}</label>
            <input class="w-full rounded border border-slate-300 bg-white px-3 py-2" id="password" name="password" type="password" autocomplete="new-password" @required(!$user->exists)>
            @error('password') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium" for="password_confirmation">Ulangi kata sandi</label>
            <input class="w-full rounded border border-slate-300 bg-white px-3 py-2" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" @required(!$user->exists)>
        </div>
        <label class="flex items-center gap-2 text-sm sm:col-span-2"><input name="is_active" type="checkbox" value="1" @checked(old('is_active', $user->is_active ?? true))> Akun aktif</label>
            <label class="flex items-center gap-2 text-sm sm:col-span-2"><input name="is_active" type="hidden" value="0"><input name="is_active" type="checkbox" value="1" @checked(old('is_active', $user->is_active ?? true))> Akun aktif</label>
        <div class="flex gap-3 sm:col-span-2">
            <button class="rounded bg-slate-900 px-4 py-2 font-medium text-white hover:bg-slate-700" type="submit">Simpan</button>
            <a class="rounded border border-slate-300 bg-white px-4 py-2 font-medium hover:bg-slate-100" href="{{ route('admin.users.index') }}">Batal</a>
        </div>
    </form>
@endsection