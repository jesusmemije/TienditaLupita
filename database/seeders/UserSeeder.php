<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $name = trim((string) config('app.admin.name'));
        $email = trim((string) config('app.admin.email'));
        $password = (string) config('app.admin.password');

        if ($name === '' || $email === '' || $password === '') {
            throw new RuntimeException('Define ADMIN_NAME, ADMIN_EMAIL y ADMIN_PASSWORD antes de ejecutar los seeders.');
        }

        User::updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => $password],
        );
    }
}
