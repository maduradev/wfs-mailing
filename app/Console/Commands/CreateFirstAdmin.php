<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class CreateFirstAdmin extends Command
{
    protected $signature = 'app:create-first-admin';

    protected $description = 'Membuat akun administrator pertama secara interaktif';

    public function handle(): int
    {
        if (User::query()->where('role', UserRole::ADMIN->value)->exists()) {
            $this->error('Akun administrator sudah tersedia.');

            return self::FAILURE;
        }

        $values = [
            'name' => trim((string) $this->ask('Nama administrator')),
            'email' => strtolower(trim((string) $this->ask('Email administrator'))),
            'password' => (string) $this->secret('Kata sandi (minimal 12 karakter)'),
        ];

        $validator = Validator::make($values, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:12'],
        ]);

        if ($validator->fails()) {
            $this->error($validator->errors()->first());

            return self::FAILURE;
        }

        $administrator = new User;
        $administrator->forceFill([
            'name' => $values['name'],
            'email' => $values['email'],
            'password' => $values['password'],
            'role' => UserRole::ADMIN,
            'is_active' => true,
        ])->save();

        $this->info('Akun administrator pertama berhasil dibuat.');

        return self::SUCCESS;
    }
}
