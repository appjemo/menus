<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Crea (o actualiza) el usuario administrador inicial.
     * Las credenciales se leen de variables de entorno para no exponerlas en el repo:
     *   ADMIN_NAME, ADMIN_EMAIL, ADMIN_PASSWORD
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if (! $email || ! $password) {
            $this->command->warn('AdminUserSeeder omitido: define ADMIN_EMAIL y ADMIN_PASSWORD en el .env');
            return;
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => env('ADMIN_NAME', 'Administrador'),
                'password' => $password, // el cast 'hashed' del modelo User lo encripta
            ],
        );

        $this->command->info("Usuario admin listo: {$user->email}");
    }
}
