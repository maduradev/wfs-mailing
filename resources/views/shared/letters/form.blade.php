@extends('layouts.app')

@section('title', $letter->exists ? 'Ubah Draf Surat' : 'Buat '.$type->label())

@section('content')
    <div class="border-b border-slate-200 pb-5">
        <p class="text-sm text-slate-600">{{ $type->label() }}</p>
        <h1 class="mt-1 text-2xl font-semibold">{{ $letter->exists ? 'Ubah Draf Surat' : 'Buat Draf Surat' }}</h1>
    </div>
    <form class="mt-6 grid max-w-3xl gap-5 sm:grid-cols-2" method="POST" action="{{ $letter->exists ? route('letters.update', $letter) : route('letters.store') }}">
        @csrf
        @if ($letter->exists) @method('PUT') @else <input type="hidden" name="type" value="{{ $type->value }}"> @endif
        <div class="sm:col-span-2">
            <label class="mb-1 block text-sm font-medium" for="title">Judul surat</label>
            <input class="w-full rounded border border-slate-300 bg-white px-3 py-2" id="title" name="title" value="{{ old('title', $letter->title) }}" maxlength="200" required>
            @error('title') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
        </div>
        @if ($type === App\Enums\LetterType::WARNING)
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-medium" for="subject_user_id">Karyawan</label>
                <select class="w-full rounded border border-slate-300 bg-white px-3 py-2" id="subject_user_id" name="subject_user_id" required>
                    <option value="">Pilih karyawan</option>
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}" @selected((string) old('subject_user_id', $letter->subject_user_id) === (string) $employee->id)>{{ $employee->name }} · {{ $employee->department?->name ?? 'Tanpa departemen' }}</option>
                    @endforeach
                </select>
                @error('subject_user_id') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
            </div>
        @endif
        <div class="sm:col-span-2">
            <label class="mb-1 block text-sm font-medium" for="reason">Alasan</label>
            <textarea class="w-full rounded border border-slate-300 bg-white px-3 py-2" id="reason" name="reason" rows="3" maxlength="5000">{{ old('reason', $letter->reason) }}</textarea>
            @error('reason') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
        </div>

        @if ($type === App\Enums\LetterType::LEAVE)
            @php($details = $letter->leaveDetails)
            <div><label class="mb-1 block text-sm font-medium" for="leave_type">Jenis cuti sesuai kebijakan perusahaan</label><input class="w-full rounded border border-slate-300 bg-white px-3 py-2" id="leave_type" name="leave_type" value="{{ old('leave_type', $details?->leave_type) }}" required>@error('leave_type') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror</div>
            <div><label class="mb-1 block text-sm font-medium" for="total_days">Jumlah hari</label><input class="w-full rounded border border-slate-300 bg-white px-3 py-2" id="total_days" name="total_days" type="number" min="0.01" step="0.01" value="{{ old('total_days', $details?->total_days) }}" required>@error('total_days') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror</div>
            <div><label class="mb-1 block text-sm font-medium" for="starts_on">Tanggal mulai</label><input class="w-full rounded border border-slate-300 bg-white px-3 py-2" id="starts_on" name="starts_on" type="date" value="{{ old('starts_on', $details?->starts_on?->format('Y-m-d')) }}" required>@error('starts_on') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror</div>
            <div><label class="mb-1 block text-sm font-medium" for="ends_on">Tanggal selesai</label><input class="w-full rounded border border-slate-300 bg-white px-3 py-2" id="ends_on" name="ends_on" type="date" value="{{ old('ends_on', $details?->ends_on?->format('Y-m-d')) }}" required>@error('ends_on') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror</div>
            <div class="sm:col-span-2"><label class="mb-1 block text-sm font-medium" for="details">Catatan</label><textarea class="w-full rounded border border-slate-300 bg-white px-3 py-2" id="details" name="details" rows="3">{{ old('details', $details?->details) }}</textarea></div>
            <div class="sm:col-span-2"><label class="mb-1 block text-sm font-medium" for="return_address">Alamat selama cuti</label><textarea class="w-full rounded border border-slate-300 bg-white px-3 py-2" id="return_address" name="return_address" rows="2">{{ old('return_address', $details?->return_address) }}</textarea></div>
        @elseif ($type === App\Enums\LetterType::MUTATION)
            @php($details = $letter->mutationDetails)
            @if (auth()->user()->role->value === 'hrd-manager')
                <div><label class="mb-1 block text-sm font-medium" for="subject_user_id">Karyawan</label><select class="w-full rounded border border-slate-300 bg-white px-3 py-2" id="subject_user_id" name="subject_user_id" required><option value="">Pilih karyawan</option>@foreach ($employees as $employee)<option value="{{ $employee->id }}" @selected((string) old('subject_user_id', $letter->subject_user_id) === (string) $employee->id)>{{ $employee->name }}</option>@endforeach</select></div>
            @endif
            <div><label class="mb-1 block text-sm font-medium" for="from_department_id">Departemen asal</label><select class="w-full rounded border border-slate-300 bg-white px-3 py-2" id="from_department_id" name="from_department_id"><option value="">Belum ditetapkan</option>@foreach ($departments as $department)<option value="{{ $department->id }}" @selected((string) old('from_department_id', $details?->from_department_id ?? auth()->user()->department_id) === (string) $department->id)>{{ $department->name }}</option>@endforeach</select></div>
            <div><label class="mb-1 block text-sm font-medium" for="to_department_id">Departemen tujuan</label><select class="w-full rounded border border-slate-300 bg-white px-3 py-2" id="to_department_id" name="to_department_id" required><option value="">Pilih departemen</option>@foreach ($departments as $department)<option value="{{ $department->id }}" @selected((string) old('to_department_id', $details?->to_department_id) === (string) $department->id)>{{ $department->name }}</option>@endforeach</select>@error('to_department_id') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror</div>
            <div><label class="mb-1 block text-sm font-medium" for="from_position_id">Jabatan asal</label><select class="w-full rounded border border-slate-300 bg-white px-3 py-2" id="from_position_id" name="from_position_id"><option value="">Belum ditetapkan</option>@foreach ($positions as $position)<option value="{{ $position->id }}" @selected((string) old('from_position_id', $details?->from_position_id ?? auth()->user()->position_id) === (string) $position->id)>{{ $position->department->name }} · {{ $position->name }}</option>@endforeach</select></div>
            <div><label class="mb-1 block text-sm font-medium" for="to_position_id">Jabatan tujuan</label><select class="w-full rounded border border-slate-300 bg-white px-3 py-2" id="to_position_id" name="to_position_id" required><option value="">Pilih jabatan</option>@foreach ($positions as $position)<option value="{{ $position->id }}" @selected((string) old('to_position_id', $details?->to_position_id) === (string) $position->id)>{{ $position->department->name }} · {{ $position->name }}</option>@endforeach</select>@error('to_position_id') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror</div>
            <div><label class="mb-1 block text-sm font-medium" for="effective_on">Tanggal berlaku</label><input class="w-full rounded border border-slate-300 bg-white px-3 py-2" id="effective_on" name="effective_on" type="date" value="{{ old('effective_on', $details?->effective_on?->format('Y-m-d')) }}" required>@error('effective_on') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror</div>
            <div class="sm:col-span-2"><label class="mb-1 block text-sm font-medium" for="details">Catatan</label><textarea class="w-full rounded border border-slate-300 bg-white px-3 py-2" id="details" name="details" rows="3">{{ old('details', $details?->details) }}</textarea></div>
        @else
            @php($details = $letter->warningDetails)
            <div><label class="mb-1 block text-sm font-medium" for="warning_level">Tingkat peringatan sesuai kebijakan</label><input class="w-full rounded border border-slate-300 bg-white px-3 py-2" id="warning_level" name="warning_level" value="{{ old('warning_level', $details?->warning_level) }}">@error('warning_level') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror</div>
            <div><label class="mb-1 block text-sm font-medium" for="offense_on">Tanggal kejadian</label><input class="w-full rounded border border-slate-300 bg-white px-3 py-2" id="offense_on" name="offense_on" type="date" value="{{ old('offense_on', $details?->offense_on?->format('Y-m-d')) }}"></div>
            <div class="sm:col-span-2"><label class="mb-1 block text-sm font-medium" for="description">Uraian</label><textarea class="w-full rounded border border-slate-300 bg-white px-3 py-2" id="description" name="description" rows="5" required>{{ old('description', $details?->description) }}</textarea>@error('description') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror</div>
            <div class="sm:col-span-2"><label class="mb-1 block text-sm font-medium" for="legal_basis">Dasar</label><textarea class="w-full rounded border border-slate-300 bg-white px-3 py-2" id="legal_basis" name="legal_basis" rows="3">{{ old('legal_basis', $details?->legal_basis) }}</textarea></div>
            <div><label class="mb-1 block text-sm font-medium" for="valid_from">Berlaku mulai</label><input class="w-full rounded border border-slate-300 bg-white px-3 py-2" id="valid_from" name="valid_from" type="date" value="{{ old('valid_from', $details?->valid_from?->format('Y-m-d')) }}"></div>
            <div><label class="mb-1 block text-sm font-medium" for="valid_until">Berlaku sampai</label><input class="w-full rounded border border-slate-300 bg-white px-3 py-2" id="valid_until" name="valid_until" type="date" value="{{ old('valid_until', $details?->valid_until?->format('Y-m-d')) }}">@error('valid_until') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror</div>
        @endif

        <div class="flex gap-3 sm:col-span-2">
            <button class="rounded bg-slate-900 px-4 py-2 font-medium text-white" type="submit">Simpan draf</button>
            <a class="rounded border border-slate-300 bg-white px-4 py-2 font-medium" href="{{ route('letters.index') }}">Batal</a>
        </div>
    </form>
@endsection