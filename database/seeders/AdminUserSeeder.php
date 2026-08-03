<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $name = (string) config('cancionero.admin.name');
        $email = (string) config('cancionero.admin.email');
        $password = (string) config('cancionero.admin.password');
        if ($password === '') {
            $message = 'ADMIN_PASSWORD no está configurada; no se creó el usuario administrador.';
            if (app()->isProduction()) {
                throw new RuntimeException($message);
            } $this->command?->warn($message);

            return;
        }
        if (app()->isProduction() && ! $this->isStrong($password)) {
            throw new RuntimeException('ADMIN_PASSWORD debe tener al menos 12 caracteres, mayúscula, minúscula, número y símbolo en producción.');
        }
        User::firstOrCreate(['email' => $email], ['name' => $name, 'password' => Hash::make($password), 'is_admin' => true, 'email_verified_at' => now()]);
        $this->command?->info("Administrador disponible: {$email}");
    }

    private function isStrong(string $password): bool
    {
        return strlen($password) >= 12 && preg_match('/[a-z]/', $password) && preg_match('/[A-Z]/', $password) && preg_match('/\d/', $password) && preg_match('/[^A-Za-z0-9]/', $password);
    }
}
